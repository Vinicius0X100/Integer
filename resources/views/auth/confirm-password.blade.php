@extends('layouts.app')

@section('page-title', 'Confirmar Senha')

@section('content')
<div class="container d-flex flex-column justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="max-width: 450px; width: 100%; background-color: var(--apple-card-bg); backdrop-filter: saturate(180%) blur(20px);">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-shield-lock-fill text-primary fs-3"></i>
                </div>
                <h4 class="fw-bold text-white">Área Segura</h4>
                <p class="text-white-50 small mb-0">Por favor, confirme sua senha para continuar.</p>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div class="mb-4">
                    <label for="password" class="form-label text-white fw-medium small">Senha</label>
                    <input id="password" type="password" class="form-control rounded-pill px-3 py-2 bg-dark border-secondary text-white @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" autofocus placeholder="Sua senha de acesso">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold">
                        Confirmar Senha
                    </button>
                    <a href="{{ url()->previous() }}" class="btn btn-link text-white-50 text-decoration-none btn-sm">
                        Cancelar e Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
