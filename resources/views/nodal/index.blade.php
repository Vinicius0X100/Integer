@extends('layouts.app')

@section('page-title', 'Nodal — Empresas')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white mb-1">
                @if(file_exists(public_path('img/Nodal-Icon.png')))
                    <img src="{{ asset('img/Nodal-Icon.png') }}" alt="Nodal" style="height: 28px; width: auto; object-fit: contain; margin-right: 10px; vertical-align: middle;">
                @endif
                Empresas Provisionadas
            </h2>
            <p class="text-white-50 mb-0">Listagem de todas as organizações provisionadas no Nodal.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('nodal.settings') }}" class="btn btn-dark rounded-pill px-4 py-2 shadow-sm border border-secondary border-opacity-25">
                <i class="bi bi-gear-fill me-2"></i> Configurações
            </a>
            <a href="{{ route('nodal.create') }}" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                <i class="bi bi-plus-lg me-2"></i> Nova Empresa
            </a>
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

    {{-- Alerta de API Key não configurada --}}
    @if(empty(env('NODAL_SYSTEM_API_KEY')))
        <div class="alert alert-warning rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            A <strong>NODAL_SYSTEM_API_KEY</strong> não está configurada.
            <a href="{{ route('nodal.settings') }}" class="alert-link ms-1">Configurar agora &rarr;</a>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-dark bg-opacity-50 border-secondary border-opacity-10">
        <div class="card-body p-3">
            <form action="{{ route('nodal.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-secondary border-opacity-25 text-white-50"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-transparent border-secondary border-opacity-25 text-white" placeholder="Buscar por nome, e-mail, responsável..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                        <option value="">Todos os Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativo</option>
                        <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Com Erro</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Filtrar</button>
                    @if(request()->anyFilled(['search', 'status']))
                        <a href="{{ route('nodal.index') }}" class="btn btn-outline-light rounded-circle" title="Limpar Filtros"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Organização</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Responsável</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Nodal ID</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Status</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Provisionado em</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($organizations as $org)
                            <tr>
                                <td class="px-4 py-3 border-bottom-0">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center text-primary fw-bold me-3" style="width: 40px; height: 40px; font-size: 1rem;">
                                            {{ strtoupper(substr($org->nome, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $org->nome }}</div>
                                            @if($org->slug)
                                                <div class="text-muted small">{{ $org->slug }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 border-bottom-0">
                                    <div class="fw-medium">{{ $org->owner_name }}</div>
                                    <div class="text-muted small">{{ $org->owner_email }}</div>
                                </td>
                                <td class="px-4 py-3 border-bottom-0">
                                    @if($org->nodal_organization_id)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">#{{ $org->nodal_organization_id }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border-bottom-0">
                                    @if($org->status === 'active')
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">
                                            <i class="bi bi-check-circle me-1"></i> Ativo
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3" title="{{ $org->provisioning_error }}">
                                            <i class="bi bi-exclamation-circle me-1"></i> Erro
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border-bottom-0">
                                    <span class="text-muted small">
                                        {{ $org->provisionado_em ? $org->provisionado_em->format('d/m/Y H:i') : '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 border-bottom-0 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if($org->nodal_login_url)
                                            <a href="{{ $org->nodal_login_url }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Acessar Nodal">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('nodal.edit', $org->id) }}" class="btn btn-sm btn-outline-light rounded-pill px-3" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('nodal.destroy', $org->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta organização permanentemente? Esta ação não pode ser desfeita.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <div class="mb-3">
                                        @if(file_exists(public_path('img/Nodal-Icon.png')))
                                            <img src="{{ asset('img/Nodal-Icon.png') }}" alt="Nodal" style="height: 48px; opacity: 0.3;">
                                        @else
                                            <i class="bi bi-building fs-1 opacity-25"></i>
                                        @endif
                                    </div>
                                    <p class="mb-1 fw-medium">Nenhuma empresa provisionada ainda.</p>
                                    <a href="{{ route('nodal.create') }}" class="btn btn-primary rounded-pill px-4 mt-2">
                                        <i class="bi bi-plus-lg me-2"></i> Provisionar Primeira Empresa
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($organizations->hasPages())
            <div class="card-footer bg-transparent border-top border-secondary border-opacity-10 px-4 py-3">
                {{ $organizations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
