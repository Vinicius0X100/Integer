@extends('layouts.app')

@section('page-title', 'Financeiro — Faturamento Nodal')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-white mb-1 d-flex align-items-center">
                @if(file_exists(public_path('img/Nodal-Icon.png')))
                    <img src="{{ asset('img/Nodal-Icon.png') }}" alt="Nodal" style="height: 28px; width: auto; object-fit: contain; margin-right: 10px;">
                @else
                    <i class="bi bi-receipt-cutoff me-2"></i>
                @endif
                Faturamento Nodal
            </h2>
            <p class="text-white-50 mb-0">Painel administrativo e controle de emissão fiscal das faturas do Nodal.</p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            {{-- Botão de Exportação GET --}}
            <a href="{{ route('nodal-billing.export', request()->query()) }}" class="btn btn-outline-success rounded-pill px-4 py-2 shadow-sm">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i> Exportar Contabilidade (CSV)
            </a>

            {{-- Botão de Sync Manual --}}
            <form action="{{ route('nodal-billing.sync') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm">
                    <i class="bi bi-arrow-clockwise me-2"></i> Sincronizar com Nodal
                </button>
            </form>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('nodal-billing.index') }}" method="GET" id="filter-form">
                <div class="row g-3">
                    {{-- Busca --}}
                    <div class="col-md-3">
                        <label for="search" class="form-label text-muted small fw-medium">Empresa / CNPJ</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" id="search" class="form-control border-start-0 ps-0" placeholder="Nome, Razão Social ou CNPJ..." value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Competência --}}
                    <div class="col-md-2">
                        <label for="period" class="form-label text-muted small fw-medium">Competência</label>
                        <select name="period" id="period" class="form-select">
                            <option value="">Todas</option>
                            @foreach($availableCompetences as $comp)
                                <option value="{{ $comp }}" {{ request('period') === $comp ? 'selected' : '' }}>{{ $comp }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Pagamento --}}
                    <div class="col-md-2">
                        <label for="payment_status" class="form-label text-muted small fw-medium">Pagamento</label>
                        <select name="payment_status" id="payment_status" class="form-select">
                            <option value="">Todos</option>
                            <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                            <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Pago</option>
                            <option value="overdue" {{ request('payment_status') === 'overdue' ? 'selected' : '' }}>Vencido</option>
                            <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Falhou</option>
                            <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Reembolsado</option>
                        </select>
                    </div>

                    {{-- Status Fiscal --}}
                    <div class="col-md-3">
                        <label for="fiscal_status" class="form-label text-muted small fw-medium">Status Fiscal</label>
                        <select name="fiscal_status" id="fiscal_status" class="form-select">
                            <option value="">Todos</option>
                            @foreach($fiscalStatuses as $fStatus)
                                <option value="{{ $fStatus->value }}" {{ request('fiscal_status') === $fStatus->value ? 'selected' : '' }}>
                                    {{ $fStatus->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dados Incompletos & Botões --}}
                    <div class="col-md-2 d-flex flex-column justify-content-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="incomplete_fiscal_data" value="1" id="incomplete_fiscal_data" {{ request('incomplete_fiscal_data') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label small text-muted" for="incomplete_fiscal_data">
                                Dados incompletos
                            </label>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100 py-2">
                                <i class="bi bi-filter me-1"></i> Filtrar
                            </button>
                            <a href="{{ route('nodal-billing.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2" title="Limpar Filtros">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0" id="table-container">
            @include('nodal_billing.partials.table')
        </div>
    </div>
</div>
@endsection
