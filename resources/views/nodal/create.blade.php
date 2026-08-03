@extends('layouts.app')

@section('page-title', 'Nodal — Nova Empresa')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white mb-1">
                @if(file_exists(public_path('img/Nodal-Icon.png')))
                    <img src="{{ asset('img/Nodal-Icon.png') }}" alt="Nodal" style="height: 26px; width: auto; object-fit: contain; margin-right: 10px; vertical-align: middle;">
                @endif
                Provisionar Nova Empresa
            </h2>
            <p class="text-white-50 mb-0">Preencha os dados abaixo para criar uma nova organização no Nodal.</p>
        </div>
        <a href="{{ route('nodal.index') }}" class="btn btn-outline-light rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i> Voltar
        </a>
    </div>

    {{-- Alerta de API Key não configurada --}}
    @if(empty(env('NODAL_SYSTEM_API_KEY')))
        <div class="alert alert-warning rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Atenção: a <strong>NODAL_SYSTEM_API_KEY</strong> não está configurada. O provisioning falhará até que a chave seja configurada.
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
            <form action="{{ route('nodal.store') }}" method="POST" id="nodalForm">
                @csrf

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
                               value="{{ old('nome') }}"
                               required>
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white-50">
                            Slug <span class="text-muted small">(opcional)</span>
                        </label>
                        <input type="text"
                               name="slug"
                               id="inputSlug"
                               class="form-control bg-dark text-white border-secondary @error('slug') is-invalid @enderror"
                               placeholder="diocese-sao-paulo"
                               value="{{ old('slug') }}">
                        <div class="form-text text-white-50">Identificador único na URL. Se não informado, será gerado automaticamente.</div>
                        @error('slug')
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
                    Este usuário será criado como <strong>Owner</strong> da organização no Nodal. Você é responsável por notificá-lo das credenciais de acesso.
                </p>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-white-50">Nome Completo <span class="text-danger">*</span></label>
                        <input type="text"
                               name="owner_name"
                               class="form-control bg-dark text-white border-secondary @error('owner_name') is-invalid @enderror"
                               placeholder="Ex: João da Silva"
                               value="{{ old('owner_name') }}"
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
                               value="{{ old('owner_email') }}"
                               required>
                        @error('owner_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white-50">Senha Inicial <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text"
                                   name="owner_password"
                                   id="inputPassword"
                                   class="form-control bg-dark text-white border-secondary @error('owner_password') is-invalid @enderror"
                                   placeholder="Mínimo 8 caracteres"
                                   value="{{ old('owner_password') }}"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="btnGeneratePassword" title="Gerar senha segura">
                                <i class="bi bi-stars"></i>
                            </button>
                        </div>
                        <div class="form-text text-white-50">Você deve informar esta senha ao responsável. O Nodal não envia e-mail de boas-vindas.</div>
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
                        <i class="bi bi-cloud-upload me-2"></i> Provisionar no Nodal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-gerar slug a partir do nome
    document.getElementById('inputNome').addEventListener('input', function () {
        const slugField = document.getElementById('inputSlug');
        if (slugField.value === '' || slugField.dataset.manuallyEdited !== 'true') {
            slugField.value = this.value
                .toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-');
        }
    });

    document.getElementById('inputSlug').addEventListener('input', function () {
        this.dataset.manuallyEdited = 'true';
    });

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
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Provisionando...';
    });
</script>
@endpush
