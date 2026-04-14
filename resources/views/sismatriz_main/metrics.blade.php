@extends('layouts.app')

@section('page-title', 'Métricas e KPIs - SisMatriz')

@section('content')
<div class="container-fluid">
    <!-- Header & Filters -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-bar-chart-fill fs-4"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold">Métricas e KPIs</h5>
                <small class="text-muted">Análise quantitativa e acessos em tempo real</small>
            </div>
        </div>

        <form action="{{ route('sismatriz-main.metrics') }}" method="GET" class="d-flex flex-wrap gap-2 align-items-end">
            <div class="d-flex flex-column">
                <label class="small text-muted fw-bold ms-2 mb-1">Paróquia</label>
                <select name="paroquia_id" class="form-select bg-white border-0 shadow-sm rounded-pill" style="min-width: 200px;" onchange="this.form.submit()">
                    <option value="">Todas as Paróquias</option>
                    @foreach($paroquias as $paroquia)
                        <option value="{{ $paroquia->id }}" {{ $selectedParoquia == $paroquia->id ? 'selected' : '' }}>
                            {{ $paroquia->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex flex-column">
                <label class="small text-muted fw-bold ms-2 mb-1">Cargo</label>
                <select name="role" class="form-select bg-white border-0 shadow-sm rounded-pill" style="min-width: 150px;" onchange="this.form.submit()">
                    <option value="">Todos os Cargos</option>
                    @foreach($roles as $id => $name)
                        <option value="{{ $id }}" {{ $selectedRole == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex flex-column">
                <label class="small text-muted fw-bold ms-2 mb-1">De</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control bg-white border-0 shadow-sm rounded-pill" onchange="this.form.submit()">
            </div>

            <div class="d-flex flex-column">
                <label class="small text-muted fw-bold ms-2 mb-1">Até</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control bg-white border-0 shadow-sm rounded-pill" onchange="this.form.submit()">
            </div>
            
            <div class="mb-1">
                <button type="submit" class="btn btn-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Section: Usuários e Acessos -->
    <h6 class="text-uppercase text-muted fw-bold mb-3 ps-2 border-start border-4 border-primary">Usuários e Acessos</h6>
    <!-- Cards Row -->
    <div class="row g-4 mb-4">
        <!-- Acolytes / Users Count -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-start z-1 position-relative">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold mb-1 d-flex align-items-center gap-1">
                                {{ $selectedRole ? 'Usuários (Filtro)' : 'Acólitos' }}
                                <i class="bi bi-question-circle-fill text-muted opacity-50" data-bs-toggle="tooltip" title="Total de usuários ativos com o cargo selecionado (Padrão: Acólitos)."></i>
                            </h6>
                            <h2 class="display-5 fw-bold mb-0 text-primary">{{ number_format($usersCount, 0, ',', '.') }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Accesses -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-start z-1 position-relative">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold mb-1 d-flex align-items-center gap-1">
                                Acessos
                                <i class="bi bi-question-circle-fill text-muted opacity-50" data-bs-toggle="tooltip" title="Total de logins realizados."></i>
                            </h6>
                            <h2 class="display-5 fw-bold mb-0 text-success">{{ number_format($totalAccesses, 0, ',', '.') }}</h2>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-3">
                            <i class="bi bi-activity fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Web Accesses -->
        <div class="col-12 col-md-6 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 text-center">
                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-inline-flex p-3 mb-3">
                        <i class="bi bi-globe fs-4"></i>
                    </div>
                    <h6 class="text-muted small text-uppercase fw-bold mb-1">Site</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($webAccesses, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>

        <!-- Android Accesses -->
        <div class="col-12 col-md-6 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 text-center">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex p-3 mb-3">
                        <i class="bi bi-android fs-4"></i>
                    </div>
                    <h6 class="text-muted small text-uppercase fw-bold mb-1">Android</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($androidAccesses, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>


        <!-- iOS Accesses -->
        <div class="col-12 col-md-6 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 text-center">
                    <div class="bg-dark bg-opacity-10 text-dark rounded-circle d-inline-flex p-3 mb-3">
                        <i class="bi bi-apple fs-4"></i>
                    </div>
                    <h6 class="text-muted small text-uppercase fw-bold mb-1">iOS</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($iosAccesses, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-start z-1 position-relative">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold mb-1 d-flex align-items-center gap-1">
                                Usuários ativos
                                <i class="bi bi-question-circle-fill text-muted opacity-50" data-bs-toggle="tooltip" title="Total de usuários ativos (status = 0) considerando os filtros selecionados."></i>
                            </h6>
                            <h2 class="display-5 fw-bold mb-0 text-success">{{ number_format($usersActiveCount, 0, ',', '.') }}</h2>
                            <div class="small text-muted mt-2">Total: {{ number_format($usersTotal, 0, ',', '.') }}</div>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-3">
                            <i class="bi bi-person-check-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-start z-1 position-relative">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold mb-1 d-flex align-items-center gap-1">
                                Usuários inativos
                                <i class="bi bi-question-circle-fill text-muted opacity-50" data-bs-toggle="tooltip" title="Total de usuários inativos (status = 1) considerando os filtros selecionados."></i>
                            </h6>
                            <h2 class="display-5 fw-bold mb-0 text-danger">{{ number_format($usersInactiveCount, 0, ',', '.') }}</h2>
                            <div class="small text-muted mt-2">Total: {{ number_format($usersTotal, 0, ',', '.') }}</div>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-3">
                            <i class="bi bi-person-x-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-start z-1 position-relative">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold mb-1 d-flex align-items-center gap-1">
                                Senha alterada
                                <i class="bi bi-question-circle-fill text-muted opacity-50" data-bs-toggle="tooltip" title="Usuários que já alteraram a senha (is_pass_change = 1) considerando os filtros selecionados."></i>
                            </h6>
                            <h2 class="display-5 fw-bold mb-0 text-primary">{{ number_format($usersPasswordChangedCount, 0, ',', '.') }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                            <i class="bi bi-shield-lock-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-start z-1 position-relative">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold mb-1 d-flex align-items-center gap-1">
                                Senha padrão
                                <i class="bi bi-question-circle-fill text-muted opacity-50" data-bs-toggle="tooltip" title="Usuários que ainda não alteraram a senha (is_pass_change = 0) considerando os filtros selecionados."></i>
                            </h6>
                            <h2 class="display-5 fw-bold mb-0 text-warning">{{ number_format($usersPasswordDefaultCount, 0, ',', '.') }}</h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                            <i class="bi bi-key-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Registros e Sacramentos -->
    <h6 class="text-uppercase text-muted fw-bold mb-3 ps-2 border-start border-4 border-warning mt-4">Registros e Sacramentos</h6>
    <!-- New Metrics Row (Registers, Watcheds, Batismos) -->
    <div class="row g-4 mb-4">
        <!-- Registers -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1 d-flex align-items-center gap-1">
                            Registros
                            <i class="bi bi-question-circle-fill text-muted opacity-50" data-bs-toggle="tooltip" title="Total de registros contabilizados no período."></i>
                        </h6>
                        <h2 class="display-5 fw-bold mb-0 text-primary">{{ number_format($registersCount, 0, ',', '.') }}</h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                        <i class="bi bi-file-earmark-text-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Apurações (VinWatcheds) -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1 d-flex align-items-center gap-1">
                            Apurações
                            <i class="bi bi-question-circle-fill text-muted opacity-50" data-bs-toggle="tooltip" title="Total de apurações (vínculos) realizadas no período."></i>
                        </h6>
                        <h2 class="display-5 fw-bold mb-0 text-warning">{{ number_format($vinWatchedsCount, 0, ',', '.') }}</h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                        <i class="bi bi-check-circle-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batismos -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1 d-flex align-items-center gap-1">
                            Batismos
                            <i class="bi bi-question-circle-fill text-muted opacity-50" data-bs-toggle="tooltip" title="Total de batismos realizados no período."></i>
                        </h6>
                        <h2 class="display-5 fw-bold mb-0 text-info">{{ number_format($batismosCount, 0, ',', '.') }}</h2>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info rounded-circle p-3">
                        <i class="bi bi-droplet-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Access Evolution Chart -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0">Evolução de Acessos</h5>
                    <small class="text-muted">Comparativo por dispositivo</small>
                </div>
                <div class="card-body p-4">
                    <div style="height: 300px;">
                        <canvas id="accessChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registers Breakdown Chart -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0">Visão Geral</h5>
                    <small class="text-muted">Registros e Sacramentos</small>
                </div>
                <div class="card-body p-4 position-relative">
                    <div style="height: 300px;">
                        <canvas id="registersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // Access Chart
        const ctx = document.getElementById('accessChart').getContext('2d');
        const accessChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        label: 'Site',
                        data: {!! json_encode($chartDataWeb) !!},
                        borderColor: '#0dcaf0', // Info/Cyan
                        backgroundColor: 'rgba(13, 202, 240, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 4
                    },
                    {
                        label: 'Android',
                        data: {!! json_encode($chartDataAndroid) !!},
                        borderColor: '#198754', // Success/Green
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 4
                    },
                    {
                        label: 'iOS',
                        data: {!! json_encode($chartDataIOS) !!},
                        borderColor: '#212529', // Dark
                        backgroundColor: 'rgba(33, 37, 41, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#212529',
                        bodyColor: '#212529',
                        borderColor: 'rgba(0,0,0,0.1)',
                        borderWidth: 1,
                        padding: 10
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        });

        // Registers Comparison Chart (Doughnut)
        const ctxRegisters = document.getElementById('registersChart').getContext('2d');
        const registersChart = new Chart(ctxRegisters, {
            type: 'doughnut',
            data: {
                labels: ['Registros', 'Apurações', 'Batismos'],
                datasets: [{
                    data: [{{ $registersCount }}, {{ $vinWatchedsCount }}, {{ $batismosCount }}],
                    backgroundColor: [
                        '#0d6efd', // Primary
                        '#ffc107', // Warning
                        '#0dcaf0'  // Info
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                },
                cutout: '70%'
            }
        });
    });
</script>
@endpush
@endsection
