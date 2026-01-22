@extends('layouts.app')

@section('page-title', 'Usuários SisMatriz Ticket')

@section('content')
<div class="card border-0 shadow-sm" style="border-radius: 18px; overflow: hidden;">
    <div class="card-body p-0">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show m-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show m-4" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Barra de Ferramentas -->
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center p-4 border-bottom gap-3">
            <div class="d-flex align-items-center gap-2">
                @if(file_exists(public_path('img/sismatriz-ticket-logo.jpg')))
                    <img src="{{ asset('img/sismatriz-ticket-logo.jpg') }}" alt="Ticket Logo" style="width: 32px; height: 32px; object-fit: contain;">
                @else
                    <i class="bi bi-ticket-perforated fs-4 text-muted"></i>
                @endif
                <h5 class="card-title text-muted fw-bold text-uppercase m-0">SisMatriz Ticket</h5>
            </div>
            
            <div class="d-flex flex-column flex-md-row gap-2 w-100 w-lg-auto align-items-stretch align-items-md-center">
                <form id="filterForm" action="{{ route('sismatriz.index') }}" method="GET" class="d-flex flex-column flex-md-row gap-2 flex-grow-1 align-items-stretch align-items-md-center">
                    <!-- Busca -->
                    <div class="input-group flex-nowrap" style="min-width: 250px;">
                        <span class="input-group-text bg-transparent border-end-0 rounded-start-pill ps-3">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por nome ou email..." value="{{ request('search') }}">
                    </div>

                    <!-- Filtro Role -->
                    <select name="role" class="form-select rounded-pill" style="min-width: 140px;">
                        <option value="">Função: Todas</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-secondary rounded-pill">Filtrar</button>
                    @if(request()->anyFilled(['search', 'role']))
                        <a href="{{ route('sismatriz.index') }}" class="btn btn-outline-danger rounded-circle" title="Limpar Filtros"><i class="bi bi-x-lg"></i></a>
                    @endif
                </form>

                <div class="d-flex gap-2">
                    <button onclick="submitBulkAction('pdf')" class="btn btn-dark rounded-pill px-4 d-flex align-items-center justify-content-center whitespace-nowrap shadow-sm border border-secondary border-opacity-25">
                        <i class="bi bi-printer me-2"></i> PDF
                    </button>
                    <a href="{{ route('sismatriz.create') }}" class="btn btn-primary rounded-pill px-4 d-flex align-items-center justify-content-center whitespace-nowrap">
                        <i class="bi bi-plus-lg me-2"></i> Novo
                    </a>
                </div>
            </div>
        </div>

        <form id="bulkForm" action="{{ route('sismatriz.bulk_action') }}" method="POST">
            @csrf
            <input type="hidden" name="action" id="bulkActionInput">
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 border-bottom" style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="checkAll">
                            </th>
                            <th class="ps-4 py-3 text-uppercase text-muted small fw-bold border-bottom">
                                <a href="{{ route('sismatriz.index', array_merge(request()->query(), ['sort' => 'name', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-muted d-flex align-items-center">
                                    Usuário <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.7rem;"></i>
                                </a>
                            </th>
                            <th class="py-3 text-uppercase text-muted small fw-bold border-bottom">Sacratech ID</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold border-bottom">
                                <a href="{{ route('sismatriz.index', array_merge(request()->query(), ['sort' => 'role', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-muted d-flex align-items-center">
                                    Função <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.7rem;"></i>
                                </a>
                            </th>
                            <th class="py-3 text-uppercase text-muted small fw-bold border-bottom">
                                <a href="{{ route('sismatriz.index', array_merge(request()->query(), ['sort' => 'created_at', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-muted d-flex align-items-center">
                                    Criado em <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.7rem;"></i>
                                </a>
                            </th>
                            <th class="text-end pe-4 py-3 text-uppercase text-muted small fw-bold border-bottom">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="px-4 py-3">
                                    <input type="checkbox" name="selected[]" value="{{ $user->id }}" class="form-check-input row-checkbox">
                                </td>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold me-3" style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-body">{{ $user->name }}</div>
                                            <div class="text-muted small">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    @if($user->sacratech_id)
                                        <span class="badge bg-info bg-opacity-10 text-info rounded-pill">#{{ $user->sacratech_id }}</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <span class="text-muted small">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-' }}</span>
                                </td>
                                <td class="text-end pe-4 py-3">
                                    <div class="btn-group">
                                        <a href="{{ route('sismatriz.edit', $user->id) }}" class="btn btn-sm btn-icon btn-light rounded-circle me-1" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-icon btn-light text-danger rounded-circle" title="Excluir" onclick="openDeleteModal({{ $user->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted d-flex flex-column align-items-center">
                                        <i class="bi bi-search fs-1 mb-3 opacity-25"></i>
                                        <p class="mb-0">Nenhum usuário encontrado.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        @if($users->hasPages())
            <div class="d-flex justify-content-center border-top p-3">
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
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="confirmBulkAction()">
                Aplicar
            </button>
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
                                <input class="form-check-input" type="checkbox" name="columns[]" value="sacratech_id" id="col_sacratech_id">
                                <label class="form-check-label" for="col_sacratech_id">Sacratech ID</label>
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
                                <input class="form-check-input" type="checkbox" name="columns[]" value="email" id="col_email" checked>
                                <label class="form-check-label" for="col_email">Email</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="role" id="col_role" checked>
                                <label class="form-check-label" for="col_role">Papel</label>
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

<!-- Modal Confirmação Bulk Action / Delete -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg" style="background-color: var(--apple-card-bg); backdrop-filter: saturate(180%) blur(20px);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-white">Autenticação Necessária</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="text-white-50 mb-3">Para realizar esta ação, confirme sua senha de administrador.</p>
                <div class="form-group">
                    <input type="password" id="confirmPassword" class="form-control bg-dark border-secondary border-opacity-25 text-white" placeholder="Sua senha...">
                </div>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-10">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" onclick="submitConfirmedAction()">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Delete Form for Single Item -->
<form id="deleteForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Storage Management
        const STORAGE_KEY = 'sismatriz_ticket_selected_ids';
        const INTERNAL_NAV_KEY = 'sismatriz_ticket_internal_nav';
        
        function getSelectedIds() {
            return new Set(JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'));
        }
        
        function saveSelectedIds(ids) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(Array.from(ids)));
            updateToolbar();
        }

        const checkAll = document.getElementById('checkAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkToolbar = document.getElementById('bulkToolbar');
        const selectedCount = document.getElementById('selectedCount');
        
        function updateToolbar() {
            const ids = getSelectedIds();
            
            if(selectedCount) selectedCount.textContent = ids.size;
            
            if(bulkToolbar) {
                if(ids.size > 0) {
                    bulkToolbar.style.removeProperty('display');
                } else {
                    bulkToolbar.style.display = 'none';
                }
            }

            // Update Select All State
            if(checkAll && rowCheckboxes.length > 0) {
                const allVisibleSelected = Array.from(rowCheckboxes).every(cb => ids.has(cb.value));
                checkAll.checked = allVisibleSelected;
            }
        }

        // Initialize Selection
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
                tr.style.backgroundColor = 'var(--bs-primary-bg-subtle)';
            } else {
                tr.classList.remove('table-active');
                tr.style.removeProperty('background-color');
            }
        }

        const initialIds = getSelectedIds();
        rowCheckboxes.forEach(cb => {
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

        // Mark internal navigation for filter forms
        const filterForm = document.getElementById('filterForm');
        if (filterForm) {
            filterForm.addEventListener('submit', () => {
                sessionStorage.setItem(INTERNAL_NAV_KEY, 'true');
            });
            // Handle select onchange (though this form doesn't seem to use onchange submit by default, but just in case)
            filterForm.querySelectorAll('select').forEach(select => {
                select.addEventListener('change', () => {
                     // If the select submits the form, or if we want to be safe
                     // The user code for Ticket index uses a button to filter, but Main uses onchange.
                     // Just adding for completeness if they add auto-submit later.
                });
            });
        }

        if(checkAll) {
            checkAll.addEventListener('change', function() {
                const ids = getSelectedIds();
                rowCheckboxes.forEach(cb => {
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

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const ids = getSelectedIds();
                updateRowStyle(this);
                if(this.checked) {
                    ids.add(this.value);
                } else {
                    ids.delete(this.value);
                }
                saveSelectedIds(ids);
            });
        });

        // Expose for global use
        window.getSelectedIds = getSelectedIds;
    });

    let currentActionType = null;
    let currentDeleteId = null;

    // Helper to safely get modal instance
    function getModal(id) {
        const el = document.getElementById(id);
        if (!el) return null;
        return bootstrap.Modal.getOrCreateInstance(el);
    }

    function confirmBulkAction() {
        const action = document.getElementById('bulkActionSelect').value;
        if (!action) return;

        // Check if any item is selected from storage
        const ids = window.getSelectedIds();
        if (ids.size === 0) {
             alert('Nenhum usuário selecionado.');
             return;
        }

        if (action === 'pdf') {
            const modal = getModal('pdfColumnsModal');
            if (modal) modal.show();
            return;
        }

        currentActionType = 'bulk';
        document.getElementById('confirmPassword').value = '';
        const modal = getModal('confirmModal');
        if (modal) modal.show();
    }

    function openDeleteModal(id) {
        currentActionType = 'single_delete';
        currentDeleteId = id;
        document.getElementById('confirmPassword').value = '';
        const modal = getModal('confirmModal');
        if (modal) modal.show();
    }

    function submitConfirmedAction() {
        const password = document.getElementById('confirmPassword').value;
        if(!password) {
            alert('Senha obrigatória');
            return;
        }

        if(currentActionType === 'bulk') {
            const form = document.getElementById('bulkForm');
            const action = document.getElementById('bulkActionSelect').value;
            document.getElementById('bulkActionInput').value = action;
            
            // Inject selected IDs and disable default checkboxes
            const ids = window.getSelectedIds();
            
            // Remove any existing hidden inputs we might have added previously
            form.querySelectorAll('input[name="selected[]"][type="hidden"]').forEach(el => el.remove());
            
            // Disable visible checkboxes so they don't submit duplicates/partial lists
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.disabled = true);
            
            // Add hidden inputs for all selected IDs
            ids.forEach(id => {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected[]';
                input.value = id;
                form.appendChild(input);
            });
            
            let pwdInput = document.createElement('input');
            pwdInput.type = 'hidden';
            pwdInput.name = 'password';
            pwdInput.value = password;
            form.appendChild(pwdInput);
            
            form.submit();
        } else if(currentActionType === 'single_delete') {
            const form = document.getElementById('deleteForm');
            form.action = `/sismatriz/users/${currentDeleteId}`;
            form.submit();
        }
    }

    function generatePdfWithColumns() {
        // Use submitBulkAction logic but add columns
        const form = document.getElementById('bulkForm');
        const originalAction = form.action;
        const originalTarget = form.target;

        // Prepare form for PDF
        const ids = window.getSelectedIds();
        
        // Remove any existing hidden inputs
        form.querySelectorAll('input[name="selected[]"][type="hidden"]').forEach(el => el.remove());
        form.querySelectorAll('input[name="columns[]"][type="hidden"]').forEach(el => el.remove());
        
        // Disable visible checkboxes temporarily
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.disabled = true);
        
        // Add hidden inputs
        const hiddenInputs = [];
        ids.forEach(id => {
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected[]';
            input.value = id;
            form.appendChild(input);
            hiddenInputs.push(input);
        });

        // Add selected columns
        const columnsForm = document.getElementById('pdfColumnsForm');
        const formData = new FormData(columnsForm);
        for (const [key, value] of formData.entries()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key; // columns[]
            input.value = value;
            form.appendChild(input);
            hiddenInputs.push(input);
        }

        form.action = "{{ route('sismatriz.pdf') }}";
        form.target = "_blank";
        
        form.submit();

        // Close modal
        const modalEl = document.getElementById('pdfColumnsModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        // Restore form state after a short delay
        setTimeout(() => {
            form.action = originalAction;
            form.target = originalTarget;
            document.getElementById('bulkActionSelect').value = '';
            
            // Remove hidden inputs
            hiddenInputs.forEach(input => input.remove());
            
            // Re-enable checkboxes
            checkboxes.forEach(cb => cb.disabled = false);
        }, 500);
    }

    function submitBulkAction(type) {
        if(type === 'pdf') {
            // Check if any item is selected from storage
            const ids = window.getSelectedIds();
            if (ids.size === 0) {
                 alert('Nenhum usuário selecionado.');
                 return;
            }
            
            const modal = new bootstrap.Modal(document.getElementById('pdfColumnsModal'));
            modal.show();
        }
    }
</script>
@endpush
