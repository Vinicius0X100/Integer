<?php

namespace App\Services;

use App\Enums\FiscalStatus;
use App\Models\ExternalBillingInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NodalBillingSyncService
{
    protected NodalBillingClient $client;

    public function __construct(NodalBillingClient $client)
    {
        $this->client = $client;
    }

    /**
     * Sincroniza as faturas do Nodal de forma idempotente e protegida por Mutex.
     *
     * @param  array  $options  ['period', 'invoice', 'full', 'updated_since']
     * @return array
     */
    public function sync(array $options = []): array
    {
        $lock = Cache::lock('nodal_billing_sync', 300);

        if (! $lock->get()) {
            return [
                'status'  => 'already_running',
                'message' => 'Sincronização já está em andamento.',
                'synced'  => 0,
                'created' => 0,
                'updated' => 0,
            ];
        }

        try {
            $processedCount = 0;
            $createdCount = 0;
            $updatedCount = 0;

            $filters = [];

            if (! empty($options['period'])) {
                $filters['period'] = $options['period'];
            }

            if (! empty($options['invoice'])) {
                // Sync de uma invoice específica
                $singleData = $this->client->getInvoice($options['invoice']);
                $item = $singleData['data'] ?? $singleData;
                $res = $this->processInvoiceItem($item);

                if ($res === 'created') $createdCount++;
                if ($res === 'updated') $updatedCount++;
                if ($res) $processedCount++;

                return [
                    'status'  => 'success',
                    'synced'  => $processedCount,
                    'created' => $createdCount,
                    'updated' => $updatedCount,
                ];
            }

            // Sync Incremental via updated_since se não for --full e sem period explicito
            if (empty($options['full']) && empty($options['period']) && empty($options['updated_since'])) {
                $maxLocalUpdated = ExternalBillingInvoice::max('source_updated_at');

                if ($maxLocalUpdated) {
                    // Janela de segurança de 10 minutos
                    $filters['updated_since'] = Carbon::parse($maxLocalUpdated)->subMinutes(10)->toIso8601String();
                }
            } elseif (! empty($options['updated_since'])) {
                $filters['updated_since'] = $options['updated_since'];
            }

            $page = 1;
            $perPage = 50;

            do {
                $filters['page'] = $page;
                $filters['per_page'] = $perPage;

                $response = $this->client->fetchInvoices($filters);
                $items = $response['data'] ?? [];
                $meta = $response['meta'] ?? [];

                foreach ($items as $item) {
                    $result = $this->processInvoiceItem($item);

                    if ($result === 'created') {
                        $createdCount++;
                        $processedCount++;
                    } elseif ($result === 'updated') {
                        $updatedCount++;
                        $processedCount++;
                    } elseif ($result === 'skipped') {
                        // Ignorado por ser payload antigo ou idêntico
                        $processedCount++;
                    }
                }

                $lastPage = $meta['last_page'] ?? $page;
                $page++;
            } while ($page <= $lastPage);

            Log::info('Nodal Billing Sync concluído com sucesso.', [
                'synced'  => $processedCount,
                'created' => $createdCount,
                'updated' => $updatedCount,
            ]);

            return [
                'status'  => 'success',
                'synced'  => $processedCount,
                'created' => $createdCount,
                'updated' => $updatedCount,
            ];
        } finally {
            $lock->release();
        }
    }

    /**
     * Processa um item individual da API Nodal e atualiza/cria na tabela local.
     *
     * @param  array  $item
     * @return string  'created'|'updated'|'skipped'|'error'
     */
    protected function processInvoiceItem(array $item): string
    {
        $nodalInvoiceUuid = $item['uuid'] ?? $item['nodal_invoice_uuid'] ?? null;

        if (! $nodalInvoiceUuid) {
            return 'error';
        }

        $incomingSourceUpdatedAt = isset($item['updated_at'])
            ? Carbon::parse($item['updated_at'])
            : (isset($item['source_updated_at']) ? Carbon::parse($item['source_updated_at']) : now());

        /** @var ExternalBillingInvoice|null $invoice */
        $invoice = ExternalBillingInvoice::where('nodal_invoice_uuid', $nodalInvoiceUuid)->first();

        // Data Guard: Se o registro local já existir e o payload remoto for MAIS ANTIGO, ignorar atualização
        if ($invoice && $invoice->source_updated_at && $incomingSourceUpdatedAt->lt($invoice->source_updated_at)) {
            Log::info("Nodal Sync: Payload remoto descartado por ser mais antigo que o registro local.", [
                'nodal_invoice_uuid' => $nodalInvoiceUuid,
                'incoming_updated'   => $incomingSourceUpdatedAt->toIso8601String(),
                'local_updated'      => $invoice->source_updated_at->toIso8601String(),
            ]);

            return 'skipped';
        }

        $isNew = ! $invoice;

        if ($isNew) {
            $invoice = new ExternalBillingInvoice();
            $invoice->nodal_invoice_uuid = $nodalInvoiceUuid;
            $invoice->fiscal_status = FiscalStatus::PENDING;
        }

        // Mapeamento Whitelist estrito de campos do Nodal
        $paymentData = $item['payment'] ?? null;

        $whitelistedPayload = [
            'nodal_organization_uuid'   => $item['organization_uuid'] ?? $item['nodal_organization_uuid'] ?? null,
            'company_name'              => $item['company_name'] ?? $item['organization']['name'] ?? null,
            'trade_name'                => $item['trade_name'] ?? $item['organization']['trade_name'] ?? null,
            'tax_id'                    => $item['tax_id'] ?? $item['organization']['tax_id'] ?? $item['organization']['cnpj'] ?? null,
            'billing_email'             => $item['billing_email'] ?? $item['organization']['billing_email'] ?? null,
            'fiscal_data_complete'      => (bool) ($item['fiscal_data_complete'] ?? false),
            'period_start'              => isset($item['period_start']) ? Carbon::parse($item['period_start']) : null,
            'period_end'                => isset($item['period_end']) ? Carbon::parse($item['period_end']) : null,
            'competence'                => $item['competence'] ?? '',
            'service_description'       => $item['service_description'] ?? null,
            'plan_name'                 => $item['plan_name'] ?? null,
            'subscription_amount_cents' => (int) ($item['subscription_amount_cents'] ?? 0),
            'overage_amount_cents'      => (int) ($item['overage_amount_cents'] ?? 0),
            'total_amount_cents'        => (int) ($item['total_amount_cents'] ?? 0),
            'currency'                  => $item['currency'] ?? 'BRL',
            'invoice_status'            => $item['status'] ?? $item['invoice_status'] ?? 'open',
            'payment_provider'          => $paymentData['provider'] ?? $item['payment_provider'] ?? null,
            'payment_method'            => $paymentData['method'] ?? $item['payment_method'] ?? null,
            'payment_status'            => $paymentData['status'] ?? $item['payment_status'] ?? null,
            'paid_at'                   => isset($paymentData['paid_at']) ? Carbon::parse($paymentData['paid_at']) : (isset($item['paid_at']) ? Carbon::parse($item['paid_at']) : null),
            'source_updated_at'         => $incomingSourceUpdatedAt,
            'last_synced_at'            => now(),
        ];

        // Aplica o payload da whitelist no model. Note que NUNCA preenchemos fiscal_status, nfse_number, nfse_issued_at, fiscal_notes aqui.
        $invoice->fill($whitelistedPayload);
        $invoice->save();

        return $isNew ? 'created' : 'updated';
    }
}
