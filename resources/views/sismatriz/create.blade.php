@extends('layouts.app')

@section('page-title', 'Novo Usuário - SisMatriz')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Criar Novo Usuário SisMatriz Ticket</h5>
                        <a href="{{ route('sismatriz.index') }}" class="btn btn-light rounded-pill px-4">
                            <i class="bi bi-arrow-left me-2"></i> Voltar
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('sismatriz.store') }}" method="POST" id="createSisMatrizUserForm">
                        @csrf

                        <!-- Informações Básicas -->
                        <h6 class="text-uppercase text-muted fw-bold small mb-3">Informações da Conta</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-medium">Nome Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label fw-medium">Papel (Role) <span class="text-danger">*</span></label>
                                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                    <option value="">Selecione...</option>
                                    <option value="organizer" {{ old('role') == 'organizer' ? 'selected' : '' }}>Organizador</option>
                                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Usuário</option>
                                </select>
                                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="sacratech_id" class="form-label fw-medium">Sacratech ID</label>
                                <input type="number" class="form-control @error('sacratech_id') is-invalid @enderror" id="sacratech_id" name="sacratech_id" value="{{ old('sacratech_id') }}">
                                <div class="form-text">ID do usuário na tabela principal (opcional).</div>
                                @error('sacratech_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Segurança -->
                        <h6 class="text-uppercase text-muted fw-bold small mb-3 border-top pt-4">Segurança</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-medium">Senha <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-medium">Confirmar Senha <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="{{ route('sismatriz.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                            <button type="submit" id="btnSubmit" class="btn btn-primary rounded-pill px-5 fw-bold">Criar Usuário</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('createSisMatrizUserForm');
        const btnSubmit = document.getElementById('btnSubmit');

        if (form) {
            form.addEventListener('submit', function() {
                setTimeout(() => {
                    if (form.checkValidity()) {
                        btnSubmit.disabled = true;
                        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Criando...';
                    }
                }, 0);
            });
        }
    });
</script>
@endsection
