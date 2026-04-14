@extends('layouts.app')

@section('page-title', 'Serviços e Automações')

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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> Verifique os campos e tente novamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom p-4">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                    <i class="bi bi-robot fs-4"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">Serviços e Automações</h5>
                    <small class="text-muted">Configurações do Integer e integrações com automações</small>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <form method="POST" action="{{ route('services_automations.update') }}">
                @csrf
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <div class="fw-bold">Inativação automática de usuários (SisMatriz)</div>
                        <div class="text-muted small">
                            Quando habilitado, o Integer poderá disparar um webhook para automação no n8n para inativar usuários inativos.
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="autoDeactivateSwitch" name="enabled" value="1" {{ $enabled ? 'checked' : '' }}>
                        <label class="form-check-label" for="autoDeactivateSwitch">{{ $enabled ? 'Ativo' : 'Inativo' }}</label>
                    </div>
                </div>

                <div id="autoDeactivateConfig" class="{{ $enabled ? '' : 'd-none' }} mt-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold">Dias máximos sem acesso</label>
                            <input type="number" min="1" max="3650" class="form-control" name="max_inactive_days" value="{{ old('max_inactive_days', $maxInactiveDays) }}">
                            <div class="form-text">Ex.: 90 (recomendado). Após esse limite, o usuário entra na lista de inativação.</div>
                        </div>
                        <div class="col-12 col-md-8">
                            <div class="p-3 bg-light rounded-4">
                                <div class="fw-bold mb-1">Como isso será usado no n8n</div>
                                <div class="small text-muted">
                                    O n8n vai rodar automaticamente a cada 5 dias, ler esta configuração diretamente no banco integer_db (tabela settings) e inativar os usuários do SisMatriz (status = 1) que estiverem acima do limite.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button class="btn btn-primary rounded-pill px-4" type="submit">
                        <i class="bi bi-save me-2"></i>Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const switchEl = document.getElementById('autoDeactivateSwitch')
        const configEl = document.getElementById('autoDeactivateConfig')
        const labelEl = document.querySelector('label[for="autoDeactivateSwitch"]')

        function sync() {
            const enabled = !!switchEl.checked
            configEl.classList.toggle('d-none', !enabled)
            if (labelEl) labelEl.textContent = enabled ? 'Ativo' : 'Inativo'
        }

        if (switchEl) {
            switchEl.addEventListener('change', sync)
            sync()
        }
    })
</script>
@endpush
