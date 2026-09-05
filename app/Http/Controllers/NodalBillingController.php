<?php

namespace App\Http\Controllers;

use App\Enums\FiscalStatus;
use App\Models\AutomationAuditLog;
use App\Models\ExternalBillingInvoice;
use App\Services\NodalBillingSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NodalBillingController extends Controller
{
    public function index(Request $request)
    {
        $query = ExternalBillingInvoice::query();

        // Filtro por Competência
        if ($request->filled('period')) {
            $query->where('competence', $request->period);
        }

        // Filtro por Empresa / Busca (Razão Social, Nome Fantasia, CNPJ)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('trade_name', 'like', "%{$search}%")
                    ->orWhere('tax_id', 'like', "%{$search}%");
            });
        }

        // Filtro por Status de Pagamento
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filtro por Status Fiscal
        if ($request->filled('fiscal_status')) {
            $query->where('fiscal_status', $request->fiscal_status);
        }

        // Filtro por Dados Incompletos
        if ($request->filled('incomplete_fiscal_data') && $request->incomplete_fiscal_data == '1') {
            $query->where('fiscal_data_complete', false);
        }

        $invoices = $query->orderByDesc('source_updated_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            $tableHtml = view('nodal_billing.partials.table', compact('invoices'))->render();
            return response()->json(['table' => $tableHtml]);
        }

        // Competências disponíveis para o select de filtro
        $availableCompetences = ExternalBillingInvoice::query()
            ->select('competence')
            ->distinct()
            ->orderByDesc('competence')
            ->pluck('competence');

        $fiscalStatuses = FiscalStatus::cases();

        return view('nodal_billing.index', compact('invoices', 'availableCompetences', 'fiscalStatuses'));
    }

    public function show($id)
    {
        $invoice = ExternalBillingInvoice::findOrFail($id);
        $fiscalStatuses = FiscalStatus::cases();

        return view('nodal_billing.show', compact('invoice', 'fiscalStatuses'));
    }

    public function updateFiscal(Request $request, $id)
    {
        $invoice = ExternalBillingInvoice::findOrFail($id);

        $allowedStatusValues = array_map(fn ($case) => $case->value, FiscalStatus::cases());

        $request->validate([
            'fiscal_status'  => 'required|string|in:' . implode(',', $allowedStatusValues),
            'nfse_number'    => 'nullable|string|max:100',
            'nfse_issued_at' => 'nullable|date',
            'fiscal_notes'   => 'nullable|string',
        ]);

        $previousState = [
            'fiscal_status'  => $invoice->fiscal_status?->value ?? (string) $invoice->fiscal_status,
            'nfse_number'    => $invoice->nfse_number,
            'nfse_issued_at' => $invoice->nfse_issued_at ? $invoice->nfse_issued_at->toIso8601String() : null,
            'fiscal_notes'   => $invoice->fiscal_notes,
        ];

        $invoice->fiscal_status  = FiscalStatus::from($request->fiscal_status);
        $invoice->nfse_number    = $request->nfse_number;
        $invoice->nfse_issued_at = $request->filled('nfse_issued_at') ? Carbon::parse($request->nfse_issued_at) : null;
        $invoice->fiscal_notes   = $request->fiscal_notes;
        $invoice->save();

        $newState = [
            'fiscal_status'  => $invoice->fiscal_status->value,
            'nfse_number'    => $invoice->nfse_number,
            'nfse_issued_at' => $invoice->nfse_issued_at ? $invoice->nfse_issued_at->toIso8601String() : null,
            'fiscal_notes'   => $invoice->fiscal_notes,
        ];

        // Auditoria no AutomationAuditLog
        $automationKey = 'fiscal_status_changed';
        if ($previousState['nfse_number'] !== $newState['nfse_number']) {
            $automationKey = $previousState['nfse_number'] ? 'nfse_updated' : 'nfse_registered';
        }

        AutomationAuditLog::create([
            'automation_key'  => $automationKey,
            'automation_name' => 'Faturamento Nodal - Controle Fiscal',
            'status'          => 'success',
            'started_at'      => now(),
            'finished_at'     => now(),
            'summary'         => [
                'user_id'            => Auth::id(),
                'user_email'         => Auth::user()?->email,
                'invoice_id'         => $invoice->id,
                'nodal_invoice_uuid' => $invoice->nodal_invoice_uuid,
            ],
            'details' => [
                'previous_state' => $previousState,
                'new_state'      => $newState,
            ],
        ]);

        return redirect()->route('nodal-billing.show', $invoice->id)
            ->with('success', 'Dados fiscais atualizados com sucesso!');
    }

    public function sync(Request $request, NodalBillingSyncService $syncService)
    {
        $result = $syncService->sync();

        AutomationAuditLog::create([
            'automation_key'  => 'nodal_billing_synced_manual',
            'automation_name' => 'Faturamento Nodal - Sincronização Manual',
            'status'          => ($result['status'] ?? '') === 'already_running' ? 'warning' : 'success',
            'started_at'      => now(),
            'finished_at'     => now(),
            'summary'         => [
                'user_id'    => Auth::id(),
                'user_email' => Auth::user()?->email,
                'result'     => $result,
            ],
        ]);

        if (($result['status'] ?? '') === 'already_running') {
            return redirect()->back()->with('warning', 'Uma sincronização com o Nodal já está em andamento.');
        }

        return redirect()->back()->with('success', sprintf(
            'Sincronização concluída! %d faturas processadas (%d novas, %d atualizadas).',
            $result['synced'] ?? 0,
            $result['created'] ?? 0,
            $result['updated'] ?? 0
        ));
    }

    public function export(Request $request)
    {
        $query = ExternalBillingInvoice::query();

        // Aplicar os mesmos filtros da tela (GET query params)
        if ($request->filled('period')) {
            $query->where('competence', $request->period);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('trade_name', 'like', "%{$search}%")
                    ->orWhere('tax_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('fiscal_status')) {
            $query->where('fiscal_status', $request->fiscal_status);
        }

        if ($request->filled('incomplete_fiscal_data') && $request->incomplete_fiscal_data == '1') {
            $query->where('fiscal_data_complete', false);
        }

        $invoices = $query->orderByDesc('source_updated_at')->get();

        // Auditoria de Exportação
        AutomationAuditLog::create([
            'automation_key'  => 'accounting_export_generated',
            'automation_name' => 'Faturamento Nodal - Exportação Contabilidade',
            'status'          => 'success',
            'started_at'      => now(),
            'finished_at'     => now(),
            'summary'         => [
                'user_id'    => Auth::id(),
                'user_email' => Auth::user()?->email,
                'records'    => $invoices->count(),
                'filters'    => $request->query(),
            ],
        ]);

        $fileName = 'Exportacao_Contabilidade_Nodal_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            'Content-type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8 para Excel
            fwrite($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Cabeçalho do CSV
            fputcsv($file, [
                'Razão Social',
                'Nome Fantasia',
                'CNPJ',
                'Competência',
                'Descrição do Serviço',
                'Mensalidade',
                'Uso Adicional',
                'Valor Total',
                'Status Pagamento',
                'Data Pagamento',
                'E-mail Financeiro',
            ], ';');

            foreach ($invoices as $inv) {
                fputcsv($file, [
                    $this->sanitizeCsvField($inv->company_name ?? '-'),
                    $this->sanitizeCsvField($inv->trade_name ?? '-'),
                    $this->sanitizeCsvField($inv->tax_id ?? '-'),
                    $this->sanitizeCsvField($inv->competence ?? '-'),
                    $this->sanitizeCsvField($inv->service_description ?? '-'),
                    $inv->subscription_amount_formatted,
                    $inv->overage_amount_formatted,
                    $inv->total_amount_formatted,
                    $this->sanitizeCsvField($inv->payment_status ?? '-'),
                    $inv->paid_at ? $inv->paid_at->format('d/m/Y H:i') : '-',
                    $this->sanitizeCsvField($inv->billing_email ?? '-'),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Sanitiza campos textuais para prevenir CSV Formula Injection no Excel/Softwares Contábeis.
     */
    protected function sanitizeCsvField(?string $value): string
    {
        if (is_null($value) || $value === '') {
            return '';
        }

        $firstChar = substr(trim($value), 0, 1);

        if (in_array($firstChar, ['=', '+', '-', '@'], true)) {
            return "'" . $value;
        }

        return $value;
    }
}
