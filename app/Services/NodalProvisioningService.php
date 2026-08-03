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

        if (!empty($organizationData['slug'])) {
            $payload['organization']['slug'] = $organizationData['slug'];
        }

        $response = Http::withToken(config('services.nodal.api_key'))
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
}
