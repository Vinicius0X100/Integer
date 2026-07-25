@extends('layouts.app')

@section('page-title', 'Usuários - SisMatriz Principal')

@section('content')
<style>
    @keyframes sismatrizPulseDanger {
        0%, 100% { opacity: 0.35; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.06); }
    }
    .sismatriz-blink-danger {
        animation: sismatrizPulseDanger 1.15s infinite;
    }
    @keyframes sismatrizShimmer {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
    }
    .shimmer-loading {
        animation: sismatrizShimmer 2s infinite linear;
        background: linear-gradient(to right, #eff1f3 4%, #e2e2e2 25%, #eff1f3 36%);
        background-size: 1000px 100%;
        opacity: 0.5;
        border-radius: 4px;
        color: transparent !important;
        pointer-events: none;
    }
    .shimmer-loading * {
        visibility: hidden;
    }
</style>
<div class="container-fluid py-4">
    <!-- Feedback Messages -->
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

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary position-relative">
                        @if(file_exists(public_path('img/sismatriz-logo.png')))
                            <img src="{{ asset('img/sismatriz-logo.png') }}" alt="SisMatriz Logo" class="position-absolute top-50 start-50 translate-middle" style="width: 32px; height: 32px; object-fit: contain;">
                            <div style="width: 24px; height: 24px;"></div> <!-- Spacer -->
                        @else
                            <i class="bi bi-people-fill fs-4"></i>
                        @endif
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Gerenciamento de Usuários (Principal)</h5>
                        <small class="text-muted">Administre os acessos do SisMatriz Principal</small>
                    </div>
                </div>
                <div class="d-flex gap-2 w-100 w-md-auto justify-content-end">
                    <button type="button" class="btn btn-light rounded-pill px-4 d-flex align-items-center gap-2 fw-medium border flex-grow-1 flex-md-grow-0 justify-content-center" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="bi bi-download text-muted"></i>
                        <span class="text-muted">Exportar</span>
                    </button>
                    <a href="{{ route('sismatriz-main.create') }}" class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2 fw-medium flex-grow-1 flex-md-grow-0 justify-content-center">
                        <i class="bi bi-plus-lg"></i>
                        <span>Novo Usuário</span>
                    </a>
                </div>
            </div>

            <form action="{{ route('sismatriz-main.index') }}" method="GET" id="mainSearchForm" class="d-flex flex-column gap-3">
                <!-- Linha 1: Filtros -->
                <div class="d-flex flex-column flex-md-row gap-2">
                    <select name="status" class="form-select bg-light border-0 rounded-pill filter-input" style="min-width: 140px; flex: 1;">
                        <option value="">Status: Todos</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Ativo</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Inativo</option>
                    </select>

                    <select name="paroquia_id" class="form-select bg-light border-0 rounded-pill filter-input" style="min-width: 180px; flex: 2;">
                        <option value="">Paróquia: Todas</option>
                        @foreach($paroquias as $p)
                            <option value="{{ $p->id }}" {{ request('paroquia_id') == $p->id ? 'selected' : '' }}>{{ \Illuminate\Support\Str::limit($p->name, 40) }}</option>
                        @endforeach
                    </select>

                    <select name="role" class="form-select bg-light border-0 rounded-pill filter-input" style="min-width: 180px; flex: 2;">
                        <option value="">Cargo: Todos</option>
                        @foreach($rolesMap as $id => $roleName)
                            <option value="{{ $id }}" {{ request('role') == $id ? 'selected' : '' }}>{{ $roleName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Linha 2: Busca Grande -->
                <div class="input-group input-group-lg bg-light rounded-pill overflow-hidden border border-light shadow-sm">
                    <span class="input-group-text bg-transparent border-0 ps-4">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" id="mainSearchInput" name="search" class="form-control bg-transparent border-0 shadow-none fs-6 filter-input" placeholder="Buscar por nome, email ou login..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary px-4 fw-bold fs-6 border-0">
                        Pesquisar
                    </button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="width: 40px;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </div>
                        </th>
                        <th class="py-3 text-uppercase text-muted small fw-bold">
                            <a href="{{ route('sismatriz-main.index', array_merge(request()->query(), ['sort' => 'name', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-muted d-flex align-items-center gap-1">
                                Usuário
                                @if(request('sort') === 'name')
                                    <i class="bi bi-arrow-{{ request('order') === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th class="py-3 text-uppercase text-muted small fw-bold">
                            <a href="{{ route('sismatriz-main.index', array_merge(request()->query(), ['sort' => 'user', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-muted d-flex align-items-center gap-1">
                                Login/Email
                                @if(request('sort') === 'user')
                                    <i class="bi bi-arrow-{{ request('order') === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th class="py-3 text-uppercase text-muted small fw-bold">Cargos</th>
                        <th class="py-3 text-uppercase text-muted small fw-bold">Paróquia</th>
                        <th class="py-3 text-uppercase text-muted small fw-bold text-center">
                            <a href="{{ route('sismatriz-main.index', array_merge(request()->query(), ['sort' => 'is_pass_change', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-muted d-flex align-items-center justify-content-center gap-1">
                                Senha
                                @if(request('sort') === 'is_pass_change')
                                    <i class="bi bi-arrow-{{ request('order') === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th class="py-3 text-uppercase text-muted small fw-bold text-center">
                            <a href="{{ route('sismatriz-main.index', array_merge(request()->query(), ['sort' => 'status', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-muted d-flex align-items-center justify-content-center gap-1">
                                Status
                                @if(request('sort') === 'status')
                                    <i class="bi bi-arrow-{{ request('order') === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th class="py-3 text-uppercase text-muted small fw-bold text-center">Último login</th>
                        <th class="py-3 text-uppercase text-muted small fw-bold text-end pe-4">Ações</th>
                    </tr>
                </thead>
                @include('sismatriz_main.partials.table')
            </table>
        </div>

        <div class="card-footer bg-white border-top p-4 {{ $users->hasPages() ? '' : 'd-none' }}" id="paginationContainer">
            @include('sismatriz_main.partials.pagination')
        </div>
    </div>
</div>

<!-- Bulk Actions Toolbar -->
<div id="bulkToolbar" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 p-3 bg-dark rounded-4 shadow-lg border border-secondary border-opacity-25" style="z-index: 1050; min-width: 400px; display: none; backdrop-filter: blur(10px);">
    <div class="d-flex align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center text-white">
            <span class="badge bg-primary rounded-pill me-2" id="selectedCount">0</span>
            <span class="small">selecionados</span>
        </div>
        <div class="d-flex gap-2">
            <select id="bulkActionSelect" class="form-select form-select-sm bg-secondary bg-opacity-10 border-secondary border-opacity-25 text-white" style="width: 150px;">
                <option value="">Ações...</option>
                <option value="delete">Excluir</option>
            </select>
            <button onclick="submitBulkAction()" class="btn btn-sm btn-light rounded-pill px-3">Aplicar</button>
        </div>
    </div>
</div>

<!-- Modal Delete Confirmation -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="bg-danger bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-1"></i>
                    </div>
                    <h5 class="fw-bold">Atenção! Esta ação é irreversível.</h5>
                    <p class="text-muted">
                        Você está prestes a excluir o usuário <strong id="deleteUserName"></strong>.<br>
                        Todos os dados vinculados a este usuário serão afetados permanentemente.
                    </p>
                </div>
                
                <form id="deleteUserForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="deleteAdminPassword" name="password" placeholder="Senha do Administrador" required>
                        <label for="deleteAdminPassword">Senha do Administrador</label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger rounded-pill py-2 fw-bold">
                            Confirmar Exclusão
                        </button>
                        <button type="button" class="btn btn-light rounded-pill py-2" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhes -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Detalhes do Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="userDetailsContent">
                <!-- Content loaded via JS -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Password for Bulk Action -->
<div class="modal fade" id="bulkActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Confirmar Ação em Massa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Para continuar, digite sua senha de administrador:</p>
                <input type="password" id="bulkActionPassword" class="form-control" placeholder="Sua senha">
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" onclick="confirmBulkAction()">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<form id="bulkActionForm" action="{{ route('sismatriz-main.bulk_action') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="action" id="formAction">
    <input type="hidden" name="password" id="formPassword">
    <div id="selectedInputs"></div>
</form>



<!-- Modal Export -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Exportar Usuários</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('sismatriz-main.export') }}" method="POST" id="exportForm">
                    @csrf
                    
                    <div id="exportErrorAlert" class="alert alert-danger d-none rounded-3 small mb-3"></div>

                    <div class="row g-4">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3 text-primary bg-primary bg-opacity-10 p-2 rounded-3 d-inline-block"><i class="bi bi-funnel-fill me-2"></i>Filtros</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small text-muted text-uppercase fw-bold">Buscar</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                                        <input type="text" name="search" class="form-control bg-light border-0" placeholder="Nome, email ou login..." value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted text-uppercase fw-bold">Paróquia</label>
                                    <select name="paroquia_id" class="form-select bg-light border-0">
                                        <option value="">Todas</option>
                                        @foreach($paroquias as $p)
                                            <option value="{{ $p->id }}" {{ request('paroquia_id') == $p->id ? 'selected' : '' }}>{{ \Illuminate\Support\Str::limit($p->name, 30) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted text-uppercase fw-bold">Cargo</label>
                                    <select name="role" class="form-select bg-light border-0">
                                        <option value="">Todos</option>
                                        @foreach($rolesMap as $id => $roleName)
                                            <option value="{{ $id }}" {{ request('role') == $id ? 'selected' : '' }}>{{ $roleName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted text-uppercase fw-bold">Status</label>
                                    <select name="status" class="form-select bg-light border-0">
                                        <option value="">Todos</option>
                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Ativo</option>
                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Inativo</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted text-uppercase fw-bold">Status da Senha</label>
                                    <select name="is_pass_change" class="form-select bg-light border-0">
                                        <option value="">Todos</option>
                                        <option value="1" {{ request('is_pass_change') === '1' ? 'selected' : '' }}>Alterada</option>
                                        <option value="0" {{ request('is_pass_change') === '0' ? 'selected' : '' }}>Padrão</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <hr class="border-secondary border-opacity-10 my-0">
                            <div id="exportPreview" class="alert py-2 small d-none mt-3 mb-0 border-0 bg-primary bg-opacity-10 text-primary">
                                <div class="fw-bold d-flex align-items-center gap-2">
                                    <i class="bi bi-people-fill"></i> 
                                    <span><span id="exportPreviewCount">0</span> usuários encontrados</span>
                                </div>
                                <div id="exportPreviewNames" class="mt-1 opacity-75 fw-medium"></div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <h6 class="fw-bold mb-3 text-primary bg-primary bg-opacity-10 p-2 rounded-3 d-inline-block"><i class="bi bi-layout-three-columns me-2"></i>Colunas</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="columns[]" value="id" id="exp_col_id">
                                        <label class="form-check-label" for="exp_col_id">ID</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="columns[]" value="name" id="exp_col_name" checked>
                                        <label class="form-check-label" for="exp_col_name">Nome</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="columns[]" value="user" id="exp_col_user" checked>
                                        <label class="form-check-label" for="exp_col_user">Login</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="columns[]" value="email" id="exp_col_email" checked>
                                        <label class="form-check-label" for="exp_col_email">Email</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="columns[]" value="paroquia" id="exp_col_paroquia" checked>
                                        <label class="form-check-label" for="exp_col_paroquia">Paróquia</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="columns[]" value="roles" id="exp_col_roles" checked>
                                        <label class="form-check-label" for="exp_col_roles">Cargos</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="columns[]" value="status" id="exp_col_status" checked>
                                        <label class="form-check-label" for="exp_col_status">Status</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="columns[]" value="is_pass_change" id="exp_col_pass">
                                        <label class="form-check-label" for="exp_col_pass">Status da Senha</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="columns[]" value="created_at" id="exp_col_created_at">
                                        <label class="form-check-label" for="exp_col_created_at">Data Cadastro</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <h6 class="fw-bold mb-3 text-primary bg-primary bg-opacity-10 p-2 rounded-3 d-inline-block"><i class="bi bi-file-earmark-text me-2"></i>Formato</h6>
                            <div class="d-flex flex-column gap-3">
                                <div class="form-check card p-3 border hover-shadow transition-all">
                                    <input class="form-check-input" type="radio" name="format" id="format_pdf" value="pdf" checked>
                                    <label class="form-check-label d-flex align-items-center gap-2" for="format_pdf">
                                        <i class="bi bi-file-pdf-fill text-danger fs-4"></i> 
                                        <div>
                                            <div class="fw-bold">PDF</div>
                                            <div class="small text-muted">Documento portátil</div>
                                        </div>
                                    </label>
                                </div>
                                <div class="form-check card p-3 border hover-shadow transition-all">
                                    <input class="form-check-input" type="radio" name="format" id="format_csv" value="csv">
                                    <label class="form-check-label d-flex align-items-center gap-2" for="format_csv">
                                        <i class="bi bi-file-earmark-excel-fill text-success fs-4"></i> 
                                        <div>
                                            <div class="fw-bold">Excel (CSV)</div>
                                            <div class="small text-muted">Planilha editável</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 pe-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="exportForm" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                    <i class="bi bi-download me-2"></i> Exportar Dados
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Storage Management
    const STORAGE_KEY = 'sismatriz_main_selected_ids';
    const INTERNAL_NAV_KEY = 'sismatriz_main_internal_nav';

    function getSelectedIds() {
        return new Set(JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'));
    }

    function saveSelectedIds(ids) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(Array.from(ids)));
        updateToolbar();
    }

    // Bulk Selection Logic
    const selectAll = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkToolbar = document.getElementById('bulkToolbar');
    const selectedCount = document.getElementById('selectedCount');

    function updateToolbar() {
        const ids = getSelectedIds();
        if (selectedCount) selectedCount.textContent = ids.size;
        
        if (bulkToolbar) {
            if (ids.size > 0) {
                bulkToolbar.style.display = 'block';
            } else {
                bulkToolbar.style.display = 'none';
            }
        }
        
        // Update Select All Checkbox State
        if (selectAll && userCheckboxes.length > 0) {
            const allVisibleSelected = Array.from(userCheckboxes).every(cb => ids.has(cb.value));
            selectAll.checked = allVisibleSelected;
        }
    }

    // Initialize Selection
    document.addEventListener('DOMContentLoaded', function() {
        // Clear selection if not an internal navigation (pagination, sort, filter)
        if (!sessionStorage.getItem(INTERNAL_NAV_KEY)) {
            localStorage.removeItem(STORAGE_KEY);
        }
        sessionStorage.removeItem(INTERNAL_NAV_KEY);

        // Helper for row highlighting
        function updateRowStyle(checkbox) {
            const tr = checkbox.closest('tr');
            if (checkbox.checked) {
                tr.classList.add('table-active');
                // Optional: add a subtle border or background if table-active isn't enough
                tr.style.backgroundColor = 'var(--bs-primary-bg-subtle)';
            } else {
                tr.classList.remove('table-active');
                tr.style.removeProperty('background-color');
            }
        }

        const initialIds = getSelectedIds();
        userCheckboxes.forEach(cb => {
            if (initialIds.has(cb.value)) {
                cb.checked = true;
                updateRowStyle(cb);
            }
        });
        updateToolbar();

        // Mark internal navigation for pagination and sorting links
        document.querySelectorAll('.pagination a, th a').forEach(link => {
            link.addEventListener('click', () => {
                sessionStorage.setItem(INTERNAL_NAV_KEY, 'true');
            });
        });

        // Mark internal navigation for filter forms/inputs
        const filterForm = document.querySelector('form[action*="sismatriz-main.index"]'); // Generic selector or specific ID if available
        if (filterForm) {
            filterForm.addEventListener('submit', () => {
                sessionStorage.setItem(INTERNAL_NAV_KEY, 'true');
            });
            // Handle select onchange
            filterForm.querySelectorAll('select').forEach(select => {
                select.addEventListener('change', () => {
                    sessionStorage.setItem(INTERNAL_NAV_KEY, 'true');
                });
            });
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const ids = getSelectedIds();
                userCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                    updateRowStyle(cb);
                    if (this.checked) {
                        ids.add(cb.value);
                    } else {
                        ids.delete(cb.value);
                    }
                });
                saveSelectedIds(ids);
            });
        }

        userCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const ids = getSelectedIds();
                updateRowStyle(this);
                if (this.checked) {
                    ids.add(this.value);
                } else {
                    ids.delete(this.value);
                }
                saveSelectedIds(ids);
            });
        });
    });

    // Bulk Action Submission
    let currentAction = '';
    const bulkActionSelect = document.getElementById('bulkActionSelect');
    
    // Helper to safely get modal instance
    function getModal(id) {
        const el = document.getElementById(id);
        if (!el) return null;
        return bootstrap.Modal.getOrCreateInstance(el);
    }

    function submitBulkAction() {
        const action = bulkActionSelect.value;
        if (!action) return;

        const ids = getSelectedIds();
        if (ids.size === 0) {
            alert('Nenhum usuário selecionado.');
            return;
        }

        if (action === 'pdf') {
            // Show columns modal
            const modal = getModal('pdfColumnsModal');
            if (modal) modal.show();
        } else {
            currentAction = action;
            document.getElementById('bulkActionPassword').value = '';
            const modal = getModal('bulkActionModal');
            if (modal) modal.show();
        }
    }

    function generatePdfWithColumns() {
        const form = document.getElementById('pdfForm');
        const inputsContainer = document.getElementById('pdfSelectedInputs');
        inputsContainer.innerHTML = '';
        
        const ids = getSelectedIds();
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected[]';
            input.value = id;
            inputsContainer.appendChild(input);
        });

        // Add selected columns
        const columnsForm = document.getElementById('pdfColumnsForm');
        const formData = new FormData(columnsForm);
        for (const [key, value] of formData.entries()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key; // columns[]
            input.value = value;
            inputsContainer.appendChild(input);
        }
        
        form.submit();
        
        // Close modal and reset select
        const modalEl = document.getElementById('pdfColumnsModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
        bulkActionSelect.value = '';
    }

    function confirmBulkAction() {
        const password = document.getElementById('bulkActionPassword').value;
        if (!password) {
            alert('Por favor, digite sua senha.');
            return;
        }

        const form = document.getElementById('bulkActionForm');
        document.getElementById('formAction').value = currentAction;
        document.getElementById('formPassword').value = password;
        
        const inputsContainer = document.getElementById('selectedInputs');
        inputsContainer.innerHTML = '';
        
        const ids = getSelectedIds();
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected[]';
            input.value = id;
            inputsContainer.appendChild(input);
        });

        form.submit();
        
        // Clear selection after action (assuming success redirects, but for safety)
        // localStorage.removeItem(STORAGE_KEY); 
        // Better to let page reload handle it, or clear if we want.
        // Usually after delete, items are gone.
    }

    // Modal Instances
    let userDetailsModal;

    document.addEventListener('DOMContentLoaded', function() {
        const userDetailsEl = document.getElementById('userDetailsModal');
        if (userDetailsEl) {
            userDetailsModal = new bootstrap.Modal(userDetailsEl);
        }
    });

    function showUserDetails(userId) {
        const content = document.getElementById('userDetailsContent');
        content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';
        if (userDetailsModal) userDetailsModal.show();

        fetch(`/sismatriz-main/${userId}`)
            .then(response => response.json())
            .then(user => {
                content.innerHTML = `
                    <div class="text-center mb-4">
                        ${user.avatar_url ? 
                            `<img src="${user.avatar_url}" class="rounded-circle shadow-sm mb-3" style="width: 100px; height: 100px; object-fit: cover;">` :
                            `<div class="avatar-initial rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2rem;">
                                ${user.name.charAt(0)}
                            </div>`
                        }
                        <h4 class="fw-bold mb-1">${user.name}</h4>
                        <p class="text-muted mb-0">${user.email}</p>
                    </div>

                    ${user.inactive_alert ? `
                        <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div>
                                <div class="fw-bold">Usuário com risco de inativação</div>
                                <div class="small">Sem acessar há ${Number.parseInt(user.inactive_days, 10).toLocaleString('pt-BR')} dias. Ideal inativar o usuário.</div>
                            </div>
                        </div>
                    ` : ''}
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <small class="text-uppercase text-muted fw-bold d-block mb-2">Informações de Acesso</small>
                                <div class="mb-2"><strong>Login:</strong> ${user.user}</div>
                                <div class="mb-2"><strong>Paróquia:</strong> ${user.paroquia ? user.paroquia.name : 'N/A'}</div>
                                <div class="mb-2"><strong>Status:</strong> <span class="badge ${user.status == 0 ? 'bg-success' : 'bg-danger'} bg-opacity-10 text-${user.status == 0 ? 'success' : 'danger'} rounded-pill">${user.status == 0 ? 'Ativo' : 'Inativo'}</span></div>
                                <div><strong>Senha:</strong> <span class="badge ${user.is_pass_change == 1 ? 'bg-success' : 'bg-warning'} bg-opacity-10 text-${user.is_pass_change == 1 ? 'success' : 'warning'} rounded-pill">${user.is_pass_change == 1 ? 'Alterada' : 'Padrão'}</span></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <small class="text-uppercase text-muted fw-bold d-block mb-2">Permissões</small>
                                <div class="d-flex flex-wrap gap-1">
                                    ${user.role_names.length > 0 ? 
                                        user.role_names.map(role => `<span class="badge bg-white text-dark border shadow-sm">${role}</span>`).join('') : 
                                        '<span class="text-muted">Nenhuma função atribuída</span>'
                                    }
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-uppercase text-muted fw-bold d-block mb-2">Auditoria</small>
                                <div class="row small">
                                    <div class="col-md-6"><strong>Criado em:</strong> ${user.formatted_created_at}</div>
                                    <div class="col-md-6"><strong>Último login:</strong> ${user.formatted_last_login}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            })
            .catch(error => {
                content.innerHTML = '<div class="alert alert-danger">Erro ao carregar detalhes do usuário.</div>';
            });
    }
    
    function confirmDelete(userId, userName) {
        document.getElementById('deleteUserName').textContent = userName;
        document.getElementById('deleteUserForm').action = `/sismatriz-main/${userId}`;
        document.getElementById('deleteAdminPassword').value = '';
        
        const modalEl = document.getElementById('deleteConfirmationModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    // Export Modal Logic - Inject Selected IDs
    const exportModalEl = document.getElementById('exportModal');
    if (exportModalEl) {
        // Handle Form Submit (Loading State)
        const exportForm = document.getElementById('exportForm');
        if (exportForm) {
            exportForm.addEventListener('submit', function(e) {
                // Prevent default submission to handle validation first
                e.preventDefault();
                e.stopPropagation();

                // Button is outside the form (in modal footer), so we select by form attribute
                const btn = document.querySelector('button[type="submit"][form="exportForm"]');
                if (!btn) return;

                const originalText = btn.innerHTML;
                const errorAlert = document.getElementById('exportErrorAlert');
                
                // Hide previous errors
                if (errorAlert) errorAlert.classList.add('d-none');
                
                // Show verifying state
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Verificando...';
                
                // Prepare data for check
                const formData = new FormData(this);
                
                // Check if any results exist
                fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.count === 0) {
                        // Show error in modal
                        if (errorAlert) {
                            errorAlert.textContent = 'Nenhum registro encontrado para os filtros selecionados.';
                            errorAlert.classList.remove('d-none');
                        }
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    } else {
                        // Proceed to download
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Exportando...';
                        
                        // Submit naturally for download (bypass listener)
                        // Using HTMLFormElement.prototype.submit.call(form) or just form.submit() 
                        // bypasses the onsubmit handler, preventing infinite loop
                        this.submit();
                        
                        // Reset button after delay
                        setTimeout(() => {
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                            
                            // Force hide global loader if it appeared anyway
                            const globalLoader = document.getElementById('global-page-loader');
                            if (globalLoader) {
                                globalLoader.style.opacity = '0';
                                setTimeout(() => { globalLoader.classList.add('d-none'); }, 300);
                            }
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // In case of error, try to submit anyway or show error
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    if (errorAlert) {
                        errorAlert.textContent = 'Erro ao verificar dados. Tente novamente.';
                        errorAlert.classList.remove('d-none');
                    }
                });
            });
        }

        exportModalEl.addEventListener('show.bs.modal', function () {
            const form = document.getElementById('exportForm');
            if (!form) return;

            // Remove any existing selected inputs
            form.querySelectorAll('input[name="selected[]"]').forEach(el => el.remove());
            
            const ids = getSelectedIds();
            if (ids.size > 0) {
                // Add selected IDs
                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected[]';
                    input.value = id;
                    form.appendChild(input);
                });
                
                // Disable filters when specific items are selected
                const filterInputs = form.querySelectorAll('select, input[type="text"]');
                filterInputs.forEach(input => input.disabled = true);
                
                // Show a notice
                let notice = form.querySelector('.selection-notice');
                if (!notice) {
                    notice = document.createElement('div');
                    notice.className = 'alert alert-info selection-notice mb-3 small py-2';
                    notice.innerHTML = `<i class="bi bi-info-circle-fill me-2"></i> Exportando <strong>${ids.size}</strong> usuário(s) selecionado(s). Filtros ignorados.`;
                    form.insertBefore(notice, form.firstChild);
                }
                document.getElementById('exportPreview')?.classList.add('d-none');
            } else {
                // Re-enable filters
                const filterInputs = form.querySelectorAll('select, input[type="text"]');
                filterInputs.forEach(input => input.disabled = false);
                
                // Remove notice
                const notice = form.querySelector('.selection-notice');
                if (notice) notice.remove();
                
                // Trigger preview
                updateExportPreview();
            }
        });
        
        // Export Live Preview Logic
        let exportPreviewTimeout;
        const exportFormInputs = exportModalEl.querySelectorAll('select, input[type="text"]');
        
        function updateExportPreview() {
            if(getSelectedIds().size > 0) return; // Ignore if specific items selected
            
            const form = document.getElementById('exportForm');
            if(!form) return;
            
            const formData = new FormData(form);
            const previewEl = document.getElementById('exportPreview');
            const countEl = document.getElementById('exportPreviewCount');
            const namesEl = document.getElementById('exportPreviewNames');
            
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(previewEl) {
                    previewEl.classList.remove('d-none');
                    if(data.count === 0) {
                        previewEl.classList.remove('bg-primary', 'text-primary');
                        previewEl.classList.add('bg-danger', 'text-danger');
                    } else {
                        previewEl.classList.remove('bg-danger', 'text-danger');
                        previewEl.classList.add('bg-primary', 'text-primary');
                    }
                }
                if(countEl) countEl.textContent = data.count;
                if(namesEl && data.preview) {
                    if(data.count > data.preview.length) {
                        namesEl.innerHTML = data.preview.join(', ') + '...';
                    } else {
                        namesEl.innerHTML = data.preview.join(', ');
                    }
                }
            })
            .catch(err => console.error('Preview error', err));
        }
        
        exportFormInputs.forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(exportPreviewTimeout);
                exportPreviewTimeout = setTimeout(updateExportPreview, 500);
            });
            input.addEventListener('change', function() {
                clearTimeout(exportPreviewTimeout);
                exportPreviewTimeout = setTimeout(updateExportPreview, 500);
            });
        });
    }

    // Main Table Live Search (AJAX)
    let mainSearchTimeout;
    const mainSearchForm = document.getElementById('mainSearchForm');
    const filterInputs = document.querySelectorAll('.filter-input');
    const paginationContainer = document.getElementById('paginationContainer');
    
    function applyShimmer() {
        const tableBody = document.getElementById('usersTableBody');
        if(tableBody) {
            const rows = tableBody.querySelectorAll('tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                cells.forEach(cell => {
                    cell.classList.add('shimmer-loading');
                });
            });
        }
    }
    
    function performSearch(url = null) {
        applyShimmer();
        
        let fetchUrl = url;
        if (!fetchUrl) {
            const formData = new FormData(mainSearchForm);
            const params = new URLSearchParams(formData);
            fetchUrl = `${mainSearchForm.action}?${params.toString()}`;
        }
        
        window.history.replaceState({}, '', fetchUrl);
        
        fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.table) {
                const tableBody = document.getElementById('usersTableBody');
                if (tableBody) tableBody.outerHTML = data.table;
                bindTableCheckboxes(); // Re-bind checkboxes logic
            }
            if(paginationContainer && data.pagination !== undefined) {
                paginationContainer.innerHTML = data.pagination;
                if(data.pagination.trim() === '') {
                    paginationContainer.classList.add('d-none');
                } else {
                    paginationContainer.classList.remove('d-none');
                }
                bindAjaxLinks();
            }
            bindAjaxLinks(); // Re-bind for table headers too
        })
        .catch(error => console.error('Error fetching data:', error));
    }
    
    filterInputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(mainSearchTimeout);
            mainSearchTimeout = setTimeout(() => performSearch(), 600);
        });
        if(input.tagName === 'SELECT') {
            input.addEventListener('change', function() {
                clearTimeout(mainSearchTimeout);
                performSearch();
            });
        }
    });
    
    if (mainSearchForm) {
        mainSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearTimeout(mainSearchTimeout);
            performSearch();
        });
    }
    
    function bindAjaxLinks() {
        // Pagination Links
        const pagLinks = paginationContainer ? paginationContainer.querySelectorAll('.pagination a') : [];
        // Header Sort Links
        const sortLinks = document.querySelectorAll('thead th a');
        
        const allLinks = [...pagLinks, ...sortLinks];
        
        allLinks.forEach(link => {
            // Remove existing listener to prevent duplicates
            const newLink = link.cloneNode(true);
            link.parentNode.replaceChild(newLink, link);
            
            newLink.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                if (url) performSearch(url);
            });
        });
    }
    
    // Initial Bind
    bindAjaxLinks();

    const mainSearchInput = document.getElementById('mainSearchInput');
    if(mainSearchInput && mainSearchInput.value) {
        mainSearchInput.focus();
        mainSearchInput.setSelectionRange(mainSearchInput.value.length, mainSearchInput.value.length);
    }
</script>
@endpush
