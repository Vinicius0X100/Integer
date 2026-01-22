@extends('layouts.app')

@section('page-title', 'Editar Serviço')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white mb-1">Editar Serviço</h2>
            <p class="text-white-50 mb-0">Atualizar informações do serviço #{{ $servico->id }}</p>
        </div>
        <a href="{{ route('servicos.index') }}" class="btn btn-outline-light rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i> Voltar
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-dark text-white">
        <div class="card-body p-4">
            <form action="{{ route('servicos.update', $servico->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <h6 class="text-uppercase text-secondary fw-bold small mb-3">Informações Básicas</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-white-50">Cliente</label>
                        <select name="cliente_id" class="form-select bg-dark text-white border-secondary" required>
                            <option value="">Selecione um cliente...</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ $servico->cliente_id == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nome ?? $cliente->razao_social }} ({{ $cliente->tipo === 'PF' ? 'CPF: ' . $cliente->cpf_formatado : 'CNPJ: ' . $cliente->cnpj_formatado }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white-50">Título do Serviço</label>
                        <input type="text" name="titulo" class="form-control bg-dark text-white border-secondary" required placeholder="Ex: Desenvolvimento de Site" value="{{ $servico->titulo }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white-50">Tipo de Serviço</label>
                        <select name="tipo_servico" class="form-select bg-dark text-white border-secondary">
                            <option value="">Selecione o tipo...</option>
                            <option value="Desenvolvimento de Software" {{ $servico->tipo_servico == 'Desenvolvimento de Software' ? 'selected' : '' }}>Desenvolvimento de Software</option>
                            <option value="Manutenção de Sistema" {{ $servico->tipo_servico == 'Manutenção de Sistema' ? 'selected' : '' }}>Manutenção de Sistema</option>
                            <option value="Consultoria em TI" {{ $servico->tipo_servico == 'Consultoria em TI' ? 'selected' : '' }}>Consultoria em TI</option>
                            <option value="SaaS" {{ $servico->tipo_servico == 'SaaS' ? 'selected' : '' }}>SaaS (Software as a Service)</option>
                            <option value="Infraestrutura/DevOps" {{ $servico->tipo_servico == 'Infraestrutura/DevOps' ? 'selected' : '' }}>Infraestrutura/DevOps</option>
                            <option value="Design UI/UX" {{ $servico->tipo_servico == 'Design UI/UX' ? 'selected' : '' }}>Design UI/UX</option>
                            <option value="Integração de API" {{ $servico->tipo_servico == 'Integração de API' ? 'selected' : '' }}>Integração de API</option>
                            <option value="Outro" {{ $servico->tipo_servico == 'Outro' ? 'selected' : '' }}>Outro</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-white-50">Descrição Detalhada</label>
                        <textarea name="descricao" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Detalhes do serviço prestado...">{{ $servico->descricao }}</textarea>
                    </div>
                </div>

                <hr class="border-secondary border-opacity-10 my-4">

                <h6 class="text-uppercase text-secondary fw-bold small mb-3">Financeiro</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Valor Total (R$)</label>
                        <input type="text" name="valor_total" id="valor_total" class="form-control bg-dark text-white border-secondary" required placeholder="0,00" value="{{ number_format($servico->valor_total, 2, ',', '.') }}" oninput="formatCurrency(this); calcLucro(); calcParcelas();">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Custo Interno (R$) <small class="text-muted">(Opcional)</small></label>
                        <input type="text" name="custo_interno" id="custo_interno" class="form-control bg-dark text-white border-secondary" placeholder="0,00" value="{{ $servico->custo_interno ? number_format($servico->custo_interno, 2, ',', '.') : '' }}" oninput="formatCurrency(this); calcLucro()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Lucro Estimado</label>
                        <input type="text" id="lucro_estimado" class="form-control bg-secondary text-white border-0" readonly placeholder="Calculado auto" value="{{ $servico->lucro_estimado ? 'R$ ' . number_format($servico->lucro_estimado, 2, ',', '.') : '' }}">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="parcelado" name="parcelado" onchange="toggleParcelas()" {{ $servico->parcelado ? 'checked' : '' }}>
                            <label class="form-check-label text-white" for="parcelado">Pagamento Parcelado?</label>
                        </div>

                        <div class="row g-3 {{ $servico->parcelado ? '' : 'd-none' }}" id="parcelas-container">
                            <div class="col-md-6">
                                <label class="form-label text-white-50">Qtd. Parcelas</label>
                                <input type="number" name="qtd_parcelas" id="qtd_parcelas" class="form-control bg-dark text-white border-secondary" min="2" max="120" value="{{ $servico->qtd_parcelas }}" oninput="calcParcelas()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50">Valor da Parcela (R$)</label>
                                <input type="text" name="valor_parcela" id="valor_parcela" class="form-control bg-dark text-white border-secondary" readonly value="{{ $servico->valor_parcela ? number_format($servico->valor_parcela, 2, ',', '.') : '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="recorrente" name="recorrente" onchange="toggleRecorrencia()" {{ $servico->recorrente ? 'checked' : '' }}>
                            <label class="form-check-label text-white" for="recorrente">Serviço Recorrente (SaaS)?</label>
                        </div>

                        <div class="row g-3 {{ $servico->recorrente ? '' : 'd-none' }}" id="recorrencia-container">
                            <div class="col-md-6">
                                <label class="form-label text-white-50">Valor Recorrência (R$)</label>
                                <input type="text" name="valor_recorrencia" id="valor_recorrencia" class="form-control bg-dark text-white border-secondary" placeholder="0,00" value="{{ $servico->valor_recorrencia ? number_format($servico->valor_recorrencia, 2, ',', '.') : '' }}" oninput="formatCurrency(this)">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <span class="text-white-50 small mb-2">* Cobrança Mensal</span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-secondary border-opacity-10 my-4">

                <h6 class="text-uppercase text-secondary fw-bold small mb-3">Prazos e Arquivos</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Data do Serviço</label>
                        <input type="date" name="data_servico" class="form-control bg-dark text-white border-secondary" required value="{{ $servico->data_servico ? $servico->data_servico->format('Y-m-d') : '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Prazo de Entrega <small class="text-muted">(Opcional)</small></label>
                        <input type="date" name="prazo_entrega" class="form-control bg-dark text-white border-secondary" value="{{ $servico->prazo_entrega ? $servico->prazo_entrega->format('Y-m-d') : '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Status Atual</label>
                        <select name="status" class="form-select bg-dark text-white border-secondary">
                            <option value="pendente" {{ $servico->status == 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="em_andamento" {{ $servico->status == 'em_andamento' ? 'selected' : '' }}>Em Andamento</option>
                            <option value="concluido" {{ $servico->status == 'concluido' ? 'selected' : '' }}>Concluído</option>
                            <option value="cancelado" {{ $servico->status == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Anexar Novo Contrato</label>
                        <input type="file" name="contrato" class="form-control bg-dark text-white border-secondary" accept=".pdf,.doc,.docx,.jpg,.png">
                        @if($servico->contrato_path)
                            <div class="mt-2">
                                <a href="{{ Storage::url($servico->contrato_path) }}" target="_blank" class="text-primary small text-decoration-none">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> Ver contrato atual
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-5">
                    <a href="{{ route('servicos.index') }}" class="btn btn-dark border-secondary px-4 rounded-pill">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function formatCurrency(input) {
        let value = input.value.replace(/\D/g, '');
        value = (value / 100).toFixed(2) + '';
        value = value.replace('.', ',');
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        input.value = value;
    }

    function parseMoney(value) {
        if (!value) return 0;
        return parseFloat(value.replace(/\./g, '').replace(',', '.'));
    }

    function formatMoney(value) {
        return value.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function calcLucro() {
        const total = parseMoney(document.getElementById('valor_total').value);
        const custo = parseMoney(document.getElementById('custo_interno').value);
        const lucro = total - custo;
        document.getElementById('lucro_estimado').value = 'R$ ' + formatMoney(lucro);
    }

    function toggleParcelas() {
        const isParcelado = document.getElementById('parcelado').checked;
        const container = document.getElementById('parcelas-container');
        if (isParcelado) {
            container.classList.remove('d-none');
        } else {
            container.classList.add('d-none');
            document.getElementById('qtd_parcelas').value = '';
            document.getElementById('valor_parcela').value = '';
        }
    }

    function calcParcelas() {
        if (!document.getElementById('parcelado').checked) return;
        
        const total = parseMoney(document.getElementById('valor_total').value);
        const qtd = parseInt(document.getElementById('qtd_parcelas').value) || 1;
        
        if (qtd > 0) {
            const parcela = total / qtd;
            document.getElementById('valor_parcela').value = formatMoney(parcela);
        }
    }

    function toggleRecorrencia() {
        const isRecorrente = document.getElementById('recorrente').checked;
        const container = document.getElementById('recorrencia-container');
        if (isRecorrente) {
            container.classList.remove('d-none');
        } else {
            container.classList.add('d-none');
            document.getElementById('valor_recorrencia').value = '';
        }
    }
</script>
@endsection
