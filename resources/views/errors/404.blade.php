@extends('layouts.app')

@section('page-title', 'Página Não Encontrada')

@section('content')
<div class="d-flex flex-column align-items-center justify-content-center h-100 py-5">
    <div class="text-center">
        <h1 class="display-1 fw-bold text-secondary mb-0" style="font-size: 8rem; opacity: 0.2;">404</h1>
        <h2 class="h2 fw-bold mb-3">Página não encontrada</h2>
        <p class="text-muted mb-4" style="max-width: 400px; margin: 0 auto;">
            Desculpe, a página que você está procurando não existe ou foi movida. Verifique a URL ou volte para a página inicial.
        </p>
        
        <div class="d-flex justify-content-center gap-3">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i> Voltar
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-house-door me-2"></i> Ir para o Início
            </a>
        </div>
    </div>
</div>

<style>
    /* Ensure full height if possible */
    main {
        min-height: 80vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
</style>
@endsection
