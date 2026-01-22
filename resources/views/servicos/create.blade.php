@extends('layouts.app')

@section('page-title', 'Novo Serviço')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white mb-1">Novo Serviço</h2>
            <p class="text-white-50 mb-0">Cadastrar um novo serviço prestado.</p>
        </div>
        <a href="{{ route('servicos.index') }}" class="btn btn-outline-light rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i> Voltar
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-dark text-white">
        <div class="card-body p-4">
            <form action="{{ route('servicos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <h6 class="text-uppercase text-secondary fw-bold small mb-3">Informações Básicas</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-white-50">Cliente</label>
                        <select name="cliente_id" class="form-select bg-dark text-white border-secondary" required>
                            <option value="">Selecione um cliente...</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}">
                                    {{ $cliente->nome ?? $cliente->razao_social }} ({{ $cliente->tipo === 'PF' ? 'CPF: ' . $cliente->cpf_formatado : 'CNPJ: ' . $cliente->cnpj_formatado }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white-50">Título do Serviço</label>
                        <input type="text" name="titulo" class="form-control bg-dark text-white border-secondary" required placeholder="Ex: Desenvolvimento de Site">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white-50">Tipo de Serviço</label>
                        <select name="tipo_servico" class="form-select bg-dark text-white border-secondary">
                            <option value="">Selecione o tipo...</option>
                            <option value="Desenvolvimento de Software">Desenvolvimento de Software</option>
                            <option value="Manutenção de Sistema">Manutenção de Sistema</option>
                            <option value="Gerencia de Sistema">Gerencia de Sistema</option>
                            <option value="Suporte Técnico">Suporte Técnico</option>
                            <option value="SaaS">SaaS (Software as a Service)</option>
                            <option value="Infraestrutura/DevOps">Infraestrutura/DevOps</option>
                            <option value="Design UI/UX">Design UI/UX</option>
                            <option value="Integração de API">Integração de API</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-white-50">Descrição Detalhada</label>
                        <textarea name="descricao" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Detalhes do serviço prestado..."></textarea>
                    </div>
                </div>

                <hr class="border-secondary border-opacity-10 my-4">

                <h6 class="text-uppercase text-secondary fw-bold small mb-3">Financeiro</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Valor Total (R$)</label>
                        <input type="text" name="valor_total" id="valor_total" class="form-control bg-dark text-white border-secondary" required placeholder="0,00" oninput="formatCurrency(this); calcLucro(); calcParcelas();">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Custo Interno (R$) <small class="text-muted">(Opcional)</small></label>
                        <input type="text" name="custo_interno" id="custo_interno" class="form-control bg-dark text-white border-secondary" placeholder="0,00" oninput="formatCurrency(this); calcLucro()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Lucro Estimado</label>
                        <input type="text" id="lucro_estimado" class="form-control bg-secondary text-white border-0" readonly placeholder="Calculado auto">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="parcelado" name="parcelado" onchange="toggleParcelas()">
                            <label class="form-check-label text-white" for="parcelado">Pagamento Parcelado?</label>
                        </div>

                        <div class="row g-3 d-none" id="parcelas-container">
                            <div class="col-md-6">
                                <label class="form-label text-white-50">Qtd. Parcelas</label>
                                <input type="number" name="qtd_parcelas" id="qtd_parcelas" class="form-control bg-dark text-white border-secondary" min="2" max="120" oninput="calcParcelas()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50">Valor da Parcela (R$)</label>
                                <input type="text" name="valor_parcela" id="valor_parcela" class="form-control bg-dark text-white border-secondary" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="recorrente" name="recorrente" onchange="toggleRecorrencia()">
                            <label class="form-check-label text-white" for="recorrente">Serviço Recorrente (SaaS)?</label>
                        </div>

                        <div class="row g-3 d-none" id="recorrencia-container">
                            <div class="col-md-6">
                                <label class="form-label text-white-50">Valor Recorrência (R$)</label>
                                <input type="text" name="valor_recorrencia" id="valor_recorrencia" class="form-control bg-dark text-white border-secondary" placeholder="0,00" oninput="formatCurrency(this)">
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
                        <input type="date" name="data_servico" class="form-control bg-dark text-white border-secondary" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Prazo de Entrega <small class="text-muted">(Opcional)</small></label>
                        <input type="date" name="prazo_entrega" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Status Atual</label>
                        <select name="status" class="form-select bg-dark text-white border-secondary">
                            <option value="pendente">Pendente</option>
                            <option value="em_andamento">Em Andamento</option>
                            <option value="concluido">Concluído</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Anexar Contrato</label>
                        <input type="file" name="contrato" class="form-control bg-dark text-white border-secondary" accept=".pdf,.doc,.docx,.jpg,.png">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-5">
                    <a href="{{ route('servicos.index') }}" class="btn btn-dark border-secondary px-4 rounded-pill">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold">Salvar Serviço</button>
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
