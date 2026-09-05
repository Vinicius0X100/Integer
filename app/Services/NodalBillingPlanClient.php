<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

class NodalBillingPlanClient
{
    /**
     * Obter a chave de API administrativa.
     */
    protected function getAdminApiKey(): string
    {
        return config('services.nodal.integer_admin_api_key', '');
    }

    /**
     * Obter a URL base da API Nodal.
     */
    protected function getBaseUrl(): string
    {
        return config('services.nodal.base_url', 'https://nodal.sacratech.com');
    }

    /**
     * Criar a instância de cliente HTTP com autenticação Bearer administrative.
     */
    protected function client()
    {
        $apiKey = $this->getAdminApiKey();

        if (empty($apiKey)) {
            Log::error('NodalBillingPlanClient: INTEGER_ADMIN_API_KEY não configurada em config/services.php.');
        }

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept'        => 'application/json',
        ])
        ->baseUrl($this->getBaseUrl())
        ->timeout(15);
    }

    /**
     * Listar planos (públicos e ocultos).
     *
     * GET /api/v1/internal/integer/billing/plans
     */
    public function listPlans(array $filters = []): array
    {
        try {
            $response = $this->client()->get('/api/v1/internal/integer/billing/plans', $filters);

            if ($response->failed()) {
                $this->logError('listPlans', $response);
                $response->throw();
            }

            return $response->json();
        } catch (\Exception $e) {
            $this->logException('listPlans', $e);
            throw $e;
        }
    }

    /**
     * Obter detalhes de um plano específico pelo UUID.
     *
     * GET /api/v1/internal/integer/billing/plans/{uuid}
     */
    public function getPlan(string $uuid): array
    {
        try {
            $response = $this->client()->get("/api/v1/internal/integer/billing/plans/{$uuid}");

            if ($response->failed()) {
                $this->logError('getPlan', $response);
                $response->throw();
            }

            return $response->json();
        } catch (\Exception $e) {
            $this->logException('getPlan', $e);
            throw $e;
        }
    }

    /**
     * Criar um novo plano no catálogo Nodal.
     *
     * POST /api/v1/internal/integer/billing/plans
     */
    public function createPlan(array $data): array
    {
        try {
            $response = $this->client()->asJson()->post('/api/v1/internal/integer/billing/plans', $data);

            if ($response->failed()) {
                $this->logError('createPlan', $response);
                $response->throw();
            }

            return $response->json();
        } catch (\Exception $e) {
            $this->logException('createPlan', $e);
            throw $e;
        }
    }

    /**
     * Atualizar um plano existente (sem permitir editar code).
     *
     * PATCH /api/v1/internal/integer/billing/plans/{uuid}
     */
    public function updatePlan(string $uuid, array $data): array
    {
        // Garantir que o campo 'code' não seja enviado na edição se estiver presente
        unset($data['code']);

        try {
            $response = $this->client()->asJson()->patch("/api/v1/internal/integer/billing/plans/{$uuid}", $data);

            if ($response->failed()) {
                $this->logError('updatePlan', $response);
                $response->throw();
            }

            return $response->json();
        } catch (\Exception $e) {
            $this->logException('updatePlan', $e);
            throw $e;
        }
    }

    /**
     * Atribuir plano a uma organização no Nodal.
     *
     * PATCH /api/v1/internal/integer/billing/organizations/{organization_uuid}/billing-plan
     */
    public function assignPlan(string $organizationUuid, array $data): array
    {
        // Garantir compatibilidade com contratos (plan_uuid vs billing_plan_uuid vs plan_code)
        $payload = [];
        if (isset($data['plan_uuid'])) {
            $payload['plan_uuid'] = $data['plan_uuid'];
            $payload['billing_plan_uuid'] = $data['plan_uuid'];
        } elseif (isset($data['billing_plan_uuid'])) {
            $payload['plan_uuid'] = $data['billing_plan_uuid'];
            $payload['billing_plan_uuid'] = $data['billing_plan_uuid'];
        } elseif (isset($data['plan_code'])) {
            $payload['plan_code'] = $data['plan_code'];
        } else {
            $payload = $data;
        }

        try {
            $response = $this->client()->asJson()->patch(
                "/api/v1/internal/integer/billing/organizations/{$organizationUuid}/billing-plan",
                $payload
            );

            if ($response->failed()) {
                $this->logError('assignPlan', $response);
                $response->throw();
            }

            return $response->json();
        } catch (\Exception $e) {
            $this->logException('assignPlan', $e);
            throw $e;
        }
    }

    /**
     * Tentar obter o plano atualmente atribuído a uma organização no Nodal.
     *
     * GET /api/v1/internal/integer/billing/organizations/{organization_uuid}/billing-plan
     */
    public function getOrganizationPlan(string $organizationUuid): ?array
    {
        try {
            $response = $this->client()->get("/api/v1/internal/integer/billing/organizations/{$organizationUuid}/billing-plan");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // Falha silenciosa para plano atual quando o endpoint ainda não existe ou 404
        }

        return null;
    }

    /**
     * Registrar erro HTTP no log de forma segura sem expor a API key.
     */
    protected function logError(string $method, $response): void
    {
        Log::error("NodalBillingPlanClient::{$method} falhou com status {$response->status()}", [
            'status' => $response->status(),
            'body'   => $response->json() ?? $response->body(),
        ]);
    }

    /**
     * Registrar exceção no log sem expor segredos.
     */
    protected function logException(string $method, \Exception $e): void
    {
        Log::error("NodalBillingPlanClient::{$method} lançou exceção: {$e->getMessage()}");
    }
}
