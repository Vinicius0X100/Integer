@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center min-vh-75 align-items-center">
        <div class="col-md-5">
            <div class="text-center mb-5">
                <!-- LOGO GRANDE PLACEHOLDER -->
                <!-- <img src="/path/to/logo-large.png" width="100" class="mb-3"> -->
                <h1 class="fw-bold display-6">Bem-vindo ao Integer</h1>
                <p class="text-secondary">Faça login para acessar o sistema administrativo</p>
            </div>

            <div class="card p-4 shadow-lg">
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label text-secondary small fw-bold ms-1">EMAIL</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="nome@exemplo.com">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label text-secondary small fw-bold ms-1">SENHA</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label text-secondary" for="remember">
                                    Lembrar este computador (15 dias)
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Entrar
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-4 text-center border-top pt-3">
                        <small class="text-muted d-block mb-2" style="font-size: 0.75rem;">Identidade provida por</small>
                        @if(file_exists(public_path('img/sacratech-id.png')))
                            <img src="{{ asset('img/sacratech-id.png') }}" alt="Sacratech iD" height="20" class="opacity-75">
                        @else
                            <span class="badge bg-light text-secondary border rounded-pill px-3">Sacratech iD</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
