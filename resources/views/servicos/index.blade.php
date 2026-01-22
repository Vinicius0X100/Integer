@extends('layouts.app')

@section('page-title', 'Serviços Prestados')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white mb-1">Controle de Serviços</h2>
            <p class="text-white-50 mb-0">Gerenciamento de serviços prestados aos clientes.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-dark rounded-pill px-4 py-2 shadow-sm border border-secondary border-opacity-25" data-bs-toggle="modal" data-bs-target="#pdfModal">
                <i class="bi bi-printer me-2"></i> Gerar PDF
            </button>
            <a href="{{ route('servicos.create') }}" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                <i class="bi bi-plus-lg me-2"></i> Novo Serviço
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-dark bg-opacity-50 border-secondary border-opacity-10">
        <div class="card-body p-3">
            <form action="{{ route('servicos.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-secondary border-opacity-25 text-white-50"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-transparent border-secondary border-opacity-25 text-white" placeholder="Buscar serviço ou cliente..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                        <option value="">Status</option>
                        <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="em_andamento" {{ request('status') == 'em_andamento' ? 'selected' : '' }}>Em Andamento</option>
                        <option value="concluido" {{ request('status') == 'concluido' ? 'selected' : '' }}>Concluído</option>
                        <option value="cancelado" {{ request('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="tipo_servico" class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                        <option value="">Tipo de Serviço</option>
                        <option value="Desenvolvimento de Software" {{ request('tipo_servico') == 'Desenvolvimento de Software' ? 'selected' : '' }}>Desenvolvimento</option>
                        <option value="Manutenção" {{ request('tipo_servico') == 'Manutenção' ? 'selected' : '' }}>Manutenção</option>
                        <option value="Consultoria em TI" {{ request('tipo_servico') == 'Consultoria em TI' ? 'selected' : '' }}>Consultoria</option>
                        <option value="SaaS" {{ request('tipo_servico') == 'SaaS' ? 'selected' : '' }}>SaaS</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="recorrente" class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                        <option value="">Recorrência</option>
                        <option value="1" {{ request('recorrente') == '1' ? 'selected' : '' }}>Sim (SaaS)</option>
                        <option value="0" {{ request('recorrente') == '0' ? 'selected' : '' }}>Não</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Filtrar</button>
                    @if(request()->anyFilled(['search', 'status', 'tipo_servico', 'recorrente']))
                        <a href="{{ route('servicos.index') }}" class="btn btn-outline-light rounded-circle" title="Limpar Filtros"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <form id="bulkForm" action="{{ route('servicos.bulk_action') }}" method="POST">
                @csrf
                <input type="hidden" name="action" id="bulkActionInput">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="px-4 py-3 border-0" style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="checkAll">
                            </th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">
                                <a href="{{ route('servicos.index', array_merge(request()->query(), ['sort' => 'titulo', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-secondary d-flex align-items-center">
                                    Serviço <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.7rem;"></i>
                                </a>
                            </th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Cliente</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">
                                <a href="{{ route('servicos.index', array_merge(request()->query(), ['sort' => 'valor_total', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-secondary d-flex align-items-center">
                                    Valor Total <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.7rem;"></i>
                                </a>
                            </th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Parcelamento</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">
                                <a href="{{ route('servicos.index', array_merge(request()->query(), ['sort' => 'data_servico', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-secondary d-flex align-items-center">
                                    Data <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.7rem;"></i>
                                </a>
                            </th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">
                                <a href="{{ route('servicos.index', array_merge(request()->query(), ['sort' => 'status', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-secondary d-flex align-items-center">
                                    Status <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.7rem;"></i>
                                </a>
                            </th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($servicos as $servico)
                            <tr>
                                <td class="px-4 py-3 border-bottom-0">
                                    <input type="checkbox" class="form-check-input row-checkbox" name="selected[]" value="{{ $servico->id }}">
                                </td>
                                <td class="px-4 py-3 border-bottom-0">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold me-3" style="width: 40px; height: 40px;">
                                            <i class="bi bi-briefcase"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-white">{{ $servico->titulo }}</h6>
                                            <small class="text-white-50 d-block">{{ Str::limit($servico->descricao, 30) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 border-bottom-0">
                                    <a href="{{ route('clientes.index', ['open_client' => $servico->cliente_id]) }}" class="text-decoration-none text-white fw-medium hover-underline">
                                        {{ $servico->cliente->nome ?? $servico->cliente->razao_social }} <i class="bi bi-box-arrow-up-right ms-1 text-white-50" style="font-size: 0.75rem;"></i>
                                    </a>
                                </td>
                                <td class="px-4 py-3 border-bottom-0">
                                    <span class="text-white fw-bold">R$ {{ number_format($servico->valor_total, 2, ',', '.') }}</span>
                                    @if($servico->lucro_estimado)
                                        <small class="d-block text-success" style="font-size: 0.75rem;">
                                            Lucro: R$ {{ number_format($servico->lucro_estimado, 2, ',', '.') }}
                                        </small>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border-bottom-0">
                                    @if($servico->parcelado)
                                        <span class="badge bg-light text-dark border border-secondary border-opacity-25 rounded-pill">
                                            {{ $servico->qtd_parcelas }}x R$ {{ number_format($servico->valor_parcela, 2, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-white-50 small">À vista</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border-bottom-0">
                                    <div class="d-flex flex-column">
                                        <span class="text-white small">Exec: {{ $servico->data_servico->format('d/m/Y') }}</span>
                                        @if($servico->prazo_entrega)
                                            <span class="text-white-50 small">Prazo: {{ $servico->prazo_entrega->format('d/m/Y') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 border-bottom-0">
                                    @php
                                        $statusClass = match($servico->status) {
                                            'pendente' => 'bg-warning bg-opacity-10 text-warning',
                                            'em_andamento' => 'bg-info bg-opacity-10 text-info',
                                            'concluido' => 'bg-success bg-opacity-10 text-success',
                                            'cancelado' => 'bg-danger bg-opacity-10 text-danger',
                                            default => 'bg-secondary bg-opacity-10 text-secondary'
                                        };
                                        $statusLabel = match($servico->status) {
                                            'pendente' => 'Pendente',
                                            'em_andamento' => 'Em Andamento',
                                            'concluido' => 'Concluído',
                                            'cancelado' => 'Cancelado',
                                            default => $servico->status
                                        };
                                    @endphp
                                    <span class="badge rounded-pill {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 border-bottom-0 text-end">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-icon btn-dark text-white-50 hover-white rounded-circle me-1" 
                                                title="Detalhes"
                                                onclick='showServicoDetails(@json($servico))'>
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="{{ route('servicos.edit', $servico->id) }}" class="btn btn-sm btn-icon btn-dark text-white-50 hover-white rounded-circle me-1" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-icon btn-dark text-white-50 hover-danger rounded-circle" title="Excluir" onclick="openDeleteServiceModal({{ $servico->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-5 text-center text-white-50">
                                    <div class="mb-3">
                                        <i class="bi bi-briefcase display-4 opacity-25"></i>
                                    </div>
                                    <p class="mb-0">Nenhum serviço registrado.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </form>
            @if($servicos->hasPages())
                <div class="px-4 py-3 border-top border-secondary border-opacity-10">
                    {{ $servicos->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- PDF Modal -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg" style="background-color: var(--apple-card-bg); backdrop-filter: saturate(180%) blur(20px);">
            <div class="modal-header border-bottom border-secondary border-opacity-10">
                <h5 class="modal-title fw-bold text-white">Gerar Relatório PDF</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('servicos.pdf') }}" method="GET" target="_blank">
                <div class="modal-body p-4">
                    <p class="text-white-50 mb-3">Selecione as colunas que deseja incluir no relatório:</p>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="cliente" id="colCliente" checked>
                                <label class="form-check-label text-white" for="colCliente">Cliente</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="titulo" id="colTitulo" checked>
                                <label class="form-check-label text-white" for="colTitulo">Título/Descrição</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="valor" id="colValor" checked>
                                <label class="form-check-label text-white" for="colValor">Valor Total</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="status" id="colStatus" checked>
                                <label class="form-check-label text-white" for="colStatus">Status</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="data" id="colData" checked>
                                <label class="form-check-label text-white" for="colData">Data</label>
                            </div>
                        </div>
                    </div>
                    <!-- Hidden filters to persist current view -->
                    @foreach(request()->all() as $key => $value)
                        @if(!in_array($key, ['page', 'columns']))
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-10">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" onclick="setTimeout(() => bootstrap.Modal.getInstance(document.getElementById('pdfModal')).hide(), 1000)">
                        <i class="bi bi-file-earmark-pdf me-2"></i> Gerar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detalhes -->
<div class="modal fade" id="modalDetalhes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-secondary border-opacity-25 shadow-lg">
            <div class="modal-header border-bottom border-secondary border-opacity-10">
                <h5 class="modal-title text-white fw-bold">Detalhes do Serviço</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="text-white-50 small">Título</label>
                        <p class="fw-bold text-white mb-0 h5" id="detailTitulo"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white-50 small">Tipo de Serviço</label>
                        <p class="fw-bold text-info mb-0" id="detailTipo"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white-50 small">Cliente</label>
                        <p class="fw-bold text-white mb-0" id="detailCliente"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white-50 small">Status</label>
                        <p class="fw-bold text-white mb-0" id="detailStatus"></p>
                    </div>
                    <div class="col-12">
                        <label class="text-white-50 small">Descrição Detalhada</label>
                        <p class="text-white mb-0 p-3 bg-secondary bg-opacity-10 rounded-3" id="detailDescricao"></p>
                    </div>
                    
                    <div class="col-12"><hr class="border-secondary border-opacity-10"></div>
                    
                    <div class="col-md-3">
                        <label class="text-white-50 small">Valor Total</label>
                        <p class="fw-bold text-success mb-0" id="detailValor"></p>
                    </div>
                    <div class="col-md-3">
                        <label class="text-white-50 small">Lucro Estimado</label>
                        <p class="fw-bold text-primary mb-0" id="detailLucro"></p>
                    </div>
                    <div class="col-md-3">
                        <label class="text-white-50 small">Parcelamento</label>
                        <p class="fw-bold text-white mb-0" id="detailParcelamento"></p>
                    </div>
                    <div class="col-md-3">
                        <label class="text-white-50 small">Recorrência (SaaS)</label>
                        <p class="fw-bold text-warning mb-0" id="detailRecorrencia"></p>
                    </div>

                    <div class="col-12"><hr class="border-secondary border-opacity-10"></div>

                    <div class="col-md-6">
                        <label class="text-white-50 small">Data do Serviço</label>
                        <p class="fw-bold text-white mb-0" id="detailData"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white-50 small">Prazo de Entrega</label>
                        <p class="fw-bold text-white mb-0" id="detailPrazo"></p>
                    </div>
                    
                    <div class="col-12" id="divContrato">
                        <label class="text-white-50 small">Contrato Anexo</label>
                        <p class="mb-0"><a href="#" id="linkContrato" target="_blank" class="btn btn-sm btn-outline-light rounded-pill"><i class="bi bi-file-earmark-pdf me-2"></i> Visualizar Contrato</a></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-10">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmação Exclusão Serviço -->
<div class="modal fade" id="deleteServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg" style="background-color: var(--apple-card-bg); backdrop-filter: saturate(180%) blur(20px);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-white">Confirmar Exclusão</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="text-center mb-4">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-exclamation-triangle text-danger fs-2"></i>
                    </div>
                    <h4 class="fw-bold text-white">Tem certeza?</h4>
                    <p class="text-white-50">Você está prestes a excluir este serviço. Esta ação não poderá ser desfeita.</p>
                </div>
                <form id="deleteServiceForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger rounded-pill py-2 fw-bold">
                            Sim, excluir serviço
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
                <option value="concluido">Marcar Concluído</option>
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
        <div class="modal-content rounded-4 border-0 shadow-lg" style="background-color: var(--apple-card-bg); backdrop-filter: saturate(180%) blur(20px);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-white">Confirmar Ação em Massa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="text-center mb-4">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-shield-lock text-warning fs-2"></i>
                    </div>
                    <h4 class="fw-bold text-white">Autenticação Necessária</h4>
                    <p class="text-white-50">Para realizar esta ação em massa, confirme sua senha de administrador.</p>
                </div>
                
                <div class="form-group">
                    <label class="text-white-50 small mb-2">Sua Senha</label>
                    <input type="password" id="bulkPassword" class="form-control bg-dark border-secondary border-opacity-25 text-white" placeholder="Digite sua senha...">
                </div>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-10">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" onclick="submitBulkForm()">Confirmar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('checkAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkToolbar = document.getElementById('bulkToolbar');
        const selectedCount = document.getElementById('selectedCount');

        function updateToolbar() {
            const count = document.querySelectorAll('.row-checkbox:checked').length;
            if (selectedCount) selectedCount.textContent = count;
            if (bulkToolbar) {
                if (count > 0) {
                    bulkToolbar.style.removeProperty('display');
                } else {
                    bulkToolbar.style.display = 'none';
                }
            }
        }

        if(checkAll) {
            checkAll.addEventListener('change', function() {
                rowCheckboxes.forEach(cb => cb.checked = this.checked);
                updateToolbar();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                if (!this.checked && checkAll) checkAll.checked = false;
                if (checkAll && document.querySelectorAll('.row-checkbox:checked').length === rowCheckboxes.length) {
                    checkAll.checked = true;
                }
                updateToolbar();
            });
        });
    });

    function confirmBulkAction() {
        const action = document.getElementById('bulkActionSelect').value;
        if (!action) return;

        // For PDF, no password needed, just submit
        if (action === 'pdf') {
            const form = document.getElementById('bulkForm');
            const originalTarget = form.target;
            const originalAction = form.action;
            
            form.target = '_blank';
            form.action = "{{ route('servicos.pdf') }}";
            
            // Add a hidden input to say it's from bulk selection if needed, 
            // but the IDs are already there in selected[]
            
            form.submit();
            
            // Reset with delay to ensure submission uses correct action
            setTimeout(() => {
                form.target = originalTarget;
                form.action = originalAction;
                document.getElementById('bulkActionSelect').value = '';
            }, 100);
            return;
        }

        // For Delete or Status Update, require password
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
        
        // Add password field to form dynamically
        let pwdInput = document.createElement('input');
        pwdInput.type = 'hidden';
        pwdInput.name = 'password';
        pwdInput.value = password;
        form.appendChild(pwdInput);

        form.submit();
    }

    window.showServicoDetails = function(data) {
        const modal = new bootstrap.Modal(document.getElementById('modalDetalhes'));
        
        // Populate fields
        document.getElementById('detailTitulo').textContent = data.titulo;
        document.getElementById('detailTipo').textContent = data.tipo_servico || '-';
        document.getElementById('detailDescricao').textContent = data.descricao || 'Sem descrição';
        
        // Format values
        const valor = parseFloat(data.valor_total).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
        document.getElementById('detailValor').textContent = valor;
        
        if (data.lucro_estimado) {
            const lucro = parseFloat(data.lucro_estimado).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
            document.getElementById('detailLucro').textContent = lucro;
        } else {
            document.getElementById('detailLucro').textContent = '-';
        }

        // Status
        const statusMap = {
            'pendente': 'Pendente',
            'em_andamento': 'Em Andamento',
            'concluido': 'Concluído',
            'cancelado': 'Cancelado'
        };
        document.getElementById('detailStatus').textContent = statusMap[data.status] || data.status;

        // Dates
        const formatDate = (dateStr) => {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('pt-BR');
        };
        document.getElementById('detailData').textContent = formatDate(data.data_servico);
        document.getElementById('detailPrazo').textContent = formatDate(data.prazo_entrega);

        // Recurrence/Parcelas
        if (data.recorrente) {
            const valorRec = parseFloat(data.valor_recorrencia).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
            document.getElementById('detailRecorrencia').textContent = `Sim - ${valorRec}/mês`;
        } else {
            document.getElementById('detailRecorrencia').textContent = 'Não';
        }

        if (data.parcelado) {
            const valorParc = parseFloat(data.valor_parcela).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
            document.getElementById('detailParcelamento').textContent = `${data.qtd_parcelas}x de ${valorParc}`;
        } else {
            document.getElementById('detailParcelamento').textContent = 'À vista';
        }

        // Client info
        if (data.cliente) {
            document.getElementById('detailCliente').textContent = data.cliente.nome || data.cliente.razao_social;
        } else {
            document.getElementById('detailCliente').textContent = '-';
        }

        modal.show();
    };

    window.openDeleteServiceModal = function(id) {
        const form = document.getElementById('deleteServiceForm');
        form.action = `/servicos/${id}`;
        const modal = new bootstrap.Modal(document.getElementById('deleteServiceModal'));
        modal.show();
    };
</script>
@endpush
