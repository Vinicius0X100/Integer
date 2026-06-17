@extends('layouts.app')

@section('page-title', 'Detalhes da Campanha')

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

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Visualizar Campanha</h4>
            <p class="text-muted mb-0 small">Histórico de disparo, status da integração n8n e conteúdo enviado.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('campanhas_email.index') }}" class="btn btn-light rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i>Voltar para Listagem
            </a>
            @if($campanha->status !== 'enviando')
                <form id="form-reenviar-campanha" method="POST" action="{{ route('campanhas_email.reenviar', $campanha) }}" class="d-inline">
                    @csrf
                    <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalConfirmarEnvio">
                        <i class="bi bi-arrow-repeat me-2"></i>Disparar / Reenviar
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-4">
        {{-- Detalhes da Campanha (Esquerda) --}}
        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>Informações Gerais</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <span class="text-muted small d-block">Assunto do Email</span>
                        <strong class="fs-5 text-dark dark-text-override">{{ $campanha->titulo }}</strong>
                    </div>

                    <hr class="my-3 opacity-10">

                    <div class="row g-3">
                        <div class="col-6">
                            <span class="text-muted small d-block">Produto Alvo</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold mt-1">
                                {{ \App\Models\CampanhaEmail::produtoLabel($campanha->produto) }}
                            </span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block">Status do Envio</span>
                            <span class="badge bg-{{ \App\Models\CampanhaEmail::statusBadgeClass($campanha->status) }} text-white fw-semibold mt-1">
                                {{ \App\Models\CampanhaEmail::statusLabel($campanha->status) }}
                            </span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block">Tipo de Seleção</span>
                            <span class="text-dark dark-text-override mt-1 d-block fw-semibold">
                                {{ $campanha->destinatarios_tipo == 'todos' ? 'Todos os usuários' : 'Lista selecionada' }}
                            </span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block">Total Destinatários</span>
                            <span class="text-dark dark-text-override mt-1 d-block fw-semibold fs-5">
                                {{ number_format($campanha->total_destinatarios) }}
                            </span>
                        </div>
                        <div class="col-12">
                            <span class="text-muted small d-block">Enviado em</span>
                            <span class="text-dark dark-text-override mt-1 d-block">
                                {{ $campanha->enviado_em ? $campanha->enviado_em->format('d/m/Y H:i:s') : 'Não disparado' }}
                            </span>
                        </div>
                        <div class="col-12">
                            <span class="text-muted small d-block">Criador por</span>
                            <span class="text-dark dark-text-override mt-1 d-block">
                                {{ $criador ? trim(($criador->nome ?? '') . ' ' . ($criador->sobrenome ?? '')) : 'Sistema' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Retorno do Webhook n8n --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-braces me-2 text-primary"></i>Retorno do Servidor n8n</h5>
                </div>
                <div class="card-body p-4 bg-light bg-opacity-5 rounded-bottom-4">
                    @if($campanha->webhook_response)
                        <pre class="m-0 font-monospace text-wrap bg-dark p-3 rounded-4" style="font-size: 0.8rem; color: #a5d6ff; max-height: 200px; overflow-y: auto;">{{ $campanha->webhook_response }}</pre>
                    @else
                        <p class="text-muted mb-0 small">Nenhum log de resposta do webhook n8n disponível para esta campanha.</p>
                    @endif
                </div>
            </div>

            {{-- Guia de Integração n8n --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-question-circle me-2 text-primary"></i>Configuração no n8n</h5>
                </div>
                <div class="card-body p-4">
                    <p class="small text-muted mb-3">Para receber e enviar os emails no n8n:</p>
                    
                    <ol class="small text-muted ps-3 mb-4">
                        <li class="mb-2">Adicione um node do tipo <strong>Webhook</strong> (Método POST, Response: "Immediately").</li>
                        <li class="mb-2">Cole a URL gerada no seu arquivo <code>.env</code> na chave <code>N8N_CAMPANHA_EMAIL_WEBHOOK_URL</code>.</li>
                        <li class="mb-2">Use o node <strong>Loop Over Items</strong> para iterar o array <code>destinatarios</code> do JSON recebido.</li>
                        <li class="mb-2">Use o node <strong>Send Email</strong> ou seu provedor SMTP para disparar o HTML (use <code>@{{nome}}</code> dinâmico no corpo) e configure os headers anti-spam para otimização de entrega.</li>
                    </ol>

                    <div class="p-3 bg-light rounded-4">
                        <h6 class="fw-bold mb-2 small text-dark dark-text-override"><i class="bi bi-shield-check me-1 text-success"></i>Headers Anti-Spam Recomendados:</h6>
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-1"><code>List-Unsubscribe: &lt;mailto:descadastrar@sacratech.com&gt;</code></li>
                            <li class="mb-1"><code>X-Mailer: Integer Email Marketing v1.0</code></li>
                            <li class="mb-1"><code>Precedence: bulk</code></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Corpo do Email Enviado (Direita) --}}
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-envelope-open me-2 text-primary"></i>Visualização do Email</h5>
                    <span class="badge bg-secondary rounded-pill"><i class="bi bi-code-slash"></i> HTML Renderizado</span>
                </div>
                <div class="card-body p-4 bg-light d-flex flex-column" style="min-height: 500px;">
                    <div class="w-100 bg-white border border-bottom-0 rounded-top-4 p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="text-muted small">Assunto:</span>
                            <strong class="small text-dark dark-text-override">{{ $campanha->titulo }}</strong>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small">Para:</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary small">destinatarios@sacratech.com</span>
                        </div>
                    </div>
                    <div class="w-100 border rounded-bottom-4 bg-white shadow-sm flex-grow-1" style="min-height: 400px; position: relative;">
                        <iframe id="preview-frame" sandbox="allow-same-origin" class="w-100 h-100 border-0 rounded-bottom-4" style="background-color: #ffffff;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Confirmação de Envio --}}
    <div class="modal fade" id="modalConfirmarEnvio" tabindex="-1" aria-labelledby="modalConfirmarEnvioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalConfirmarEnvioLabel">Confirmar Envio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4 text-center">
                    <div class="mb-3">
                        <i class="bi bi-send-fill text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="mb-3">Deseja realmente disparar esta campanha?</h5>
                    <p class="text-muted small mb-0">Esta ação irá enviar os emails aos destinatários pelo n8n e não poderá ser desfeita.</p>
                </div>
                <div class="modal-footer border-top-0 d-flex justify-content-center pt-0 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-2\' role=\'status\' aria-hidden=\'true\'></span>Enviando...'; document.getElementById('form-reenviar-campanha').submit()">
                        Sim, Enviar Agora
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Dados passados ao JS de forma segura via JSON para evitar conflito com o compilador Blade --}}
<script type="application/json" id="campanha-html-data">
    {!! json_encode($campanha->corpo_html) !!}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var previewFrame = document.getElementById('preview-frame');

    // Ler o HTML da campanha a partir do elemento JSON (nunca via template literal)
    var rawHtml = '';
    try {
        rawHtml = JSON.parse(document.getElementById('campanha-html-data').textContent || '""');
    } catch (e) {
        rawHtml = '';
    }

    // Substituir variável de nome para visualização amigável no preview
    var renderedHtml = rawHtml.replace(new RegExp('\\{\\{\\s*nome\\s*\\}\\}', 'g'), 'Fulano de Tal');

    if (!renderedHtml) {
        renderedHtml = '<div style="padding:20px;text-align:center;color:#aaa;">Sem conteúdo HTML registrado para esta campanha.</div>';
    }

    var doc = previewFrame.contentDocument || previewFrame.contentWindow.document;
    doc.open();
    doc.write(renderedHtml);
    doc.close();
});
</script>
@endpush
