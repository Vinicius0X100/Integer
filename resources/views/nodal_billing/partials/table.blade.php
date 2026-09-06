<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="min-width: 1000px;">
        <thead class="bg-light border-bottom">
            <tr>
                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0" style="min-width: 220px;">Empresa</th>
                <th class="px-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap">CNPJ</th>
                <th class="px-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap">Competência</th>
                <th class="px-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap">Plano / Serviço</th>
                <th class="px-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap text-end">Mensalidade</th>
                <th class="px-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap text-end">Adicional</th>
                <th class="px-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap text-end">Total</th>
                <th class="px-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap text-center">Pagamento</th>
                <th class="px-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap text-center">Status Fiscal</th>
                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $inv)
                <tr>
                    <td class="px-4 py-3 border-bottom-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center text-primary fw-bold me-3" style="width: 40px; height: 40px; font-size: 1rem;">
                                {{ strtoupper(substr($inv->company_name ?? $inv->trade_name ?? 'E', 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold text-reset">
                                    {{ $inv->company_name ?? $inv->trade_name ?? 'Empresa não informada' }}
                                    @if(!$inv->fiscal_data_complete)
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1 ms-1" style="font-size: 0.7rem;" title="Empresa com dados fiscais incompletos no Nodal">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Dados fiscais incompletos
                                        </span>
                                    @endif
                                </div>
                                @if($inv->trade_name && $inv->trade_name !== $inv->company_name)
                                    <div class="text-muted small">{{ $inv->trade_name }}</div>
                                @endif
                                <div class="text-muted small">E-mail: {{ $inv->billing_email ?? '—' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 border-bottom-0">
                        <span class="fw-mono text-muted small">{{ $inv->tax_id ?? 'Não informado' }}</span>
                    </td>
                    <td class="px-4 py-3 border-bottom-0">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">{{ $inv->competence }}</span>
                    </td>
                    <td class="px-4 py-3 border-bottom-0">
                        <div class="fw-medium text-reset">{{ $inv->plan_name ?? 'Sem Plano' }}</div>
                        <div class="text-muted small text-truncate" style="max-width: 200px;" title="{{ $inv->service_description }}">
                            {{ $inv->service_description ?? '—' }}
                        </div>
                    </td>
                    <td class="px-4 py-3 border-bottom-0 text-end">
                        <span class="text-reset">{{ $inv->subscription_amount_formatted }}</span>
                    </td>
                    <td class="px-4 py-3 border-bottom-0 text-end">
                        <span class="text-muted">{{ $inv->overage_amount_formatted }}</span>
                    </td>
                    <td class="px-4 py-3 border-bottom-0 text-end fw-bold text-reset">
                        {{ $inv->total_amount_formatted }}
                    </td>
                    <td class="px-4 py-3 border-bottom-0 text-center">
                        @php
                            $pStatus = strtolower($inv->payment_status ?? 'pending');
                            $pBadge = match($pStatus) {
                                'paid', 'confirmed' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
                                'overdue', 'failed' => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
                                default => 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25',
                            };
                            $pLabel = match($pStatus) {
                                'paid', 'confirmed' => 'Pago',
                                'overdue' => 'Vencido',
                                'failed'  => 'Falhou',
                                'refunded' => 'Reembolsado',
                                default => 'Pendente',
                            };
                        @endphp
                        <span class="badge {{ $pBadge }} rounded-pill px-3 py-1 mb-1 d-inline-block">
                            {{ $pLabel }}
                        </span>
                        @if($inv->payment_method || $inv->payment_provider)
                            <div class="text-muted" style="font-size: 0.75rem;">
                                {{ strtoupper($inv->payment_provider ?? '') }} {{ $inv->payment_method ? '('.strtoupper($inv->payment_method).')' : '' }}
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 border-bottom-0 text-center">
                        @if($inv->fiscal_status)
                            <span class="badge {{ $inv->fiscal_status->badgeClass() }} rounded-pill px-3 py-1">
                                {{ $inv->fiscal_status->label() }}
                            </span>
                        @else
                            <span class="badge bg-secondary rounded-pill px-3 py-1">Pendente</span>
                        @endif
                        @if($inv->nfse_number)
                            <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                NFS-e: <strong>#{{ $inv->nfse_number }}</strong>
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 border-bottom-0 text-end">
                        <a href="{{ route('nodal-billing.show', $inv->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            Detalhes <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-5">
                        <div class="mb-3">
                            <i class="bi bi-receipt-cutoff fs-1 opacity-25"></i>
                        </div>
                        <p class="mb-1 fw-medium fs-5">Nenhuma fatura disponível nesta competência.</p>
                        <p class="small text-white-50">As faturas sincronizadas do Nodal aparecerão aqui automaticamente.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($invoices->hasPages())
    <div class="card-footer bg-transparent border-0 px-4 py-3">
        {{ $invoices->links() }}
    </div>
@endif
