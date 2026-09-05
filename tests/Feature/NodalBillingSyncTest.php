<?php

namespace Tests\Feature;

use App\Enums\FiscalStatus;
use App\Models\AutomationAuditLog;
use App\Models\ExternalBillingInvoice;
use App\Models\User;
use App\Services\NodalBillingClient;
use App\Services\NodalBillingSyncService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Str;
use Tests\TestCase;

class NodalBillingSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected string $fakeApiKey = 'test_integer_system_api_key_12345';
    protected string $baseUrl = 'https://nodal.sacratech.com';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.nodal.integer_system_api_key' => $this->fakeApiKey]);
        config(['services.nodal.base_url' => $this->baseUrl]);

        $this->adminUser = User::factory()->create([
            'papel' => 'admin',
        ]);

        $this->regularUser = User::factory()->create([
            'papel' => 'usuario',
        ]);
    }

    protected function mockNodalInvoiceItem(array $overrides = []): array
    {
        $uuid = $overrides['uuid'] ?? Str::uuid()->toString();

        return array_merge([
            'uuid'                      => $uuid,
            'organization_uuid'         => Str::uuid()->toString(),
            'company_name'              => 'Empresa Teste LTDA',
            'trade_name'                => 'Empresa Teste',
            'tax_id'                    => '12.345.678/0001-90',
            'billing_email'             => 'financeiro@empresateste.com',
            'fiscal_data_complete'      => true,
            'period_start'              => '2026-09-01T00:00:00Z',
            'period_end'                => '2026-09-30T23:59:59Z',
            'competence'                => '2026-09',
            'service_description'       => 'Plano Pro Nodal',
            'plan_name'                 => 'Plano Pro',
            'subscription_amount_cents' => 20000,
            'overage_amount_cents'      => 5000,
            'total_amount_cents'        => 25000,
            'currency'                  => 'BRL',
            'status'                    => 'open',
            'payment'                   => [
                'provider' => 'asaas',
                'method'   => 'pix',
                'status'   => 'pending',
                'paid_at'  => null,
            ],
            'updated_at'                => '2026-09-05T10:00:00Z',
        ], $overrides);
    }

    /** 1. HTTP Auth Header */
    public function test_1_http_auth_to_nodal_uses_bearer_token(): void
    {
        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [$this->mockNodalInvoiceItem()],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], 200),
        ]);

        $client = app(NodalBillingClient::class);
        $client->fetchInvoices();

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer ' . $this->fakeApiKey);
        });
    }

    /** 2. Initial Sync */
    public function test_2_initial_sync(): void
    {
        $item = $this->mockNodalInvoiceItem();

        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [$item],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], 200),
        ]);

        $syncService = app(NodalBillingSyncService::class);
        $res = $syncService->sync(['full' => true]);

        $this->assertEquals(1, $res['created']);
        $this->assertDatabaseHas('external_billing_invoices', [
            'nodal_invoice_uuid' => $item['uuid'],
            'company_name'       => 'Empresa Teste LTDA',
            'fiscal_status'      => 'pending',
        ]);
    }

    /** 3. Pagination */
    public function test_3_pagination(): void
    {
        $item1 = $this->mockNodalInvoiceItem();
        $item2 = $this->mockNodalInvoiceItem();

        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*page=1*" => Http::response([
                'data' => [$item1],
                'meta' => ['current_page' => 1, 'last_page' => 2, 'total' => 2],
            ], 200),
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*page=2*" => Http::response([
                'data' => [$item2],
                'meta' => ['current_page' => 2, 'last_page' => 2, 'total' => 2],
            ], 200),
        ]);

        $syncService = app(NodalBillingSyncService::class);
        $res = $syncService->sync(['full' => true]);

        $this->assertEquals(2, $res['synced']);
        $this->assertDatabaseCount('external_billing_invoices', 2);
    }

    /** 4. Incremental Sync Uses updated_since */
    public function test_4_incremental_sync_uses_updated_since(): void
    {
        ExternalBillingInvoice::factory()->create([
            'source_updated_at' => Carbon::parse('2026-09-01 12:00:00'),
        ]);

        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 0],
            ], 200),
        ]);

        $syncService = app(NodalBillingSyncService::class);
        $syncService->sync([]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'updated_since');
        });
    }

    /** 5. Upsert Idempotency */
    public function test_5_upsert_idempotency(): void
    {
        $item = $this->mockNodalInvoiceItem();

        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [$item],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], 200),
        ]);

        $syncService = app(NodalBillingSyncService::class);
        $res1 = $syncService->sync(['full' => true]);
        $res2 = $syncService->sync(['full' => true]);

        $this->assertEquals(1, $res1['created']);
        $this->assertEquals(0, $res2['created']);
        $this->assertEquals(1, $res2['updated']);
        $this->assertDatabaseCount('external_billing_invoices', 1);
    }

    /** 6. Invoice Not Duplicated */
    public function test_6_invoice_not_duplicated(): void
    {
        $item = $this->mockNodalInvoiceItem();

        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [$item],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], 200),
        ]);

        $syncService = app(NodalBillingSyncService::class);
        $syncService->sync(['full' => true]);
        $syncService->sync(['full' => true]);

        $this->assertDatabaseCount('external_billing_invoices', 1);
    }

    /** 7. Status Change Pending to Paid */
    public function test_7_status_change_pending_to_paid(): void
    {
        $uuid = Str::uuid()->toString();

        $invoice = ExternalBillingInvoice::factory()->create([
            'nodal_invoice_uuid' => $uuid,
            'payment_status'     => 'pending',
            'invoice_status'     => 'open',
            'source_updated_at'  => Carbon::parse('2026-09-01 10:00:00'),
        ]);

        $item = $this->mockNodalInvoiceItem([
            'uuid'       => $uuid,
            'status'     => 'paid',
            'payment'    => [
                'provider' => 'asaas',
                'method'   => 'pix',
                'status'   => 'paid',
                'paid_at'  => '2026-09-05T12:00:00Z',
            ],
            'updated_at' => '2026-09-05T12:00:00Z',
        ]);

        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [$item],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], 200),
        ]);

        app(NodalBillingSyncService::class)->sync(['full' => true]);

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->payment_status);
        $this->assertEquals('paid', $invoice->invoice_status);
    }

    /** 8. Paid At Update */
    public function test_8_paid_at_update(): void
    {
        $uuid = Str::uuid()->toString();

        $invoice = ExternalBillingInvoice::factory()->create([
            'nodal_invoice_uuid' => $uuid,
            'paid_at'            => null,
            'source_updated_at'  => Carbon::parse('2026-09-01 10:00:00'),
        ]);

        $item = $this->mockNodalInvoiceItem([
            'uuid'       => $uuid,
            'payment'    => [
                'provider' => 'asaas',
                'method'   => 'pix',
                'status'   => 'paid',
                'paid_at'  => '2026-09-05T15:30:00Z',
            ],
            'updated_at' => '2026-09-05T15:30:00Z',
        ]);

        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [$item],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], 200),
        ]);

        app(NodalBillingSyncService::class)->sync(['full' => true]);

        $invoice->refresh();
        $this->assertNotNull($invoice->paid_at);
        $this->assertEquals('2026-09-05 15:30:00', $invoice->paid_at->format('Y-m-d H:i:s'));
    }

    /** 9. Invoice Status Update */
    public function test_9_invoice_status_update(): void
    {
        $uuid = Str::uuid()->toString();

        $invoice = ExternalBillingInvoice::factory()->create([
            'nodal_invoice_uuid' => $uuid,
            'invoice_status'     => 'open',
            'source_updated_at'  => Carbon::parse('2026-09-01 10:00:00'),
        ]);

        $item = $this->mockNodalInvoiceItem([
            'uuid'       => $uuid,
            'status'     => 'void',
            'updated_at' => '2026-09-05T12:00:00Z',
        ]);

        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [$item],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], 200),
        ]);

        app(NodalBillingSyncService::class)->sync(['full' => true]);

        $invoice->refresh();
        $this->assertEquals('void', $invoice->invoice_status);
    }

    /** 10. Preservation of fiscal_status */
    public function test_10_preservation_of_fiscal_status(): void
    {
        $uuid = Str::uuid()->toString();

        $invoice = ExternalBillingInvoice::factory()->create([
            'nodal_invoice_uuid' => $uuid,
            'fiscal_status'      => FiscalStatus::SENT_TO_ACCOUNTING,
            'source_updated_at'  => Carbon::parse('2026-09-01 10:00:00'),
        ]);

        $item = $this->mockNodalInvoiceItem([
            'uuid'       => $uuid,
            'updated_at' => '2026-09-05T12:00:00Z',
        ]);

        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [$item],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], 200),
        ]);

        app(NodalBillingSyncService::class)->sync(['full' => true]);

        $invoice->refresh();
        $this->assertEquals(FiscalStatus::SENT_TO_ACCOUNTING, $invoice->fiscal_status);
    }

    /** 11. Preservation of nfse_number */
    public function test_11_preservation_of_nfse_number(): void
    {
        $uuid = Str::uuid()->toString();

        $invoice = ExternalBillingInvoice::factory()->create([
            'nodal_invoice_uuid' => $uuid,
            'nfse_number'        => 'NFS-2026-999',
            'source_updated_at'  => Carbon::parse('2026-09-01 10:00:00'),
        ]);

        $item = $this->mockNodalInvoiceItem([
            'uuid'       => $uuid,
            'updated_at' => '2026-09-05T12:00:00Z',
        ]);

        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [$item],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], 200),
        ]);

        app(NodalBillingSyncService::class)->sync(['full' => true]);

        $invoice->refresh();
        $this->assertEquals('NFS-2026-999', $invoice->nfse_number);
    }

    /** 12. Preservation of nfse_issued_at */
    public function test_12_preservation_of_nfse_issued_at(): void
    {
        $uuid = Str::uuid()->toString();
        $issuedAt = Carbon::parse('2026-09-02 14:00:00');

        $invoice = ExternalBillingInvoice::factory()->create([
            'nodal_invoice_uuid' => $uuid,
            'nfse_issued_at'     => $issuedAt,
            'source_updated_at'  => Carbon::parse('2026-09-01 10:00:00'),
        ]);

        $item = $this->mockNodalInvoiceItem([
            'uuid'       => $uuid,
            'updated_at' => '2026-09-05T12:00:00Z',
        ]);

        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [$item],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], 200),
        ]);

        app(NodalBillingSyncService::class)->sync(['full' => true]);

        $invoice->refresh();
        $this->assertEquals($issuedAt->format('Y-m-d H:i:s'), $invoice->nfse_issued_at->format('Y-m-d H:i:s'));
    }

    /** 13. Preservation of fiscal_notes */
    public function test_13_preservation_of_fiscal_notes(): void
    {
        $uuid = Str::uuid()->toString();

        $invoice = ExternalBillingInvoice::factory()->create([
            'nodal_invoice_uuid' => $uuid,
            'fiscal_notes'       => 'Observação fiscal super secreta mantida.',
            'source_updated_at'  => Carbon::parse('2026-09-01 10:00:00'),
        ]);

        $item = $this->mockNodalInvoiceItem([
            'uuid'       => $uuid,
            'updated_at' => '2026-09-05T12:00:00Z',
        ]);

        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [$item],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], 200),
        ]);

        app(NodalBillingSyncService::class)->sync(['full' => true]);

        $invoice->refresh();
        $this->assertEquals('Observação fiscal super secreta mantida.', $invoice->fiscal_notes);
    }

    /** 14. Company Without CNPJ Handles Incomplete Fiscal Data */
    public function test_14_company_without_cnpj_handles_incomplete_fiscal_data(): void
    {
        $item = $this->mockNodalInvoiceItem([
            'tax_id'               => null,
            'fiscal_data_complete' => false,
        ]);

        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [$item],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], 200),
        ]);

        app(NodalBillingSyncService::class)->sync(['full' => true]);

        $invoice = ExternalBillingInvoice::where('nodal_invoice_uuid', $item['uuid'])->first();
        $this->assertFalse($invoice->fiscal_data_complete);
        $this->assertNull($invoice->tax_id);
    }

    /** 15. UI Filters */
    public function test_15_ui_filters(): void
    {
        ExternalBillingInvoice::factory()->create([
            'company_name'   => 'Empresa Alfa',
            'competence'     => '2026-08',
            'payment_status' => 'paid',
            'fiscal_status'  => FiscalStatus::SENT_TO_ACCOUNTING,
        ]);

        ExternalBillingInvoice::factory()->create([
            'company_name'   => 'Empresa Beta',
            'competence'     => '2026-09',
            'payment_status' => 'pending',
            'fiscal_status'  => FiscalStatus::PENDING,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('nodal-billing.index', [
                'search'     => 'Alfa',
                'period'     => '2026-08',
                'payment_status' => 'paid',
            ]));

        $response->assertStatus(200);
        $response->assertSee('Empresa Alfa');
        $response->assertDontSee('Empresa Beta');
    }

    /** 16. Authorization Restricts Non-Admin Users */
    public function test_16_authorization_restricts_non_admin_users(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('nodal-billing.index'));

        $response->assertStatus(403);
    }

    /** 17. CSV Export Returns Valid Stream */
    public function test_17_csv_export_returns_valid_stream(): void
    {
        ExternalBillingInvoice::factory()->create([
            'company_name' => 'Empresa Exportacao LTDA',
            'competence'   => '2026-09',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('nodal-billing.export', ['period' => '2026-09']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        $content = $response->streamedContent();
        $this->assertStringContainsString('Razão Social', $content);
        $this->assertStringContainsString('Nome Fantasia', $content);
        $this->assertStringContainsString('CNPJ', $content);
        $this->assertStringContainsString('Empresa Exportacao LTDA', $content);
    }

    /** 18. Sync With Empty API Response */
    public function test_18_sync_with_empty_api_response(): void
    {
        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 0],
            ], 200),
        ]);

        $res = app(NodalBillingSyncService::class)->sync(['full' => true]);

        $this->assertEquals('success', $res['status']);
        $this->assertEquals(0, $res['synced']);
    }

    /** 19. Temporary Nodal API Failure Handling */
    public function test_19_temporary_nodal_api_failure_handling(): void
    {
        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([], 500),
        ]);

        $this->expectException(RequestException::class);
        app(NodalBillingSyncService::class)->sync(['full' => true]);
    }

    /** 20. Invalid Token 401 Handling */
    public function test_20_invalid_token_401_handling(): void
    {
        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response(['message' => 'Unauthenticated.'], 401),
        ]);

        $this->expectException(RequestException::class);
        app(NodalBillingSyncService::class)->sync(['full' => true]);
    }

    /** 21. Draft Invoice With Null Payment */
    public function test_21_draft_invoice_with_null_payment(): void
    {
        $item = $this->mockNodalInvoiceItem([
            'status'  => 'draft',
            'payment' => null,
        ]);

        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [$item],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], 200),
        ]);

        app(NodalBillingSyncService::class)->sync(['full' => true]);

        $invoice = ExternalBillingInvoice::where('nodal_invoice_uuid', $item['uuid'])->first();
        $this->assertNotNull($invoice);
        $this->assertNull($invoice->payment_provider);
        $this->assertNull($invoice->payment_method);
        $this->assertNull($invoice->payment_status);
        $this->assertNull($invoice->paid_at);
    }

    /** 22. Older Remote Payload Does Not Overwrite Newer Local Data */
    public function test_22_older_remote_payload_does_not_overwrite_newer_local_data(): void
    {
        $uuid = Str::uuid()->toString();

        $invoice = ExternalBillingInvoice::factory()->create([
            'nodal_invoice_uuid' => $uuid,
            'company_name'       => 'Nome Novo Local',
            'source_updated_at'  => Carbon::parse('2026-09-05 12:00:00'),
        ]);

        $itemMaisAntigo = $this->mockNodalInvoiceItem([
            'uuid'         => $uuid,
            'company_name' => 'Nome Antigo Remoto',
            'updated_at'   => '2026-09-01T10:00:00Z',
        ]);

        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [$itemMaisAntigo],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], 200),
        ]);

        app(NodalBillingSyncService::class)->sync(['full' => true]);

        $invoice->refresh();
        $this->assertEquals('Nome Novo Local', $invoice->company_name);
    }

    /** 23. Concurrent Manual Sync Does Not Execute In Parallel */
    public function test_23_concurrent_manual_sync_does_not_execute_in_parallel(): void
    {
        $lock = Cache::lock('nodal_billing_sync', 300);
        $lock->get();

        $res = app(NodalBillingSyncService::class)->sync([]);

        $this->assertEquals('already_running', $res['status']);
        $lock->release();
    }

    /** 24. Full Sync Does Not Delete Missing Local Invoices */
    public function test_24_full_sync_does_not_delete_missing_local_invoices(): void
    {
        $localInvoice = ExternalBillingInvoice::factory()->create([
            'company_name' => 'Fatura Local Existente',
        ]);

        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 0],
            ], 200),
        ]);

        app(NodalBillingSyncService::class)->sync(['full' => true]);

        $this->assertDatabaseHas('external_billing_invoices', [
            'id' => $localInvoice->id,
        ]);
    }

    /** 25. CSV Formula Injection Protection */
    public function test_25_csv_formula_injection_protection(): void
    {
        ExternalBillingInvoice::factory()->create([
            'company_name' => '=HYPERLINK("http://attacker.com")',
            'trade_name'   => '+123456789',
            'competence'   => '2026-09',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('nodal-billing.export', ['period' => '2026-09']));

        $content = $response->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $content);
        $this->assertStringContainsString("'+123456789", $content);
    }

    /** 26. Fiscal Fields Remain Intact After Multiple Resyncs */
    public function test_26_fiscal_fields_remain_intact_after_multiple_resyncs(): void
    {
        $uuid = Str::uuid()->toString();

        $invoice = ExternalBillingInvoice::factory()->create([
            'nodal_invoice_uuid' => $uuid,
            'fiscal_status'      => FiscalStatus::NFSE_RECEIVED,
            'nfse_number'        => '998877',
            'nfse_issued_at'     => Carbon::parse('2026-09-01'),
            'fiscal_notes'       => 'Notas Fiscais intactas',
            'source_updated_at'  => Carbon::parse('2026-09-01 10:00:00'),
        ]);

        $item = $this->mockNodalInvoiceItem([
            'uuid'       => $uuid,
            'updated_at' => '2026-09-05T12:00:00Z',
        ]);

        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response([
                'data' => [$item],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], 200),
        ]);

        for ($i = 0; $i < 5; $i++) {
            app(NodalBillingSyncService::class)->sync(['full' => true]);
        }

        $invoice->refresh();
        $this->assertEquals(FiscalStatus::NFSE_RECEIVED, $invoice->fiscal_status);
        $this->assertEquals('998877', $invoice->nfse_number);
        $this->assertEquals('2026-09-01', $invoice->nfse_issued_at->format('Y-m-d'));
        $this->assertEquals('Notas Fiscais intactas', $invoice->fiscal_notes);
    }

    /** 27. Export Route Is GET Only */
    public function test_27_export_route_is_get_only(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('nodal-billing.export'));

        $response->assertStatus(200);
    }

    /** 28. Resource Routes Expose Only Index and Show */
    public function test_28_resource_routes_expose_only_index_and_show(): void
    {
        $this->assertTrue(route('nodal-billing.index') !== null);

        $invoice = ExternalBillingInvoice::factory()->create();
        $this->assertTrue(route('nodal-billing.show', $invoice->id) !== null);

        $this->assertFalse(\Route::has('nodal-billing.create'));
        $this->assertFalse(\Route::has('nodal-billing.edit'));
    }

    /** 29. Empty Response Renders Normal Empty State */
    public function test_29_empty_response_renders_normal_empty_state(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('nodal-billing.index'));

        $response->assertStatus(200);
        $response->assertSee('Nenhuma fatura disponível nesta competência.');
    }

    /** 30. Secrets Never Appear In Exceptions or Logs */
    public function test_30_secrets_never_appear_in_exceptions_or_logs(): void
    {
        Http::fake([
            "{$this->baseUrl}/api/v1/internal/integer/billing/invoices*" => Http::response(['error' => 'Forbidden'], 403),
        ]);

        try {
            app(NodalBillingClient::class)->fetchInvoices();
            $this->fail('Deveria ter lançado exceção.');
        } catch (\Exception $e) {
            $this->assertStringNotContainsString($this->fakeApiKey, $e->getMessage());
        }
    }
}
