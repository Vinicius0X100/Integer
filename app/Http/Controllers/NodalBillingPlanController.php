<?php

namespace App\Http\Controllers;

use App\Models\AutomationAuditLog;
use App\Services\NodalBillingPlanClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

class NodalBillingPlanController extends Controller
{
    public function __construct(
        protected NodalBillingPlanClient $client
    ) {}

    /**
     * Exibe a listagem administrativa dos Planos de Licenciamento do Nodal.
     */
    public function index(Request $request)
    {
        $filters = [];
        if ($request->filled('tab') && in_array($request->tab, ['public', 'hidden'])) {
            $filters['visibility'] = $request->tab;
        }
        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }
        if ($request->filled('status')) {
            $filters['status'] = $request->status;
        }
        if ($request->filled('visibility')) {
            $filters['visibility'] = $request->visibility;
        }
        if ($request->filled('unlimited')) {
            $filters['unlimited'] = $request->unlimited;
        }

        $plans = [];
        $apiError = null;

        try {
            $response = $this->client->listPlans($filters);

            // Suporta formatos de resposta: array direto ou objeto envelopado em ['data' => [...]]
            if (isset($response['data']) && is_array($response['data'])) {
                $plans = $response['data'];
            } elseif (is_array($response)) {
                $plans = $response;
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $status = $e->response?->status();
            if ($status === 401 || $status === 403) {
                $apiError = 'Erro de autenticação ou permissão na API do Nodal. Verifique se a INTEGER_ADMIN_API_KEY foi configurada corretamente.';
            } else {
                $apiError = 'Não foi possível carregar os planos do Nodal. Verifique a conectividade ou tente novamente.';
            }
        } catch (\Exception $e) {
            $apiError = 'Não foi possível carregar os planos do Nodal. Tente novamente.';
        }

        return view('nodal_plans.index', compact('plans', 'apiError', 'filters'));
    }

    /**
     * Processa a criação de um novo plano no Nodal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                                 => 'required|string|max:255',
            'code'                                 => 'required|string|max:100|regex:/^[a-z0-9-]+$/',
            'description'                          => 'nullable|string|max:1000',
            'monthly_price_cents'                  => 'required|integer|min:0',
            'included_users'                       => 'nullable|integer|min:0',
            'included_ai_credits'                  => 'nullable|integer|min:0',
            'integrations_limit'                   => 'nullable|integer|min:0',
            'overage_price_per_1000_credits_cents' => 'nullable|integer|min:0',
            'is_public'                            => 'nullable|boolean',
            'is_unlimited'                         => 'nullable|boolean',
            'is_active'                            => 'nullable|boolean',
            'is_enterprise'                        => 'nullable|boolean',
            'default_postpaid_enabled'             => 'nullable|boolean',
            'default_postpaid_limit_cents'         => 'nullable|integer|min:0',
            'features_text'                        => 'nullable|string',
        ], [
            'name.required'                => 'O nome do plano é obrigatório.',
            'code.required'                => 'O código do plano é obrigatório.',
            'code.regex'                   => 'O código deve conter apenas letras minúsculas, números e hífens.',
            'monthly_price_cents.required' => 'A mensalidade é obrigatória.',
        ]);

        // Converter texto de características (1 por linha) para array de strings
        $featuresJson = [];
        if (!empty($validated['features_text'])) {
            $featuresJson = array_values(array_filter(array_map('trim', explode("\n", $validated['features_text']))));
        }

        $data = [
            'name'                                 => $validated['name'],
            'code'                                 => strtolower(trim($validated['code'])),
            'description'                          => $validated['description'] ?? null,
            'monthly_price_cents'                  => (int) $validated['monthly_price_cents'],
            'included_users'                       => isset($validated['included_users']) ? (int) $validated['included_users'] : null,
            'included_ai_credits'                  => isset($validated['included_ai_credits']) ? (int) $validated['included_ai_credits'] : null,
            'integrations_limit'                   => isset($validated['integrations_limit']) ? (int) $validated['integrations_limit'] : 0,
            'overage_price_per_1000_credits_cents' => isset($validated['overage_price_per_1000_credits_cents']) ? (int) $validated['overage_price_per_1000_credits_cents'] : null,
            'is_public'                            => $request->has('is_public') ? (bool) $request->is_public : true,
            'is_unlimited'                         => $request->has('is_unlimited') ? (bool) $request->is_unlimited : false,
            'is_active'                            => $request->has('is_active') ? (bool) $request->is_active : true,
            'is_enterprise'                        => $request->has('is_enterprise') ? (bool) $request->is_enterprise : false,
            'default_postpaid_enabled'             => $request->has('default_postpaid_enabled') ? (bool) $request->default_postpaid_enabled : false,
            'default_postpaid_limit_cents'         => isset($validated['default_postpaid_limit_cents']) ? (int) $validated['default_postpaid_limit_cents'] : null,
            'features_json'                        => $featuresJson,
        ];

        try {
            $result = $this->client->createPlan($data);

            // Registro de auditoria
            AutomationAuditLog::create([
                'automation_key'  => 'nodal_billing_plan_created',
                'automation_name' => 'Planos de Licenciamento Nodal - Criação de Plano',
                'status'          => 'success',
                'started_at'      => now(),
                'finished_at'     => now(),
                'summary'         => [
                    'user_id'    => Auth::id(),
                    'user_email' => Auth::user()?->email,
                    'code'       => $data['code'],
                    'name'       => $data['name'],
                    'plan_uuid'  => $result['uuid'] ?? $result['id'] ?? null,
                ],
                'details' => [
                    'new_state' => $data,
                ],
            ]);

            return redirect()->route('nodal-plans.index')
                ->with('success', "Plano \"{$data['name']}\" criado com sucesso no Nodal!");

        } catch (RequestException $e) {
            $status = $e->response?->status();
            $body   = $e->response?->json() ?? [];

            if ($status === 422) {
                $errors = $body['errors'] ?? [];
                return back()
                    ->withInput()
                    ->withErrors($errors)
                    ->with('error', $body['message'] ?? 'Os dados informados são inválidos ou o código do plano já está em uso.');
            }

            if ($status === 401 || $status === 403) {
                return back()
                    ->withInput()
                    ->with('error', 'Erro de autenticação/permissão na API administrativa do Nodal.');
            }

            return back()
                ->withInput()
                ->with('error', 'Ocorreu um erro ao criar o plano no Nodal: ' . ($body['message'] ?? 'Indisponibilidade de serviço.'));
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Não foi possível conectar ao Nodal para criar o plano.');
        }
    }

    /**
     * Processa a atualização de um plano existente no Nodal.
     */
    public function update(Request $request, string $uuid)
    {
        $validated = $request->validate([
            'name'                                 => 'required|string|max:255',
            'description'                          => 'nullable|string|max:1000',
            'monthly_price_cents'                  => 'required|integer|min:0',
            'included_users'                       => 'nullable|integer|min:0',
            'included_ai_credits'                  => 'nullable|integer|min:0',
            'integrations_limit'                   => 'nullable|integer|min:0',
            'overage_price_per_1000_credits_cents' => 'nullable|integer|min:0',
            'is_public'                            => 'nullable|boolean',
            'is_unlimited'                         => 'nullable|boolean',
            'is_active'                            => 'nullable|boolean',
            'is_enterprise'                        => 'nullable|boolean',
            'default_postpaid_enabled'             => 'nullable|boolean',
            'default_postpaid_limit_cents'         => 'nullable|integer|min:0',
            'features_text'                        => 'nullable|string',
        ], [
            'name.required'                => 'O nome do plano é obrigatório.',
            'monthly_price_cents.required' => 'A mensalidade é obrigatória.',
        ]);

        $isActive = $request->has('is_active') ? (bool) $request->is_active : false;

        $featuresJson = [];
        if (!empty($validated['features_text'])) {
            $featuresJson = array_values(array_filter(array_map('trim', explode("\n", $validated['features_text']))));
        }

        $data = [
            'name'                                 => $validated['name'],
            'description'                          => $validated['description'] ?? null,
            'monthly_price_cents'                  => (int) $validated['monthly_price_cents'],
            'included_users'                       => isset($validated['included_users']) ? (int) $validated['included_users'] : null,
            'included_ai_credits'                  => isset($validated['included_ai_credits']) ? (int) $validated['included_ai_credits'] : null,
            'integrations_limit'                   => isset($validated['integrations_limit']) ? (int) $validated['integrations_limit'] : 0,
            'overage_price_per_1000_credits_cents' => isset($validated['overage_price_per_1000_credits_cents']) ? (int) $validated['overage_price_per_1000_credits_cents'] : null,
            'is_public'                            => $request->has('is_public') ? (bool) $request->is_public : false,
            'is_unlimited'                         => $request->has('is_unlimited') ? (bool) $request->is_unlimited : false,
            'is_active'                            => $isActive,
            'is_enterprise'                        => $request->has('is_enterprise') ? (bool) $request->is_enterprise : false,
            'default_postpaid_enabled'             => $request->has('default_postpaid_enabled') ? (bool) $request->default_postpaid_enabled : false,
            'default_postpaid_limit_cents'         => isset($validated['default_postpaid_limit_cents']) ? (int) $validated['default_postpaid_limit_cents'] : null,
            'features_json'                        => $featuresJson,
        ];

        // O campo 'code' é rigorosamente imutável e nunca enviado na atualização
        unset($data['code']);

        try {
            $this->client->updatePlan($uuid, $data);

            $automationKey = $isActive ? 'nodal_billing_plan_updated' : 'nodal_billing_plan_retired';
            $automationName = $isActive
                ? 'Planos de Licenciamento Nodal - Edição de Plano'
                : 'Planos de Licenciamento Nodal - Aposentadoria de Plano';

            AutomationAuditLog::create([
                'automation_key'  => $automationKey,
                'automation_name' => $automationName,
                'status'          => 'success',
                'started_at'      => now(),
                'finished_at'     => now(),
                'summary'         => [
                    'user_id'    => Auth::id(),
                    'user_email' => Auth::user()?->email,
                    'plan_uuid'  => $uuid,
                    'name'       => $data['name'],
                    'is_active'  => $isActive,
                ],
                'details' => [
                    'new_state' => $data,
                ],
            ]);

            $message = $isActive
                ? "Plano \"{$data['name']}\" atualizado com sucesso no Nodal!"
                : "Plano \"{$data['name']}\" inativado/aposentado com sucesso!";

            return redirect()->route('nodal-plans.index')->with('success', $message);

        } catch (RequestException $e) {
            $status = $e->response?->status();
            $body   = $e->response?->json() ?? [];

            if ($status === 422) {
                return back()->withInput()->withErrors($body['errors'] ?? [])->with('error', $body['message'] ?? 'Dados inválidos.');
            }

            return back()->withInput()->with('error', 'Erro ao atualizar o plano no Nodal: ' . ($body['message'] ?? 'Falha de comunicação.'));
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Não foi possível conectar ao Nodal para atualizar o plano.');
        }
    }

    /**
     * Atribui um plano de licenciamento a uma organização do Nodal.
     */
    public function assign(Request $request, string $organizationUuid)
    {
        $request->validate([
            'plan_uuid' => 'required|string',
        ], [
            'plan_uuid.required' => 'Selecione um plano válido para atribuição.',
        ]);

        $planUuid = $request->plan_uuid;

        try {
            $this->client->assignPlan($organizationUuid, [
                'plan_uuid'         => $planUuid,
                'billing_plan_uuid' => $planUuid,
            ]);

            AutomationAuditLog::create([
                'automation_key'  => 'nodal_organization_plan_changed',
                'automation_name' => 'Nodal - Alteração de Plano de Licenciamento da Organização',
                'status'          => 'success',
                'started_at'      => now(),
                'finished_at'     => now(),
                'summary'         => [
                    'user_id'           => Auth::id(),
                    'user_email'        => Auth::user()?->email,
                    'organization_uuid' => $organizationUuid,
                    'plan_uuid'         => $planUuid,
                ],
                'details' => [
                    'assigned_plan_uuid' => $planUuid,
                ],
            ]);

            return back()->with('success', 'Plano de licenciamento alterado com sucesso no Nodal!');

        } catch (RequestException $e) {
            $status = $e->response?->status();
            $body   = $e->response?->json() ?? [];

            if ($status === 422) {
                return back()->with('error', $body['message'] ?? 'O Nodal recusou a alteração de plano.');
            }

            return back()->with('error', 'Erro ao alterar o plano no Nodal: ' . ($body['message'] ?? 'Indisponibilidade.'));
        } catch (\Exception $e) {
            return back()->with('error', 'Não foi possível conectar ao Nodal para alterar o plano.');
        }
    }
}
