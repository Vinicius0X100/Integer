<?php

namespace Database\Factories;

use App\Enums\FiscalStatus;
use App\Models\ExternalBillingInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExternalBillingInvoice>
 */
class ExternalBillingInvoiceFactory extends Factory
{
    protected $model = ExternalBillingInvoice::class;

    public function definition(): array
    {
        $subCents = fake()->numberBetween(10000, 50000);
        $overageCents = fake()->numberBetween(0, 10000);

        return [
            'nodal_invoice_uuid'        => Str::uuid()->toString(),
            'nodal_organization_uuid'   => Str::uuid()->toString(),
            'company_name'              => fake()->company(),
            'trade_name'                => fake()->companySuffix(),
            'tax_id'                    => fake()->numerify('##.###.###/0001-##'),
            'billing_email'             => fake()->companyEmail(),
            'fiscal_data_complete'      => true,
            'period_start'              => now()->startOfMonth(),
            'period_end'                => now()->endOfMonth(),
            'competence'                => now()->format('Y-m'),
            'service_description'       => 'Assinatura Plataforma Nodal',
            'plan_name'                 => 'Plano Pro',
            'subscription_amount_cents' => $subCents,
            'overage_amount_cents'      => $overageCents,
            'total_amount_cents'        => $subCents + $overageCents,
            'currency'                  => 'BRL',
            'invoice_status'            => 'open',
            'payment_provider'          => 'asaas',
            'payment_method'            => 'pix',
            'payment_status'            => 'pending',
            'paid_at'                   => null,
            'source_updated_at'         => now(),
            'last_synced_at'            => now(),
            'fiscal_status'             => FiscalStatus::PENDING,
            'nfse_number'               => null,
            'nfse_issued_at'            => null,
            'fiscal_notes'              => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'paid',
            'invoice_status' => 'paid',
            'paid_at'        => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_provider' => null,
            'payment_method'   => null,
            'payment_status'   => null,
            'paid_at'          => null,
        ]);
    }

    public function incompleteFiscalData(): static
    {
        return $this->state(fn (array $attributes) => [
            'fiscal_data_complete' => false,
            'tax_id'               => null,
            'trade_name'           => null,
        ]);
    }
}
