@extends('layouts.app')

@section('page-title', 'Usuários - SisMatriz Principal')

@section('content')
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
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
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
                <div class="d-flex gap-2 w-100 w-md-auto align-items-center">
                    <form action="{{ route('sismatriz-main.index') }}" method="GET" class="d-flex flex-column flex-md-row gap-2 flex-grow-1">
                        
                        <select name="status" class="form-select bg-light border-0 rounded-pill" style="min-width: 120px;" onchange="this.form.submit()">
                            <option value="">Status: Todos</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Ativo</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Inativo</option>
                        </select>

                        <select name="paroquia_id" class="form-select bg-light border-0 rounded-pill" style="min-width: 150px; max-width: 200px;" onchange="this.form.submit()">
                            <option value="">Paróquia: Todas</option>
                            @foreach($paroquias as $p)
                                <option value="{{ $p->id }}" {{ request('paroquia_id') == $p->id ? 'selected' : '' }}>{{ \Illuminate\Support\Str::limit($p->name, 20) }}</option>
                            @endforeach
                        </select>

                        <select name="role" class="form-select bg-light border-0 rounded-pill" style="min-width: 140px; max-width: 250px;" onchange="this.form.submit()">
                            <option value="">Cargo: Todos</option>
                            @foreach($rolesMap as $id => $roleName)
                                <option value="{{ $id }}" {{ request('role') == $id ? 'selected' : '' }}>{{ $roleName }}</option>
                            @endforeach
                        </select>

                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 border-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control bg-light border-start-0 rounded-end-pill border-0" placeholder="Buscar..." value="{{ request('search') }}">
                        </div>
                    </form>
                    <a href="{{ route('sismatriz-main.create') }}" class="btn btn-primary rounded-pill px-3 d-flex align-items-center gap-2 fw-medium whitespace-nowrap">
                        <i class="bi bi-plus-lg"></i>
                        <span class="d-none d-lg-inline">Novo</span>
                    </a>
                </div>
            </div>
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
                        <th class="py-3 text-uppercase text-muted small fw-bold text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-4">
                                <div class="form-check">
                                    <input class="form-check-input user-checkbox" type="checkbox" name="selected[]" value="{{ $user->id }}">
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($user->avatar && $user->avatar !== 'unknow_user.png')
                                        <img src="https://central.sismatriz.online/storage/uploads/avatars/{{ $user->avatar }}" alt="{{ $user->name }}" class="rounded-circle me-3 border" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="avatar-initial rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold me-3" style="width: 40px; height: 40px;">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                                        <div class="small text-muted">ID: {{ $user->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $user->user }}</span>
                                    <span class="small text-muted">{{ $user->email }}</span>
                                </div>
                            </td>
                            <td>
                                @php
                                    $userRoleIds = $user->rule ? explode(',', $user->rule) : [];
                                    $userRoleNames = [];
                                    foreach($userRoleIds as $rid) {
                                        if(isset($rolesMap[$rid])) {
                                            $userRoleNames[] = $rolesMap[$rid];
                                        }
                                    }
                                @endphp
                                @if(count($userRoleNames) > 0)
                                    <span class="badge bg-info bg-opacity-10 text-info rounded-pill mb-1" title="{{ implode(', ', $userRoleNames) }}">
                                        {{ count($userRoleNames) }} Cargo(s)
                                    </span>
                                    <div class="small text-muted text-truncate" style="max-width: 200px;">
                                        {{ implode(', ', $userRoleNames) }}
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                @if($user->paroquia && $user->paroquia->foto)
                                    <img src="https://central.sismatriz.online/storage/uploads/paroquias/{{ $user->paroquia->foto }}" alt="{{ $user->paroquia->name }}" title="{{ $user->paroquia->name }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;" data-bs-toggle="tooltip">
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($user->is_pass_change == 1)
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Alterada</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Padrão</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($user->status == 0)
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Ativo</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Inativo</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-light rounded-circle" onclick="showUserDetails({{ $user->id }})" title="Detalhes">
                                        <i class="bi bi-eye text-primary"></i>
                                    </button>
                                    <a href="{{ route('sismatriz-main.edit', $user->id) }}" class="btn btn-sm btn-light rounded-circle" title="Editar">
                                        <i class="bi bi-pencil text-primary"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light rounded-circle" onclick='confirmDelete({{ $user->id }}, @json($user->name))' title="Excluir">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                    <i class="bi bi-inbox fs-1 mb-3 opacity-50"></i>
                                    <p class="mb-0">Nenhum usuário encontrado.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="card-footer bg-white border-top p-4">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
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
                <option value="pdf">Gerar PDF</option>
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

<!-- Modal PDF Columns -->
<div class="modal fade" id="pdfColumnsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Configurar Relatório PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Selecione as colunas que deseja exibir no relatório:</p>
                <form id="pdfColumnsForm">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="id" id="col_id">
                                <label class="form-check-label" for="col_id">ID</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="name" id="col_name" checked>
                                <label class="form-check-label" for="col_name">Nome</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="user" id="col_user" checked>
                                <label class="form-check-label" for="col_user">Usuário (Login)</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="email" id="col_email" checked>
                                <label class="form-check-label" for="col_email">Email</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="paroquia" id="col_paroquia" checked>
                                <label class="form-check-label" for="col_paroquia">Paróquia</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="roles" id="col_roles" checked>
                                <label class="form-check-label" for="col_roles">Cargos</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="status" id="col_status" checked>
                                <label class="form-check-label" for="col_status">Status</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="created_at" id="col_created_at">
                                <label class="form-check-label" for="col_created_at">Data de Cadastro</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" onclick="generatePdfWithColumns()">Gerar PDF</button>
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

<form id="pdfForm" action="{{ route('sismatriz-main.pdf') }}" method="POST" style="display: none;" target="_blank">
    @csrf
    <input type="hidden" name="search" value="{{ request('search') }}">
    <div id="pdfSelectedInputs"></div>
</form>

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
                                    <div class="col-md-6"><strong>Último acesso:</strong> ${user.formatted_last_attempt}</div>
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
</script>
@endpush
