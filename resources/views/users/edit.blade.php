@extends('layouts.app')

@section('page-title', 'Editar Usuário')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Editar Usuário: {{ $user->nome }} {{ $user->sobrenome }}</h5>
                        <a href="{{ route('users.index') }}" class="btn btn-light rounded-pill px-4">
                            <i class="bi bi-arrow-left me-2"></i> Voltar
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Informações Pessoais -->
                        <h6 class="text-uppercase text-muted fw-bold small mb-3">Informações Pessoais</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="nome" class="form-label fw-medium">Nome <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome', $user->nome) }}" required>
                                @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="sobrenome" class="form-label fw-medium">Sobrenome <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('sobrenome') is-invalid @enderror" id="sobrenome" name="sobrenome" value="{{ old('sobrenome', $user->sobrenome) }}" required>
                                @error('sobrenome') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="data_nascimento" class="form-label fw-medium">Data de Nascimento</label>
                                <input type="date" class="form-control @error('data_nascimento') is-invalid @enderror" id="data_nascimento" name="data_nascimento" value="{{ old('data_nascimento', $user->data_nascimento ? $user->data_nascimento->format('Y-m-d') : '') }}">
                                @error('data_nascimento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="telefone" class="form-label fw-medium">Telefone</label>
                                <input type="text" class="form-control @error('telefone') is-invalid @enderror" id="telefone" name="telefone" value="{{ old('telefone', $user->telefone) }}">
                                @error('telefone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Conta e Acesso -->
                        <h6 class="text-uppercase text-muted fw-bold small mb-3 border-top pt-4">Conta e Acesso</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="nome_usuario" class="form-label fw-medium">Nome de Usuário</label>
                                <input type="text" class="form-control @error('nome_usuario') is-invalid @enderror" id="nome_usuario" name="nome_usuario" value="{{ old('nome_usuario', $user->nome_usuario) }}">
                                @error('nome_usuario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="senha" class="form-label fw-medium">Nova Senha (deixe em branco para manter)</label>
                                <input type="password" class="form-control @error('senha') is-invalid @enderror" id="senha" name="senha">
                                @error('senha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="senha_confirmation" class="form-label fw-medium">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="senha_confirmation" name="senha_confirmation">
                            </div>
                            <div class="col-md-6">
                                <label for="papel" class="form-label fw-medium">Papel <span class="text-danger">*</span></label>
                                <select class="form-select @error('papel') is-invalid @enderror" id="papel" name="papel" required>
                                    <option value="usuario" {{ old('papel', $user->papel) == 'usuario' ? 'selected' : '' }}>Usuário</option>
                                    <option value="admin" {{ old('papel', $user->papel) == 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                                @error('papel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="1" {{ old('status', $user->status) == '1' ? 'selected' : '' }}>Ativo</option>
                                    <option value="0" {{ old('status', $user->status) == '0' ? 'selected' : '' }}>Inativo</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Endereço -->
                        <h6 class="text-uppercase text-muted fw-bold small mb-3 border-top pt-4">Endereço</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="cep" class="form-label fw-medium">CEP</label>
                                <input type="text" class="form-control @error('cep') is-invalid @enderror" id="cep" name="cep" value="{{ old('cep', $user->cep) }}">
                            </div>
                            <div class="col-md-8">
                                <label for="endereco" class="form-label fw-medium">Endereço</label>
                                <input type="text" class="form-control @error('endereco') is-invalid @enderror" id="endereco" name="endereco" value="{{ old('endereco', $user->endereco) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="numero" class="form-label fw-medium">Número</label>
                                <input type="text" class="form-control @error('numero') is-invalid @enderror" id="numero" name="numero" value="{{ old('numero', $user->numero) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="bairro" class="form-label fw-medium">Bairro</label>
                                <input type="text" class="form-control @error('bairro') is-invalid @enderror" id="bairro" name="bairro" value="{{ old('bairro', $user->bairro) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="cidade" class="form-label fw-medium">Cidade</label>
                                <input type="text" class="form-control @error('cidade') is-invalid @enderror" id="cidade" name="cidade" value="{{ old('cidade', $user->cidade) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="estado" class="form-label fw-medium">Estado</label>
                                <input type="text" class="form-control @error('estado') is-invalid @enderror" id="estado" name="estado" value="{{ old('estado', $user->estado) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="pais" class="form-label fw-medium">País</label>
                                <input type="text" class="form-control @error('pais') is-invalid @enderror" id="pais" name="pais" value="{{ old('pais', $user->pais) }}">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="{{ route('users.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                            <button type="submit" id="btnSubmit" class="btn btn-primary rounded-pill px-5 fw-bold">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('editUserForm');
        const btnSubmit = document.getElementById('btnSubmit');

        if (form) {
            form.addEventListener('submit', function() {
                // Pequeno delay para garantir que a validação HTML5 ocorra
                setTimeout(() => {
                    if (form.checkValidity()) {
                        // Botão loading
                        btnSubmit.disabled = true;
                        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Salvando...';
                    }
                }, 0);
            });
        }

        const cepInput = document.getElementById('cep');
        const cepLoading = document.getElementById('cep-loading');
        const fields = {
            endereco: document.getElementById('endereco'),
            bairro: document.getElementById('bairro'),
            cidade: document.getElementById('cidade'),
            estado: document.getElementById('estado'),
            pais: document.getElementById('pais'),
            numero: document.getElementById('numero')
        };

        if (cepInput) {
            cepInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                // Máscara simples 00000-000
                if (value.length > 5) {
                    value = value.substring(0, 5) + '-' + value.substring(5, 8);
                }
                e.target.value = value;

                const cep = value.replace('-', '');

                if (cep.length === 8) {
                    cepInput.style.cursor = 'wait';
                    
                    fetch(`https://viacep.com.br/ws/${cep}/json/`)
                        .then(response => response.json())
                        .then(data => {
                            if (!data.erro) {
                                if(fields.endereco) fields.endereco.value = data.logradouro;
                                if(fields.bairro) fields.bairro.value = data.bairro;
                                if(fields.cidade) fields.cidade.value = data.localidade;
                                if(fields.estado) fields.estado.value = data.uf;
                                if(fields.pais) fields.pais.value = 'Brasil';
                                
                                if(fields.numero) fields.numero.focus();
                            }
                        })
                        .catch(err => console.error('Erro ViaCEP:', err))
                        .finally(() => {
                            cepInput.style.cursor = 'text';
                        });
                }
            });
        }
    });
</script>
@endsection
