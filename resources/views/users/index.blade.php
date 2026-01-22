@extends('layouts.app')

@section('page-title', 'Usuários do Sacratech iD')

@section('content')
<div class="card border-0 shadow-sm" style="border-radius: 18px; overflow: hidden;">
    <div class="card-body p-0">
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show m-4" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show m-4" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Barra de Ferramentas (Filtros e Ações) -->
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center p-4 border-bottom gap-3">
            <div class="d-flex align-items-center gap-2">
                @if(file_exists(public_path('img/sacratech-id.png')))
                    <img src="{{ asset('img/sacratech-id.png') }}" alt="Sacratech iD" height="24">
                @endif
                <h5 class="card-title text-muted fw-bold text-uppercase m-0">Usuários do Sacratech iD</h5>
            </div>
            
            <div class="d-flex flex-column flex-md-row gap-2 w-100 w-lg-auto align-items-stretch align-items-md-center">
                <form id="filterForm" action="{{ route('users.index') }}" method="GET" class="d-flex flex-column flex-md-row gap-2 flex-grow-1 align-items-stretch align-items-md-center">
                    <!-- Busca -->
                    <div class="input-group flex-nowrap" style="min-width: 250px;">
                        <span class="input-group-text bg-transparent border-end-0 rounded-start-pill ps-3">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" id="searchInput" name="search" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por nome ou email..." value="{{ request('search') }}">
                    </div>

                    <!-- Filtros Selects -->
                    <div class="d-flex gap-2">
                        <!-- Filtro Status -->
                        <select name="status" id="statusFilter" class="form-select rounded-pill" style="min-width: 140px;">
                            <option value="">Status: Todos</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inativo</option>
                        </select>

                        <!-- Filtro Papel -->
                        <select name="role" id="roleFilter" class="form-select rounded-pill" style="min-width: 140px;">
                            <option value="">Papel: Todos</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>

                <div class="d-flex gap-2">
                    <button onclick="openPdfModal()" class="btn btn-dark rounded-pill px-4 d-flex align-items-center justify-content-center whitespace-nowrap shadow-sm border border-secondary border-opacity-25">
                        <i class="bi bi-printer me-2"></i> PDF
                    </button>
                    <a href="{{ route('users.create') }}" class="btn btn-primary rounded-pill px-4 d-flex align-items-center justify-content-center whitespace-nowrap">
                        <i class="bi bi-plus-lg me-2"></i> Novo
                    </a>
                </div>
            </div>
        </div>

        <form id="bulkForm" action="{{ route('users.bulk_action') }}" method="POST">
            @csrf
            <input type="hidden" name="action" id="bulkActionInput">
            <div id="users-table-container">
                @include('users.partials.table')
            </div>
        </form>
    </div>
</div>

<!-- Modal Detalhes -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0 bg-light">
                <h5 class="modal-title fw-bold ms-2">Detalhes do Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="detailsModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger fw-bold ms-2">Excluir Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="bg-danger-subtle text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                            <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Atenção!</h4>
                        <p class="text-muted">Você está prestes a excluir o usuário <strong id="deleteUserName" class="text-dark"></strong>.</p>
                    </div>
                    
                    <div class="alert alert-danger border-0 bg-danger-subtle text-danger p-3 rounded-3 mb-4">
                        <div class="d-flex">
                            <i class="bi bi-x-octagon-fill fs-4 me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Ação Irreversível</h6>
                                <p class="mb-0 small">Esta ação não pode ser desfeita e pode acarretar em consequências graves para a gestão dos dados e auditoria.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="admin_password" class="form-label fw-bold small text-secondary text-uppercase tracking-wide">Confirme sua senha de Administrador</label>
                        <input type="password" class="form-control form-control-lg bg-light border-0" id="admin_password" name="admin_password" required placeholder="••••••••">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-medium" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-medium shadow-sm">
                        <i class="bi bi-trash me-2"></i> Excluir Permanentemente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal PDF -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold ms-2">Gerar Relatório PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('users.pdf') }}" method="POST" target="_blank">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted mb-4">Selecione as informações que deseja incluir no relatório.</p>
                    
                    <!-- Hidden filters -->
                    <input type="hidden" name="search" id="pdfSearch">
                    <input type="hidden" name="status" id="pdfStatus">
                    <input type="hidden" name="role" id="pdfRole">

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check form-switch p-3 bg-light rounded-3">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="avatar" id="colAvatar" checked>
                                <label class="form-check-label fw-medium ms-2" for="colAvatar">Avatar</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch p-3 bg-light rounded-3">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="email" id="colEmail" checked>
                                <label class="form-check-label fw-medium ms-2" for="colEmail">Email</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch p-3 bg-light rounded-3">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="papel" id="colPapel" checked>
                                <label class="form-check-label fw-medium ms-2" for="colPapel">Papel</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch p-3 bg-light rounded-3">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="status" id="colStatus" checked>
                                <label class="form-check-label fw-medium ms-2" for="colStatus">Status</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch p-3 bg-light rounded-3">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="ultimo_login" id="colLogin" checked>
                                <label class="form-check-label fw-medium ms-2" for="colLogin">Último Login</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4 shadow-sm">
                        <i class="bi bi-file-earmark-pdf me-2"></i> Gerar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Actions Toolbar -->
<div id="bulkToolbar" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 p-3 bg-white rounded-4 shadow-lg border border-secondary border-opacity-10" style="z-index: 1050; min-width: 400px; display: none; backdrop-filter: blur(10px);">
    <div class="d-flex align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center text-dark">
            <span class="badge bg-primary rounded-pill me-2" id="selectedCount">0</span>
            <span class="small fw-bold">selecionados</span>
        </div>
        <div class="d-flex gap-2">
            <select id="bulkActionSelect" class="form-select form-select-sm bg-light border-secondary border-opacity-25 text-dark" style="width: 150px;">
                <option value="">Ações...</option>
                <option value="delete">Excluir</option>
                <option value="activate">Ativar</option>
                <option value="deactivate">Inativar</option>
                <option value="pdf">Gerar PDF</option>
            </select>
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="confirmBulkAction()">
                Aplicar
            </button>
        </div>
    </div>
</div>

<!-- Modal Confirmação Bulk Action -->
<div class="modal fade" id="bulkConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Confirmar Ação em Massa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="text-center mb-4">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-shield-lock text-warning fs-2"></i>
                    </div>
                    <h4 class="fw-bold text-dark">Autenticação Necessária</h4>
                    <p class="text-muted">Para realizar esta ação em massa, confirme sua senha de administrador.</p>
                </div>
                
                <div class="form-group">
                    <label class="text-muted small mb-2 fw-bold text-uppercase">Sua Senha</label>
                    <input type="password" id="bulkPassword" class="form-control form-control-lg bg-light border-0" placeholder="Digite sua senha...">
                </div>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-10">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" onclick="submitBulkForm()">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function updateToolbar() {
        const count = document.querySelectorAll('.row-checkbox:checked').length;
        const selectedCount = document.getElementById('selectedCount');
        const bulkToolbar = document.getElementById('bulkToolbar');
        
        if (selectedCount) selectedCount.textContent = count;
        
        if (bulkToolbar) {
            if (count > 0) {
                bulkToolbar.style.removeProperty('display');
            } else {
                bulkToolbar.style.display = 'none';
            }
        }
        
        // Update checkAll state
        const checkAll = document.getElementById('checkAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        if (checkAll && rowCheckboxes.length > 0) {
            checkAll.checked = count === rowCheckboxes.length;
        }
    }

    function confirmBulkAction() {
        const action = document.getElementById('bulkActionSelect').value;
        if (!action) return;

        // For PDF, no password needed
        if (action === 'pdf') {
            const form = document.getElementById('bulkForm');
            const originalTarget = form.target;
            const originalAction = form.action;
            
            form.target = '_blank';
            form.action = "{{ route('users.pdf') }}";
            
            form.submit();
            
            setTimeout(() => {
                form.target = originalTarget;
                form.action = originalAction;
                document.getElementById('bulkActionSelect').value = '';
            }, 100);
            return;
        }

        // For other actions, require password
        document.getElementById('bulkPassword').value = '';
        const modal = new bootstrap.Modal(document.getElementById('bulkConfirmModal'));
        modal.show();
    }

    function submitBulkForm() {
        const action = document.getElementById('bulkActionSelect').value;
        const password = document.getElementById('bulkPassword').value;
        const form = document.getElementById('bulkForm');

        if (!password) {
            alert('Por favor, digite sua senha.');
            return;
        }

        document.getElementById('bulkActionInput').value = action;
        
        // Add password field dynamically
        let pwdInput = document.createElement('input');
        pwdInput.type = 'hidden';
        pwdInput.name = 'password';
        pwdInput.value = password;
        form.appendChild(pwdInput);

        form.submit();
    }

    function openPdfModal() {
        document.getElementById('pdfSearch').value = document.getElementById('searchInput').value;
        document.getElementById('pdfStatus').value = document.getElementById('statusFilter').value;
        document.getElementById('pdfRole').value = document.getElementById('roleFilter').value;

        const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
        modal.show();
    }

    // Event delegation for checkboxes
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'checkAll') {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => cb.checked = e.target.checked);
            updateToolbar();
        }
        if (e.target && e.target.classList.contains('row-checkbox')) {
            updateToolbar();
        }
    });

    function openDetailsModal(userId) {
        const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
        const body = document.getElementById('detailsModalBody');
        
        modal.show();
        body.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
        `;

        fetch(`/users/${userId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(user => {
            body.innerHTML = `
                <div class="bg-light p-4 text-center border-bottom">
                    <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm mb-3 position-relative" style="width: 100px; height: 100px;">
                        ${user.avatar_url ? 
                            `<img src="${user.avatar_url}" class="rounded-circle w-100 h-100 object-fit-cover">` : 
                            `<span class="fs-1 fw-bold text-secondary">${user.nome ? user.nome.charAt(0) : user.email.charAt(0)}</span>`
                        }
                        <span class="position-absolute bottom-0 end-0 p-2 border border-2 border-white rounded-circle ${user.status == 1 ? 'bg-success' : 'bg-danger'}">
                            <span class="visually-hidden">Status</span>
                        </span>
                    </div>
                    <h3 class="fw-bold mb-1">${user.nome_exibicao || (user.nome + ' ' + user.sobrenome)}</h3>
                    <p class="text-muted mb-2">${user.email}</p>
                    <span class="badge bg-secondary rounded-pill px-3 py-2 text-uppercase small tracking-wide">${user.papel}</span>
                </div>

                <div class="p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h6 class="text-uppercase text-muted small fw-bold mb-3 d-flex align-items-center">
                                    <i class="bi bi-person-lines-fill me-2"></i> Informações Pessoais
                                </h6>
                                <div class="mb-2"><span class="text-muted small d-block">Nome Completo:</span> <span class="fw-medium">${user.nome} ${user.sobrenome}</span></div>
                                <div class="mb-2"><span class="text-muted small d-block">Data de Nascimento:</span> <span class="fw-medium">${user.data_nascimento ? new Date(user.data_nascimento).toLocaleDateString('pt-BR') : '-'}</span></div>
                                <div class="mb-0"><span class="text-muted small d-block">CPF/ID:</span> <span class="fw-medium text-break">${user.id}</span></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h6 class="text-uppercase text-muted small fw-bold mb-3 d-flex align-items-center">
                                    <i class="bi bi-geo-alt-fill me-2"></i> Contato & Endereço
                                </h6>
                                <div class="mb-2"><span class="text-muted small d-block">Telefone:</span> <span class="fw-medium">${user.telefone || '-'}</span></div>
                                <div class="mb-0"><span class="text-muted small d-block">Endereço:</span> 
                                    <span class="fw-medium">
                                        ${user.endereco || ''}, ${user.numero || ''} <br>
                                        ${user.bairro || ''} - ${user.cidade || ''}/${user.estado || ''}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h6 class="text-uppercase text-muted small fw-bold mb-3 d-flex align-items-center">
                                    <i class="bi bi-activity me-2"></i> Sistema
                                </h6>
                                <div class="mb-2"><span class="text-muted small d-block">Criado em:</span> <span class="fw-medium">${new Date(user.criado_em).toLocaleString('pt-BR')}</span></div>
                                <div class="mb-2"><span class="text-muted small d-block">Email Verificado:</span> <span class="fw-medium">${user.email_verificado_em ? new Date(user.email_verificado_em).toLocaleString('pt-BR') : 'Não'}</span></div>
                                <div class="mb-0"><span class="text-muted small d-block">Nextcloud Status:</span> <span class="fw-medium">${user.nextcloud_status || '-'}</span></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h6 class="text-uppercase text-muted small fw-bold mb-3 d-flex align-items-center">
                                    <i class="bi bi-shield-lock-fill me-2"></i> Segurança
                                </h6>
                                <div class="mb-2"><span class="text-muted small d-block">Último Login:</span> <span class="fw-medium">${user.ultimo_login_em ? new Date(user.ultimo_login_em).toLocaleString('pt-BR') : 'Nunca'}</span></div>
                                <div class="mb-2"><span class="text-muted small d-block">IP:</span> <span class="fw-medium font-monospace">${user.ultimo_login_ip || '-'}</span></div>
                                <div class="mb-0"><span class="text-muted small d-block">2FA:</span> 
                                    ${user.dois_fatores_ativo ? 
                                        '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Ativo</span>' : 
                                        '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Inativo</span>'
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            body.innerHTML = `<div class="alert alert-danger m-4">Erro ao carregar detalhes do usuário.</div>`;
        });
    }

    function openDeleteModal(userId, userName) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const form = document.getElementById('deleteForm');
        const nameSpan = document.getElementById('deleteUserName');
        
        form.action = `/users/${userId}`;
        nameSpan.textContent = userName;
        
        modal.show();
    }

    // Busca Dinâmica
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const roleFilter = document.getElementById('roleFilter');
        const tableContainer = document.getElementById('users-table-container');

        function fetchUsers() {
            const params = new URLSearchParams({
                search: searchInput.value,
                status: statusFilter.value,
                role: roleFilter.value
            });

            // Mostra loading no container da tabela
            tableContainer.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="text-muted mt-3 mb-0">Carregando resultados...</p>
                </div>
            `;

            // Atualiza URL sem recarregar
            window.history.pushState({}, '', `${window.location.pathname}?${params.toString()}`);

            fetch(`${window.location.pathname}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                tableContainer.innerHTML = html;
                updateToolbar(); // Reset toolbar state after reload
            })
            .catch(error => {
                console.error('Erro ao buscar usuários:', error);
                tableContainer.innerHTML = `
                    <div class="alert alert-danger m-4 text-center">
                        <i class="bi bi-exclamation-triangle-fill fs-1 d-block mb-3"></i>
                        Erro ao carregar dados. Por favor, tente novamente.
                    </div>
                `;
            });
        }

        // Debounce para o input de busca
        let timeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(fetchUsers, 300);
        });

        // Eventos para os selects
        statusFilter.addEventListener('change', fetchUsers);
        roleFilter.addEventListener('change', fetchUsers);
        
        // Paginação via AJAX (delegação de eventos)
        tableContainer.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' && e.target.closest('.pagination')) {
                e.preventDefault();
                const url = e.target.href;
                
                // Extrai parâmetros atuais e combina com a página
                const currentParams = new URLSearchParams(window.location.search);
                const pageUrl = new URL(url);
                const page = pageUrl.searchParams.get('page');
                
                if (page) {
                    currentParams.set('page', page);
                    window.history.pushState({}, '', `${window.location.pathname}?${currentParams.toString()}`);
                    
                    // Mostra loading no container da tabela
                    tableContainer.innerHTML = `
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                            <p class="text-muted mt-3 mb-0">Carregando página ${page}...</p>
                        </div>
                    `;

                    fetch(`${window.location.pathname}?${currentParams.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        tableContainer.innerHTML = html;
                        updateToolbar(); // Reset toolbar state
                    })
                    .catch(error => {
                        console.error('Erro na paginação:', error);
                        tableContainer.innerHTML = `
                            <div class="alert alert-danger m-4 text-center">
                                <i class="bi bi-exclamation-triangle-fill fs-1 d-block mb-3"></i>
                                Erro ao carregar página. Por favor, tente novamente.
                            </div>
                        `;
                    });
                }
            }
        });
    });
</script>
@endsection
