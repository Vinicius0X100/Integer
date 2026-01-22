@extends('layouts.app')

@section('page-title', 'Editar Usuário - SisMatriz Principal')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Editar Usuário SisMatriz Principal: {{ $user->name }}</h5>
                        <a href="{{ route('sismatriz-main.index') }}" class="btn btn-light rounded-pill px-4">
                            <i class="bi bi-arrow-left me-2"></i> Voltar
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('sismatriz-main.update', $user->id) }}" method="POST" id="editSisMatrizMainUserForm">
                        @csrf
                        @method('PUT')

                        <!-- Informações Básicas -->
                        <h6 class="text-uppercase text-muted fw-bold small mb-3">Informações da Conta</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-medium">Nome Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="user" class="form-label fw-medium">Nome de Usuário (Login) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('user') is-invalid @enderror" id="user" name="user" value="{{ old('user', $user->user) }}" required>
                                @error('user') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <label for="email" class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Configurações -->
                        <h6 class="text-uppercase text-muted fw-bold small mb-3 border-top pt-4">Configurações de Acesso</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="paroquia_id" class="form-label fw-medium">Paróquia <span class="text-danger">*</span></label>
                                <select class="form-select @error('paroquia_id') is-invalid @enderror" id="paroquia_id" name="paroquia_id" required>
                                    <option value="">Selecione a Paróquia...</option>
                                    @foreach($paroquias as $paroquia)
                                        <option value="{{ $paroquia->id }}" {{ old('paroquia_id', $user->paroquia_id) == $paroquia->id ? 'selected' : '' }}>
                                            {{ $paroquia->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('paroquia_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="0" {{ old('status', $user->status) == '0' ? 'selected' : '' }}>Ativo</option>
                                    <option value="1" {{ old('status', $user->status) == '1' ? 'selected' : '' }}>Inativo</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Cargos e Permissões</label>
                                <div class="card bg-light border-0 p-3" style="max-height: 250px; overflow-y: auto;">
                                    <div class="row g-2">
                                        @foreach($roles as $id => $roleName)
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $id }}" id="role_{{ $id }}" 
                                                        {{ in_array($id, old('roles', $userRoles)) ? 'checked' : '' }}>
                                                    <label class="form-check-label small" for="role_{{ $id }}">
                                                        {{ $roleName }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('roles') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Segurança -->
                        <h6 class="text-uppercase text-muted fw-bold small mb-3 border-top pt-4">Segurança</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-medium">Nova Senha (deixe em branco para manter)</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-medium">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="{{ route('sismatriz-main.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
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
        const form = document.getElementById('editSisMatrizMainUserForm');
        const btnSubmit = document.getElementById('btnSubmit');

        if (form) {
            form.addEventListener('submit', function() {
                setTimeout(() => {
                    if (form.checkValidity()) {
                        btnSubmit.disabled = true;
                        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Salvando...';
                    }
                }, 0);
            });
        }
    });
</script>
@endsection
