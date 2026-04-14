@extends('layouts.app')

@section('page-title', 'Relatórios do Sistema (LOGS)')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h5 class="mb-1 fw-bold">Auditorias das Automações</h5>
            <div class="text-muted small">Histórico de execuções e resultados das rotinas automáticas</div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Atualizar
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('system_logs.index') }}" class="d-flex flex-wrap gap-2 align-items-end">
                <div class="d-flex flex-column">
                    <label class="small text-muted fw-bold ms-2 mb-1">Automação</label>
                    <select name="automation_key" class="form-select bg-white border-0 shadow-sm rounded-pill" style="min-width: 260px;" onchange="this.form.submit()">
                        <option value="">Todas</option>
                        @foreach($automationKeys as $key)
                            <option value="{{ $key }}" {{ (string) $selectedAutomationKey === (string) $key ? 'selected' : '' }}>{{ $key }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex flex-column">
                    <label class="small text-muted fw-bold ms-2 mb-1">Status</label>
                    <select name="status" class="form-select bg-white border-0 shadow-sm rounded-pill" style="min-width: 180px;" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="success" {{ (string) $selectedStatus === 'success' ? 'selected' : '' }}>Sucesso</option>
                        <option value="failed" {{ (string) $selectedStatus === 'failed' ? 'selected' : '' }}>Falha</option>
                        <option value="running" {{ (string) $selectedStatus === 'running' ? 'selected' : '' }}>Em execução</option>
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

                <div class="ms-auto">
                    <a href="{{ route('system_logs.index') }}" class="btn btn-light rounded-pill px-4">
                        <i class="bi bi-x-circle me-2"></i>Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-uppercase text-muted small fw-bold">Data/Hora</th>
                            <th class="text-uppercase text-muted small fw-bold">Automação</th>
                            <th class="text-uppercase text-muted small fw-bold text-center">Status</th>
                            <th class="text-uppercase text-muted small fw-bold">Resumo</th>
                            <th class="text-uppercase text-muted small fw-bold text-end">Duração</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($logs as $log)
                            <tr>
                                <td class="text-muted small">
                                    {{ $log->started_at ? $log->started_at->format('d/m/Y H:i:s') : ($log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-') }}
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $log->automation_name ?: 'Automação' }}</div>
                                    <div class="small text-muted">{{ $log->automation_key }}</div>
                                </td>
                                <td class="text-center">
                                    @if($log->status === 'success')
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Sucesso</span>
                                    @elseif($log->status === 'failed')
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Falha</span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Execução</span>
                                    @endif
                                </td>
                                <td class="small text-muted" style="max-width: 520px;">
                                    @php
                                        $summary = is_array($log->summary) ? $log->summary : [];
                                        $pairs = [];
                                        foreach ($summary as $k => $v) {
                                            if (is_array($v) || is_object($v)) continue;
                                            $pairs[] = $k . ': ' . $v;
                                        }
                                    @endphp
                                    @if(count($pairs) > 0)
                                        {{ \Illuminate\Support\Str::limit(implode(' | ', $pairs), 160) }}
                                    @else
                                        -
                                    @endif
                                    @if($log->error_message)
                                        <div class="text-danger small mt-1">{{ \Illuminate\Support\Str::limit($log->error_message, 140) }}</div>
                                    @endif
                                </td>
                                <td class="text-end text-muted small">
                                    {{ $log->duration_ms ? number_format((int) $log->duration_ms, 0, ',', '.') . ' ms' : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 mb-3 opacity-50 d-block"></i>
                                    Nenhum relatório encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

