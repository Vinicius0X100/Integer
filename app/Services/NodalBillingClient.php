<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NodalBillingClient
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.nodal.base_url', 'https://nodal.sacratech.com');
        $this->apiKey  = (string) config('services.nodal.integer_system_api_key', '');
    }

    /**
     * Busca faturas no Nodal com filtros e paginação.
     *
     * @param  array  $filters  ['period', 'status', 'payment_status', 'organization_uuid', 'updated_since', 'page', 'per_page']
     * @return array
     *
     * @throws RequestException
     */
    public function fetchInvoices(array $filters = []): array
    {
        $query = array_filter([
            'period'            => $filters['period'] ?? null,
            'status'            => $filters['status'] ?? null,
            'payment_status'    => $filters['payment_status'] ?? null,
            'organization_uuid' => $filters['organization_uuid'] ?? null,
            'updated_since'     => $filters['updated_since'] ?? null,
            'page'              => $filters['page'] ?? null,
            'per_page'          => $filters['per_page'] ?? null,
        ], fn ($val) => ! is_null($val) && $val !== '');

        $response = $this->makeRequest('GET', '/api/v1/internal/integer/billing/invoices', $query);

        return $response->json();
    }

    /**
     * Busca os detalhes de uma fatura específica no Nodal.
     *
     * @param  string  $invoiceUuid
     * @return array
     *
     * @throws RequestException
     */
    public function getInvoice(string $invoiceUuid): array
    {
        $response = $this->makeRequest('GET', "/api/v1/internal/integer/billing/invoices/{$invoiceUuid}");

        return $response->json();
    }

    /**
     * Executa a requisição HTTP configurada com Bearer Token, timeout e retry seguro.
     */
    protected function makeRequest(string $method, string $path, array $queryParams = []): Response
    {
        $pendingRequest = Http::withToken($this->apiKey)
            ->baseUrl($this->baseUrl)
            ->acceptJson()
            ->timeout(15)
            ->retry(3, 100, function (\Exception $exception, $request) {
                // Retry apenas para erros de conexão ou respostas 5xx e 429
                if ($exception instanceof RequestException && $exception->response) {
                    $status = $exception->response->status();
                    return $status === 429 || $status >= 500;
                }
                // Se for timeout ou erro de rede sem resposta, é seguro tentar novamente
                return true;
            }, throw: false);

        if (strtolower($method) === 'get') {
            $response = $pendingRequest->get($path, $queryParams);
        } else {
            $response = $pendingRequest->post($path, $queryParams);
        }

        if ($response->status() === 401 || $response->status() === 403) {
            Log::critical('Nodal Billing Client: Falha de Autenticação Server-to-Server.', [
                'status' => $response->status(),
                'path'   => $path,
            ]);
        }

        if ($response->failed()) {
            $response->throw();
        }

        return $response;
    }
}
