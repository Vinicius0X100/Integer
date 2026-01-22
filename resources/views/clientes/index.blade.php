@extends('layouts.app')

@section('page-title', 'Clientes')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white mb-1">Gerenciamento de Clientes</h2>
            <p class="text-white-50 mb-0">Listagem e administração da base de clientes.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-dark rounded-pill px-4 py-2 shadow-sm border border-secondary border-opacity-25" data-bs-toggle="modal" data-bs-target="#pdfModal">
                <i class="bi bi-printer me-2"></i> Gerar PDF
            </button>
            <a href="{{ route('clientes.create') }}" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                <i class="bi bi-plus-lg me-2"></i> Novo Cliente
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-dark bg-opacity-50 border-secondary border-opacity-10">
        <div class="card-body p-3">
            <form action="{{ route('clientes.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-secondary border-opacity-25 text-white-50"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-transparent border-secondary border-opacity-25 text-white" placeholder="Buscar por nome, CPF/CNPJ..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="tipo" class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                        <option value="">Todos os Tipos</option>
                        <option value="PF" {{ request('tipo') == 'PF' ? 'selected' : '' }}>Pessoa Física</option>
                        <option value="PJ" {{ request('tipo') == 'PJ' ? 'selected' : '' }}>Pessoa Jurídica</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status_financeiro" class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                        <option value="">Todas as Modalidades</option>
                        <option value="pago" {{ request('status_financeiro') == 'pago' ? 'selected' : '' }}>Pagos</option>
                        <option value="gratuito" {{ request('status_financeiro') == 'gratuito' ? 'selected' : '' }}>Gratuitos</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Filtrar</button>
                    @if(request()->anyFilled(['search', 'tipo', 'status_financeiro']))
                        <a href="{{ route('clientes.index') }}" class="btn btn-outline-light rounded-circle" title="Limpar Filtros"><i class="bi bi-x-lg"></i></a>
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
            <form id="bulkForm" action="{{ route('clientes.bulk_action') }}" method="POST">
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
                                    <a href="{{ route('clientes.index', array_merge(request()->query(), ['sort' => 'nome', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-secondary d-flex align-items-center">
                                        Cliente <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.7rem;"></i>
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">
                                    <a href="{{ route('clientes.index', array_merge(request()->query(), ['sort' => 'tipo', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-secondary d-flex align-items-center">
                                        Tipo <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.7rem;"></i>
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Serviço</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0">Contato</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clientes as $cliente)
                                <tr>
                                    <td class="px-4 py-3 border-bottom-0">
                                        <input type="checkbox" name="selected[]" value="{{ $cliente->id }}" class="form-check-input row-checkbox">
                                    </td>
                                <td class="px-4 py-3 border-bottom-0">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold me-3" style="width: 40px; height: 40px;">
                                            @php
                                                $initial = '';
                                                if ($cliente->tipo === 'PF') {
                                                    $initial = substr($cliente->nome, 0, 1);
                                                } else {
                                                    $initial = substr($cliente->nome ?? $cliente->razao_social, 0, 1);
                                                }
                                            @endphp
                                            {{ strtoupper($initial) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-white">
                                                @if($cliente->tipo === 'PF')
                                                    {{ $cliente->nome }}
                                                @else
                                                    {{ $cliente->nome ?? $cliente->razao_social }}
                                                @endif
                                            </h6>
                                            <small class="text-white-50 d-block">
                                                @if($cliente->tipo === 'PF')
                                                    CPF: {{ $cliente->cpf_formatado }}
                                                @else
                                                    CNPJ: {{ $cliente->cnpj_formatado }}
                                                    @if($cliente->nome && $cliente->razao_social)
                                                        <span class="d-block text-muted small" style="font-size: 0.75rem;">
                                                            {{ $cliente->razao_social }}
                                                        </span>
                                                    @endif
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 border-bottom-0">
                                    <span class="badge rounded-pill {{ $cliente->tipo === 'PJ' ? 'bg-info bg-opacity-10 text-info' : 'bg-warning bg-opacity-10 text-warning' }}">
                                        {{ $cliente->tipo }}
                                    </span>
                                    @if($cliente->tipo === 'PJ' && $cliente->tipo_empresa)
                                        <small class="text-muted ms-1">{{ $cliente->tipo_empresa }}</small>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border-bottom-0">
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium text-white">{{ $cliente->tipo_servico ?? 'N/A' }}</span>
                                        <small class="text-white-50">
                                            @if($cliente->modalidade_valor === 'pago')
                                                <span class="text-muted" title="Valor Oculto">
                                                    <i class="bi bi-circle-fill" style="font-size: 5px;"></i>
                                                    <i class="bi bi-circle-fill" style="font-size: 5px;"></i>
                                                    <i class="bi bi-circle-fill" style="font-size: 5px;"></i>
                                                    <i class="bi bi-circle-fill" style="font-size: 5px;"></i>
                                                    <i class="bi bi-circle-fill" style="font-size: 5px;"></i>
                                                    <i class="bi bi-circle-fill" style="font-size: 5px;"></i>
                                                </span>
                                            @else
                                                Gratuito
                                            @endif
                                        </small>
                                    </div>
                                </td>
                                <td class="px-4 py-3 border-bottom-0">
                                    <div class="d-flex flex-column">
                                        @if($cliente->email)
                                            <small class="text-white mb-1"><i class="bi bi-envelope me-1"></i> {{ $cliente->email }}</small>
                                        @endif
                                        @if($cliente->telefone)
                                            <small class="text-white-50"><i class="bi bi-telephone me-1"></i> {{ $cliente->telefone }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 border-bottom-0 text-end">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-light rounded-pill me-1 px-3" 
                                                onclick="showDetails({{ $cliente->id }})" title="Ver Detalhes">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-sm btn-outline-light rounded-pill me-1 px-3" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="Excluir" onclick="openDeleteClientModal({{ $cliente->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-inbox text-secondary display-4 mb-3"></i>
                                        <h5 class="text-white fw-bold">Nenhum cliente encontrado</h5>
                                        <p class="text-white-50">Comece cadastrando um novo cliente no sistema.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
                <option value="pdf">Gerar PDF</option>
            </select>
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="confirmBulkAction()">
                Aplicar
            </button>
        </div>
    </div>
</div>

<!-- Modal PDF -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg" style="background-color: var(--apple-card-bg); backdrop-filter: saturate(180%) blur(20px);">
            <div class="modal-header border-bottom border-secondary border-opacity-10">
                <h5 class="modal-title fw-bold text-white">Gerar Relatório PDF</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('clientes.pdf') }}" method="POST" target="_blank" id="pdfForm">
                    @csrf
                    <!-- Hidden filters from main form -->
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="tipo" value="{{ request('tipo') }}">
                    <input type="hidden" name="status_financeiro" value="{{ request('status_financeiro') }}">
                    
                    <div class="mb-4">
                        <label class="form-label text-white-50 small text-uppercase fw-bold">Tipo de Relatório</label>
                        <div class="d-grid gap-2">
                            <input type="radio" class="btn-check" name="type" id="pdfSimple" value="simple" checked>
                            <label class="btn btn-outline-light text-start p-3 rounded-3 border-secondary border-opacity-25" for="pdfSimple">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-list-ul fs-4 me-3 text-primary"></i>
                                    <div>
                                        <div class="fw-bold">Listagem Simples</div>
                                        <div class="small text-muted">Apenas nomes e contatos básicos</div>
                                    </div>
                                </div>
                            </label>

                            <input type="radio" class="btn-check" name="type" id="pdfDetailed" value="detailed">
                            <label class="btn btn-outline-light text-start p-3 rounded-3 border-secondary border-opacity-25" for="pdfDetailed">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-text fs-4 me-3 text-success"></i>
                                    <div>
                                        <div class="fw-bold">Relatório Detalhado</div>
                                        <div class="small text-muted">Todas as informações, incluindo financeiro</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill py-2" onclick="setTimeout(() => bootstrap.Modal.getInstance(document.getElementById('pdfModal')).hide(), 500)">
                            <i class="bi bi-file-pdf me-2"></i> Gerar PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhes -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg" style="background-color: var(--apple-card-bg); backdrop-filter: saturate(180%) blur(20px);">
            <div class="modal-header border-bottom border-secondary border-opacity-10">
                <h5 class="modal-title fw-bold text-white" id="modalTitle">Detalhes do Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="modalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
                
                <div id="modalContent" class="d-none">
                    <!-- Dados Pessoais/Empresariais -->
                    <h6 class="text-uppercase text-secondary fw-bold small mb-3">Identificação</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="text-white-50 small" id="labelNome">Nome</label>
                            <p class="fw-bold text-white mb-0" id="detailNome"></p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-white-50 small">Tipo</label>
                            <p class="fw-bold text-white mb-0" id="detailTipo"></p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-white-50 small">CPF / CNPJ</label>
                            <p class="fw-bold text-white mb-0" id="detailDoc"></p>
                        </div>
                        
                        <!-- Campos extras PJ -->
                        <div class="col-md-12 pj-field">
                            <label class="text-white-50 small">Razão Social</label>
                            <p class="fw-bold text-white mb-0" id="detailRazaoSocial"></p>
                        </div>
                        <div class="col-md-6 pj-field">
                            <label class="text-white-50 small">Responsável Legal</label>
                            <p class="fw-bold text-white mb-0" id="detailResp"></p>
                        </div>
                        <div class="col-md-6 pj-field">
                            <label class="text-white-50 small">Tipo Empresa</label>
                            <p class="fw-bold text-white mb-0" id="detailTipoEmpresa"></p>
                        </div>
                    </div>

                    <hr class="border-secondary border-opacity-10 my-4">

                    <!-- Serviço -->
                    <h6 class="text-uppercase text-secondary fw-bold small mb-3">Serviço e Cobrança</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="text-white-50 small">Tipo de Serviço</label>
                            <p class="fw-bold text-white mb-0" id="detailServicoTipo"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-white-50 small">Modalidade</label>
                            <p class="fw-bold text-white mb-0" id="detailModalidade"></p>
                        </div>
                        <div class="col-12">
                            <label class="text-white-50 small">Descrição</label>
                            <p class="text-white mb-0" id="detailDescricao"></p>
                        </div>
                        
                        <!-- Financeiro -->
                        <div class="col-md-4">
                            <label class="text-white-50 small">Valor Total</label>
                            <p class="fw-bold text-white mb-0" id="detailValor"></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-white-50 small">Cobrança</label>
                            <p class="fw-bold text-white mb-0" id="detailCobrancaTipo"></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-white-50 small">Automático?</label>
                            <p class="fw-bold text-white mb-0" id="detailAuto"></p>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="text-white-50 small">Parcelado?</label>
                            <p class="fw-bold text-white mb-0" id="detailParcelado"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-white-50 small">Valor da Parcela</label>
                            <p class="fw-bold text-white mb-0" id="detailValorParcela"></p>
                        </div>
                    </div>

                    <hr class="border-secondary border-opacity-10 my-4">

                    <!-- Contato e Endereço -->
                    <h6 class="text-uppercase text-secondary fw-bold small mb-3">Contato e Localização</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-white-50 small">Email</label>
                            <p class="fw-bold text-white mb-0" id="detailEmail"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-white-50 small">Telefone</label>
                            <p class="fw-bold text-white mb-0" id="detailTelefone"></p>
                        </div>
                        <div class="col-12">
                            <label class="text-white-50 small">Endereço Completo</label>
                            <p class="text-white mb-0" id="detailEndereco"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-10">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmação Bulk Action -->
<div class="modal fade" id="bulkConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg" style="background-color: var(--apple-card-bg); backdrop-filter: saturate(180%) blur(20px);">
            <div class="modal-header border-bottom border-secondary border-opacity-10">
                <h5 class="modal-title fw-bold text-white">Confirmar Ação em Massa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-shield-lock text-warning fs-2"></i>
                    </div>
                    <h4 class="fw-bold text-white">Autenticação Necessária</h4>
                    <p class="text-white-50">Para realizar esta ação em massa, confirme sua senha de administrador.</p>
                </div>
                
                <div class="form-group">
                    <label class="text-white-50 small mb-2 fw-bold text-uppercase">Sua Senha</label>
                    <input type="password" id="bulkPassword" class="form-control form-control-lg bg-dark border-secondary border-opacity-25 text-white" placeholder="Digite sua senha...">
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
    // Bulk Actions Logic
    document.addEventListener('DOMContentLoaded', function() {
        // Open Client Modal if parameter exists
        const urlParams = new URLSearchParams(window.location.search);
        const openClientId = urlParams.get('open_client');
        if (openClientId) {
            showDetails(openClientId);
        }

        const checkAll = document.getElementById('checkAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkToolbar = document.getElementById('bulkToolbar');
        const selectedCount = document.getElementById('selectedCount');

        function updateToolbar() {
            const count = document.querySelectorAll('.row-checkbox:checked').length;
            selectedCount.textContent = count;
            if (count > 0) {
                bulkToolbar.style.removeProperty('display');
            } else {
                bulkToolbar.style.display = 'none';
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
                if (!this.checked) checkAll.checked = false;
                if (document.querySelectorAll('.row-checkbox:checked').length === rowCheckboxes.length) {
                    checkAll.checked = true;
                }
                updateToolbar();
            });
        });
    });

    function confirmBulkAction() {
        const action = document.getElementById('bulkActionSelect').value;
        if (!action) return;
        
        const form = document.getElementById('bulkForm');
        
        if (action === 'delete') {
            document.getElementById('bulkPassword').value = '';
            const modal = new bootstrap.Modal(document.getElementById('bulkConfirmModal'));
            modal.show();
        } else if (action === 'pdf') {
            const originalTarget = form.target;
            const originalAction = form.action;
            
            form.target = '_blank';
            form.action = "{{ route('clientes.pdf') }}";
            
            let typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'type';
            typeInput.value = 'detailed';
            form.appendChild(typeInput);
            
            form.submit();
            
            // Delay cleanup to ensure form submission captures the input
            setTimeout(() => {
                form.target = originalTarget;
                form.action = originalAction;
                if (typeInput.parentNode) {
                    form.removeChild(typeInput);
                }
                document.getElementById('bulkActionSelect').value = '';
            }, 100);
        }
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

    window.openDeleteClientModal = function(id) {
        const form = document.getElementById('deleteClientForm');
        form.action = `/clientes/${id}`;
        // Clear password field
        document.getElementById('password').value = '';
        const modal = new bootstrap.Modal(document.getElementById('deleteClientModal'));
        modal.show();
    };

    // Alias for backward compatibility if needed
    window.submitBulkAction = confirmBulkAction;

    function showDetails(id) {
        const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
        modal.show();
        
        document.getElementById('modalLoading').classList.remove('d-none');
        document.getElementById('modalContent').classList.add('d-none');
        document.getElementById('modalTitle').textContent = 'Carregando...';

        fetch(`/clientes/${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('modalTitle').textContent = data.nome || data.razao_social;
                
                // Helper functions
                const formatDoc = (doc, type) => {
                    if(!doc) return '-';
                    const nums = doc.toString().replace(/\D/g, '');
                    if(type === 'PF') {
                        return nums.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
                    } else {
                        return nums.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
                    }
                };

                // Identificação
                document.getElementById('detailNome').textContent = data.nome || '-';
                document.getElementById('detailTipo').textContent = data.tipo;
                
                // CPF/CNPJ Logic
                const docValue = data.tipo === 'PF' ? (data.cpf || data.cpf_formatado) : (data.cnpj || data.cnpj_formatado);
                document.getElementById('detailDoc').textContent = formatDoc(docValue, data.tipo);
                
                // PJ Fields
                const pjFields = document.querySelectorAll('.pj-field');
                if (data.tipo === 'PJ') {
                    pjFields.forEach(el => el.classList.remove('d-none'));
                    document.getElementById('detailRazaoSocial').textContent = data.razao_social || '-';
                    document.getElementById('detailResp').textContent = data.responsavel_legal || '-';
                    document.getElementById('detailTipoEmpresa').textContent = data.tipo_empresa || '-';
                    document.getElementById('labelNome').textContent = 'Nome Fantasia';
                } else {
                    pjFields.forEach(el => el.classList.add('d-none'));
                    document.getElementById('labelNome').textContent = 'Nome';
                }

                // Serviço
                document.getElementById('detailServicoTipo').textContent = data.tipo_servico || '-';
                document.getElementById('detailModalidade').textContent = data.modalidade_valor === 'pago' ? 'Pago' : 'Gratuito';
                document.getElementById('detailDescricao').textContent = data.descricao_servico || 'Sem descrição';
                
                // Financeiro
                if (data.modalidade_valor === 'pago') {
                    const valor = parseFloat(data.valor_servico);
                    document.getElementById('detailValor').textContent = 'R$ ' + (isNaN(valor) ? '0,00' : valor.toLocaleString('pt-BR', {minimumFractionDigits: 2}));
                    document.getElementById('detailCobrancaTipo').textContent = data.tipo_cobranca || '-';
                    
                    // Parcelas Logic
                    if(data.parcelado) {
                         document.getElementById('detailParcelado').textContent = `Sim (${data.parcelas}x)`;
                         
                         let vParcela = parseFloat(data.valor_parcela);
                         // Fallback calculation if not saved
                         if((isNaN(vParcela) || vParcela === 0) && data.parcelas > 0) {
                             vParcela = valor / data.parcelas;
                         }
                         
                         document.getElementById('detailValorParcela').textContent = 'R$ ' + (isNaN(vParcela) ? '0,00' : vParcela.toLocaleString('pt-BR', {minimumFractionDigits: 2}));
                    } else {
                         document.getElementById('detailParcelado').textContent = 'Não';
                         document.getElementById('detailValorParcela').textContent = '-';
                    }

                    document.getElementById('detailAuto').textContent = data.cobranca_automatica ? 'Sim' : 'Não';
                } else {
                    document.getElementById('detailValor').textContent = '-';
                    document.getElementById('detailCobrancaTipo').textContent = '-';
                    document.getElementById('detailParcelado').textContent = '-';
                    document.getElementById('detailValorParcela').textContent = '-';
                    document.getElementById('detailAuto').textContent = '-';
                }

                // Contato
                document.getElementById('detailEmail').textContent = data.email || '-';
                document.getElementById('detailTelefone').textContent = data.telefone || '-';
                
                // Endereço
                let endereco = [];
                if(data.logradouro) {
                    let log = data.logradouro;
                    if(data.numero) log += ', ' + data.numero;
                    endereco.push(log);
                }
                if(data.complemento) endereco.push(data.complemento);
                if(data.bairro) endereco.push(data.bairro);
                if(data.cidade) endereco.push(data.cidade + (data.uf ? '/' + data.uf : ''));
                if(data.cep) endereco.push('CEP: ' + data.cep);
                
                document.getElementById('detailEndereco').textContent = endereco.length > 0 ? endereco.join(' - ') : 'Não informado';

                document.getElementById('modalLoading').classList.add('d-none');
                document.getElementById('modalContent').classList.remove('d-none');
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('modalTitle').textContent = 'Erro ao carregar';
                document.getElementById('modalLoading').innerHTML = '<p class="text-danger">Erro ao carregar dados do cliente.</p>';
            });
    }
</script>
@endpush
