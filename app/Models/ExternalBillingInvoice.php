<?php

namespace App\Models;

use App\Enums\FiscalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalBillingInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'nodal_invoice_uuid',
        'nodal_organization_uuid',
        'company_name',
        'trade_name',
        'tax_id',
        'billing_email',
        'fiscal_data_complete',
        'period_start',
        'period_end',
        'competence',
        'service_description',
        'plan_name',
        'subscription_amount_cents',
        'overage_amount_cents',
        'total_amount_cents',
        'currency',
        'invoice_status',
        'payment_provider',
        'payment_method',
        'payment_status',
        'paid_at',
        'source_updated_at',
        'last_synced_at',
        'fiscal_status',
        'nfse_number',
        'nfse_issued_at',
        'fiscal_notes',
    ];

    protected $casts = [
        'fiscal_data_complete'      => 'boolean',
        'period_start'              => 'datetime',
        'period_end'                => 'datetime',
        'paid_at'                   => 'datetime',
        'source_updated_at'         => 'datetime',
        'last_synced_at'            => 'datetime',
        'nfse_issued_at'            => 'datetime',
        'fiscal_status'             => FiscalStatus::class,
        'subscription_amount_cents' => 'integer',
        'overage_amount_cents'      => 'integer',
        'total_amount_cents'        => 'integer',
    ];

    public function getSubscriptionAmountFormattedAttribute(): string
    {
        return 'R$ ' . number_format(($this->subscription_amount_cents ?? 0) / 100, 2, ',', '.');
    }

    public function getOverageAmountFormattedAttribute(): string
    {
        return 'R$ ' . number_format(($this->overage_amount_cents ?? 0) / 100, 2, ',', '.');
    }

    public function getTotalAmountFormattedAttribute(): string
    {
        return 'R$ ' . number_format(($this->total_amount_cents ?? 0) / 100, 2, ',', '.');
    }
}
