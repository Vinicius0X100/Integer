@extends('layouts.app')

@section('page-title', 'Planos de Licenciamento — Nodal')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white mb-1">
                <i class="bi bi-box-seam-fill me-2 text-primary"></i>Planos de Licenciamento
            </h2>
            <p class="text-white-50 mb-0">Administração do catálogo comercial e assinaturas do Nodal.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNovoPlano" @if(!empty($apiError)) disabled @endif>
                <i class="bi bi-plus-lg me-2"></i> Novo Plano
            </button>
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

    {{-- Erro de Indisponibilidade da API do Nodal --}}
    @if(!empty($apiError))
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-danger bg-opacity-10 border border-danger border-opacity-25 text-white p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold text-danger mb-1">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>Indisponibilidade do Nodal
                    </h5>
                    <p class="mb-0 text-white-50">{{ $apiError }}</p>
                </div>
                <a href="{{ route('nodal-plans.index') }}" class="btn btn-danger rounded-pill px-4">
                    <i class="bi bi-arrow-clockwise me-1"></i> Tentar novamente
                </a>
            </div>
        </div>
    @endif

    {{-- Abas de Navegação no Topo --}}
    <div class="d-flex gap-2 mb-4">
        <a href="{{ route('nodal-plans.index', array_merge(request()->except('tab'), ['tab' => 'all'])) }}"
           class="btn rounded-pill px-4 py-2 {{ request('tab', 'all') === 'all' ? 'btn-primary' : 'btn-dark border border-secondary border-opacity-25' }}">
            Todos
        </a>
        <a href="{{ route('nodal-plans.index', array_merge(request()->except('tab'), ['tab' => 'public'])) }}"
           class="btn rounded-pill px-4 py-2 {{ request('tab') === 'public' ? 'btn-primary' : 'btn-dark border border-secondary border-opacity-25' }}">
            👁 Públicos
        </a>
        <a href="{{ route('nodal-plans.index', array_merge(request()->except('tab'), ['tab' => 'hidden'])) }}"
           class="btn rounded-pill px-4 py-2 {{ request('tab') === 'hidden' ? 'btn-primary' : 'btn-dark border border-secondary border-opacity-25' }}">
            👁‍🗨 Ocultos
        </a>
    </div>

    {{-- Filtros --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-dark bg-opacity-50 border-secondary border-opacity-10">
        <div class="card-body p-3">
            <form action="{{ route('nodal-plans.index') }}" method="GET" class="row g-3 align-items-center">
                <input type="hidden" name="tab" value="{{ request('tab', 'all') }}">

                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-secondary border-opacity-25 text-white-50"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-transparent border-secondary border-opacity-25 text-white" placeholder="Buscar por nome ou código..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                        <option value="" class="bg-dark text-white">Todos os Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }} class="bg-dark text-white">Ativos</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }} class="bg-dark text-white">Inativos</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="unlimited" class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                        <option value="" class="bg-dark text-white">Todos os Limites</option>
                        <option value="1" {{ request('unlimited') === '1' ? 'selected' : '' }} class="bg-dark text-white">Ilimitados</option>
                        <option value="0" {{ request('unlimited') === '0' ? 'selected' : '' }} class="bg-dark text-white">Limitados</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Filtrar</button>
                    @if(request()->anyFilled(['search', 'status', 'unlimited']))
                        <a href="{{ route('nodal-plans.index', ['tab' => request('tab', 'all')]) }}" class="btn btn-outline-light rounded-circle" title="Limpar Filtros"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabela de Planos --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Plano</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Código</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Visibilidade</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Status</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Mensalidade</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Usuários</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Créditos IA</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Excedente</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Ilimitado</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Empresas</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plans as $plan)
                            @php
                                $uuid = $plan['uuid'] ?? $plan['id'] ?? '';
                                $code = $plan['code'] ?? '';
                                $name = $plan['name'] ?? '';
                                $isPublic = (bool) ($plan['is_public'] ?? true);
                                $isActive = (bool) ($plan['is_active'] ?? true);
                                $isUnlimited = (bool) ($plan['is_unlimited'] ?? false);
                                $monthlyPriceCents = (int) ($plan['monthly_price_cents'] ?? 0);
                                $overagePriceCents = (int) ($plan['overage_price_cents'] ?? 0);
                                $maxUsers = $plan['max_users'] ?? null;
                                $aiCredits = $plan['ai_credits'] ?? null;
                                $orgsCount = (int) ($plan['organizations_count'] ?? $plan['companies_count'] ?? 0);
                            @endphp
                            <tr>
                                <td class="px-4 py-3 border-bottom-0">
                                    <div class="fw-semibold text-white">{{ $name }}</div>
                                    @if(!empty($plan['description']))
                                        <div class="text-white-50 small text-truncate" style="max-width: 200px;">{{ $plan['description'] }}</div>
                                    @endif
                                </td>

                                <td class="px-4 py-3 border-bottom-0">
                                    <span class="badge bg-dark border border-secondary text-white font-monospace">{{ $code }}</span>
                                </td>

                                <td class="px-4 py-3 border-bottom-0">
                                    @if($isPublic)
                                        <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3" data-bs-toggle="tooltip" title="Disponível comercialmente no Nodal.">
                                            <i class="bi bi-eye me-1"></i> Público
                                        </span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3" data-bs-toggle="tooltip" title="Disponível apenas para atribuição administrativa pela SacraTech.">
                                            <i class="bi bi-eye-slash me-1"></i> Oculto
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 border-bottom-0">
                                    @if($isActive)
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">
                                            <i class="bi bi-check-circle me-1"></i> Ativo
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">
                                            <i class="bi bi-pause-circle me-1"></i> Inativo
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 border-bottom-0 fw-semibold text-white">
                                    R$ {{ number_format($monthlyPriceCents / 100, 2, ',', '.') }}
                                </td>

                                <td class="px-4 py-3 border-bottom-0">
                                    @if($isUnlimited)
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">∞ Ilimitado</span>
                                    @else
                                        <span class="text-white-50">{{ $maxUsers ? number_format($maxUsers, 0, ',', '.') : '—' }}</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 border-bottom-0">
                                    @if($isUnlimited)
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">∞ Ilimitada</span>
                                    @else
                                        <span class="text-white-50">{{ $aiCredits ? number_format($aiCredits, 0, ',', '.') : '—' }}</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 border-bottom-0">
                                    @if($isUnlimited)
                                        <span class="text-white-50">Não aplicável</span>
                                    @else
                                        @if($overagePriceCents > 0)
                                            <span class="text-white-50">R$ {{ number_format($overagePriceCents / 100, 2, ',', '.') }} / 1.000</span>
                                        @else
                                            <span class="text-white-50">—</span>
                                        @endif
                                    @endif
                                </td>

                                <td class="px-4 py-3 border-bottom-0">
                                    @if($isUnlimited)
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Sim</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Não</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 border-bottom-0 fw-bold text-white">
                                    {{ $orgsCount }}
                                </td>

                                <td class="px-4 py-3 border-bottom-0 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalEditarPlano-{{ $uuid }}">
                                        <i class="bi bi-pencil me-1"></i> Editar
                                    </button>
                                </td>
                            </tr>

                            {{-- Modal Editar Plano --}}
                            <div class="modal fade" id="modalEditarPlano-{{ $uuid }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content bg-dark text-white border-secondary">
                                        <form action="{{ route('nodal-plans.update', $uuid) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-header border-secondary">
                                                <h5 class="modal-title fw-bold">
                                                    <i class="bi bi-pencil me-2 text-primary"></i>Editar Plano: {{ $name }}
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body p-4">
                                                <p class="text-white-50 mb-3">
                                                    Este plano está atribuído a <strong>{{ $orgsCount }}</strong> {{ $orgsCount === 1 ? 'organização' : 'organizações' }}.
                                                </p>

                                                @if($orgsCount > 0)
                                                    <div class="alert alert-warning rounded-4 border-0 shadow-sm mb-4" role="alert">
                                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                                        <strong>Atenção:</strong> Alterações comerciais poderão afetar organizações vinculadas nos períodos abertos/futuros.
                                                    </div>
                                                @endif

                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-8">
                                                        <label class="form-label text-white-50">Nome do Plano <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" class="form-control bg-dark text-white border-secondary" value="{{ old('name', $name) }}" required>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label text-white-50">Código <span class="text-muted small">(Identificador Permanente)</span></label>
                                                        <input type="text" class="form-control bg-dark text-white-50 border-secondary font-monospace" value="{{ $code }}" readonly disabled>
                                                        <div class="form-text text-white-50">O código não pode ser alterado após a criação.</div>
                                                    </div>
                                                </div>

                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label text-white-50">Mensalidade (Centavos) <span class="text-danger">*</span></label>
                                                        <input type="number" name="monthly_price_cents" class="form-control bg-dark text-white border-secondary" value="{{ old('monthly_price_cents', $monthlyPriceCents) }}" min="0" required>
                                                        <div class="form-text text-white-50">Ex: 59900 = R$ 599,00</div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label text-white-50">Limite de Usuários</label>
                                                        <input type="number" name="max_users" class="form-control bg-dark text-white border-secondary" value="{{ old('max_users', $maxUsers) }}" min="1" placeholder="Ex: 50">
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label text-white-50">Créditos IA</label>
                                                        <input type="number" name="ai_credits" class="form-control bg-dark text-white border-secondary" value="{{ old('ai_credits', $aiCredits) }}" min="0" placeholder="Ex: 10000">
                                                    </div>
                                                </div>

                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label text-white-50">Preço de Excedente / 1.000 (Centavos)</label>
                                                        <input type="number" name="overage_price_cents" class="form-control bg-dark text-white border-secondary" value="{{ old('overage_price_cents', $overagePriceCents) }}" min="0" placeholder="Ex: 2500">
                                                    </div>
                                                </div>

                                                <div class="row g-3 mb-3">
                                                    <div class="col-12">
                                                        <label class="form-label text-white-50">Descrição / Notas Comerciais</label>
                                                        <textarea name="description" class="form-control bg-dark text-white border-secondary" rows="2">{{ old('description', $plan['description'] ?? '') }}</textarea>
                                                    </div>
                                                </div>

                                                <div class="d-flex flex-wrap gap-4 mt-4 pt-3 border-top border-secondary">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="is_public" value="1" id="edit_public_{{ $uuid }}" {{ $isPublic ? 'checked' : '' }}>
                                                        <label class="form-check-label text-white" for="edit_public_{{ $uuid }}">Plano Público</label>
                                                    </div>

                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="is_unlimited" value="1" id="edit_unlimited_{{ $uuid }}" {{ $isUnlimited ? 'checked' : '' }}>
                                                        <label class="form-check-label text-white" for="edit_unlimited_{{ $uuid }}">Plano Ilimitado</label>
                                                    </div>

                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_active_{{ $uuid }}" {{ $isActive ? 'checked' : '' }}>
                                                        <label class="form-check-label text-white" for="edit_active_{{ $uuid }}">Ativo (Desmarque para Aposentar)</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer border-secondary">
                                                <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary rounded-pill px-4">Salvar Alterações</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5 text-white-50">
                                    <i class="bi bi-inbox text-secondary display-4 d-block mb-3"></i>
                                    Nenhum plano encontrado no catálogo Nodal.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Novo Plano --}}
<div class="modal fade" id="modalNovoPlano" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary">
            <form action="{{ route('nodal-plans.store') }}" method="POST">
                @csrf
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-lg me-2 text-primary"></i>Criar Novo Plano de Licenciamento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="alert alert-info rounded-4 border-0 shadow-sm mb-4" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        O plano será cadastrado diretamente no Nodal. Chaves e identificadores serão preservados.
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-white-50">Nome do Plano <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-dark text-white border-secondary" placeholder="Ex: Starter, Business, SacraTech Internal" value="{{ old('name') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white-50">Código (Slug) <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control bg-dark text-white border-secondary font-monospace" placeholder="Ex: starter, business, sacratech-internal" value="{{ old('code') }}" required>
                            <div class="form-text text-warning small">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Identificador permanente. Não poderá ser alterado após a criação.
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-white-50">Mensalidade (Centavos) <span class="text-danger">*</span></label>
                            <input type="number" name="monthly_price_cents" class="form-control bg-dark text-white border-secondary" value="{{ old('monthly_price_cents', 0) }}" min="0" required>
                            <div class="form-text text-white-50">Ex: 59900 = R$ 599,00. 0 = Sem cobrança.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white-50">Limite de Usuários</label>
                            <input type="number" name="max_users" class="form-control bg-dark text-white border-secondary" value="{{ old('max_users') }}" min="1" placeholder="Ex: 50">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white-50">Créditos IA</label>
                            <input type="number" name="ai_credits" class="form-control bg-dark text-white border-secondary" value="{{ old('ai_credits') }}" min="0" placeholder="Ex: 10000">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-white-50">Preço de Excedente / 1.000 (Centavos)</label>
                            <input type="number" name="overage_price_cents" class="form-control bg-dark text-white border-secondary" value="{{ old('overage_price_cents') }}" min="0" placeholder="Ex: 2500">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label text-white-50">Descrição / Notas Comerciais</label>
                            <textarea name="description" class="form-control bg-dark text-white border-secondary" rows="2" placeholder="Descreva os benefícios e termos deste plano">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-4 mt-4 pt-3 border-top border-secondary">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_public" value="1" id="new_public" checked>
                            <label class="form-check-label text-white" for="new_public">Plano Público (Disponível no catálogo)</label>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_unlimited" value="1" id="new_unlimited">
                            <label class="form-check-label text-white" for="new_unlimited">Plano Ilimitado</label>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="new_active" checked>
                            <label class="form-check-label text-white" for="new_active">Ativo</label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Criar Plano</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
