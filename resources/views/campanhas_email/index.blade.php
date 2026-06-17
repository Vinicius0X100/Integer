@extends('layouts.app')

@section('page-title', 'Campanhas de Email')

@section('content')
<div class="container-fluid py-4">

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

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-envelope-paper-fill me-2 text-primary"></i>Campanhas de Email
            </h4>
            <p class="text-muted mb-0 small">Crie e gerencie campanhas de email marketing para o ecossistema Sacratech.</p>
        </div>
        <a href="{{ route('campanhas_email.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-plus-lg me-2"></i>Nova Campanha
        </a>
    </div>

    {{-- Cards de resumo --}}
    <div class="row g-3 mb-4">
        @php
            $total     = $campanhas->total();
            $enviadas  = \App\Models\CampanhaEmail::where('status', 'enviado')->count();
            $rascunhos = \App\Models\CampanhaEmail::where('status', 'rascunho')->count();
            $erros     = \App\Models\CampanhaEmail::where('status', 'erro')->count();
        @endphp
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle flex-shrink-0">
                        <i class="bi bi-envelope-paper fs-5 text-primary"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ $total }}</div>
                        <div class="text-muted small">Total</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle flex-shrink-0">
                        <i class="bi bi-check-circle fs-5 text-success"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ $enviadas }}</div>
                        <div class="text-muted small">Enviadas</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="bg-secondary bg-opacity-10 p-3 rounded-circle flex-shrink-0">
                        <i class="bi bi-file-earmark-text fs-5 text-secondary"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ $rascunhos }}</div>
                        <div class="text-muted small">Rascunhos</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="bg-danger bg-opacity-10 p-3 rounded-circle flex-shrink-0">
                        <i class="bi bi-exclamation-triangle fs-5 text-danger"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ $erros }}</div>
                        <div class="text-muted small">Com Erro</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            @if($campanhas->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-envelope-paper fs-1 text-muted mb-3 d-block"></i>
                    <p class="text-muted mb-3">Nenhuma campanha criada ainda.</p>
                    <a href="{{ route('campanhas_email.create') }}" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-plus-lg me-2"></i>Criar primeira campanha
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3 border-0 text-muted small fw-bold text-uppercase" style="font-size:0.75rem">Campanha</th>
                                <th class="py-3 border-0 text-muted small fw-bold text-uppercase" style="font-size:0.75rem">Produto</th>
                                <th class="py-3 border-0 text-muted small fw-bold text-uppercase" style="font-size:0.75rem">Status</th>
                                <th class="py-3 border-0 text-muted small fw-bold text-uppercase" style="font-size:0.75rem">Destinatários</th>
                                <th class="py-3 border-0 text-muted small fw-bold text-uppercase" style="font-size:0.75rem">Enviado em</th>
                                <th class="pe-4 py-3 border-0 text-muted small fw-bold text-uppercase text-end" style="font-size:0.75rem">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campanhas as $campanha)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="fw-semibold">{{ $campanha->titulo }}</div>
                                    <div class="text-muted small">Criada em {{ $campanha->criado_em->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="py-3">
                                    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary fw-semibold" style="font-size:0.78rem">
                                        {{ \App\Models\CampanhaEmail::produtoLabel($campanha->produto) }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <span class="badge rounded-pill bg-{{ \App\Models\CampanhaEmail::statusBadgeClass($campanha->status) }} text-white fw-semibold" style="font-size:0.78rem">
                                        @if($campanha->status === 'enviando')
                                            <span class="spinner-border spinner-border-sm me-1" style="width:0.6rem;height:0.6rem;"></span>
                                        @endif
                                        {{ \App\Models\CampanhaEmail::statusLabel($campanha->status) }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    @if($campanha->total_destinatarios > 0)
                                        <span class="fw-semibold">{{ number_format($campanha->total_destinatarios) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @if($campanha->enviado_em)
                                        {{ $campanha->enviado_em->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('campanhas_email.show', $campanha) }}"
                                           class="btn btn-sm btn-light rounded-pill px-3"
                                           title="Ver detalhes">
                                            <i class="bi bi-eye me-1"></i>Ver
                                        </a>

                                        @if($campanha->status !== 'enviando')
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                    title="Reenviar campanha"
                                                    data-bs-toggle="modal" data-bs-target="#modalReenviar-{{ $campanha->id }}">
                                                <i class="bi bi-arrow-repeat me-1"></i>Reenviar
                                            </button>

                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                    title="Excluir"
                                                    data-bs-toggle="modal" data-bs-target="#modalExcluir-{{ $campanha->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>



                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($campanhas->hasPages())
                    <div class="px-4 py-3 border-top">
                        {{ $campanhas->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

@foreach($campanhas as $campanha)
    @if($campanha->status !== 'enviando')
        {{-- Modal Reenviar --}}
        <div class="modal fade" id="modalReenviar-{{ $campanha->id }}" tabindex="-1" aria-labelledby="modalReenviarLabel-{{ $campanha->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4 text-start">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold" id="modalReenviarLabel-{{ $campanha->id }}">Reenviar Campanha</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4 text-center">
                        <div class="mb-3">
                            <i class="bi bi-send-fill text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="mb-2">Deseja reenviar a campanha <strong>{{ $campanha->titulo }}</strong>?</h5>
                        <p class="text-muted small mb-0">Ela será disparada novamente ao n8n.</p>
                    </div>
                    <div class="modal-footer border-top-0 d-flex justify-content-center pt-0 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <form method="POST" action="{{ route('campanhas_email.reenviar', $campanha) }}" onsubmit="let btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.innerHTML = '<span class=\'spinner-border spinner-border-sm me-2\' role=\'status\' aria-hidden=\'true\'></span>Enviando...';">
                            @csrf
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Sim, Reenviar Agora</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Excluir --}}
        <div class="modal fade" id="modalExcluir-{{ $campanha->id }}" tabindex="-1" aria-labelledby="modalExcluirLabel-{{ $campanha->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4 text-start">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold text-danger" id="modalExcluirLabel-{{ $campanha->id }}">Excluir Campanha</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4 text-center">
                        <div class="mb-3">
                            <i class="bi bi-trash3-fill text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="mb-2">Deseja excluir a campanha <strong>{{ $campanha->titulo }}</strong>?</h5>
                        <p class="text-muted small mb-0">Esta ação é permanente e não poderá ser desfeita.</p>
                    </div>
                    <div class="modal-footer border-top-0 d-flex justify-content-center pt-0 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <form method="POST" action="{{ route('campanhas_email.destroy', $campanha) }}" onsubmit="let btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.innerHTML = '<span class=\'spinner-border spinner-border-sm me-2\' role=\'status\' aria-hidden=\'true\'></span>Excluindo...';">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger rounded-pill px-4">Sim, Excluir</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection
