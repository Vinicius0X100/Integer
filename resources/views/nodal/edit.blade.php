@extends('layouts.app')

@section('page-title', 'Nodal — Editar Empresa')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white mb-1">
                @if(file_exists(public_path('img/Nodal-Icon.png')))
                    <img src="{{ asset('img/Nodal-Icon.png') }}" alt="Nodal" style="height: 26px; width: auto; object-fit: contain; margin-right: 10px; vertical-align: middle;">
                @endif
                Editar Organização: {{ $organization->nome }}
            </h2>
            <p class="text-white-50 mb-0">Atualize os dados da organização e do responsável no Nodal.</p>
        </div>
        <a href="{{ route('nodal.index') }}" class="btn btn-outline-light rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i> Voltar
        </a>
    </div>

    {{-- Alerta de API Key não configurada --}}
    @if(empty(config('services.nodal.api_key')))
        <div class="alert alert-warning rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Atenção: a <strong>NODAL_SYSTEM_API_KEY</strong> não está configurada. A atualização falhará até que a chave seja configurada.
            <a href="{{ route('nodal.settings') }}" class="alert-link ms-1">Configurar agora &rarr;</a>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-4">
            <form action="{{ route('nodal.update', $organization->id) }}" method="POST" id="nodalForm">
                @csrf
                @method('PUT')

                {{-- Dados da Organização --}}
                <h5 class="fw-bold text-white mb-3">
                    <i class="bi bi-building me-2"></i>Dados da Organização
                </h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label text-white-50">Nome da Empresa <span class="text-danger">*</span></label>
                        <input type="text"
                               name="nome"
                               id="inputNome"
                               class="form-control bg-dark text-white border-secondary @error('nome') is-invalid @enderror"
                               placeholder="Ex: Diocese de São Paulo"
                               value="{{ old('nome', $organization->nome) }}"
                               required>
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white-50">
                            Slug <span class="text-muted small">(Não pode ser alterado)</span>
                        </label>
                        <input type="text"
                               class="form-control bg-dark text-white-50 border-secondary"
                               value="{{ $organization->slug }}"
                               readonly disabled>
                        <div class="form-text text-white-50">O slug não pode ser modificado após a criação.</div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label text-white-50">CNPJ <span class="text-muted small">(opcional)</span></label>
                        <input type="text"
                               name="cnpj"
                               class="form-control bg-dark text-white border-secondary @error('cnpj') is-invalid @enderror"
                               placeholder="Ex: 12.345.678/0001-90"
                               value="{{ old('cnpj', $organization->cnpj) }}">
                        @error('cnpj')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white-50">Setor/Ramo <span class="text-muted small">(opcional)</span></label>
                        <input type="text"
                               name="industry"
                               class="form-control bg-dark text-white border-secondary @error('industry') is-invalid @enderror"
                               placeholder="Ex: Tecnologia da Informação"
                               value="{{ old('industry', $organization->industry) }}">
                        @error('industry')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white-50">Endereço <span class="text-muted small">(opcional)</span></label>
                        <input type="text"
                               name="address"
                               class="form-control bg-dark text-white border-secondary @error('address') is-invalid @enderror"
                               placeholder="Ex: Av. Paulista, 1000 - SP"
                               value="{{ old('address', $organization->address) }}">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="border-secondary border-opacity-25 my-4">

                {{-- Dados do Responsável --}}
                <h5 class="fw-bold text-white mb-3">
                    <i class="bi bi-person-badge me-2"></i>Responsável / Administrador
                </h5>
                <p class="text-white-50 small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    A API do Nodal localizará o usuário dono (Owner) da organização e atualizará automaticamente o perfil e as credenciais dele.
                </p>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-white-50">Nome Completo <span class="text-danger">*</span></label>
                        <input type="text"
                               name="owner_name"
                               class="form-control bg-dark text-white border-secondary @error('owner_name') is-invalid @enderror"
                               placeholder="Ex: João da Silva"
                               value="{{ old('owner_name', $organization->owner_name) }}"
                               required>
                        @error('owner_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white-50">E-mail <span class="text-danger">*</span></label>
                        <input type="email"
                               name="owner_email"
                               class="form-control bg-dark text-white border-secondary @error('owner_email') is-invalid @enderror"
                               placeholder="joao@diocesesp.com.br"
                               value="{{ old('owner_email', $organization->owner_email) }}"
                               required>
                        @error('owner_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white-50">Nova Senha <span class="text-muted small">(Deixe em branco para manter a atual)</span></label>
                        <div class="input-group">
                            <input type="text"
                                   name="owner_password"
                                   id="inputPassword"
                                   class="form-control bg-dark text-white border-secondary @error('owner_password') is-invalid @enderror"
                                   placeholder="Mínimo 8 caracteres"
                                   value="{{ old('owner_password') }}">
                            <button class="btn btn-outline-secondary" type="button" id="btnGeneratePassword" title="Gerar senha segura">
                                <i class="bi bi-stars"></i>
                            </button>
                        </div>
                        @error('owner_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="border-secondary border-opacity-25 my-4">

                {{-- Plano de Licenciamento (Nodal) --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-white mb-0">
                        <i class="bi bi-box-seam-fill me-2 text-primary"></i>Plano de Licenciamento (Nodal)
                    </h5>
                    @if($organization->nodal_organization_uuid)
                        <button type="button" class="btn btn-outline-primary rounded-pill px-4 btn-sm" data-bs-toggle="modal" data-bs-target="#modalAlterarPlano">
                            <i class="bi bi-arrow-repeat me-1"></i> Alterar Plano
                        </button>
                    @endif
                </div>

                <div class="card bg-dark bg-opacity-50 border-secondary border-opacity-25 rounded-4 mb-4">
                    <div class="card-body p-4">
                        @if($currentPlan)
                            <div class="row align-items-center g-3">
                                <div class="col-md-4">
                                    <div class="text-white-50 small">Plano Atual</div>
                                    <div class="fw-bold text-white fs-5">{{ $currentPlan['name'] ?? '—' }}</div>
                                    @if(!empty($currentPlan['code']))
                                        <span class="badge bg-dark border border-secondary text-white font-monospace mt-1">{{ $currentPlan['code'] }}</span>
                                    @endif
                                </div>
                                <div class="col-md-2">
                                    <div class="text-white-50 small">Visibilidade</div>
                                    @if(!empty($currentPlan['is_public']))
                                        <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">
                                            <i class="bi bi-eye me-1"></i> Público
                                        </span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">
                                            <i class="bi bi-eye-slash me-1"></i> Oculto
                                        </span>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <div class="text-white-50 small">Mensalidade</div>
                                    <div class="fw-semibold text-white">
                                        @if(isset($currentPlan['monthly_price_cents']))
                                            R$ {{ number_format($currentPlan['monthly_price_cents'] / 100, 2, ',', '.') }}
                                        @else
                                            R$ 0,00
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-white-50 small">Limites</div>
                                    @if(!empty($currentPlan['is_unlimited']))
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">∞ Ilimitado</span>
                                    @else
                                        <div class="text-white-50 small">
                                            Usuários: {{ $currentPlan['max_users'] ?? '∞' }} | IA: {{ $currentPlan['ai_credits'] ?? '∞' }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-white-50 py-2">
                                <i class="bi bi-info-circle me-2 text-warning"></i>
                                Nenhum plano ativo informado pelo Nodal para esta organização.
                            </div>
                        @endif
                    </div>
                </div>

                <hr class="border-secondary border-opacity-25 my-4">

                <div class="d-flex gap-3 justify-content-end">
                    <a href="{{ route('nodal.index') }}" class="btn btn-outline-light rounded-pill px-4">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5" id="submitBtn">
                        <i class="bi bi-cloud-arrow-up me-2"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Alterar Plano de Licenciamento --}}
@if($organization->nodal_organization_uuid)
<div class="modal fade" id="modalAlterarPlano" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary">
            <form action="{{ route('nodal-plans.assign', $organization->nodal_organization_uuid) }}" method="POST" id="formAlterarPlano">
                @csrf
                @method('PATCH')

                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-box-seam-fill me-2 text-primary"></i>Alterar Plano de Licenciamento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4" id="stepSelecaoPlano">
                    <p class="text-white-50 mb-4">
                        Selecione o novo plano comercial ou administrativo para a organização <strong>{{ $organization->nome }}</strong>.
                    </p>

                    @php
                        $publicPlans = collect($plans)->filter(fn($p) => !empty($p['is_public']) && (!isset($p['is_active']) || $p['is_active']));
                        $hiddenPlans = collect($plans)->filter(fn($p) => empty($p['is_public']) && (!isset($p['is_active']) || $p['is_active']));
                    @endphp

                    {{-- Planos Públicos --}}
                    <div class="mb-4">
                        <h6 class="fw-bold text-info text-uppercase small tracking-wider mb-3">
                            <i class="bi bi-eye me-1"></i> Planos Públicos
                        </h6>
                        <div class="row g-3">
                            @forelse($publicPlans as $p)
                                @php
                                    $pUuid = $p['uuid'] ?? $p['id'] ?? '';
                                    $pName = $p['name'] ?? '';
                                    $pPrice = (int) ($p['monthly_price_cents'] ?? 0);
                                @endphp
                                <div class="col-md-6">
                                    <div class="form-check card bg-dark border-secondary p-3 rounded-3 shadow-sm h-100 cursor-pointer">
                                        <input class="form-check-input me-2 plan-radio-option" type="radio" name="plan_uuid" id="plan_{{ $pUuid }}" value="{{ $pUuid }}" data-name="{{ $pName }}" required>
                                        <label class="form-check-label w-100 cursor-pointer" for="plan_{{ $pUuid }}">
                                            <div class="fw-bold text-white">{{ $pName }}</div>
                                            <div class="text-white-50 small">R$ {{ number_format($pPrice / 100, 2, ',', '.') }} / mês</div>
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-white-50 small">Nenhum plano público disponível.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Planos Ocultos --}}
                    <div class="mb-4">
                        <h6 class="fw-bold text-warning text-uppercase small tracking-wider mb-3">
                            <i class="bi bi-eye-slash me-1"></i> Planos Ocultos (Administrativos / Especiais)
                        </h6>
                        <div class="row g-3">
                            @forelse($hiddenPlans as $p)
                                @php
                                    $pUuid = $p['uuid'] ?? $p['id'] ?? '';
                                    $pName = $p['name'] ?? '';
                                    $pPrice = (int) ($p['monthly_price_cents'] ?? 0);
                                @endphp
                                <div class="col-md-6">
                                    <div class="form-check card bg-dark border-secondary p-3 rounded-3 shadow-sm h-100 cursor-pointer">
                                        <input class="form-check-input me-2 plan-radio-option" type="radio" name="plan_uuid" id="plan_{{ $pUuid }}" value="{{ $pUuid }}" data-name="{{ $pName }}" required>
                                        <label class="form-check-label w-100 cursor-pointer" for="plan_{{ $pUuid }}">
                                            <div class="fw-bold text-white">{{ $pName }}</div>
                                            <div class="text-white-50 small">R$ {{ number_format($pPrice / 100, 2, ',', '.') }} / mês</div>
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-white-50 small">Nenhum plano oculto disponível.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary rounded-pill px-4" id="btnAvancarConfirmacao">
                            Avançar &rarr;
                        </button>
                    </div>
                </div>

                {{-- Passo de Confirmação --}}
                <div class="modal-body p-4 d-none" id="stepConfirmacaoPlano">
                    <div class="alert alert-warning rounded-4 border-0 shadow-sm mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span id="textoModalConfirmacaoHeader">
                            Alterar o plano da <strong>{{ $organization->nome }}</strong> de <strong>{{ $currentPlan['name'] ?? 'Plano Atual' }}</strong> para <strong id="nomeNovoPlanoConfirmacao"></strong>?
                        </span>
                    </div>

                    <p class="text-white-50 mb-4">
                        A alteração entra em vigor imediatamente no período de faturamento aberto. O consumo já realizado no período será preservado.
                    </p>

                    <div class="d-flex gap-3 justify-content-end">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" id="btnVoltarSelecao">
                            Voltar
                        </button>
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">
                            Confirmar alteração
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    // Gerar senha segura
    document.getElementById('btnGeneratePassword').addEventListener('click', function () {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789@#$!';
        let password = '';
        for (let i = 0; i < 14; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('inputPassword').value = password;
    });

    // Prevenir double submit
    document.getElementById('nodalForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Salvando...';
    });

    // Modal de Troca de Plano (Passos de Seleção e Confirmação)
    const btnAvancar = document.getElementById('btnAvancarConfirmacao');
    const btnVoltar = document.getElementById('btnVoltarSelecao');
    const stepSelecao = document.getElementById('stepSelecaoPlano');
    const stepConfirmacao = document.getElementById('stepConfirmacaoPlano');
    const nomeNovoPlanoSpan = document.getElementById('nomeNovoPlanoConfirmacao');

    if (btnAvancar) {
        btnAvancar.addEventListener('click', function () {
            const selectedRadio = document.querySelector('input[name="plan_uuid"]:checked');
            if (!selectedRadio) {
                alert('Por favor, selecione um plano.');
                return;
            }

            const planName = selectedRadio.getAttribute('data-name');
            nomeNovoPlanoSpan.innerText = planName;

            stepSelecao.classList.add('d-none');
            stepConfirmacao.classList.remove('d-none');
        });
    }

    if (btnVoltar) {
        btnVoltar.addEventListener('click', function () {
            stepConfirmacao.classList.add('d-none');
            stepSelecao.classList.remove('d-none');
        });
    }
</script>
@endpush

