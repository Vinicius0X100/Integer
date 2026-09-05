@extends('layouts.app')

@section('page-title', 'Detalhes da Fatura Nodal')

@section('content')
<div class="container-fluid">
    {{-- Top Action Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('nodal-billing.index') }}" class="text-white-50 text-decoration-none small mb-2 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Voltar para Faturamento Nodal
            </a>
            <h2 class="fw-bold text-white mb-1">
                Fatura #{{ substr($invoice->nodal_invoice_uuid, 0, 8) }}...
            </h2>
            <p class="text-white-50 mb-0">Competência: <strong class="text-white">{{ $invoice->competence }}</strong> | Status Nodal: <span class="badge bg-secondary bg-opacity-25 text-white rounded-pill px-3">{{ strtoupper($invoice->invoice_status) }}</span></p>
        </div>
        <div>
            <span class="badge {{ $invoice->fiscal_status->badgeClass() }} rounded-pill px-4 py-2 fs-6">
                {{ $invoice->fiscal_status->label() }}
            </span>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$invoice->fiscal_data_complete)
        <div class="alert alert-warning border-0 rounded-4 shadow-sm mb-4 p-3 d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-3 text-warning me-3"></i>
            <div>
                <h6 class="fw-bold mb-1">Dados fiscais incompletos no Nodal</h6>
                <p class="mb-0 small text-white-50">Esta empresa possui cadastros pendentes no Nodal (como CNPJ, razão social ou e-mail financeiro ausentes). Não invente nem altere cadastros de origem diretamente.</p>
            </div>
        </div>
    @endif

    <div class="row g-4">
        {{-- Left Column: Info Cards --}}
        <div class="col-lg-7">
            {{-- Card Empresa --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-white mb-0"><i class="bi bi-building me-2 text-primary"></i> Dados da Empresa</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Razão Social</span>
                            <span class="fw-semibold text-reset fs-6">{{ $invoice->company_name ?? 'Não informada' }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Nome Fantasia</span>
                            <span class="fw-semibold text-reset fs-6">{{ $invoice->trade_name ?? '—' }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">CNPJ</span>
                            <span class="fw-mono text-reset fs-6">{{ $invoice->tax_id ?? 'Não informado' }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">E-mail Financeiro</span>
                            <span class="text-reset fs-6">{{ $invoice->billing_email ?? 'Não informado' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Cobrança e Valores --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-white mb-0"><i class="bi bi-cash-stack me-2 text-success"></i> Cobrança & Valores</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <span class="text-muted small d-block">Competência</span>
                            <span class="fw-bold fs-5 text-white">{{ $invoice->competence }}</span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small d-block">Plano</span>
                            <span class="fw-semibold text-reset fs-6">{{ $invoice->plan_name ?? 'Não informado' }}</span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small d-block">Serviço</span>
                            <span class="text-reset small d-block">{{ $invoice->service_description ?? '—' }}</span>
                        </div>
                    </div>
                    <hr class="border-secondary border-opacity-25 my-3">
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <span class="text-muted">Mensalidade:</span>
                        <span class="fw-medium text-reset">{{ $invoice->subscription_amount_formatted }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <span class="text-muted">Uso Adicional / Excedentes:</span>
                        <span class="fw-medium text-reset">{{ $invoice->overage_amount_formatted }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top border-secondary border-opacity-25">
                        <span class="fw-bold fs-5 text-white">Total da Fatura:</span>
                        <span class="fw-bold fs-4 text-success">{{ $invoice->total_amount_formatted }}</span>
                    </div>
                </div>
            </div>

            {{-- Card Pagamento --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-white mb-0"><i class="bi bi-credit-card me-2 text-info"></i> Status do Pagamento (Nodal)</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Provedor</span>
                            <span class="fw-semibold text-reset">{{ strtoupper($invoice->payment_provider ?? '—') }}</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Método</span>
                            <span class="fw-semibold text-reset">{{ strtoupper($invoice->payment_method ?? '—') }}</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Status Pagamento</span>
                            <span class="fw-semibold text-reset">{{ ucfirst($invoice->payment_status ?? 'Pendente') }}</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Data Pagamento</span>
                            <span class="fw-medium text-reset">{{ $invoice->paid_at ? $invoice->paid_at->format('d/m/Y H:i') : 'Não pago' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Fiscal Control Form (Integer Only) --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 100px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-white mb-0"><i class="bi bi-file-earmark-check me-2 text-warning"></i> Controle Fiscal Integer</h5>
                    <p class="text-white-50 small mb-0 mt-1">Estes campos pertencem exclusivamente ao Integer e nunca são sobrescritos pelo Nodal.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('nodal-billing.update-fiscal', $invoice->id) }}" method="POST">
                        @csrf

                        {{-- Status Fiscal --}}
                        <div class="mb-3">
                            <label for="fiscal_status" class="form-label text-muted small fw-medium">Status Fiscal Local</label>
                            <select name="fiscal_status" id="fiscal_status" class="form-select @error('fiscal_status') is-invalid @enderror" required>
                                @foreach($fiscalStatuses as $fStatus)
                                    <option value="{{ $fStatus->value }}" {{ (old('fiscal_status', $invoice->fiscal_status?->value) === $fStatus->value) ? 'selected' : '' }}>
                                        {{ $fStatus->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('fiscal_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Número NFS-e --}}
                        <div class="mb-3">
                            <label for="nfse_number" class="form-label text-muted small fw-medium">Número da NFS-e</label>
                            <input type="text" name="nfse_number" id="nfse_number" class="form-control @error('nfse_number') is-invalid @enderror" placeholder="Ex: 202600123" value="{{ old('nfse_number', $invoice->nfse_number) }}">
                            @error('nfse_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Data de Emissão --}}
                        <div class="mb-3">
                            <label for="nfse_issued_at" class="form-label text-muted small fw-medium">Data de Emissão da NFS-e</label>
                            <input type="date" name="nfse_issued_at" id="nfse_issued_at" class="form-control @error('nfse_issued_at') is-invalid @enderror" value="{{ old('nfse_issued_at', $invoice->nfse_issued_at ? $invoice->nfse_issued_at->format('Y-m-d') : '') }}">
                            @error('nfse_issued_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Observações Fiscais --}}
                        <div class="mb-4">
                            <label for="fiscal_notes" class="form-label text-muted small fw-medium">Observações Fiscais Internas</label>
                            <textarea name="fiscal_notes" id="fiscal_notes" rows="4" class="form-control @error('fiscal_notes') is-invalid @enderror" placeholder="Anotações para contabilidade ou histórico local...">{{ old('fiscal_notes', $invoice->fiscal_notes) }}</textarea>
                            @error('fiscal_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-warning w-100 rounded-pill py-2 font-weight-bold text-dark">
                            <i class="bi bi-save me-1"></i> Salvar Alterações Fiscais
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
