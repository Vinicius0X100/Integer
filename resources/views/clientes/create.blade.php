@extends('layouts.app')

@section('page-title', 'Novo Cliente')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white mb-1">Novo Cliente</h2>
            <p class="text-white-50 mb-0">Preencha o formulário abaixo para cadastrar um novo cliente.</p>
        </div>
        <a href="{{ route('clientes.index') }}" class="btn btn-outline-light rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i> Voltar
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-4">
            <form action="{{ route('clientes.store') }}" method="POST" id="clienteForm">
                @csrf
                
                <!-- Tipo de Pessoa -->
                <h5 class="fw-bold text-white mb-3"><i class="bi bi-person-badge me-2"></i>Tipo de Cliente</h5>
                <div class="mb-4">
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="tipo" id="tipoPF" value="PF" checked onchange="toggleTipo()">
                        <label class="btn btn-outline-primary" for="tipoPF">Pessoa Física (PF)</label>

                        <input type="radio" class="btn-check" name="tipo" id="tipoPJ" value="PJ" onchange="toggleTipo()">
                        <label class="btn btn-outline-primary" for="tipoPJ">Pessoa Jurídica (PJ)</label>
                    </div>
                </div>

                <!-- Dados Pessoais / Empresariais -->
                <div class="row g-3 mb-4">
                    <!-- PF Fields -->
                    <div class="col-md-6 pf-field">
                        <label class="form-label text-white-50">Nome Completo</label>
                        <input type="text" name="nome" id="inputNomePF" class="form-control bg-dark text-white border-secondary" placeholder="Ex: João da Silva">
                    </div>
                    <div class="col-md-3 pf-field">
                        <label class="form-label text-white-50">CPF</label>
                        <input type="text" name="cpf" class="form-control bg-dark text-white border-secondary" placeholder="000.000.000-00">
                    </div>
                    <div class="col-md-3 pf-field">
                        <label class="form-label text-white-50">RG</label>
                        <input type="text" name="rg" class="form-control bg-dark text-white border-secondary">
                    </div>

                    <!-- PJ Fields -->
                    <div class="col-md-6 pj-field d-none">
                        <label class="form-label text-white-50">Nome Fantasia</label>
                        <input type="text" name="nome" id="inputNomePJ" class="form-control bg-dark text-white border-secondary" disabled>
                    </div>
                    <div class="col-md-6 pj-field d-none">
                        <label class="form-label text-white-50">Razão Social</label>
                        <input type="text" name="razao_social" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-md-3 pj-field d-none">
                        <label class="form-label text-white-50">CNPJ</label>
                        <input type="text" name="cnpj" class="form-control bg-dark text-white border-secondary" placeholder="00.000.000/0000-00">
                    </div>
                    <div class="col-md-3 pj-field d-none">
                        <label class="form-label text-white-50">Tipo Empresa</label>
                        <select name="tipo_empresa" class="form-select bg-dark text-white border-secondary">
                            <option value="">Selecione...</option>
                            <option value="MEI">MEI</option>
                            <option value="ME">ME</option>
                            <option value="EPP">EPP</option>
                            <option value="LTDA">LTDA</option>
                            <option value="SA">SA</option>
                            <option value="Cooperativa">Cooperativa</option>
                            <option value="Associação">Associação</option>
                            <option value="Fundação">Fundação</option>
                            <option value="Sindicato">Sindicato</option>
                            <option value="Religioso">Religioso</option>
                            <option value="Instituição Pública">Instituição Pública</option>
                        </select>
                    </div>
                    <div class="col-md-6 pj-field d-none">
                        <label class="form-label text-white-50">Responsável Legal</label>
                        <input type="text" name="responsavel_legal" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-md-6 pj-field d-none">
                        <label class="form-label text-white-50">Representante</label>
                        <input type="text" name="representante" class="form-control bg-dark text-white border-secondary">
                    </div>
                </div>

                <hr class="border-secondary border-opacity-25 my-4">

                <!-- Serviço e Financeiro -->
                <h5 class="fw-bold text-white mb-3"><i class="bi bi-currency-dollar me-2"></i>Serviço e Financeiro</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label text-white-50">Tipo de Serviço</label>
                        <select name="tipo_servico" class="form-select bg-dark text-white border-secondary">
                            <option value="SaaS">SaaS</option>
                            <option value="Sob Demanda">Sob Demanda</option>
                            <option value="Manutenção">Manutenção</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white-50">Modalidade</label>
                        <select name="modalidade_valor" id="modalidade" class="form-select bg-dark text-white border-secondary" onchange="toggleFinanceiro()">
                            <option value="gratuito">Gratuito</option>
                            <option value="pago">Pago</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white-50">Tipo Cobrança</label>
                        <select name="tipo_cobranca" class="form-select bg-dark text-white border-secondary">
                            <option value="mensal">Mensal</option>
                            <option value="valor_unico">Valor Único</option>
                        </select>
                    </div>

                    <!-- Campos Financeiros (Aparecem apenas se Pago) -->
                    <div class="col-12 financeiro-field d-none">
                        <div class="card bg-secondary bg-opacity-10 border-0 p-3">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label text-white-50">Valor do Serviço (R$)</label>
                                    <input type="text" name="valor_servico" class="form-control bg-dark text-white border-secondary" placeholder="0,00" oninput="formatCurrency(this)">
                                </div>
                                <div class="col-md-8 d-flex align-items-center pt-4">
                                    <div class="form-check form-switch me-4">
                                        <input class="form-check-input" type="checkbox" name="parcelado" value="1" id="checkParcelado" onchange="toggleParcelas()">
                                        <label class="form-check-label text-white" for="checkParcelado">Parcelado?</label>
                                    </div>
                                    <div class="form-check form-switch me-4">
                                        <input class="form-check-input" type="checkbox" name="contrato_ativo" value="1" id="checkContrato">
                                        <label class="form-check-label text-white" for="checkContrato">Contrato Ativo</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="cobranca_automatica" value="1" id="checkAuto">
                                        <label class="form-check-label text-white" for="checkAuto">Cobrança Automática</label>
                                    </div>
                                </div>
                                
                                <!-- Campos de Parcelamento -->
                                <div class="col-md-3 parcelas-field d-none">
                                    <label class="form-label text-white-50">Nº Parcelas</label>
                                    <input type="number" name="parcelas" class="form-control bg-dark text-white border-secondary" min="1">
                                </div>
                                <div class="col-md-3 parcelas-field d-none">
                                    <label class="form-label text-white-50">Valor Parcela (Opcional)</label>
                                    <input type="text" name="valor_parcela" class="form-control bg-dark text-white border-secondary" placeholder="Calculado auto se vazio" oninput="formatCurrency(this)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-white-50">Descrição do Serviço</label>
                        <textarea name="descricao_servico" class="form-control bg-dark text-white border-secondary" rows="3"></textarea>
                    </div>
                </div>

                <hr class="border-secondary border-opacity-25 my-4">

                <!-- Contato e Endereço -->
                <h5 class="fw-bold text-white mb-3"><i class="bi bi-geo-alt me-2"></i>Contato e Endereço</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-white-50">Email</label>
                        <input type="email" name="email" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white-50">Telefone</label>
                        <input type="text" name="telefone" class="form-control bg-dark text-white border-secondary">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label text-white-50">CEP</label>
                        <input type="text" name="cep" class="form-control bg-dark text-white border-secondary" onblur="buscaCep(this.value)">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label text-white-50">Logradouro</label>
                        <input type="text" name="logradouro" id="logradouro" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-white-50">Número</label>
                        <input type="text" name="numero" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Complemento</label>
                        <input type="text" name="complemento" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white-50">Bairro</label>
                        <input type="text" name="bairro" id="bairro" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white-50">Cidade</label>
                        <input type="text" name="cidade" id="cidade" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white-50">UF</label>
                        <input type="text" name="uf" id="uf" class="form-control bg-dark text-white border-secondary" maxlength="2">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-5">
                    <a href="{{ route('clientes.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">Salvar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleTipo() {
        const isPF = document.getElementById('tipoPF').checked;
        const pfFields = document.querySelectorAll('.pf-field');
        const pjFields = document.querySelectorAll('.pj-field');
        const inputNomePF = document.getElementById('inputNomePF');
        const inputNomePJ = document.getElementById('inputNomePJ');

        if (isPF) {
            pfFields.forEach(el => el.classList.remove('d-none'));
            pjFields.forEach(el => el.classList.add('d-none'));
            if(inputNomePF) inputNomePF.disabled = false;
            if(inputNomePJ) inputNomePJ.disabled = true;
        } else {
            pfFields.forEach(el => el.classList.add('d-none'));
            pjFields.forEach(el => el.classList.remove('d-none'));
            if(inputNomePF) inputNomePF.disabled = true;
            if(inputNomePJ) inputNomePJ.disabled = false;
        }
    }

    function toggleFinanceiro() {
        const modalidade = document.getElementById('modalidade').value;
        const finFields = document.querySelector('.financeiro-field');
        
        if (modalidade === 'pago') {
            finFields.classList.remove('d-none');
        } else {
            finFields.classList.add('d-none');
        }
    }

    function toggleParcelas() {
        const isParcelado = document.getElementById('checkParcelado').checked;
        const parcFields = document.querySelectorAll('.parcelas-field');
        
        if (isParcelado) {
            parcFields.forEach(el => el.classList.remove('d-none'));
        } else {
            parcFields.forEach(el => el.classList.add('d-none'));
        }
    }

    function buscaCep(cep) {
        cep = cep.replace(/\D/g, '');
        if (cep.length === 8) {
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(res => res.json())
                .then(data => {
                    if (!data.erro) {
                        document.getElementById('logradouro').value = data.logradouro;
                        document.getElementById('bairro').value = data.bairro;
                        document.getElementById('cidade').value = data.localidade;
                        document.getElementById('uf').value = data.uf;
                    }
                })
                .catch(err => console.error('Erro CEP:', err));
        }
    }

    function formatCurrency(input) {
        let value = input.value.replace(/\D/g, '');
        value = (value / 100).toFixed(2) + '';
        value = value.replace('.', ',');
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        input.value = value;
    }

    // Init
    toggleTipo();
    toggleFinanceiro();
    toggleParcelas();
</script>
@endsection
