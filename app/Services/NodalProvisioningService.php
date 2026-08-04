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
     * @return array  Payload da resposta 201: ['message', 'organization_id', 'user_id', 'login_url']
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
     * @param int   $nodalId            ID da organização no Nodal
     * @param array $organizationData   Dados da organização a atualizar (name, cnpj, industry, address)
     * @param array $ownerData          Opcional. Dados do dono a atualizar (name, email, password)
     * @return array
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function updateCompany(int $nodalId, array $organizationData, array $ownerData = []): array
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
            ->put("/api/v1/organizations/{$nodalId}", $payload);

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
     * @param int $nodalId ID da organização no Nodal
     * @return array
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function deleteCompany(int $nodalId): array
    {
        $response = Http::withHeaders([
            'X-System-Api-Key' => config('services.nodal.api_key')
        ])
            ->baseUrl(config('services.nodal.base_url'))
            ->acceptJson()
            ->delete("/api/v1/organizations/{$nodalId}");

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
}
