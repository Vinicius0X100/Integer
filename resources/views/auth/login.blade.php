@extends('layouts.app')

@section('content')
<style>
    /* Custom Scrollbar for this page only if needed, though mostly hidden */
    ::-webkit-scrollbar {
        width: 8px;
    }
    ::-webkit-scrollbar-track {
        background: transparent;
    }
    ::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 4px;
    }

    .login-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(40px);
        -webkit-backdrop-filter: blur(40px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        box-shadow: 0 40px 80px -12px rgba(0, 0, 0, 0.6);
        padding: 3.5rem 2.5rem;
        width: 100%;
        max-width: 520px;
        animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }
    
    /* Subtle shine effect */
    .login-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.03), transparent);
        animation: shine 8s infinite linear;
        pointer-events: none;
    }

    .form-control-apple {
        background: rgba(0, 0, 0, 0.25) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        border-radius: 14px;
        height: 54px;
        padding-left: 1.2rem;
        padding-right: 1.2rem;
        font-size: 16px;
        font-weight: 400;
        transition: all 0.25s ease;
    }

    .form-control-apple:focus {
        background: rgba(0, 0, 0, 0.4) !important;
        border-color: #2997ff !important;
        box-shadow: none !important;
        outline: none !important;
    }

    .form-control-apple::placeholder {
        color: rgba(255, 255, 255, 0.3);
    }

    .btn-apple {
        background: #0071e3;
        background: linear-gradient(180deg, #0077ED 0%, #006ED9 100%);
        border: none;
        border-radius: 14px;
        height: 54px;
        font-weight: 600;
        font-size: 17px;
        letter-spacing: -0.01em;
        box-shadow: 0 4px 12px rgba(0, 113, 227, 0.3);
        transition: background-color 0.2s ease;
    }

    .btn-apple:hover {
        background: linear-gradient(180deg, #0080FF 0%, #0075E6 100%);
        box-shadow: 0 4px 12px rgba(0, 113, 227, 0.3);
    }

    .btn-apple:active {
        background: #006ED9;
        box-shadow: none;
    }

    .apple-checkbox .form-check-input {
        background-color: rgba(255,255,255,0.1);
        border-color: rgba(255,255,255,0.3);
        width: 1.2em;
        height: 1.2em;
        margin-top: 0.15em;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .apple-checkbox .form-check-input:checked {
        background-color: #2997ff;
        border-color: #2997ff;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
    }

    .apple-link {
        color: #2997ff;
        transition: color 0.2s ease;
    }
    .apple-link:hover {
        color: #5fb0ff;
        text-decoration: underline !important;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    @keyframes shine {
        0% { left: -100%; }
        20% { left: 100%; }
        100% { left: 100%; }
    }
    
    .logo-glow {
        filter: drop-shadow(0 0 30px rgba(255,255,255,0.15));
        transition: transform 0.5s ease;
    }
    
    .logo-glow:hover {
        transform: scale(1.05);
    }
    
    .glass-pill {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
    }
</style>

<div class="container d-flex justify-content-center align-items-center min-vh-100 py-5">
    <div class="login-card">
        <div class="text-center mb-5">
            <img src="{{ asset('img/logo-white-full.png') }}" alt="Integer Logo" class="mb-1 img-fluid logo-glow" style="max-height: 180px;">
            <h1 class="fw-semibold text-white h4 mb-2" style="letter-spacing: -0.5px;">Bem-vindo</h1>
            <p class="text-white-50 small">Acesse sua conta para continuar</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="mb-4">
                <label for="email" class="form-label text-white-50 small fw-medium ms-2 mb-2">E-mail</label>
                <input id="email" type="email" class="form-control form-control-apple @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="nome@exemplo.com">
                @error('email')
                    <span class="text-danger small mt-2 d-block ms-2 fade-in">
                        <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label for="password" class="form-label text-white-50 small fw-medium ms-2 mb-0">Senha</label>
                </div>
                <input id="password" type="password" class="form-control form-control-apple @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
                @error('password')
                    <span class="text-danger small mt-2 d-block ms-2 fade-in">
                        <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check apple-checkbox ms-1">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label text-white-50 small pt-1" for="remember">
                        Lembrar-me
                    </label>
                </div>
                
                @if (Route::has('password.request'))
                    <a class="apple-link text-decoration-none small fw-medium" href="{{ route('password.request') }}">
                        Esqueceu a senha?
                    </a>
                @endif
            </div>

            <div class="d-grid pt-2">
                <button type="submit" class="btn btn-primary btn-apple text-white">
                    Entrar
                </button>
            </div>
        </form>
        
        <div class="mt-5 text-center pt-4 border-top border-white border-opacity-10">
            <p class="text-white-50 small mb-3" style="font-size: 0.75rem;">Identidade segura por</p>
            <div class="d-inline-flex align-items-center justify-content-center px-4 py-2 rounded-pill glass-pill">
                @if(file_exists(public_path('img/sacratech-id.png')))
                    <img src="{{ asset('img/sacratech-id.png') }}" alt="Sacratech iD" height="18" class="me-2 opacity-90">
                @endif
                <span class="text-white small fw-medium tracking-wide">Sacratech iD</span>
            </div>
        </div>
    </div>
</div>
@endsection
