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
</script>
@endpush
