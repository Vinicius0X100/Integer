@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<style>
    .hover-scale {
        transition: transform 0.2s ease, background-color 0.2s ease;
    }
    .hover-scale:hover {
        transform: translateY(-3px);
        background-color: rgba(255,255,255,0.2) !important;
    }
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .backdrop-blur {
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
</style>
<div class="row g-4">
    <!-- Welcome Section -->
    <div class="col-12">
        <div class="card border-0 shadow-sm bg-primary text-white overflow-hidden position-relative">
            <div class="card-body p-5 position-relative" style="z-index: 2;">
                <div class="row align-items-center">
                    <div class="col-lg-8 mb-4 mb-lg-0">
                        <h2 class="fw-bold mb-2">Bem-vindo, {{ Auth::user()->nome_exibicao ?? Auth::user()->nome }}!</h2>
                        <p class="lead opacity-75 mb-4">
                            Visão geral do sistema Integer. Aqui estão os dados em tempo real da sua plataforma.
                        </p>
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="{{ route('users.create') }}" class="btn btn-light rounded-pill px-4 fw-bold text-primary hover-scale">
                                <i class="bi bi-plus-lg me-2"></i> Novo Usuário
                            </a>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-light rounded-pill px-4 fw-bold hover-scale">
                                <i class="bi bi-people me-2"></i> Gerenciar Usuários
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="weather-widget p-3 rounded-4 bg-white bg-opacity-10 backdrop-blur border border-white border-opacity-25 text-end">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="text-start">
                                    <i id="weather-icon" class="bi bi-cloud-sun fs-1"></i>
                                </div>
                                <div>
                                    <h1 class="display-4 fw-bold mb-0 lh-1" id="clock-time">--:--</h1>
                                    <p class="mb-0 opacity-75 small text-uppercase" id="clock-date">--</p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-end border-top border-white border-opacity-25 pt-3 mt-2">
                                <div class="text-start">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-geo-alt-fill opacity-75"></i>
                                        <span id="weather-location" class="fw-bold">Localizando...</span>
                                    </div>
                                    <small class="opacity-75 d-block" id="weather-desc">--</small>
                                </div>
                                <div class="display-6 fw-bold" id="weather-temp">--°</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Abstract Decoration -->
            <div class="position-absolute top-0 end-0 h-100 w-50 d-none d-md-block" 
                 style="background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%); transform: skewX(-20deg) translateX(20%);">
            </div>
        </div>
    </div>

    <!-- Stats Cards Row 1: Users -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Total de Usuários</h6>
                        <h2 class="display-5 fw-bold mb-0 count-up" data-value="{{ $totalUsers }}">0</h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                </div>
                
                <div class="mt-4">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Ativos ({{ $activeUsers }})</span>
                        <span>Inativos ({{ $inactiveUsers }})</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        @php
                            $activePercent = $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $activePercent }}%"></div>
                        <div class="progress-bar bg-secondary opacity-25" role="progressbar" style="width: {{ 100 - $activePercent }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Novos (30 dias)</h6>
                        <h2 class="display-5 fw-bold mb-0 count-up" data-value="{{ $newUsersMonth }}">0</h2>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-3">
                        <i class="bi bi-person-plus-fill fs-4"></i>
                    </div>
                </div>
                
                <div class="mt-4">
                    <p class="mb-0 small {{ $growth >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                        <i class="bi {{ $growth >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i> 
                        {{ number_format(abs($growth), 1) }}% 
                        <span class="text-muted fw-normal">vs mês anterior</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Armazenamento</h6>
                        <h2 class="display-5 fw-bold mb-0">45%</h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                        <i class="bi bi-hdd-fill fs-4"></i>
                    </div>
                </div>
                
                <div class="mt-4">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>450GB usados</span>
                        <span>1TB total</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 45%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row 2: Financial -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Lucro Realizado (Mês)</h6>
                        <h2 class="display-6 fw-bold mb-0 text-success">R$ {{ number_format($lucroRealizadoMes, 2, ',', '.') }}</h2>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-3">
                        <i class="bi bi-currency-dollar fs-4"></i>
                    </div>
                </div>
                <p class="mb-0 small text-muted">Baseado em serviços concluídos este mês.</p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Lucro Presumido</h6>
                        <h2 class="display-6 fw-bold mb-0 text-info">R$ {{ number_format($lucroPresumido, 2, ',', '.') }}</h2>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info rounded-circle p-3">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                </div>
                <p class="mb-0 small text-muted">Previsão de serviços em andamento/pendentes.</p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Serviços Concluídos</h6>
                        <h2 class="display-6 fw-bold mb-0">{{ $servicosConcluidos }}</h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                </div>
                <p class="mb-0 small text-muted">Total de serviços finalizados com sucesso.</p>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row 3: Advanced Financials -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold mb-0">Lucro por Quadrimestre</h5>
                <p class="text-muted small">Comparativo de desempenho dos últimos períodos</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    @foreach($quadrimesters as $quad)
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 h-100 {{ $quad['is_current'] ? 'bg-primary bg-opacity-10 border border-primary border-opacity-25' : 'bg-light border border-light' }}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge {{ $quad['is_current'] ? 'bg-primary' : 'bg-secondary bg-opacity-25 text-secondary' }} rounded-pill">
                                        {{ $quad['label'] }}
                                    </span>
                                    @if($quad['is_current'])
                                        <small class="text-primary fw-bold"><i class="bi bi-circle-fill small me-1"></i>Atual</small>
                                    @endif
                                </div>
                                <h4 class="mb-0 fw-bold {{ $quad['is_current'] ? 'text-primary' : 'text-dark' }}">
                                    R$ {{ number_format($quad['lucro'], 2, ',', '.') }}
                                </h4>
                                <small class="text-muted">Lucro Realizado</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Ticket Médio</h6>
                        <h2 class="display-6 fw-bold mb-0 text-dark">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</h2>
                    </div>
                    <div class="bg-secondary bg-opacity-10 text-dark rounded-circle p-3">
                        <i class="bi bi-receipt fs-4"></i>
                    </div>
                </div>
                <p class="mb-0 small text-muted">Valor médio recebido por serviço concluído.</p>
                <hr class="my-4 opacity-10">
                <div class="d-flex align-items-center gap-3">
                     <div class="bg-success bg-opacity-10 text-success rounded-circle p-2">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div>
                        <small class="d-block text-muted lh-1">Maior Lucro (Quad)</small>
                        <span class="fw-bold text-dark">R$ {{ number_format($quadrimesters->max('lucro'), 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section: Financial & Roles -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">Evolução Financeira</h5>
                    <p class="text-muted small">Lucro realizado nos últimos 6 meses</p>
                </div>
            </div>
            <div class="card-body p-4">
                <canvas id="financialChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold mb-0">Distribuição por Papel</h5>
                <p class="text-muted small">Usuários por nível de acesso</p>
            </div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center">
                <div style="height: 250px; width: 100%;">
                    <canvas id="roleDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section: Users & Productivity -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <div class="d-flex align-items-center gap-2 mb-0">
                    @if(file_exists(public_path('img/sacratech-id.png')))
                        <img src="{{ asset('img/sacratech-id.png') }}" alt="Sacratech iD" height="20">
                    @endif
                    <h5 class="fw-bold mb-0">Usuários Sacratech iD</h5>
                </div>
                <p class="text-muted small">Evolução de novos usuários nos últimos 6 meses</p>
            </div>
            <div class="card-body p-4">
                <canvas id="userEvolutionChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold mb-0">Produtividade de Serviços</h5>
                <p class="text-muted small">Comparativo: Criados vs Concluídos</p>
            </div>
            <div class="card-body p-4">
                <canvas id="productivityChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation for Numbers
        const counters = document.querySelectorAll('.count-up');
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-value');
            const duration = 1000; // 1s
            const increment = target / (duration / 16); // 60fps
            
            let current = 0;
            const updateCount = () => {
                current += increment;
                if (current < target) {
                    counter.innerText = Math.ceil(current);
                    requestAnimationFrame(updateCount);
                } else {
                    counter.innerText = target;
                }
            };
            updateCount();
        });

        // Setup Charts Theme Colors
        const getThemeColors = () => {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            return {
                text: isDark ? '#f5f5f7' : '#1d1d1f',
                grid: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)',
                tooltipBg: isDark ? '#333' : '#fff',
                tooltipText: isDark ? '#fff' : '#000'
            };
        };

        let theme = getThemeColors();

        // Common Chart Options
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: theme.text,
                        usePointStyle: true,
                        font: { family: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" }
                    }
                },
                tooltip: {
                    backgroundColor: theme.tooltipBg,
                    titleColor: theme.tooltipText,
                    bodyColor: theme.tooltipText,
                    borderColor: theme.grid,
                    borderWidth: 1,
                    padding: 10
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: theme.grid, borderDash: [5, 5] },
                    ticks: { color: theme.text }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: theme.text }
                }
            }
        };

        // 1. Financial Chart
        const ctxFinancial = document.getElementById('financialChart').getContext('2d');
        const financialChart = new Chart(ctxFinancial, {
            type: 'bar',
            data: {
                labels: {!! $months->toJson() !!},
                datasets: [
                    {
                        label: 'Receita Total',
                        data: {!! $receitaCounts->toJson() !!},
                        backgroundColor: 'rgba(52, 199, 89, 0.7)', // Green
                        borderColor: '#34c759',
                        borderWidth: 1,
                        borderRadius: 4,
                        order: 2
                    },
                    {
                        label: 'Custo Interno',
                        data: {!! $custoCounts->toJson() !!},
                        backgroundColor: 'rgba(255, 59, 48, 0.7)', // Red
                        borderColor: '#ff3b30',
                        borderWidth: 1,
                        borderRadius: 4,
                        order: 3
                    },
                    {
                        type: 'line',
                        label: 'Lucro Líquido',
                        data: {!! $lucroCounts->toJson() !!},
                        borderColor: '#0071e3', // Blue
                        backgroundColor: 'rgba(0, 113, 227, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#0071e3',
                        pointRadius: 4,
                        fill: false,
                        tension: 0.4,
                        order: 1
                    }
                ]
            },
            options: {
                ...commonOptions,
                plugins: {
                    ...commonOptions.plugins,
                    tooltip: {
                        ...commonOptions.plugins.tooltip,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    ...commonOptions.scales,
                    y: {
                        ...commonOptions.scales.y,
                        ticks: {
                            color: theme.text,
                            callback: function(value) {
                                return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', maximumSignificantDigits: 2 }).format(value);
                            }
                        }
                    }
                }
            }
        });

        // 2. Role Distribution Chart (Doughnut)
        const ctxRole = document.getElementById('roleDistributionChart').getContext('2d');
        const roleDistributionChart = new Chart(ctxRole, {
            type: 'doughnut',
            data: {
                labels: {!! $roles->keys()->toJson() !!},
                datasets: [{
                    data: {!! $roles->values()->toJson() !!},
                    backgroundColor: [
                        'rgba(0, 113, 227, 0.8)', // Blue
                        'rgba(52, 199, 89, 0.8)',  // Green
                        'rgba(255, 149, 0, 0.8)',  // Orange
                        'rgba(255, 59, 48, 0.8)',  // Red
                        'rgba(175, 82, 222, 0.8)'  // Purple
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
                        position: 'right',
                        labels: { color: theme.text, usePointStyle: true }
                    }
                },
                cutout: '70%'
            }
        });

        // 3. User Evolution Chart (Sacratech iD)
        const ctxUser = document.getElementById('userEvolutionChart').getContext('2d');
        const userEvolutionChart = new Chart(ctxUser, {
            type: 'line',
            data: {
                labels: {!! $months->toJson() !!},
                datasets: [{
                    label: 'Novos Usuários',
                    data: {!! $userCounts->toJson() !!},
                    borderColor: '#00c7be', // Teal
                    backgroundColor: 'rgba(0, 199, 190, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#00c7be',
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: commonOptions
        });

        // 4. Productivity Chart
        const ctxProd = document.getElementById('productivityChart').getContext('2d');
        const productivityChart = new Chart(ctxProd, {
            type: 'line',
            data: {
                labels: {!! $months->toJson() !!},
                datasets: [
                    {
                        label: 'Serviços Criados',
                        data: {!! $createdServicesCounts->toJson() !!},
                        borderColor: '#ff9500', // Orange
                        backgroundColor: 'rgba(255, 149, 0, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#ff9500',
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Serviços Concluídos',
                        data: {!! $completedServicesCounts->toJson() !!},
                        borderColor: '#5856d6', // Purple
                        backgroundColor: 'rgba(88, 86, 214, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#5856d6',
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: commonOptions
        });

        // Theme Observer Logic
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-bs-theme') {
                    const newTheme = getThemeColors();
                    
                    const updateChartColors = (chart, isDoughnut = false) => {
                        chart.options.plugins.legend.labels.color = newTheme.text;
                        chart.options.plugins.tooltip.backgroundColor = newTheme.tooltipBg;
                        chart.options.plugins.tooltip.titleColor = newTheme.tooltipText;
                        chart.options.plugins.tooltip.bodyColor = newTheme.tooltipText;
                        chart.options.plugins.tooltip.borderColor = newTheme.grid;
                        
                        if (!isDoughnut) {
                            chart.options.scales.y.grid.color = newTheme.grid;
                            chart.options.scales.y.ticks.color = newTheme.text;
                            chart.options.scales.x.ticks.color = newTheme.text;
                        }
                        chart.update();
                    };

                    updateChartColors(financialChart);
                    updateChartColors(roleDistributionChart, true);
                    updateChartColors(userEvolutionChart);
                    updateChartColors(productivityChart);
                }
            });
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-bs-theme']
        });

        // --- Clock & Weather Widget ---
        function updateClock() {
            const now = new Date();
            const timeElement = document.getElementById('clock-time');
            const dateElement = document.getElementById('clock-date');
            
            if (timeElement && dateElement) {
                timeElement.innerText = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                // Format: Quinta-feira, 21 de Jan
                const options = { weekday: 'long', day: 'numeric', month: 'short' };
                const dateStr = now.toLocaleDateString('pt-BR', options).replace('.', '');
                dateElement.innerText = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        function initWeather() {
            const tempEl = document.getElementById('weather-temp');
            const descEl = document.getElementById('weather-desc');
            const iconEl = document.getElementById('weather-icon');
            const locEl = document.getElementById('weather-location');

            // Fallback default (São Paulo)
            const defaultLat = -23.5505;
            const defaultLon = -46.6333;

            function fetchWeather(lat, lon) {
                // 1. Get Location Name (Free API)
                fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lon}&localityLanguage=pt`)
                    .then(r => r.json())
                    .then(data => {
                        // Priority: City -> Locality -> Default
                        locEl.innerText = data.city || data.locality || 'Local Atual';
                    })
                    .catch(() => {
                        locEl.innerText = 'Local Atual';
                    });

                // 2. Get Weather Data (Open-Meteo)
                fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true&timezone=auto`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.current_weather) return;

                        const weather = data.current_weather;
                        const temp = Math.round(weather.temperature);
                        const code = weather.weathercode;
                        const isDay = weather.is_day;

                        tempEl.innerText = `${temp}°`;

                        // WMO Weather Codes to Bootstrap Icons
                        const weatherMap = {
                            0: { icon: 'bi-sun-fill', desc: 'Céu Limpo' },
                            1: { icon: 'bi-sun', desc: 'Parcialmente Nublado' },
                            2: { icon: 'bi-cloud-sun', desc: 'Nublado' },
                            3: { icon: 'bi-clouds-fill', desc: 'Encoberto' },
                            45: { icon: 'bi-cloud-haze', desc: 'Neblina' },
                            48: { icon: 'bi-cloud-haze2', desc: 'Nevoeiro' },
                            51: { icon: 'bi-cloud-drizzle', desc: 'Garoa Leve' },
                            53: { icon: 'bi-cloud-drizzle', desc: 'Garoa' },
                            55: { icon: 'bi-cloud-drizzle-fill', desc: 'Garoa Forte' },
                            61: { icon: 'bi-cloud-rain', desc: 'Chuva Leve' },
                            63: { icon: 'bi-cloud-rain-fill', desc: 'Chuva' },
                            65: { icon: 'bi-cloud-rain-heavy-fill', desc: 'Chuva Forte' },
                            71: { icon: 'bi-snow', desc: 'Neve Leve' },
                            73: { icon: 'bi-snow', desc: 'Neve' },
                            75: { icon: 'bi-snow2', desc: 'Neve Forte' },
                            80: { icon: 'bi-cloud-rain', desc: 'Pancadas de Chuva' },
                            81: { icon: 'bi-cloud-rain-fill', desc: 'Pancadas Fortes' },
                            82: { icon: 'bi-cloud-rain-heavy-fill', desc: 'Tempestade' },
                            95: { icon: 'bi-cloud-lightning-rain', desc: 'Trovoadas' },
                            96: { icon: 'bi-cloud-lightning-rain-fill', desc: 'Trovoadas c/ Granizo' },
                            99: { icon: 'bi-cloud-lightning-rain-fill', desc: 'Tempestade Severa' }
                        };

                        let info = weatherMap[code] || { icon: 'bi-cloud', desc: 'Indisponível' };

                        // Night time adjustments
                        if (isDay === 0) {
                            if (code === 0) info.icon = 'bi-moon-stars-fill';
                            if (code === 1) info.icon = 'bi-moon-stars';
                            if (code === 2) info.icon = 'bi-cloud-moon';
                        }

                        iconEl.className = `bi ${info.icon} fs-1`;
                        descEl.innerText = info.desc;
                    })
                    .catch(err => {
                        console.error('Weather Error:', err);
                        descEl.innerText = 'Indisponível';
                    });
            }

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        fetchWeather(position.coords.latitude, position.coords.longitude);
                    },
                    (error) => {
                        console.log('Geo denied/error, using default SP');
                        locEl.innerText = 'São Paulo';
                        fetchWeather(defaultLat, defaultLon);
                    }
                );
            } else {
                locEl.innerText = 'São Paulo';
                fetchWeather(defaultLat, defaultLon);
            }
        }

        initWeather();
    });
</script>
@endsection
