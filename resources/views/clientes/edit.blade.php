@extends('layouts.app')

@section('page-title', 'Editar Cliente')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white mb-1">Editar Cliente</h2>
            <p class="text-white-50 mb-0">Atualize as informações do cliente abaixo.</p>
        </div>
        <a href="{{ route('clientes.index') }}" class="btn btn-outline-light rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i> Voltar
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-4">
            <form action="{{ route('clientes.update', $cliente->id) }}" method="POST" id="clienteForm">
                @csrf
                @method('PUT')
                
                <!-- Tipo de Pessoa -->
                <h5 class="fw-bold text-white mb-3"><i class="bi bi-person-badge me-2"></i>Tipo de Cliente</h5>
                <div class="mb-4">
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="tipo" id="tipoPF" value="PF" {{ $cliente->tipo == 'PF' ? 'checked' : '' }} onchange="toggleTipo()">
                        <label class="btn btn-outline-primary" for="tipoPF">Pessoa Física (PF)</label>

                        <input type="radio" class="btn-check" name="tipo" id="tipoPJ" value="PJ" {{ $cliente->tipo == 'PJ' ? 'checked' : '' }} onchange="toggleTipo()">
                        <label class="btn btn-outline-primary" for="tipoPJ">Pessoa Jurídica (PJ)</label>
                    </div>
                </div>

                <!-- Dados Pessoais / Empresariais -->
                <div class="row g-3 mb-4">
                    <!-- PF Fields -->
                    <div class="col-md-6 pf-field">
                        <label class="form-label text-white-50">Nome Completo</label>
                        <input type="text" name="nome" id="inputNomePF" class="form-control bg-dark text-white border-secondary" placeholder="Ex: João da Silva" value="{{ old('nome', $cliente->tipo == 'PF' ? $cliente->nome : '') }}">
                    </div>
                    <div class="col-md-3 pf-field">
                        <label class="form-label text-white-50">CPF</label>
                        <input type="text" name="cpf" class="form-control bg-dark text-white border-secondary" placeholder="000.000.000-00" value="{{ old('cpf', $cliente->cpf) }}">
                    </div>
                    <div class="col-md-3 pf-field">
                        <label class="form-label text-white-50">RG</label>
                        <input type="text" name="rg" class="form-control bg-dark text-white border-secondary" value="{{ old('rg', $cliente->rg) }}">
                    </div>

                    <!-- PJ Fields -->
                    <div class="col-md-6 pj-field d-none">
                        <label class="form-label text-white-50">Nome Fantasia</label>
                        <input type="text" name="nome" id="inputNomePJ" class="form-control bg-dark text-white border-secondary" disabled value="{{ old('nome', $cliente->tipo == 'PJ' ? $cliente->nome : '') }}">
                    </div>
                    <div class="col-md-6 pj-field d-none">
                        <label class="form-label text-white-50">Razão Social</label>
                        <input type="text" name="razao_social" class="form-control bg-dark text-white border-secondary" value="{{ old('razao_social', $cliente->razao_social) }}">
                    </div>
                    <div class="col-md-3 pj-field d-none">
                        <label class="form-label text-white-50">CNPJ</label>
                        <input type="text" name="cnpj" class="form-control bg-dark text-white border-secondary" placeholder="00.000.000/0000-00" value="{{ old('cnpj', $cliente->cnpj) }}">
                    </div>
                    <div class="col-md-3 pj-field d-none">
                        <label class="form-label text-white-50">Tipo Empresa</label>
                        <select name="tipo_empresa" class="form-select bg-dark text-white border-secondary">
                            <option value="">Selecione...</option>
                            <option value="MEI" {{ $cliente->tipo_empresa == 'MEI' ? 'selected' : '' }}>MEI</option>
                            <option value="ME" {{ $cliente->tipo_empresa == 'ME' ? 'selected' : '' }}>ME</option>
                            <option value="EPP" {{ $cliente->tipo_empresa == 'EPP' ? 'selected' : '' }}>EPP</option>
                            <option value="LTDA" {{ $cliente->tipo_empresa == 'LTDA' ? 'selected' : '' }}>LTDA</option>
                            <option value="SA" {{ $cliente->tipo_empresa == 'SA' ? 'selected' : '' }}>SA</option>
                            <option value="Cooperativa" {{ $cliente->tipo_empresa == 'Cooperativa' ? 'selected' : '' }}>Cooperativa</option>
                            <option value="Associação" {{ $cliente->tipo_empresa == 'Associação' ? 'selected' : '' }}>Associação</option>
                            <option value="Fundação" {{ $cliente->tipo_empresa == 'Fundação' ? 'selected' : '' }}>Fundação</option>
                            <option value="Sindicato" {{ $cliente->tipo_empresa == 'Sindicato' ? 'selected' : '' }}>Sindicato</option>
                            <option value="Religioso" {{ $cliente->tipo_empresa == 'Religioso' ? 'selected' : '' }}>Religioso</option>
                            <option value="Instituição Pública" {{ $cliente->tipo_empresa == 'Instituição Pública' ? 'selected' : '' }}>Instituição Pública</option>
                        </select>
                    </div>
                    <div class="col-md-6 pj-field d-none">
                        <label class="form-label text-white-50">Responsável Legal</label>
                        <input type="text" name="responsavel_legal" class="form-control bg-dark text-white border-secondary" value="{{ old('responsavel_legal', $cliente->responsavel_legal) }}">
                    </div>
                    <div class="col-md-6 pj-field d-none">
                        <label class="form-label text-white-50">Representante</label>
                        <input type="text" name="representante" class="form-control bg-dark text-white border-secondary" value="{{ old('representante', $cliente->representante) }}">
                    </div>
                </div>

                <hr class="border-secondary border-opacity-25 my-4">

                <!-- Serviço e Financeiro -->
                <h5 class="fw-bold text-white mb-3"><i class="bi bi-currency-dollar me-2"></i>Serviço e Financeiro</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label text-white-50">Tipo de Serviço</label>
                        <select name="tipo_servico" class="form-select bg-dark text-white border-secondary">
                            <option value="SaaS" {{ $cliente->tipo_servico == 'SaaS' ? 'selected' : '' }}>SaaS</option>
                            <option value="Sob Demanda" {{ $cliente->tipo_servico == 'Sob Demanda' ? 'selected' : '' }}>Sob Demanda</option>
                            <option value="Manutenção" {{ $cliente->tipo_servico == 'Manutenção' ? 'selected' : '' }}>Manutenção</option>
                            <option value="Outros" {{ $cliente->tipo_servico == 'Outros' ? 'selected' : '' }}>Outros</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white-50">Modalidade</label>
                        <select name="modalidade_valor" id="modalidade" class="form-select bg-dark text-white border-secondary" onchange="toggleFinanceiro()">
                            <option value="gratuito" {{ $cliente->modalidade_valor == 'gratuito' ? 'selected' : '' }}>Gratuito</option>
                            <option value="pago" {{ $cliente->modalidade_valor == 'pago' ? 'selected' : '' }}>Pago</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white-50">Tipo Cobrança</label>
                        <select name="tipo_cobranca" class="form-select bg-dark text-white border-secondary">
                            <option value="mensal" {{ $cliente->tipo_cobranca == 'mensal' ? 'selected' : '' }}>Mensal</option>
                            <option value="valor_unico" {{ $cliente->tipo_cobranca == 'valor_unico' ? 'selected' : '' }}>Valor Único</option>
                        </select>
                    </div>

                    <!-- Campos Financeiros (Aparecem apenas se Pago) -->
                    <div class="col-12 financeiro-field d-none">
                        <div class="card bg-secondary bg-opacity-10 border-0 p-3">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label text-white-50">Valor do Serviço (R$)</label>
                                    <input type="text" name="valor_servico" class="form-control bg-dark text-white border-secondary" placeholder="0,00" value="{{ old('valor_servico', $cliente->valor_servico ? number_format($cliente->valor_servico, 2, ',', '.') : '') }}" oninput="formatCurrency(this)">
                                </div>
                                <div class="col-md-8 d-flex align-items-center pt-4">
                                    <div class="form-check form-switch me-4">
                                        <input class="form-check-input" type="checkbox" name="parcelado" value="1" id="checkParcelado" {{ $cliente->parcelado ? 'checked' : '' }} onchange="toggleParcelas()">
                                        <label class="form-check-label text-white" for="checkParcelado">Parcelado?</label>
                                    </div>
                                    <div class="form-check form-switch me-4">
                                        <input class="form-check-input" type="checkbox" name="contrato_ativo" value="1" id="checkContrato" {{ $cliente->contrato_ativo ? 'checked' : '' }}>
                                        <label class="form-check-label text-white" for="checkContrato">Contrato Ativo</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="cobranca_automatica" value="1" id="checkAuto" {{ $cliente->cobranca_automatica ? 'checked' : '' }}>
                                        <label class="form-check-label text-white" for="checkAuto">Cobrança Automática</label>
                                    </div>
                                </div>
                                
                                <!-- Campos de Parcelamento -->
                                <div class="col-md-3 parcelas-field d-none">
                                    <label class="form-label text-white-50">Nº Parcelas</label>
                                    <input type="number" name="parcelas" class="form-control bg-dark text-white border-secondary" min="1" value="{{ old('parcelas', $cliente->parcelas) }}">
                                </div>
                                <div class="col-md-3 parcelas-field d-none">
                                    <label class="form-label text-white-50">Valor Parcela (Opcional)</label>
                                    <input type="text" name="valor_parcela" class="form-control bg-dark text-white border-secondary" placeholder="Calculado auto se vazio" value="{{ old('valor_parcela', $cliente->valor_parcela ? number_format($cliente->valor_parcela, 2, ',', '.') : '') }}" oninput="formatCurrency(this)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-white-50">Descrição do Serviço</label>
                        <textarea name="descricao_servico" class="form-control bg-dark text-white border-secondary" rows="3">{{ old('descricao_servico', $cliente->descricao_servico) }}</textarea>
                    </div>
                </div>

                <hr class="border-secondary border-opacity-25 my-4">

                <!-- Contato e Endereço -->
                <h5 class="fw-bold text-white mb-3"><i class="bi bi-geo-alt me-2"></i>Contato e Endereço</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-white-50">Email</label>
                        <input type="email" name="email" class="form-control bg-dark text-white border-secondary" value="{{ old('email', $cliente->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white-50">Telefone</label>
                        <input type="text" name="telefone" class="form-control bg-dark text-white border-secondary" value="{{ old('telefone', $cliente->telefone) }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label text-white-50">CEP</label>
                        <input type="text" name="cep" class="form-control bg-dark text-white border-secondary" onblur="buscaCep(this.value)" value="{{ old('cep', $cliente->cep) }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label text-white-50">Logradouro</label>
                        <input type="text" name="logradouro" id="logradouro" class="form-control bg-dark text-white border-secondary" value="{{ old('logradouro', $cliente->logradouro) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-white-50">Número</label>
                        <input type="text" name="numero" class="form-control bg-dark text-white border-secondary" value="{{ old('numero', $cliente->numero) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Complemento</label>
                        <input type="text" name="complemento" class="form-control bg-dark text-white border-secondary" value="{{ old('complemento', $cliente->complemento) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white-50">Bairro</label>
                        <input type="text" name="bairro" id="bairro" class="form-control bg-dark text-white border-secondary" value="{{ old('bairro', $cliente->bairro) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white-50">Cidade</label>
                        <input type="text" name="cidade" id="cidade" class="form-control bg-dark text-white border-secondary" value="{{ old('cidade', $cliente->cidade) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white-50">UF</label>
                        <input type="text" name="uf" id="uf" class="form-control bg-dark text-white border-secondary" maxlength="2" value="{{ old('uf', $cliente->uf) }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-5">
                    <a href="{{ route('clientes.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">Salvar Alterações</button>
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
    document.addEventListener('DOMContentLoaded', function() {
        toggleTipo();
        toggleFinanceiro();
        toggleParcelas();
    });
</script>
@endsection
