@extends('layouts.app')

@section('page-title', 'Financeiro — Faturamento Nodal')

@section('content')
<div class="container-fluid px-2 px-md-4 py-2">
    {{-- Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-white mb-1 h3 h2-md d-flex align-items-center">
                @if(file_exists(public_path('img/Nodal-Icon.png')))
                    <img src="{{ asset('img/Nodal-Icon.png') }}" alt="Nodal" style="height: 28px; width: auto; object-fit: contain; margin-right: 10px;">
                @else
                    <i class="bi bi-receipt-cutoff me-2 text-primary"></i>
                @endif
                Faturamento Nodal
            </h2>
            <p class="text-white-50 mb-0 small">Painel administrativo e controle de emissão fiscal das faturas do Nodal.</p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap w-100 w-sm-auto justify-content-sm-end">
            {{-- Botão de Exportação GET --}}
            <a href="{{ route('nodal-billing.export', request()->query()) }}" class="btn btn-outline-success rounded-pill px-3 px-sm-4 py-2 shadow-sm flex-grow-1 flex-sm-grow-0 text-center text-nowrap">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i> Exportar (CSV)
            </a>

            {{-- Botão de Sync Manual --}}
            <form action="{{ route('nodal-billing.sync') }}" method="POST" class="d-inline flex-grow-1 flex-sm-grow-0">
                @csrf
                <button type="submit" class="btn btn-primary rounded-pill px-3 px-sm-4 py-2 shadow-sm w-100 text-nowrap">
                    <i class="bi bi-arrow-clockwise me-2"></i> Sincronizar
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
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-dark bg-opacity-50 border-secondary border-opacity-10">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('nodal-billing.index') }}" method="GET" id="filter-form">
                <div class="row g-3">
                    {{-- Busca --}}
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label for="search" class="form-label text-muted small fw-medium">Empresa / CNPJ</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-secondary border-opacity-25 text-white-50"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" id="search" class="form-control bg-transparent border-secondary border-opacity-25 text-white" placeholder="Nome, Razão Social ou CNPJ..." value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Competência --}}
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="period" class="form-label text-muted small fw-medium">Competência</label>
                        <select name="period" id="period" class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                            <option value="" class="bg-dark text-white">Todas</option>
                            @foreach($availableCompetences as $comp)
                                <option value="{{ $comp }}" {{ request('period') === $comp ? 'selected' : '' }} class="bg-dark text-white">{{ $comp }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Pagamento --}}
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="payment_status" class="form-label text-muted small fw-medium">Pagamento</label>
                        <select name="payment_status" id="payment_status" class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                            <option value="" class="bg-dark text-white">Todos</option>
                            <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }} class="bg-dark text-white">Pendente</option>
                            <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }} class="bg-dark text-white">Pago</option>
                            <option value="overdue" {{ request('payment_status') === 'overdue' ? 'selected' : '' }} class="bg-dark text-white">Vencido</option>
                            <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }} class="bg-dark text-white">Falhou</option>
                            <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }} class="bg-dark text-white">Reembolsado</option>
                        </select>
                    </div>

                    {{-- Status Fiscal --}}
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label for="fiscal_status" class="form-label text-muted small fw-medium">Status Fiscal</label>
                        <select name="fiscal_status" id="fiscal_status" class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                            <option value="" class="bg-dark text-white">Todos</option>
                            @foreach($fiscalStatuses as $fStatus)
                                <option value="{{ $fStatus->value }}" {{ request('fiscal_status') === $fStatus->value ? 'selected' : '' }} class="bg-dark text-white">
                                    {{ $fStatus->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dados Incompletos & Botões --}}
                    <div class="col-12 col-lg-2 d-flex flex-column justify-content-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="incomplete_fiscal_data" value="1" id="incomplete_fiscal_data" {{ request('incomplete_fiscal_data') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label small text-muted text-nowrap" for="incomplete_fiscal_data">
                                Dados incompletos
                            </label>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 text-nowrap">
                                <i class="bi bi-filter me-1"></i> Filtrar
                            </button>
                            @if(request()->anyFilled(['search', 'period', 'payment_status', 'fiscal_status', 'incomplete_fiscal_data']))
                                <a href="{{ route('nodal-billing.index') }}" class="btn btn-outline-light rounded-circle flex-shrink-0" title="Limpar Filtros"><i class="bi bi-x-lg"></i></a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-body p-0" id="table-container">
            @include('nodal_billing.partials.table')
        </div>
    </div>
</div>
@endsection
