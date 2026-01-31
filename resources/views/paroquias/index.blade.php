@extends('layouts.app')

@section('page-title', 'Paróquias - SisMatriz')

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
                        <i class="bi bi-building-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Gerenciamento de Paróquias</h5>
                        <small class="text-muted">Administre as paróquias do sistema</small>
                    </div>
                </div>
                <div class="d-flex gap-2 w-100 w-md-auto align-items-center">
                    <form action="{{ route('paroquias.index') }}" method="GET" class="d-flex flex-column flex-md-row gap-2 flex-grow-1">
                        
                        <select name="status" class="form-select bg-light border-0 rounded-pill" style="min-width: 120px;" onchange="this.form.submit()">
                            <option value="">Status: Todos</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Ativa</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Inativa</option>
                        </select>

                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 border-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control bg-light border-start-0 rounded-end-pill border-0" placeholder="Buscar paróquia..." value="{{ request('search') }}">
                        </div>
                    </form>
                    <a href="{{ route('paroquias.create') }}" class="btn btn-primary rounded-pill px-3 d-flex align-items-center gap-2 fw-medium whitespace-nowrap">
                        <i class="bi bi-plus-lg"></i>
                        <span class="d-none d-lg-inline">Nova</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase text-muted small fw-bold">Paróquia</th>
                        <th class="py-3 text-uppercase text-muted small fw-bold">Contato</th>
                        <th class="py-3 text-uppercase text-muted small fw-bold">Localização</th>
                        <th class="py-3 text-uppercase text-muted small fw-bold">Pároco</th>
                        <th class="py-3 text-uppercase text-muted small fw-bold">Adicionada em</th>
                        <th class="py-3 text-uppercase text-muted small fw-bold text-center">Status</th>
                        <th class="py-3 text-uppercase text-muted small fw-bold text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($paroquias as $paroquia)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    @if($paroquia->foto)
                                        <img src="https://central.sismatriz.online/storage/uploads/paroquias/{{ $paroquia->foto }}" alt="{{ $paroquia->name }}" class="rounded-circle me-3 border" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="avatar-initial rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold me-3" style="width: 40px; height: 40px;">
                                            {{ substr($paroquia->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark">{{ $paroquia->name }}</div>
                                        <div class="small text-muted">ID: {{ $paroquia->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="small text-muted">{{ $paroquia->email ?? '-' }}</span>
                                    <span class="small text-muted">{{ $paroquia->phone ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="small text-muted">{{ $paroquia->address ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="small text-muted">{{ $paroquia->paroco ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="small text-muted">{{ $paroquia->added_at ? $paroquia->added_at->format('d/m/Y H:i') : '-' }}</span>
                            </td>
                            <td class="text-center">
                                @if($paroquia->status == 0)
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Ativa</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Inativa</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('paroquias.edit', $paroquia->id) }}" class="btn btn-sm btn-light rounded-circle" title="Editar">
                                        <i class="bi bi-pencil text-primary"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light rounded-circle" onclick='confirmDelete({{ $paroquia->id }}, @json($paroquia->name))' title="Excluir">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                    <i class="bi bi-building-dash fs-1 mb-3 opacity-50"></i>
                                    <p class="mb-0">Nenhuma paróquia encontrada.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paroquias->hasPages())
            <div class="card-footer bg-white border-top p-4">
                {{ $paroquias->appends(request()->query())->links() }}
            </div>
        @endif
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
                        Você está prestes a excluir a paróquia <strong id="deleteParoquiaName"></strong>.<br>
                        Todos os dados vinculados a esta paróquia serão apagados permanentemente.
                    </p>
                </div>
                
                <form id="deleteParoquiaForm" method="POST" action="">
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

@endsection

@push('scripts')
<script>
    function confirmDelete(id, name) {
        const form = document.getElementById('deleteParoquiaForm');
        form.action = `/paroquias/${id}`;
        document.getElementById('deleteParoquiaName').textContent = name;
        new bootstrap.Modal(document.getElementById('deleteConfirmationModal')).show();
    }
</script>
@endpush
