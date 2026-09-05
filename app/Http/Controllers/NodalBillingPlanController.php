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
        $visibilityFilter = null;

        // Suporta tanto 'visibility' quanto 'tab' para definir a filtragem de visibilidade
        $rawVisibility = $request->input('visibility');
        if (empty($rawVisibility)) {
            $rawVisibility = $request->input('tab');
        }

        if (in_array($rawVisibility, ['public', 'hidden'])) {
            $visibilityFilter = $rawVisibility;
            $filters['visibility'] = $rawVisibility;
            $filters['is_public'] = $rawVisibility === 'public' ? 1 : 0;
        }

        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }
        if ($request->filled('status')) {
            $filters['status'] = $request->status;
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

            // Aplicar filtragem local nos dados para garantir 100% de precisão na UI
            $plansCollection = collect($plans);

            // Helper para avaliação booleana robusta (trata bool, int e strings como "false", "0", "true", "1")
            $toBool = fn($val, $default = false) => filter_var($val ?? $default, FILTER_VALIDATE_BOOLEAN);

            // Filtro por Visibilidade (Aba ou Select: Públicos / Ocultos)
            if ($visibilityFilter === 'public') {
                $plansCollection = $plansCollection->filter(fn($p) => $toBool($p['is_public'] ?? false));
            } elseif ($visibilityFilter === 'hidden') {
                $plansCollection = $plansCollection->filter(fn($p) => ! $toBool($p['is_public'] ?? false));
            }

            // Filtro por Status (Ativos / Inativos)
            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $plansCollection = $plansCollection->filter(fn($p) => $toBool($p['is_active'] ?? true, true));
                } elseif ($request->status === 'inactive') {
                    $plansCollection = $plansCollection->filter(fn($p) => ! $toBool($p['is_active'] ?? true, true));
                }
            }

            // Filtro por Ilimitado (Sim / Não)
            if ($request->filled('unlimited')) {
                if ($request->unlimited === '1') {
                    $plansCollection = $plansCollection->filter(fn($p) => $toBool($p['is_unlimited'] ?? false));
                } elseif ($request->unlimited === '0') {
                    $plansCollection = $plansCollection->filter(fn($p) => ! $toBool($p['is_unlimited'] ?? false));
                }
            }

            // Filtro por Busca Textual
            if ($request->filled('search')) {
                $search = strtolower(trim($request->search));
                $plansCollection = $plansCollection->filter(function ($p) use ($search) {
                    $name = strtolower($p['name'] ?? '');
                    $code = strtolower($p['code'] ?? '');
                    return str_contains($name, $search) || str_contains($code, $search);
                });
            }

            $plans = array_values($plansCollection->toArray());

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
     * Converte um valor monetário (em Reais ou centavos) para um valor inteiro em centavos, sem usar float.
     */
    public function parseBrlToCents(mixed $input): ?int
    {
        if ($input === null || $input === '') {
            return null;
        }

        if (is_int($input)) {
            return $input;
        }

        $str = trim((string) $input);
        if ($str === '') {
            return null;
        }

        // Se contiver vírgula (ex: "R$ 1.990,00", "1.990,00", "22,00", "0,00")
        if (str_contains($str, ',')) {
            $parts = explode(',', $str);
            $intPart = preg_replace('/\D/', '', $parts[0] ?? '0');
            $decPart = substr(preg_replace('/\D/', '', $parts[1] ?? '0') . '00', 0, 2);
            $intPart = $intPart !== '' ? $intPart : '0';
            return ((int) $intPart) * 100 + ((int) $decPart);
        }

        // Se for string apenas numérica sem vírgula (ex: "199000", "2200", "50000", "0")
        $digitsOnly = preg_replace('/\D/', '', $str);
        if ($digitsOnly === '') {
            return null;
        }

        return (int) $digitsOnly;
    }

    /**
     * Converte um valor inteiro com separadores de milhar (ex: "50.000", "150.000") para int.
     */
    public function parseIntegerWithSeparators(mixed $input): ?int
    {
        if ($input === null || $input === '') {
            return null;
        }

        if (is_int($input)) {
            return $input;
        }

        $str = trim((string) $input);
        if ($str === '') {
            return null;
        }

        $digitsOnly = preg_replace('/\D/', '', $str);
        if ($digitsOnly === '') {
            return null;
        }

        return (int) $digitsOnly;
    }

    /**
     * Normaliza os campos comerciais do request antes da validação.
     */
    protected function normalizeCommercialFields(Request $request): array
    {
        $input = $request->all();

        if (array_key_exists('monthly_price_cents', $input)) {
            $input['monthly_price_cents'] = $this->parseBrlToCents($request->input('monthly_price_cents'));
        }
        if (array_key_exists('overage_price_per_1000_credits_cents', $input)) {
            $input['overage_price_per_1000_credits_cents'] = $this->parseBrlToCents($request->input('overage_price_per_1000_credits_cents'));
        }
        if (array_key_exists('default_postpaid_limit_cents', $input)) {
            $input['default_postpaid_limit_cents'] = $this->parseBrlToCents($request->input('default_postpaid_limit_cents'));
        }

        if (array_key_exists('included_ai_credits', $input)) {
            $input['included_ai_credits'] = $this->parseIntegerWithSeparators($request->input('included_ai_credits'));
        }
        if (array_key_exists('included_users', $input)) {
            $input['included_users'] = $this->parseIntegerWithSeparators($request->input('included_users'));
        }
        if (array_key_exists('integrations_limit', $input)) {
            $input['integrations_limit'] = $this->parseIntegerWithSeparators($request->input('integrations_limit'));
        }

        if (! $request->boolean('default_postpaid_enabled')) {
            $input['default_postpaid_limit_cents'] = null;
        }

        return $input;
    }

    /**
     * Processa a criação de um novo plano no Nodal.
     */
    public function store(Request $request)
    {
        $request->merge($this->normalizeCommercialFields($request));

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
            'is_public'                            => $request->boolean('is_public'),
            'is_unlimited'                         => $request->boolean('is_unlimited'),
            'is_active'                            => $request->boolean('is_active'),
            'is_enterprise'                        => $request->boolean('is_enterprise'),
            'default_postpaid_enabled'             => $request->boolean('default_postpaid_enabled'),
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
        // Buscar o plano existente no Nodal para preservar dados quando os campos estiverem disabled na UI
        $existingPlan = null;
        try {
            $response = $this->client->getPlan($uuid);
            if (isset($response['data']) && is_array($response['data'])) {
                $existingPlan = $response['data'];
            } elseif (is_array($response)) {
                $existingPlan = $response;
            }
        } catch (\Exception $e) {
            // Falha graciosa caso o Nodal esteja indisponível ao buscar plano existente
        }

        $request->merge($this->normalizeCommercialFields($request));

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

        $isActive = $request->boolean('is_active');
        $isPostpaid = $request->boolean('default_postpaid_enabled');

        $featuresJson = [];
        if (!empty($validated['features_text'])) {
            $featuresJson = array_values(array_filter(array_map('trim', explode("\n", $validated['features_text']))));
        }

        $data = [
            'name'                     => $validated['name'],
            'description'              => $validated['description'] ?? null,
            'monthly_price_cents'      => (int) $validated['monthly_price_cents'],
            'is_public'                => $request->boolean('is_public'),
            'is_unlimited'             => $request->boolean('is_unlimited'),
            'is_active'                => $isActive,
            'is_enterprise'            => $request->boolean('is_enterprise'),
            'default_postpaid_enabled' => $isPostpaid,
            'default_postpaid_limit_cents' => $isPostpaid
                ? (isset($validated['default_postpaid_limit_cents']) ? (int) $validated['default_postpaid_limit_cents'] : null)
                : null,
            'features_json'            => $featuresJson,
        ];

        // Tratar campos de limites que não vêm no request quando estiverem disabled no navegador
        if ($request->has('included_users')) {
            $data['included_users'] = $validated['included_users'] !== null ? (int) $validated['included_users'] : null;
        } elseif ($existingPlan && array_key_exists('included_users', $existingPlan)) {
            $data['included_users'] = $existingPlan['included_users'];
        } else {
            $data['included_users'] = null;
        }

        if ($request->has('included_ai_credits')) {
            $data['included_ai_credits'] = $validated['included_ai_credits'] !== null ? (int) $validated['included_ai_credits'] : null;
        } elseif ($existingPlan && array_key_exists('included_ai_credits', $existingPlan)) {
            $data['included_ai_credits'] = $existingPlan['included_ai_credits'];
        } else {
            $data['included_ai_credits'] = null;
        }

        if ($request->has('integrations_limit')) {
            $data['integrations_limit'] = $validated['integrations_limit'] !== null ? (int) $validated['integrations_limit'] : 0;
        } elseif ($existingPlan && array_key_exists('integrations_limit', $existingPlan)) {
            $data['integrations_limit'] = $existingPlan['integrations_limit'];
        } else {
            $data['integrations_limit'] = 0;
        }

        if ($request->has('overage_price_per_1000_credits_cents')) {
            $data['overage_price_per_1000_credits_cents'] = $validated['overage_price_per_1000_credits_cents'] !== null ? (int) $validated['overage_price_per_1000_credits_cents'] : null;
        } elseif ($existingPlan && array_key_exists('overage_price_per_1000_credits_cents', $existingPlan)) {
            $data['overage_price_per_1000_credits_cents'] = $existingPlan['overage_price_per_1000_credits_cents'];
        } else {
            $data['overage_price_per_1000_credits_cents'] = null;
        }

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
