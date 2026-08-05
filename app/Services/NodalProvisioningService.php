<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class NodalProvisioningService
{
    /**
     * Provisiona uma nova organização no Nodal.
     *
     * @param  array  $organizationData  ['name' => string, 'slug' => string|null]
     * @param  array  $ownerData         ['name' => string, 'email' => string, 'password' => string]
     * @return array  Payload da resposta 201: ['message', 'organization_uuid', 'user_uuid', 'login_url']
     *
     * @throws \Illuminate\Http\Client\RequestException  Em caso de erro 401 ou 422.
     */
    public function provisionCompany(array $organizationData, array $ownerData): array
    {
        $payload = [
            'organization' => [
                'name' => $organizationData['name'],
            ],
            'owner' => [
                'name'     => $ownerData['name'],
                'email'    => $ownerData['email'],
                'password' => $ownerData['password'],
            ],
        ];

        // Novos campos opcionais
        if (isset($organizationData['slug'])) {
            $payload['organization']['slug'] = $organizationData['slug'];
        }
        if (isset($organizationData['cnpj'])) {
            $payload['organization']['cnpj'] = $organizationData['cnpj'];
        }
        if (isset($organizationData['address'])) {
            $payload['organization']['address'] = $organizationData['address'];
        }
        if (isset($organizationData['industry'])) {
            $payload['organization']['industry'] = $organizationData['industry'];
        }

        $response = Http::withHeaders([
            'X-System-Api-Key' => config('services.nodal.api_key')
        ])
            ->baseUrl(config('services.nodal.base_url'))
            ->acceptJson()
            ->asJson()
            ->post('/api/v1/provision/organization', $payload);

        if ($response->status() === 401) {
            \Log::critical('Nodal: API Key inválida ou não configurada. Verifique NODAL_SYSTEM_API_KEY.', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        if ($response->failed()) {
            $response->throw();
        }

        return $response->json();
    }

    /**
     * Atualiza os dados de uma organização existente no Nodal.
     *
     * @param string $organizationUuid  UUID da organização no Nodal
     * @param array $organizationData   Dados da organização a atualizar (name, cnpj, industry, address)
     * @param array $ownerData          Opcional. Dados do dono a atualizar (name, email, password)
     * @return array
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function updateCompany(string $organizationUuid, array $organizationData, array $ownerData = []): array
    {
        $payload = $organizationData;

        if (!empty($ownerData)) {
            // Remove campos vazios se a senha não foi informada, por exemplo
            $payload['owner'] = array_filter($ownerData);
        }

        $response = Http::withHeaders([
            'X-System-Api-Key' => config('services.nodal.api_key')
        ])
            ->baseUrl(config('services.nodal.base_url'))
            ->acceptJson()
            ->asJson()
            ->put("/api/v1/organizations/{$organizationUuid}", $payload);

        if ($response->status() === 401) {
            \Log::critical('Nodal: API Key inválida ou não configurada na atualização.', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        if ($response->failed()) {
            $response->throw();
        }

        return $response->json();
    }

    /**
     * Exclui uma organização permanentemente no Nodal.
     *
     * @param string $organizationUuid UUID da organização no Nodal
     * @return array
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function deleteCompany(string $organizationUuid): array
    {
        $response = Http::withHeaders([
            'X-System-Api-Key' => config('services.nodal.api_key')
        ])
            ->baseUrl(config('services.nodal.base_url'))
            ->acceptJson()
            ->delete("/api/v1/organizations/{$organizationUuid}");

        if ($response->status() === 401) {
            \Log::critical('Nodal: API Key inválida ou não configurada na exclusão.', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        if ($response->failed()) {
            $response->throw();
        }

        return $response->json();
    }
    /**
     * Busca as requisições de verificação KYC pendentes.
     *
     * @return array
     */
    public function getPendingVerifications(): array
    {
        $response = Http::withHeaders([
            'X-System-Api-Key' => config('services.nodal.api_key')
        ])
            ->baseUrl(config('services.nodal.base_url'))
            ->acceptJson()
            ->get('/api/v1/verifications/pending');

        if ($response->failed()) {
            \Log::error('Nodal: Erro ao buscar verificações pendentes', ['status' => $response->status(), 'body' => $response->body()]);
            $response->throw();
        }

        return $response->json();
    }

    /**
     * Busca os detalhes de uma verificação KYC e a URL do documento.
     *
     * @param string $verificationUuid
     * @return array
     */
    public function getVerificationDetails(string $verificationUuid): array
    {
        $response = Http::withHeaders([
            'X-System-Api-Key' => config('services.nodal.api_key')
        ])
            ->baseUrl(config('services.nodal.base_url'))
            ->acceptJson()
            ->get("/api/v1/verifications/{$verificationUuid}");

        if ($response->failed()) {
            $response->throw();
        }

        return $response->json();
    }

    /**
     * Aprova uma verificação de documento no Nodal.
     *
     * @param string $verificationUuid
     * @param string|null $notes
     * @return array
     */
    public function approveVerification(string $verificationUuid, ?string $notes = null): array
    {
        $payload = [];
        if ($notes) {
            $payload['notes'] = $notes;
        }

        $response = Http::withHeaders([
            'X-System-Api-Key' => config('services.nodal.api_key')
        ])
            ->baseUrl(config('services.nodal.base_url'))
            ->acceptJson()
            ->asJson()
            ->post("/api/v1/verifications/{$verificationUuid}/approve", $payload);

        if ($response->failed()) {
            $response->throw();
        }

        return $response->json();
    }

    /**
     * Rejeita uma verificação de documento no Nodal, informando um motivo.
     *
     * @param string $verificationUuid
     * @param string $reason
     * @return array
     */
    public function rejectVerification(string $verificationUuid, string $reason): array
    {
        $response = Http::withHeaders([
            'X-System-Api-Key' => config('services.nodal.api_key')
        ])
            ->baseUrl(config('services.nodal.base_url'))
            ->acceptJson()
            ->asJson()
            ->post("/api/v1/verifications/{$verificationUuid}/reject", [
                'reason' => $reason
            ]);

        if ($response->failed()) {
            $response->throw();
        }

        return $response->json();
    }
}
