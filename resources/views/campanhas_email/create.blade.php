@extends('layouts.app')

@section('page-title', 'Nova Campanha de Email')

@section('content')
<div class="container-fluid py-4">
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">Criar Nova Campanha</h4>
            <p class="text-muted mb-0 small">Configure e envie emails personalizados com preview ao vivo.</p>
        </div>
        <a href="{{ route('campanhas_email.index') }}" class="btn btn-light rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <form method="POST" action="{{ route('campanhas_email.store') }}" id="form-campanha">
        @csrf
        <input type="hidden" name="acao" id="input-acao" value="rascunho">

        <div class="row g-4">
            {{-- Coluna do Editor (Esquerda) --}}
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom p-4">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Configurações e Conteúdo</h5>
                    </div>
                    <div class="card-body p-4">
                        {{-- Título / Assunto --}}
                        <div class="mb-4">
                            <label for="titulo" class="form-label fw-bold">Assunto do Email</label>
                            <input type="text" class="form-control @error('titulo') is-invalid @enderror" id="titulo" name="titulo" value="{{ old('titulo') }}" placeholder="Ex: Novidades da plataforma Sacratech" required>
                            @error('titulo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Selecionar Produto --}}
                        <div class="mb-4">
                            <label for="produto" class="form-label fw-bold">Produto / Serviço</label>
                            <select class="form-select @error('produto') is-invalid @enderror" id="produto" name="produto" required>
                                <option value="all" {{ old('produto') == 'all' ? 'selected' : '' }}>Todos os Produtos (Deduplicado)</option>
                                <option value="sacratech_id" {{ old('produto') == 'sacratech_id' ? 'selected' : '' }}>Sacratech iD</option>
                                <option value="sismatriz_ticket" {{ old('produto') == 'sismatriz_ticket' ? 'selected' : '' }}>SisMatriz Ticket</option>
                                <option value="sismatriz_main" {{ old('produto') == 'sismatriz_main' ? 'selected' : '' }}>SisMatriz Principal</option>
                                <option value="airlink" {{ old('produto') == 'airlink' ? 'selected' : '' }}>Airlink Locate (Em breve)</option>
                            </select>
                            @error('produto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-warning d-none" id="warning-airlink">
                                <i class="bi bi-exclamation-circle me-1"></i> Este produto está temporariamente desativado (em breve).
                            </div>
                        </div>

                        {{-- Tipo de Destinatários --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold d-block">Destinatários</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="destinatarios_tipo" id="tipo-todos" value="todos" {{ old('destinatarios_tipo', 'todos') == 'todos' ? 'checked' : '' }}>
                                <label class="form-check-label" for="tipo-todos">Todos os usuários ativos</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="destinatarios_tipo" id="tipo-selecionados" value="selecionados" {{ old('destinatarios_tipo') == 'selecionados' ? 'checked' : '' }}>
                                <label class="form-check-label" for="tipo-selecionados">Selecionar usuários manualmente</label>
                            </div>
                        </div>

                        {{-- Lista de Usuários (Carregamento Dinâmico) --}}
                        <div class="mb-4 d-none" id="container-lista-usuarios">
                            <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                                <span>Usuários do Produto</span>
                                <span class="badge bg-secondary" id="badge-selecionados">0 selecionados</span>
                            </label>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control form-control-sm" id="busca-usuario" placeholder="Buscar por nome ou email...">
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" id="btn-selecionar-todos">Selecionar Todos</button>
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-danger" id="btn-limpar-selecao">Limpar Seleção</button>
                            </div>
                            <div class="border rounded-4 p-3 overflow-y-auto bg-light bg-opacity-10" style="max-height: 250px;" id="lista-usuarios">
                                <div class="text-center py-4 text-muted" id="loader-usuarios">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Buscando usuários...
                                </div>
                                <div id="usuarios-check-list"></div>
                            </div>
                            @error('destinatarios_ids')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- HTML Editor --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="corpo_html" class="form-label fw-bold m-0">Código HTML do Email</label>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-template-padrao">
                                    Carregar Template Padrão
                                </button>
                            </div>
                            <textarea class="form-control font-monospace @error('corpo_html') is-invalid @enderror" id="corpo_html" name="corpo_html" rows="12" placeholder="Coloque o HTML do seu email aqui..." required style="font-size: 0.85rem;">{{ old('corpo_html') }}</textarea>
                            @error('corpo_html')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i> Você pode usar a variável <code>@{{nome}}</code> para personalizar com o nome de cada usuário no n8n.
                            </div>
                        </div>

                        <div class="d-flex gap-3 justify-content-end mt-4">
                            <button type="button" class="btn btn-light rounded-pill px-4" id="btn-salvar-rascunho">
                                <i class="bi bi-file-earmark-arrow-down me-2"></i>Salvar Rascunho
                            </button>
                            <button type="button" class="btn btn-primary rounded-pill px-4" id="btn-enviar-agora">
                                <i class="bi bi-send-fill me-2"></i>Enviar Campanha
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Coluna do Preview (Direita) --}}
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-eye-fill me-2 text-primary"></i>Visualização ao Vivo</h5>
                        <div class="d-flex gap-2">
                            <span class="badge bg-primary rounded-pill" id="badge-device-desktop" style="cursor: pointer;"><i class="bi bi-laptop"></i> Desktop</span>
                            <span class="badge bg-secondary rounded-pill" id="badge-device-mobile" style="cursor: pointer;"><i class="bi bi-phone"></i> Mobile</span>
                        </div>
                    </div>
                    <div class="card-body p-4 bg-light d-flex justify-content-center align-items-start">
                        <div class="w-100 d-flex flex-column align-items-center" id="preview-wrapper" style="transition: max-width 0.3s ease;">
                            {{-- Simulação de Cabeçalho do Cliente de Email --}}
                            <div class="w-100 bg-white border border-bottom-0 rounded-top-4 p-3 shadow-sm">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="text-muted small">Assunto:</span>
                                    <strong id="preview-subject" class="small">(Sem assunto)</strong>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted small">Para:</span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary small">destinatarios@sacratech.com</span>
                                </div>
                            </div>

                            {{-- Sandbox Iframe para o HTML --}}
                            <div class="w-100 border rounded-bottom-4 bg-white shadow-sm" style="min-height: 450px; position: relative;">
                                <iframe id="preview-frame" sandbox="allow-same-origin" class="w-100 border-0 rounded-bottom-4" style="background-color: #ffffff; min-height: 450px; display: block;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

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
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="btn-confirmar-envio-modal">
                        Sim, Enviar Agora
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- URL da rota processada pelo Blade (declarada antes do bloco verbatim) --}}
<script>
    const USUARIOS_URL = "{{ route('campanhas_email.usuarios') }}";
</script>

@verbatim
<script>
document.addEventListener('DOMContentLoaded', function() {
    const produtoSelect     = document.getElementById('produto');
    const warningAirlink    = document.getElementById('warning-airlink');
    const radioTodos        = document.getElementById('tipo-todos');
    const radioSelecionados = document.getElementById('tipo-selecionados');
    const containerLista    = document.getElementById('container-lista-usuarios');
    const usuariosCheckList = document.getElementById('usuarios-check-list');
    const loaderUsuarios    = document.getElementById('loader-usuarios');
    const buscaUsuario      = document.getElementById('busca-usuario');
    const badgeSelecionados = document.getElementById('badge-selecionados');
    const inputAcao         = document.getElementById('input-acao');
    const formCampanha      = document.getElementById('form-campanha');
    const inputHtml         = document.getElementById('corpo_html');
    const inputTitulo       = document.getElementById('titulo');
    const previewSubject    = document.getElementById('preview-subject');
    const previewFrame      = document.getElementById('preview-frame');
    const previewWrapper    = document.getElementById('preview-wrapper');
    const badgeDesktop      = document.getElementById('badge-device-desktop');
    const badgeMobile       = document.getElementById('badge-device-mobile');

    // -------------------------------------------------------------------------
    // Template padrão de email
    // Nota: {{nome}} aqui é texto literal para JS/n8n — não é diretiva Blade
    //       pois a diretiva verbatim do Blade envolve este bloco inteiro
    // -------------------------------------------------------------------------
    const templatePadrao = '<!DOCTYPE html>'
        + '<html><head><meta charset="utf-8"><title>Comunicado Sacratech</title>'
        + '<style>'
        + 'body{font-family:Arial,sans-serif;margin:0;padding:0;background:#f4f4f7;color:#51545e}'
        + '.wrapper{width:100%;background:#f4f4f7;padding:24px 0}'
        + '.container{max-width:570px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.05);overflow:hidden}'
        + '.header{background:#0a84ff;padding:32px;text-align:center}'
        + '.header h1{color:#fff;font-size:24px;margin:0}'
        + '.content{padding:32px;line-height:1.6}'
        + '.content h2{color:#333;margin-top:0}'
        + '.btn-wrap{margin:30px 0;text-align:center}'
        + '.btn{background:#0a84ff;color:#fff;padding:12px 24px;text-decoration:none;border-radius:4px;font-weight:bold;display:inline-block}'
        + '.footer{background:#f4f4f7;padding:24px;text-align:center;font-size:12px;color:#a8aaaf;border-top:1px solid #e8e8e8}'
        + '</style></head><body>'
        + '<div class="wrapper"><div class="container">'
        + '<div class="header"><h1>Sacratech</h1></div>'
        + '<div class="content">'
        + '<h2>Ol\u00e1, {{nome}}!</h2>'
        + '<p>Gostar\u00edamos de compartilhar uma novidade muito importante com voc\u00ea sobre os nossos servi\u00e7os e novidades no ecossistema.</p>'
        + '<p>Nossa equipe trabalhou em melhorias exclusivas para otimizar sua experi\u00eancia di\u00e1ria.</p>'
        + '<div class="btn-wrap"><a href="https://sacratech.com" class="btn" target="_blank">Acessar Painel</a></div>'
        + '<p>Se tiver qualquer d\u00favida ou sugest\u00e3o, basta responder a este email.</p>'
        + '<p>Atenciosamente,<br>Equipe Sacratech</p>'
        + '</div>'
        + '<div class="footer">'
        + '<p>Este email foi enviado automaticamente. Para parar de receber, clique em <a href="#">descadastrar</a>.</p>'
        + '<p>&copy; 2026 Sacratech. Todos os direitos reservados.</p>'
        + '</div></div></div>'
        + '</body></html>';

    // Carregar template padrão
    document.getElementById('btn-template-padrao').addEventListener('click', function() {
        if (confirm('Deseja substituir o conteúdo atual pelo template padrão?')) {
            inputHtml.value = templatePadrao;
            atualizarPreview();
        }
    });

    // Toggle Mobile/Desktop no Preview
    badgeDesktop.addEventListener('click', function() {
        previewWrapper.style.maxWidth = '100%';
        badgeDesktop.className = 'badge bg-primary rounded-pill';
        badgeMobile.className  = 'badge bg-secondary rounded-pill';
    });

    badgeMobile.addEventListener('click', function() {
        previewWrapper.style.maxWidth = '375px';
        badgeMobile.className  = 'badge bg-primary rounded-pill';
        badgeDesktop.className = 'badge bg-secondary rounded-pill';
    });

    // Atualização em Tempo Real (Debounce)
    var timeout = null;
    function atualizarPreview() {
        previewSubject.textContent = inputTitulo.value.trim() || '(Sem assunto)';

        var html = inputHtml.value
            || '<div style="padding:20px;text-align:center;color:#aaa;">Coloque o código HTML no editor para visualizar</div>';

        // Substituir {{nome}} com prévia amigável
        var renderedHtml = html.replace(new RegExp('\\{\\{\\s*nome\\s*\\}\\}', 'g'), 'Fulano de Tal');

        var doc = previewFrame.contentDocument || previewFrame.contentWindow.document;
        doc.open();
        doc.write(renderedHtml);
        doc.close();
    }

    inputHtml.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(atualizarPreview, 300);
    });

    inputTitulo.addEventListener('input', atualizarPreview);
    atualizarPreview();

    // -------------------------------------------------------------------------
    // Seleção de usuários via AJAX
    // -------------------------------------------------------------------------
    var cacheUsuarios = [];

    function carregarUsuarios(produto) {
        if (produto === 'airlink') {
            warningAirlink.classList.remove('d-none');
            containerLista.classList.add('d-none');
            return;
        }
        warningAirlink.classList.add('d-none');

        if (radioSelecionados.checked) {
            containerLista.classList.remove('d-none');
            loaderUsuarios.classList.remove('d-none');
            usuariosCheckList.innerHTML = '';

            fetch(USUARIOS_URL + '?produto=' + produto)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    cacheUsuarios = data;
                    renderizarUsuarios('');
                    loaderUsuarios.classList.add('d-none');
                })
                .catch(function(err) {
                    console.error(err);
                    usuariosCheckList.innerHTML = '<div class="text-danger py-2">Erro ao carregar usuários. Tente novamente.</div>';
                    loaderUsuarios.classList.add('d-none');
                });
        } else {
            containerLista.classList.add('d-none');
        }
    }

    function renderizarUsuarios(filtro) {
        usuariosCheckList.innerHTML = '';
        var search = (filtro || '').toLowerCase().trim();

        var filtrados = cacheUsuarios.filter(function(u) {
            return u.nome.toLowerCase().indexOf(search) !== -1
                || u.email.toLowerCase().indexOf(search) !== -1;
        });

        if (filtrados.length === 0) {
            usuariosCheckList.innerHTML = '<div class="text-muted text-center py-3">Nenhum usuário encontrado.</div>';
            return;
        }

        var oldChecked = new Set();
        document.querySelectorAll('input[name="destinatarios_ids[]"]:checked').forEach(function(input) {
            oldChecked.add(input.value);
        });

        filtrados.forEach(function(u) {
            var compositeId = u.produto + '_' + u.id;
            var div = document.createElement('div');
            div.className = 'form-check py-1 border-bottom border-light border-opacity-10';

            var checkbox = document.createElement('input');
            checkbox.className = 'form-check-input check-usuario';
            checkbox.type  = 'checkbox';
            checkbox.name  = 'destinatarios_ids[]';
            checkbox.value = compositeId;
            checkbox.id    = 'user-check-' + compositeId;
            if (oldChecked.has(compositeId)) checkbox.checked = true;
            checkbox.addEventListener('change', atualizarContador);

            var label = document.createElement('label');
            label.className = 'form-check-label w-100 d-flex justify-content-between';
            label.htmlFor   = 'user-check-' + compositeId;

            var badgeLabel = u.produto === 'sacratech_id' ? 'iD'
                : (u.produto === 'sismatriz_ticket' ? 'Ticket' : 'Principal');
            var badgeClass = u.produto === 'sacratech_id' ? 'bg-primary'
                : (u.produto === 'sismatriz_ticket' ? 'bg-info' : 'bg-success');

            label.innerHTML = '<div><strong>' + u.nome + '</strong><br>'
                + '<small class="text-muted">' + u.email + '</small></div>'
                + '<span class="badge ' + badgeClass + ' align-self-center" style="font-size:0.65rem">' + badgeLabel + '</span>';

            div.appendChild(checkbox);
            div.appendChild(label);
            usuariosCheckList.appendChild(div);
        });

        atualizarContador();
    }

    function atualizarContador() {
        var totalChecked = document.querySelectorAll('input[name="destinatarios_ids[]"]:checked').length;
        badgeSelecionados.textContent = totalChecked + ' selecionados';
    }

    buscaUsuario.addEventListener('input', function() {
        renderizarUsuarios(buscaUsuario.value);
    });

    document.getElementById('btn-selecionar-todos').addEventListener('click', function() {
        document.querySelectorAll('.check-usuario').forEach(function(c) { c.checked = true; });
        atualizarContador();
    });

    document.getElementById('btn-limpar-selecao').addEventListener('click', function() {
        document.querySelectorAll('.check-usuario').forEach(function(c) { c.checked = false; });
        atualizarContador();
    });

    produtoSelect.addEventListener('change', function() {
        carregarUsuarios(produtoSelect.value);
    });

    radioTodos.addEventListener('change', function() {
        containerLista.classList.add('d-none');
    });

    radioSelecionados.addEventListener('change', function() {
        carregarUsuarios(produtoSelect.value);
    });

    if (radioSelecionados.checked) {
        carregarUsuarios(produtoSelect.value);
    }

    document.getElementById('btn-salvar-rascunho').addEventListener('click', function() {
        inputAcao.value = 'rascunho';
        formCampanha.submit();
    });

    document.getElementById('btn-enviar-agora').addEventListener('click', function() {
        if (formCampanha.checkValidity()) {
            var modalConfirm = new bootstrap.Modal(document.getElementById('modalConfirmarEnvio'));
            modalConfirm.show();
        } else {
            formCampanha.reportValidity();
        }
    });

    document.getElementById('btn-confirmar-envio-modal').addEventListener('click', function() {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Enviando...';
        inputAcao.value = 'enviar';
        formCampanha.submit();
    });
});
</script>
@endverbatim
@endpush
