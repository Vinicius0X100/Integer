@extends('layouts.app')

@section('page-title', 'Nodal — Verificações KYC')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white mb-1">
                @if(file_exists(public_path('img/Nodal-Icon.png')))
                    <img src="{{ asset('img/Nodal-Icon.png') }}" alt="Nodal" style="height: 28px; width: auto; object-fit: contain; margin-right: 10px; vertical-align: middle;">
                @endif
                Verificações de Documentos
            </h2>
            <p class="text-white-50 mb-0">Listagem de empresas aguardando aprovação KYC.</p>
        </div>
        <div>
            <form action="{{ route('nodal-verifications.index') }}" method="GET" class="d-inline">
                <button type="submit" class="btn btn-outline-light rounded-pill px-4 py-2 shadow-sm border border-secondary border-opacity-25">
                    <i class="bi bi-arrow-clockwise me-2"></i> Atualizar
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Tabela --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Empresa</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Documento</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Status</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Enviado em</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($verifications as $ver)
                            <tr>
                                <td class="px-4 py-3 border-bottom-0">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center text-primary fw-bold me-3" style="width: 40px; height: 40px; font-size: 1rem;">
                                            {{ strtoupper(substr($ver->organization_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $ver->organization_name }}</div>
                                            <div class="text-muted small">ID Nodal: <span title="{{ $ver->nodal_organization_uuid }}">{{ substr($ver->nodal_organization_uuid, 0, 8) }}...</span></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 border-bottom-0">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">{{ strtoupper($ver->document_type) }}</span>
                                </td>
                                <td class="px-4 py-3 border-bottom-0">
                                    @if($ver->status === 'approved')
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Aprovado</span>
                                    @elseif($ver->status === 'rejected')
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Rejeitado</span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Pendente</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border-bottom-0">
                                    <span class="text-muted small">
                                        {{ $ver->submitted_at ? $ver->submitted_at->format('d/m/Y H:i') : '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 border-bottom-0 text-end">
                                    <a href="{{ route('nodal-verifications.show', $ver->id) }}" class="btn btn-sm btn-primary rounded-pill px-3">
                                        Verificar <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <div class="mb-3">
                                        <i class="bi bi-shield-check fs-1 opacity-25"></i>
                                    </div>
                                    <p class="mb-1 fw-medium">Nenhuma verificação pendente no momento.</p>
                                    <p class="small">As novas solicitações aparecerão aqui automaticamente.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($verifications->hasPages())
            <div class="card-footer bg-transparent border-top border-secondary border-opacity-10 px-4 py-3">
                {{ $verifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
