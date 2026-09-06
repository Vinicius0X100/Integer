@extends('layouts.app')

@section('page-title', 'Planos de Licenciamento — Nodal')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
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
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
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
    @php
        $currentTab = request('visibility', request('tab', 'all'));
    @endphp
    <div class="d-flex gap-2 mb-4">
        <a href="{{ route('nodal-plans.index', array_merge(request()->except(['tab', 'visibility']), ['tab' => 'all'])) }}"
           class="btn rounded-pill px-4 py-2 {{ in_array($currentTab, ['all', '']) ? 'btn-primary' : 'btn-dark border border-secondary border-opacity-25' }}">
            Todos
        </a>
        <a href="{{ route('nodal-plans.index', array_merge(request()->except(['tab', 'visibility']), ['tab' => 'public'])) }}"
           class="btn rounded-pill px-4 py-2 {{ $currentTab === 'public' ? 'btn-primary' : 'btn-dark border border-secondary border-opacity-25' }}">
            👁 Públicos
        </a>
        <a href="{{ route('nodal-plans.index', array_merge(request()->except(['tab', 'visibility']), ['tab' => 'hidden'])) }}"
           class="btn rounded-pill px-4 py-2 {{ $currentTab === 'hidden' ? 'btn-primary' : 'btn-dark border border-secondary border-opacity-25' }}">
            👁‍🗨 Ocultos
        </a>
    </div>

    {{-- Filtros --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-dark bg-opacity-50 border-secondary border-opacity-10">
        <div class="card-body p-3">
            <form action="{{ route('nodal-plans.index') }}" method="GET" class="row g-3 align-items-center">
                @if(request()->filled('tab'))
                    <input type="hidden" name="tab" value="{{ request('tab') }}">
                @endif

                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-secondary border-opacity-25 text-white-50"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-transparent border-secondary border-opacity-25 text-white" placeholder="Buscar por nome ou código..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="visibility" class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                        <option value="" class="bg-dark text-white">Todas as Visibilidades</option>
                        <option value="public" {{ $currentTab === 'public' ? 'selected' : '' }} class="bg-dark text-white">Públicos</option>
                        <option value="hidden" {{ $currentTab === 'hidden' ? 'selected' : '' }} class="bg-dark text-white">Ocultos</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                        <option value="" class="bg-dark text-white">Todos os Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }} class="bg-dark text-white">Ativos</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }} class="bg-dark text-white">Inativos</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="unlimited" class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                        <option value="" class="bg-dark text-white">Todos os Limites</option>
                        <option value="1" {{ request('unlimited') === '1' ? 'selected' : '' }} class="bg-dark text-white">Ilimitados</option>
                        <option value="0" {{ request('unlimited') === '0' ? 'selected' : '' }} class="bg-dark text-white">Limitados</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Filtrar</button>
                    @if(request()->anyFilled(['search', 'status', 'unlimited', 'visibility', 'tab']))
                        <a href="{{ route('nodal-plans.index') }}" class="btn btn-outline-light rounded-circle" title="Limpar Filtros"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabela de Planos --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="min-width: 1000px;">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0" style="min-width: 180px;">Plano</th>
                            <th class="px-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap">Código</th>
                            <th class="px-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap">Visibilidade</th>
                            <th class="px-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap">Status</th>
                            <th class="px-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap text-end">Mensalidade</th>
                            <th class="px-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap text-center">Usuários</th>
                            <th class="px-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap text-center">Créditos IA</th>
                            <th class="px-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap">Excedente</th>
                            <th class="px-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap text-center">Ilimitado</th>
                            <th class="px-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap text-center">Empresas</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-nowrap text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plans as $plan)
                            @php
                                $uuid = $plan['uuid'] ?? $plan['id'] ?? '';
                                $code = $plan['code'] ?? '';
                                $name = $plan['name'] ?? '';
                                $isPublic = filter_var($plan['is_public'] ?? false, FILTER_VALIDATE_BOOLEAN);
                                $isActive = filter_var($plan['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
                                $isUnlimited = filter_var($plan['is_unlimited'] ?? false, FILTER_VALIDATE_BOOLEAN);
                                $isEnterprise = filter_var($plan['is_enterprise'] ?? false, FILTER_VALIDATE_BOOLEAN);

                                // Preço Mensal
                                $monthlyPriceBrl = isset($plan['monthly_price_brl']) 
                                    ? (float) $plan['monthly_price_brl'] 
                                    : ((int) ($plan['monthly_price_cents'] ?? 0)) / 100;

                                // Preço Excedente
                                $overagePriceCents = isset($plan['overage_price_per_1000_credits_cents'])
                                    ? (int) $plan['overage_price_per_1000_credits_cents']
                                    : (int) ($plan['overage_price_cents'] ?? 0);

                                $overagePriceBrl = isset($plan['overage_price_per_1000_brl'])
                                    ? (float) $plan['overage_price_per_1000_brl']
                                    : $overagePriceCents / 100;

                                // Limites do contrato real
                                $includedUsers = $plan['included_users'] ?? $plan['max_users'] ?? null;
                                $includedAiCredits = $plan['included_ai_credits'] ?? $plan['ai_credits'] ?? null;
                                $orgsCount = (int) ($plan['organizations_count'] ?? $plan['companies_count'] ?? 0);
                            @endphp
                            <tr>
                                <td class="px-4 py-3 border-bottom-0">
                                    <div class="fw-semibold text-white">
                                        {{ $name }}
                                        @if($isEnterprise)
                                            <span class="badge bg-purple text-white ms-1" style="font-size: 0.7rem;">Enterprise</span>
                                        @endif
                                    </div>
                                    @if(!empty($plan['description']))
                                        <div class="text-white-50 small text-truncate" style="max-width: 220px;" title="{{ $plan['description'] }}">{{ $plan['description'] }}</div>
                                    @endif
                                </td>

                                <td class="px-3 py-3 border-bottom-0 text-nowrap">
                                    <span class="badge bg-dark border border-secondary text-white font-monospace px-2 py-1">{{ $code }}</span>
                                </td>

                                <td class="px-3 py-3 border-bottom-0 text-nowrap">
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

                                <td class="px-3 py-3 border-bottom-0 text-nowrap">
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

                                <td class="px-3 py-3 border-bottom-0 text-nowrap text-end fw-semibold text-white">
                                    R$ {{ number_format($monthlyPriceBrl, 2, ',', '.') }}
                                </td>

                                <td class="px-3 py-3 border-bottom-0 text-nowrap text-center">
                                    @if($isUnlimited)
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">∞ Ilimitado</span>
                                    @else
                                        <span class="text-white-50">{{ $includedUsers !== null ? number_format($includedUsers, 0, ',', '.') : '—' }}</span>
                                    @endif
                                </td>

                                <td class="px-3 py-3 border-bottom-0 text-nowrap text-center">
                                    @if($isUnlimited)
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">∞ Ilimitado</span>
                                    @else
                                        <span class="text-white-50">{{ $includedAiCredits !== null ? number_format($includedAiCredits, 0, ',', '.') : '—' }}</span>
                                    @endif
                                </td>

                                <td class="px-3 py-3 border-bottom-0 text-nowrap">
                                    @if($isUnlimited)
                                        <span class="text-white-50">—</span>
                                    @else
                                        @if($overagePriceCents > 0 || $overagePriceBrl > 0)
                                            <span class="text-white-50">R$ {{ number_format($overagePriceBrl, 2, ',', '.') }} / 1.000</span>
                                        @else
                                            <span class="text-white-50">—</span>
                                        @endif
                                    @endif
                                </td>

                                <td class="px-3 py-3 border-bottom-0 text-nowrap text-center">
                                    @if($isUnlimited)
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Sim</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Não</span>
                                    @endif
                                </td>

                                <td class="px-3 py-3 border-bottom-0 text-nowrap text-center fw-bold text-white">
                                    {{ $orgsCount }}
                                </td>

                                <td class="px-4 py-3 border-bottom-0 text-nowrap text-end">
                                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalEditarPlano-{{ $uuid }}">
                                        <i class="bi bi-pencil me-1"></i> Editar
                                    </button>
                                </td>
                            </tr>
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

{{-- Modais de Edição (fora da tabela para preservar a responsividade e semântica HTML) --}}
@foreach($plans as $plan)
    @php
        $uuid = $plan['uuid'] ?? $plan['id'] ?? '';
        $code = $plan['code'] ?? '';
        $name = $plan['name'] ?? '';
        $isPublic = filter_var($plan['is_public'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $isActive = filter_var($plan['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $isUnlimited = filter_var($plan['is_unlimited'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $isEnterprise = filter_var($plan['is_enterprise'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $defaultPostpaidEnabled = filter_var($plan['default_postpaid_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $defaultPostpaidLimitCents = $plan['default_postpaid_limit_cents'] ?? null;
        
        $overagePriceCents = isset($plan['overage_price_per_1000_credits_cents'])
            ? (int) $plan['overage_price_per_1000_credits_cents']
            : (int) ($plan['overage_price_cents'] ?? 0);

        $includedUsers = $plan['included_users'] ?? $plan['max_users'] ?? null;
        $includedAiCredits = $plan['included_ai_credits'] ?? $plan['ai_credits'] ?? null;
        $integrationsLimit = $plan['integrations_limit'] ?? 0;

        $featuresJson = $plan['features_json'] ?? $plan['features'] ?? [];
        $featuresText = is_array($featuresJson) ? implode("\n", $featuresJson) : (string) $featuresJson;

        $orgsCount = (int) ($plan['organizations_count'] ?? $plan['companies_count'] ?? 0);

        // Formatações BRL e inteiros com separadores de milhar
        $monthlyPriceCentsVal = $plan['monthly_price_cents'] ?? 0;
        $monthlyPriceFormatted = number_format(((int) $monthlyPriceCentsVal) / 100, 2, ',', '.');

        $overagePriceFormatted = $overagePriceCents > 0
            ? number_format($overagePriceCents / 100, 2, ',', '.')
            : '';

        $defaultPostpaidLimitFormatted = $defaultPostpaidLimitCents !== null
            ? number_format(((int) $defaultPostpaidLimitCents) / 100, 2, ',', '.')
            : '';

        $includedAiCreditsFormatted = $includedAiCredits !== null
            ? number_format((int) $includedAiCredits, 0, ',', '.')
            : '';

        $includedUsersFormatted = $includedUsers !== null
            ? number_format((int) $includedUsers, 0, ',', '.')
            : '';

        $integrationsLimitFormatted = $integrationsLimit !== null
            ? number_format((int) $integrationsLimit, 0, ',', '.')
            : '0';
    @endphp

    <div class="modal fade" id="modalEditarPlano-{{ $uuid }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary shadow-lg rounded-4">
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

                        <div class="alert alert-info rounded-4 border-0 shadow-sm mb-4 unlimited-warning-alert" style="display: none;">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Os limites individuais não são aplicados enquanto este plano estiver marcado como ilimitado.
                        </div>

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
                                <label class="form-label text-white-50">Mensalidade (R$) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark text-white-50 border-secondary">R$</span>
                                    <input type="text" name="monthly_price_cents" class="form-control bg-dark text-white border-secondary" value="{{ old('monthly_price_cents', $monthlyPriceFormatted) }}" placeholder="1.990,00" required>
                                </div>
                                <div class="form-text text-white-50">Ex: 1.990,00</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-white-50">Usuários Incluídos (included_users)</label>
                                <input type="text" name="included_users" class="form-control bg-dark text-white border-secondary" value="{{ old('included_users', $includedUsersFormatted) }}" placeholder="Ex: 500">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-white-50">Créditos IA Incluídos (included_ai_credits)</label>
                                <input type="text" name="included_ai_credits" class="form-control bg-dark text-white border-secondary" value="{{ old('included_ai_credits', $includedAiCreditsFormatted) }}" placeholder="Ex: 50.000">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label text-white-50">Limite de Integrações</label>
                                <input type="text" name="integrations_limit" class="form-control bg-dark text-white border-secondary" value="{{ old('integrations_limit', $integrationsLimitFormatted) }}" placeholder="Ex: 0">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-white-50">Excedente / 1.000 créditos (R$)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark text-white-50 border-secondary">R$</span>
                                    <input type="text" name="overage_price_per_1000_credits_cents" class="form-control bg-dark text-white border-secondary" value="{{ old('overage_price_per_1000_credits_cents', $overagePriceFormatted) }}" placeholder="Ex: 22,00">
                                </div>
                                <div class="form-text text-white-50">Ex: 22,00</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-white-50">Limite Pós-Pago Padrão (R$)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark text-white-50 border-secondary">R$</span>
                                    <input type="text" name="default_postpaid_limit_cents" class="form-control bg-dark text-white border-secondary" value="{{ old('default_postpaid_limit_cents', $defaultPostpaidLimitFormatted) }}" placeholder="Ex: 500,00">
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label text-white-50">Descrição / Notas Comerciais</label>
                                <textarea name="description" class="form-control bg-dark text-white border-secondary" rows="2">{{ old('description', $plan['description'] ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label text-white-50">Benefícios e Recursos (features_json - 1 por linha)</label>
                                <textarea name="features_text" class="form-control bg-dark text-white border-secondary font-monospace" rows="3" placeholder="Google Workspace + Microsoft 365&#10;APIs customizadas&#10;AI Assistant">{{ old('features_text', $featuresText) }}</textarea>
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
                                <input class="form-check-input" type="checkbox" name="is_enterprise" value="1" id="edit_enterprise_{{ $uuid }}" {{ $isEnterprise ? 'checked' : '' }}>
                                <label class="form-check-label text-white" for="edit_enterprise_{{ $uuid }}">Enterprise</label>
                            </div>

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="default_postpaid_enabled" value="1" id="edit_postpaid_{{ $uuid }}" {{ $defaultPostpaidEnabled ? 'checked' : '' }}>
                                <label class="form-check-label text-white" for="edit_postpaid_{{ $uuid }}">Pós-Pago Habilitado</label>
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
@endforeach

{{-- Modal Novo Plano --}}
<div class="modal fade" id="modalNovoPlano" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary shadow-lg rounded-4">
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

                    <div class="alert alert-info rounded-4 border-0 shadow-sm mb-4 unlimited-warning-alert" style="display: none;">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Os limites individuais não são aplicados enquanto este plano estiver marcado como ilimitado.
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-white-50">Nome do Plano <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-dark text-white border-secondary" placeholder="Ex: Enterprise, Professional" value="{{ old('name') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white-50">Código (Slug) <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control bg-dark text-white border-secondary font-monospace" placeholder="Ex: enterprise, professional" value="{{ old('code') }}" required>
                            <div class="form-text text-warning small">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Identificador permanente. Não poderá ser alterado após a criação.
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-white-50">Mensalidade (R$) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark text-white-50 border-secondary">R$</span>
                                <input type="text" name="monthly_price_cents" class="form-control bg-dark text-white border-secondary" value="{{ old('monthly_price_cents', '0,00') }}" placeholder="1.990,00" required>
                            </div>
                            <div class="form-text text-white-50">Ex: 1.990,00. 0,00 = Sem cobrança.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white-50">Usuários Incluídos (included_users)</label>
                            <input type="text" name="included_users" class="form-control bg-dark text-white border-secondary" value="{{ old('included_users') }}" placeholder="Ex: 500">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white-50">Créditos IA Incluídos (included_ai_credits)</label>
                            <input type="text" name="included_ai_credits" class="form-control bg-dark text-white border-secondary" value="{{ old('included_ai_credits') }}" placeholder="Ex: 50.000">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-white-50">Limite de Integrações</label>
                            <input type="text" name="integrations_limit" class="form-control bg-dark text-white border-secondary" value="{{ old('integrations_limit', '0') }}" placeholder="Ex: 0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white-50">Excedente / 1.000 créditos (R$)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark text-white-50 border-secondary">R$</span>
                                <input type="text" name="overage_price_per_1000_credits_cents" class="form-control bg-dark text-white border-secondary" value="{{ old('overage_price_per_1000_credits_cents') }}" placeholder="Ex: 22,00">
                            </div>
                            <div class="form-text text-white-50">Ex: 22,00</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white-50">Limite Pós-Pago Padrão (R$)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark text-white-50 border-secondary">R$</span>
                                <input type="text" name="default_postpaid_limit_cents" class="form-control bg-dark text-white border-secondary" value="{{ old('default_postpaid_limit_cents') }}" placeholder="Ex: 500,00">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label text-white-50">Descrição / Notas Comerciais</label>
                            <textarea name="description" class="form-control bg-dark text-white border-secondary" rows="2" placeholder="Descreva os termos comerciais deste plano">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label text-white-50">Benefícios e Recursos (features_json - 1 por linha)</label>
                            <textarea name="features_text" class="form-control bg-dark text-white border-secondary font-monospace" rows="3" placeholder="Google Workspace + Microsoft 365&#10;APIs customizadas&#10;AI Assistant">{{ old('features_text') }}</textarea>
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
                            <input class="form-check-input" type="checkbox" name="is_enterprise" value="1" id="new_enterprise">
                            <label class="form-check-label text-white" for="new_enterprise">Enterprise</label>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="default_postpaid_enabled" value="1" id="new_postpaid">
                            <label class="form-check-label text-white" for="new_postpaid">Pós-Pago Habilitado</label>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    function formatCurrencyInput(input) {
        let digits = input.value.replace(/\D/g, '');
        if (!digits) {
            input.value = '';
            return;
        }
        let cents = parseInt(digits, 10);
        let formatted = (cents / 100).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        input.value = formatted;
    }

    function formatIntegerInput(input) {
        let digits = input.value.replace(/\D/g, '');
        if (!digits) {
            input.value = '';
            return;
        }
        let num = parseInt(digits, 10);
        input.value = num.toLocaleString('pt-BR');
    }

    function setupPlanModalLogic(modalEl) {
        if (!modalEl) return;

        const unlimitedSwitch = modalEl.querySelector('input[name="is_unlimited"]');
        const postpaidSwitch = modalEl.querySelector('input[name="default_postpaid_enabled"]');
        const warningAlert = modalEl.querySelector('.unlimited-warning-alert');

        const limitedInputs = modalEl.querySelectorAll(
            'input[name="included_users"], input[name="included_ai_credits"], input[name="integrations_limit"], input[name="overage_price_per_1000_credits_cents"]'
        );
        const postpaidLimitInput = modalEl.querySelector('input[name="default_postpaid_limit_cents"]');

        const currencyInputs = modalEl.querySelectorAll(
            'input[name="monthly_price_cents"], input[name="overage_price_per_1000_credits_cents"], input[name="default_postpaid_limit_cents"]'
        );
        const integerInputs = modalEl.querySelectorAll(
            'input[name="included_users"], input[name="included_ai_credits"], input[name="integrations_limit"]'
        );

        currencyInputs.forEach(input => {
            input.addEventListener('input', function () {
                formatCurrencyInput(this);
            });
        });

        integerInputs.forEach(input => {
            input.addEventListener('input', function () {
                formatIntegerInput(this);
            });
        });

        function updateUnlimitedState() {
            if (!unlimitedSwitch) return;
            const isUnlimited = unlimitedSwitch.checked;

            limitedInputs.forEach(input => {
                input.disabled = isUnlimited;
                if (isUnlimited) {
                    input.classList.add('bg-secondary', 'bg-opacity-25', 'text-white-50');
                } else {
                    input.classList.remove('bg-secondary', 'bg-opacity-25', 'text-white-50');
                }
            });

            if (warningAlert) {
                warningAlert.style.display = isUnlimited ? 'block' : 'none';
            }
        }

        function updatePostpaidState() {
            if (!postpaidSwitch || !postpaidLimitInput) return;
            const isPostpaid = postpaidSwitch.checked;

            postpaidLimitInput.disabled = !isPostpaid;
            if (!isPostpaid) {
                postpaidLimitInput.classList.add('bg-secondary', 'bg-opacity-25', 'text-white-50');
            } else {
                postpaidLimitInput.classList.remove('bg-secondary', 'bg-opacity-25', 'text-white-50');
            }
        }

        if (unlimitedSwitch) {
            unlimitedSwitch.addEventListener('change', updateUnlimitedState);
        }
        if (postpaidSwitch) {
            postpaidSwitch.addEventListener('change', updatePostpaidState);
        }

        updateUnlimitedState();
        updatePostpaidState();
    }

    document.querySelectorAll('.modal').forEach(setupPlanModalLogic);
});
</script>
@endsection
