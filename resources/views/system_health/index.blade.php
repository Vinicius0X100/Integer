@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 text-white">Monitoramento de Sistemas</h2>
            <p class="text-muted small mb-0">Monitoramento em tempo real de disponibilidade e latência</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Atualizar
            </button>
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="datefilter" id="btn-day" autocomplete="off" checked onchange="loadCharts('day')">
                <label class="btn btn-outline-primary btn-sm" for="btn-day">24h</label>

                <input type="radio" class="btn-check" name="datefilter" id="btn-week" autocomplete="off" onchange="loadCharts('week')">
                <label class="btn btn-outline-primary btn-sm" for="btn-week">7 Dias</label>

                <input type="radio" class="btn-check" name="datefilter" id="btn-month" autocomplete="off" onchange="loadCharts('month')">
                <label class="btn btn-outline-primary btn-sm" for="btn-month">30 Dias</label>
            </div>
        </div>
    </div>

    <!-- Cards Row -->
    <div class="row g-4 mb-4">
        @foreach($currentStatus as $systemName => $data)
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm" style="background: var(--apple-card-bg); backdrop-filter: blur(20px);">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h5 class="card-title mb-1 fw-bold">{{ $systemName }}</h5>
                                <div class="small text-muted">{{ $data['log'] ? $data['log']->endpoint : 'N/A' }}</div>
                            </div>
                            @if($data['log'] && $data['log']->status)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                    <i class="bi bi-circle-fill small me-1"></i> Online
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2">
                                    <i class="bi bi-exclamation-triangle-fill small me-1"></i> Offline
                                </span>
                            @endif
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 rounded-3 bg-light">
                                    <div class="text-muted small mb-1">Latência Atual</div>
                                    <div class="fs-4 fw-bold {{ ($data['log'] && $data['log']->response_time_ms > 1000) ? 'text-warning' : '' }}">
                                        {{ $data['log'] ? $data['log']->response_time_ms . ' ms' : '-' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded-3 bg-light">
                                    <div class="text-muted small mb-1">Uptime (24h)</div>
                                    <div class="fs-4 fw-bold {{ $data['uptime_24h'] >= 99 ? 'text-success' : ($data['uptime_24h'] >= 95 ? 'text-warning' : 'text-danger') }}">
                                        {{ $data['uptime_24h'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-between text-muted small">
                            <span>Status Code: {{ $data['log'] ? $data['log']->status_code : '-' }}</span>
                            <span>Verificado: {{ $data['log'] ? $data['log']->created_at->diffForHumans() : 'Nunca' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Chart Section -->
    <div class="card border-0 shadow-sm mb-4" style="background: var(--apple-card-bg); backdrop-filter: blur(20px);">
        <div class="card-body p-4">
            <h5 class="card-title mb-4 fw-bold">Evolução de Latência e Disponibilidade</h5>
            <div style="height: 400px; width: 100%;">
                <canvas id="healthChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Logs Table Section -->
    <div class="card border-0 shadow-sm" style="background: var(--apple-card-bg); backdrop-filter: blur(20px);">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title fw-bold mb-0">Histórico de Monitoramento</h5>
                <a href="{{ route('system_health.pdf') }}" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Baixar PDF
                </a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Sistema</th>
                            <th>Status</th>
                            <th>Latência</th>
                            <th>Status Code</th>
                            <th>Data/Hora</th>
                            <th>Mensagem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->system_name }}</td>
                                <td>
                                    @if($log->status)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Online</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Offline</span>
                                    @endif
                                </td>
                                <td>{{ $log->response_time_ms }} ms</td>
                                <td>{{ $log->status_code ?? '-' }}</td>
                                <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="text-muted small">{{ Str::limit($log->error_message, 50) ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Nenhum registro encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0.1/dist/chartjs-adapter-moment.min.js"></script>

<script>
    let healthChart = null;

    document.addEventListener('DOMContentLoaded', function() {
        loadCharts('day');
    });

    function loadCharts(range) {
        fetch(`{{ route('system_health.metrics') }}?range=${range}`)
            .then(response => response.json())
            .then(data => {
                updateChart(data);
            })
            .catch(error => console.error('Error loading chart data:', error));
    }

    function updateChart(data) {
        const ctx = document.getElementById('healthChart').getContext('2d');
        
        const datasets = [];
        const colors = {
            'SisMatriz': '#0071e3', // Blue
            'SisMatriz Ticket': '#34c759', // Green
            'Sacratech Cloud': '#ff9500' // Orange
        };

        for (const [systemName, entries] of Object.entries(data)) {
            datasets.push({
                label: systemName,
                data: entries.map(entry => ({
                    x: entry.time,
                    y: entry.response_time
                })),
                borderColor: colors[systemName] || '#8e8e93',
                backgroundColor: (colors[systemName] || '#8e8e93') + '20',
                borderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 4,
                fill: false,
                tension: 0.4
            });
        }

        if (healthChart) {
            healthChart.destroy();
        }

        healthChart = new Chart(ctx, {
            type: 'line',
            data: {
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: getComputedStyle(document.body).getPropertyValue('--apple-text')
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + ' ms';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'hour',
                            displayFormats: {
                                hour: 'HH:mm'
                            },
                            tooltipFormat: 'DD/MM/YYYY HH:mm'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: getComputedStyle(document.body).getPropertyValue('--apple-text')
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Latência (ms)',
                            color: getComputedStyle(document.body).getPropertyValue('--apple-text')
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: getComputedStyle(document.body).getPropertyValue('--apple-text')
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
@endsection
