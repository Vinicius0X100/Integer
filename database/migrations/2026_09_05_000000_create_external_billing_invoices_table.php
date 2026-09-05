<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('external_billing_invoices', function (Blueprint $table) {
            $table->id();

            // Identificadores Nodal
            $table->uuid('nodal_invoice_uuid')->unique();
            $table->uuid('nodal_organization_uuid')->index();

            // Dados da Empresa / Snapshot
            $table->string('company_name')->nullable();
            $table->string('trade_name')->nullable();
            $table->string('tax_id')->nullable()->index();
            $table->string('billing_email')->nullable();
            $table->boolean('fiscal_data_complete')->default(false);

            // Período e Competência
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->string('competence'); // Ex: YYYY-MM

            // Detalhes do Serviço e Plano
            $table->text('service_description')->nullable();
            $table->string('plan_name')->nullable();

            // Valores em Centavos
            $table->bigInteger('subscription_amount_cents')->default(0);
            $table->bigInteger('overage_amount_cents')->default(0);
            $table->bigInteger('total_amount_cents')->default(0);
            $table->string('currency', 10)->default('BRL');

            // Status da Invoice no Nodal
            $table->string('invoice_status');

            // Informações de Pagamento (Nullable)
            $table->string('payment_provider')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->nullable()->index();
            $table->timestamp('paid_at')->nullable();

            // Metadados de Sincronização
            $table->timestamp('source_updated_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            // Campos Exclusivos de Controle Fiscal Local (Integer)
            $table->string('fiscal_status')->default('pending');
            $table->string('nfse_number')->nullable();
            $table->timestamp('nfse_issued_at')->nullable();
            $table->text('fiscal_notes')->nullable();

            $table->timestamps();

            // Índices adicionais
            $table->index(['competence', 'fiscal_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_billing_invoices');
    }
};
