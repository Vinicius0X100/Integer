@extends('layouts.app')

@section('page-title', 'Meu Perfil')

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            <!-- Profile Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 text-center">
                    <div class="mb-4 position-relative d-inline-block">
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white mx-auto shadow-sm" style="width: 100px; height: 100px; font-size: 2.5rem;">
                            {{ substr($user->nome ?? 'A', 0, 1) }}
                        </div>
                        <div class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-2" title="Ativo"></div>
                    </div>
                    
                    <h3 class="fw-bold mb-1">{{ $user->nome }} {{ $user->sobrenome }}</h3>
                    <p class="text-muted mb-3">{{ $user->email }}</p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-normal">
                            <i class="bi bi-shield-lock me-1"></i> {{ $user->role ?? 'Usuário' }}
                        </span>
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-normal">
                            <i class="bi bi-calendar3 me-1"></i> Membro desde {{ $user->created_at ? $user->created_at->format('M Y') : 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Account Management Info -->
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                    <h5 class="fw-bold mb-0">Gerenciamento de Conta</h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-light border d-flex align-items-start gap-3 rounded-3 mb-4" role="alert">
                        <i class="bi bi-info-circle-fill text-primary fs-4 mt-1"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Informações de Segurança</h6>
                            <p class="mb-0 small text-muted">
                                Para garantir a máxima segurança, as alterações de dados pessoais, senha e configurações de segurança são centralizadas no Sacratech iD.
                            </p>
                        </div>
                    </div>

                    <div class="text-center py-3">
                        <p class="mb-4 text-muted">Acesse o portal Sacratech iD para gerenciar sua conta.</p>
                        
                        <a href="https://account-id.sacratech.com" target="_blank" class="btn btn-primary btn-lg rounded-pill px-5 d-inline-flex align-items-center gap-2 shadow-sm hover-lift">
                            @if(file_exists(public_path('img/sacratech-id.png')))
                                <img src="{{ asset('img/sacratech-id.png') }}" alt="Sacratech iD" height="20" class="brightness-0 invert-1">
                            @else
                                <i class="bi bi-person-badge-fill"></i>
                            @endif
                            Acessar Sacratech iD
                            <i class="bi bi-box-arrow-up-right small ms-1"></i>
                        </a>
                        
                        <p class="mt-3 small text-muted">
                            Você será redirecionado para uma página segura externa.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    }
    
    /* Invert colors for white icon if needed, though usually unnecessary if png is colored correctly */
    .brightness-0 {
        /* Adjust based on actual image */
    }
</style>
@endsection
