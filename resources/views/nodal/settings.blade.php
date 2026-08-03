@extends('layouts.app')

@section('page-title', 'Nodal — Configurações')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white mb-1">
                @if(file_exists(public_path('img/Nodal-Icon.png')))
                    <img src="{{ asset('img/Nodal-Icon.png') }}" alt="Nodal" style="height: 26px; width: auto; object-fit: contain; margin-right: 10px; vertical-align: middle;">
                @endif
                Configurações do Nodal
            </h2>
            <p class="text-white-50 mb-0">Configure a integração entre o Integer e o Nodal.</p>
        </div>
        <a href="{{ route('nodal.index') }}" class="btn btn-outline-light rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i> Voltar
        </a>
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

    {{-- Diagnóstico de permissão do .env --}}
    @if(!$envWritable || !$envExists)
        <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4" role="alert">
            <strong><i class="bi bi-shield-exclamation me-2"></i>Problema de Permissão detectado</strong><br>
            O processo PHP (<code>{{ $phpUser }}</code>) <strong>não tem permissão de escrita</strong> no arquivo <code>.env</code>.<br>
            Execute o comando abaixo no servidor via SSH para corrigir:
            <pre class="mt-2 mb-1 p-3 rounded-3 bg-dark text-white small">chmod 664 {{ $envPath }}
# e se necessário:
chown {{ $phpUser }}:{{ $phpUser }} {{ $envPath }}</pre>
        </div>
    @else
        <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4 d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-shield-check-fill fs-5 text-success"></i>
            <div>
                <strong>Permissões OK</strong> — O processo PHP (<code>{{ $phpUser }}</code>) tem acesso de escrita no <code>.env</code>.
                Após salvar, o arquivo será atualizado automaticamente.
            </div>
        </div>
    @endif

    <div class="row g-4">
        {{-- Card de Status --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-wifi me-2"></i>Status da Integração</h5>

                    <div class="d-flex align-items-center gap-3 mb-3">
                        @if($keyConfigured)
                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-success">API Key Configurada</div>
                                <div class="text-muted small">A integração está pronta para uso.</div>
                            </div>
                        @else
                            <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-warning">API Key Não Configurada</div>
                                <div class="text-muted small">Configure a chave para ativar o provisioning.</div>
                            </div>
                        @endif
                    </div>

                    <hr class="border-secondary border-opacity-25">

                    <div class="mb-2">
                        <div class="text-muted small text-uppercase fw-bold mb-1">URL Base</div>
                        <code class="small">{{ $currentUrl ?: 'Não configurada' }}</code>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold mb-1">Endpoint de Provisioning</div>
                        <code class="small">/api/v1/provision/organization</code>
                    </div>

                    <hr class="border-secondary border-opacity-25">

                    <div class="small text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        A chave é lida diretamente do arquivo <code>.env</code> via variável <code>NODAL_SYSTEM_API_KEY</code>.
                    </div>
                </div>
            </div>
        </div>

        {{-- Card de Configuração --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="bi bi-key-fill me-2"></i>Credenciais de Acesso</h5>

                    <form action="{{ route('nodal.save-settings') }}" method="POST" id="settingsForm">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="inputApiKey" class="form-label text-white-50">
                                NODAL_SYSTEM_API_KEY <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       name="nodal_api_key"
                                       id="inputApiKey"
                                       class="form-control bg-dark text-white border-secondary font-monospace @error('nodal_api_key') is-invalid @enderror"
                                       placeholder="{{ $keyConfigured ? '••••••••••••••••••••' : 'Cole aqui a System API Key do Nodal' }}"
                                       value="{{ old('nodal_api_key') }}"
                                       autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="btnToggleKey" title="Mostrar/Ocultar chave">
                                    <i class="bi bi-eye-fill" id="toggleKeyIcon"></i>
                                </button>
                            </div>
                            @if($keyConfigured)
                                <div class="form-text text-success">
                                    <i class="bi bi-check-circle me-1"></i> Chave configurada. Preencha apenas para alterar.
                                </div>
                            @else
                                <div class="form-text text-white-50">
                                    Chave estática gerada no arquivo <code>.env</code> do Nodal como <code>NODAL_SYSTEM_API_KEY</code>.
                                </div>
                            @endif
                            @error('nodal_api_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="inputBaseUrl" class="form-label text-white-50">
                                URL Base do Nodal <span class="text-danger">*</span>
                            </label>
                            <input type="url"
                                   name="nodal_base_url"
                                   id="inputBaseUrl"
                                   class="form-control bg-dark text-white border-secondary font-monospace @error('nodal_base_url') is-invalid @enderror"
                                   placeholder="http://nodal.test"
                                   value="{{ old('nodal_base_url', $currentUrl) }}"
                                   required>
                            <div class="form-text text-white-50">
                                URL base da instalação do Nodal. Ex: <code>http://nodal.test</code> ou <code>https://app.nodal.com.br</code>
                            </div>
                            @error('nodal_base_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="border-secondary border-opacity-25 my-4">

                        <div class="alert alert-secondary rounded-4 border-0 small" role="alert">
                            <i class="bi bi-shield-lock-fill me-2"></i>
                            <strong>Segurança:</strong> A chave é armazenada exclusivamente no arquivo <code>.env</code> do servidor e nunca é exibida na interface. Mantenha-a em segredo.
                        </div>

                        <div class="d-flex gap-3 justify-content-end">
                            <a href="{{ route('nodal.index') }}" class="btn btn-outline-light rounded-pill px-4">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5" id="saveBtn">
                                <i class="bi bi-floppy-fill me-2"></i> Salvar Configurações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle visibilidade da API Key
    document.getElementById('btnToggleKey').addEventListener('click', function () {
        const input = document.getElementById('inputApiKey');
        const icon  = document.getElementById('toggleKeyIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash-fill';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye-fill';
        }
    });

    // Prevenir double submit
    document.getElementById('settingsForm').addEventListener('submit', function () {
        const btn = document.getElementById('saveBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Salvando...';
    });
</script>
@endpush
