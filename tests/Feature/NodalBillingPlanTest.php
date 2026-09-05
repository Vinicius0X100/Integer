<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\NodalOrganization;
use App\Models\AutomationAuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class NodalBillingPlanTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.nodal.integer_admin_api_key', 'test-admin-secret-key-12345');
        Config::set('services.nodal.integer_system_api_key', 'test-system-secret-key-67890');
        Config::set('services.nodal.base_url', 'http://nodal.test');

        $this->adminUser = User::factory()->create([
            'papel' => 'admin',
        ]);

        $this->regularUser = User::factory()->create([
            'papel' => 'user',
        ]);
    }

    /**
     * Helper para mock de resposta da API do Nodal.
     */
    protected function mockPlansResponse(array $plans = []): array
    {
        if (empty($plans)) {
            $plans = [
                [
                    'uuid'                 => 'plan-uuid-starter-111',
                    'name'                 => 'Starter',
                    'code'                 => 'starter',
                    'monthly_price_cents' => 59900,
                    'max_users'           => 50,
                    'ai_credits'          => 10000,
                    'overage_price_cents' => 2500,
                    'is_public'           => true,
                    'is_unlimited'        => false,
                    'is_active'           => true,
                    'organizations_count' => 12,
                    'description'         => 'Plano inicial para pequenas paróquias',
                ],
                [
                    'uuid'                 => 'plan-uuid-internal-222',
                    'name'                 => 'SacraTech Internal',
                    'code'                 => 'sacratech-internal',
                    'monthly_price_cents' => 0,
                    'max_users'           => null,
                    'ai_credits'          => null,
                    'overage_price_cents' => 0,
                    'is_public'           => false,
                    'is_unlimited'        => true,
                    'is_active'           => true,
                    'organizations_count' => 1,
                    'description'         => 'Uso interno SacraTech',
                ],
            ];
        }

        return ['data' => $plans];
    }

    /** Teste 1: Header Authorization: Bearer {INTEGER_ADMIN_API_KEY} é usado */
    public function test_01_uses_integer_admin_api_key_bearer_header(): void
    {
        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/plans' => Http::response($this->mockPlansResponse(), 200),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('nodal-plans.index'));
        $response->assertStatus(200);

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer test-admin-secret-key-12345');
        });
    }

    /** Teste 2: A chave SYSTEM nunca é usada para requisições de planos */
    public function test_02_system_key_is_never_used_for_plans(): void
    {
        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/plans' => Http::response($this->mockPlansResponse(), 200),
        ]);

        $this->actingAs($this->adminUser)->get(route('nodal-plans.index'));

        Http::assertSent(function ($request) {
            return !$request->hasHeader('Authorization', 'Bearer test-system-secret-key-67890');
        });
    }

    /** Teste 3: Listagem de planos carregada da API Nodal */
    public function test_03_lists_plans_from_nodal_api(): void
    {
        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/plans*' => Http::response($this->mockPlansResponse(), 200),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('nodal-plans.index'));
        $response->assertStatus(200);
        $response->assertSee('Starter');
        $response->assertSee('starter');
        $response->assertSee('SacraTech Internal');
    }

    /** Teste 4: Filtro por planos públicos */
    public function test_04_filters_public_plans(): void
    {
        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/plans*' => Http::response($this->mockPlansResponse(), 200),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('nodal-plans.index', ['tab' => 'public']));
        $response->assertStatus(200);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'visibility=public');
        });
    }

    /** Teste 5: Filtro por planos ocultos */
    public function test_05_filters_hidden_plans(): void
    {
        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/plans*' => Http::response($this->mockPlansResponse(), 200),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('nodal-plans.index', ['tab' => 'hidden']));
        $response->assertStatus(200);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'visibility=hidden');
        });
    }

    /** Teste 6: Criação de novo plano via POST */
    public function test_06_creates_new_plan(): void
    {
        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/plans' => Http::response([
                'uuid' => 'new-plan-uuid-333',
                'name' => 'Business',
                'code' => 'business',
            ], 201),
        ]);

        $payload = [
            'name'                => 'Business',
            'code'                => 'business',
            'monthly_price_cents' => 199000,
            'max_users'           => 500,
            'ai_credits'          => 50000,
            'overage_price_cents' => 2200,
            'is_public'           => 1,
            'is_unlimited'        => 0,
            'is_active'           => 1,
            'description'         => 'Plano intermediário',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('nodal-plans.store'), $payload);
        $response->assertRedirect(route('nodal-plans.index'));
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return $request->url() === 'http://nodal.test/api/v1/internal/integer/billing/plans' &&
                   $request['code'] === 'business' &&
                   $request['monthly_price_cents'] === 199000;
        });

        $this->assertDatabaseHas('automation_audit_logs', [
            'automation_key' => 'nodal_billing_plan_created',
        ]);
    }

    /** Teste 7: Tratar erro 422 de código duplicado */
    public function test_07_handles_duplicate_code_422_error(): void
    {
        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/plans' => Http::response([
                'message' => 'O código do plano informado já existe.',
                'errors'  => ['code' => ['O código "starter" já está em uso.']],
            ], 422),
        ]);

        $payload = [
            'name'                => 'Starter Duplicado',
            'code'                => 'starter',
            'monthly_price_cents' => 59900,
        ];

        $response = $this->actingAs($this->adminUser)->post(route('nodal-plans.store'), $payload);
        $response->assertSessionHasErrors(['code']);
    }

    /** Teste 8: Edição de plano via PATCH */
    public function test_08_updates_existing_plan(): void
    {
        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/plans/plan-uuid-starter-111' => Http::response([
                'uuid' => 'plan-uuid-starter-111',
                'name' => 'Starter Atualizado',
            ], 200),
        ]);

        $payload = [
            'name'                => 'Starter Atualizado',
            'monthly_price_cents' => 69900,
            'is_public'           => 1,
            'is_active'           => 1,
        ];

        $response = $this->actingAs($this->adminUser)->patch(route('nodal-plans.update', 'plan-uuid-starter-111'), $payload);
        $response->assertRedirect(route('nodal-plans.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('automation_audit_logs', [
            'automation_key' => 'nodal_billing_plan_updated',
        ]);
    }

    /** Teste 9: Campo code não é editável na atualização */
    public function test_09_code_field_is_immutable_on_update(): void
    {
        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/plans/plan-uuid-starter-111' => Http::response([], 200),
        ]);

        $payload = [
            'name'                => 'Starter Tentando Mudar Code',
            'code'                => 'tentativa-novo-code',
            'monthly_price_cents' => 59900,
            'is_active'           => 1,
        ];

        $this->actingAs($this->adminUser)->patch(route('nodal-plans.update', 'plan-uuid-starter-111'), $payload);

        Http::assertSent(function ($request) {
            return !array_key_exists('code', $request->data());
        });
    }

    /** Teste 10: Inativação / aposentadoria de plano */
    public function test_10_retires_plan_setting_is_active_false(): void
    {
        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/plans/plan-uuid-starter-111' => Http::response([], 200),
        ]);

        $payload = [
            'name'                => 'Starter Aposentado',
            'monthly_price_cents' => 59900,
            'is_active'           => 0,
        ];

        $response = $this->actingAs($this->adminUser)->patch(route('nodal-plans.update', 'plan-uuid-starter-111'), $payload);
        $response->assertRedirect(route('nodal-plans.index'));

        $this->assertDatabaseHas('automation_audit_logs', [
            'automation_key' => 'nodal_billing_plan_retired',
        ]);
    }

    /** Teste 11: Não existe rota ou método DELETE para planos */
    public function test_11_delete_route_does_not_exist(): void
    {
        $response = $this->actingAs($this->adminUser)->delete('/nodal-plans/plan-uuid-starter-111');
        $response->assertStatus(405); // Method Not Allowed
    }

    /** Teste 12: Plano ilimitado renderizado corretamente com legendas */
    public function test_12_renders_unlimited_plan_correctly(): void
    {
        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/plans*' => Http::response([
                'data' => [
                    [
                        'uuid'                 => 'plan-unlimited-1',
                        'name'                 => 'SacraTech Internal',
                        'code'                 => 'sacratech-internal',
                        'monthly_price_cents' => 0,
                        'is_public'           => false,
                        'is_unlimited'        => true,
                        'is_active'           => true,
                        'organizations_count' => 1,
                    ],
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('nodal-plans.index'));
        $response->assertStatus(200);
        $response->assertSee('∞ Ilimitado');
        $response->assertSee('Não aplicável');
    }

    /** Teste 13: Plano com mensalidade R$ 0,00 renderizado corretamente */
    public function test_13_renders_zero_price_plan_correctly(): void
    {
        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/plans*' => Http::response([
                'data' => [
                    [
                        'uuid'                 => 'plan-zero-1',
                        'name'                 => 'Plano Especial R$0',
                        'code'                 => 'plano-zero',
                        'monthly_price_cents' => 0,
                        'is_public'           => false,
                        'is_unlimited'        => false,
                        'is_active'           => true,
                        'organizations_count' => 0,
                    ],
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('nodal-plans.index'));
        $response->assertStatus(200);
        $response->assertSee('R$ 0,00');
        $response->assertSee('Oculto');
    }

    /** Teste 14: organizations_count exibido na tabela */
    public function test_14_displays_organizations_count(): void
    {
        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/plans*' => Http::response($this->mockPlansResponse(), 200),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('nodal-plans.index'));
        $response->assertStatus(200);
        $response->assertSee('12');
    }

    /** Teste 15: Atribuição de plano a uma organização */
    public function test_15_assigns_plan_to_organization(): void
    {
        $org = NodalOrganization::create([
            'nome'                    => 'Empresa Teste',
            'slug'                    => 'empresa-teste',
            'owner_name'              => 'Dono Teste',
            'owner_email'             => 'dono@teste.com',
            'nodal_organization_uuid' => 'org-uuid-999',
            'status'                  => 'active',
        ]);

        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/organizations/org-uuid-999/billing-plan' => Http::response(['success' => true], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->patch(route('nodal-plans.assign', 'org-uuid-999'), [
            'plan_uuid' => 'plan-uuid-starter-111',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return $request->url() === 'http://nodal.test/api/v1/internal/integer/billing/organizations/org-uuid-999/billing-plan' &&
                   $request['plan_uuid'] === 'plan-uuid-starter-111';
        });

        $this->assertDatabaseHas('automation_audit_logs', [
            'automation_key' => 'nodal_organization_plan_changed',
        ]);
    }

    /** Teste 16: Garantir uso correto do UUID público do Nodal */
    public function test_16_uses_nodal_public_uuid_for_assignment(): void
    {
        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/organizations/org-uuid-555/billing-plan' => Http::response([], 200),
        ]);

        $this->actingAs($this->adminUser)->patch(route('nodal-plans.assign', 'org-uuid-555'), [
            'plan_uuid' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'org-uuid-555') &&
                   $request['plan_uuid'] === '550e8400-e29b-41d4-a716-446655440000';
        });
    }

    /** Teste 17: Plano inativo não aparece na seleção de troca de plano */
    public function test_17_inactive_plans_are_excluded_from_org_edit_modal(): void
    {
        $org = NodalOrganization::create([
            'nome'                    => 'Empresa Inativo Teste',
            'owner_name'              => 'Resp',
            'owner_email'             => 'resp@teste.com',
            'nodal_organization_uuid' => 'org-uuid-123',
        ]);

        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/plans*' => Http::response([
                'data' => [
                    [
                        'uuid'      => 'plan-active-1',
                        'name'      => 'Plano Ativo Visível',
                        'is_public' => true,
                        'is_active' => true,
                    ],
                    [
                        'uuid'      => 'plan-inactive-1',
                        'name'      => 'Plano Inativo Escondido',
                        'is_public' => true,
                        'is_active' => false,
                    ],
                ]
            ], 200),
            'http://nodal.test/api/v1/internal/integer/billing/organizations/org-uuid-123/billing-plan' => Http::response([], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('nodal.edit', $org->id));
        $response->assertStatus(200);
        $response->assertSee('Plano Ativo Visível');
        $response->assertDontSee('Plano Inativo Escondido');
    }

    /** Teste 18: Confirmação de troca de plano */
    public function test_18_renders_plan_change_confirmation_modal_structure(): void
    {
        $org = NodalOrganization::create([
            'nome'                    => 'Diocese SP',
            'owner_name'              => 'Resp',
            'owner_email'             => 'resp@diocesesp.com',
            'nodal_organization_uuid' => 'org-uuid-777',
        ]);

        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/plans*' => Http::response($this->mockPlansResponse(), 200),
            'http://nodal.test/api/v1/internal/integer/billing/organizations/org-uuid-777/billing-plan' => Http::response([
                'name'                 => 'Starter',
                'monthly_price_cents' => 59900,
                'is_public'           => true,
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('nodal.edit', $org->id));
        $response->assertStatus(200);
        $response->assertSee('Alterar o plano da');
        $response->assertSee('A alteração entra em vigor imediatamente no período de faturamento aberto.');
    }

    /** Teste 19: API 422 exibida corretamente */
    public function test_19_displays_api_422_validation_error(): void
    {
        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/organizations/org-uuid-999/billing-plan' => Http::response([
                'message' => 'O plano selecionado é incompatível com esta organização.',
            ], 422),
        ]);

        $response = $this->actingAs($this->adminUser)->patch(route('nodal-plans.assign', 'org-uuid-999'), [
            'plan_uuid' => 'plan-incompativel',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'O plano selecionado é incompatível com esta organização.');
    }

    /** Teste 20: Indisponibilidade do Nodal tratada graciosamente */
    public function test_20_handles_nodal_api_unavailability(): void
    {
        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/plans*' => Http::response([], 500),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('nodal-plans.index'));
        $response->assertStatus(200);
        $response->assertSee('Não foi possível carregar os planos do Nodal.');
        $response->assertSee('Tentar novamente');
    }

    /** Teste 21: Usuário não admin é bloqueado pelo middleware */
    public function test_21_non_admin_user_is_blocked(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('nodal-plans.index'));
        // EnsureUserIsAdmin bloqueia ou redireciona não-admins (ex: 403 ou 302)
        $this->assertTrue(in_array($response->status(), [403, 302]));
    }

    /** Teste 22: Segredo INTEGER_ADMIN_API_KEY nunca vaza em logs ou responses */
    public function test_22_secret_api_key_is_never_exposed(): void
    {
        Http::fake([
            'http://nodal.test/api/v1/internal/integer/billing/plans*' => Http::response([], 500),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('nodal-plans.index'));

        // Garantir que a chave não aparece no HTML/Response
        $response->assertDontSee('test-admin-secret-key-12345');
    }
}
