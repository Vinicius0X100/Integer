@extends('layouts.app')

@section('page-title', 'Detalhes da Verificação KYC')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('nodal-verifications.index') }}" class="text-decoration-none text-muted mb-3 d-inline-block">
            <i class="bi bi-arrow-left me-1"></i> Voltar para Verificações
        </a>
        <h2 class="fw-bold text-white mb-1">Empresa: {{ $verification['organization_name'] ?? 'Desconhecida' }}</h2>
        <p class="text-white-50 mb-0">Enviado em: {{ !empty($verification['submitted_at']) ? \Carbon\Carbon::parse($verification['submitted_at'])->format('d/m/Y às H:i') : 'Desconhecido' }}</p>
    </div>

    {{-- Alerts --}}
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

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-10 py-3">
                    @php
                        $docLabels = [
                            'SOCIAL_CONTRACT' => 'Contrato Social',
                            'COMPANY_DOCUMENT' => 'Documento da Empresa',
                            'IDENTITY_DOCUMENT' => 'Documento de Identidade',
                            'ADDRESS_PROOF' => 'Comprovante de Endereço',
                            'CNPJ_CARD' => 'Cartão CNPJ'
                        ];
                        $rawType = $verification['document_type'] ?? 'DOCUMENTO';
                        $docLabel = $docLabels[$rawType] ?? str_replace('_', ' ', $rawType);
                    @endphp
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-file-earmark-text me-2"></i> Documento Analisado ({{ $docLabel }})</h5>
                </div>
                <div class="card-body p-0 text-center" style="min-height: 500px; background-color: #f8f9fa;">
                    @if(!empty($verification['document_url']))
                        <iframe src="{{ $verification['document_url'] }}" width="100%" height="600px" style="border: none;"></iframe>
                    @else
                        <div class="d-flex flex-column justify-content-center align-items-center h-100 py-5 text-muted">
                            <i class="bi bi-file-earmark-x fs-1 mb-3"></i>
                            <h5>Documento não disponível</h5>
                            <p class="small">Não foi possível carregar a pré-visualização deste documento.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-10 py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-check2-circle me-2"></i> Ação Necessária</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-4">Revise o documento ao lado e ateste se os dados condizem com o cadastro da empresa. Após aprovar, a empresa será notificada e liberada no Nodal.</p>
                    
                    @if(($verification['status'] ?? 'under_review') === 'under_review')
                        <div class="d-grid gap-3">
                            <button type="button" class="btn btn-success rounded-pill py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAprovar">
                                <i class="bi bi-check-lg me-1"></i> Aprovar Documento
                            </button>
                            <button type="button" class="btn btn-danger rounded-pill py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRejeitar">
                                <i class="bi bi-x-lg me-1"></i> Rejeitar e Solicitar Novo
                            </button>
                        </div>
                    @else
                        <div class="alert alert-secondary border-0 rounded-3 text-center">
                            Esta solicitação já foi processada. <br>
                            Status atual: <strong>{{ $verification['status'] === 'verified' ? 'Aprovado' : ($verification['status'] === 'rejected' ? 'Rejeitado' : 'Em Análise') }}</strong>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-10 py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2"></i> Dados Cadastrais</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush rounded-4">
                        @php
                            $fieldLabels = [
                                'organization_name' => 'Nome da Organização (Sistema)',
                                'company_name' => 'Razão Social',
                                'trade_name' => 'Nome Fantasia',
                                'cnpj' => 'CNPJ',
                                'website' => 'Site / URL',
                                'linkedin' => 'LinkedIn',
                                'responsible_name' => 'Nome do Responsável',
                                'responsible_position' => 'Cargo do Responsável',
                                'corporate_email' => 'E-mail Corporativo',
                                'phone' => 'Telefone'
                            ];
                        @endphp
                        @foreach($verification as $key => $value)
                            @if(!in_array($key, ['document_url', 'id', 'uuid', 'organization_uuid', 'document_type', 'status', 'submitted_at', 'updated_at', 'reviewed_at', 'notes', 'reason']) && !empty($value))
                                @php
                                    $label = $fieldLabels[$key] ?? str_replace('_', ' ', $key);
                                @endphp
                                @if(is_array($value))
                                    <li class="list-group-item px-4 py-3 bg-transparent">
                                        <div class="small text-muted text-uppercase fw-bold mb-2">{{ $label }}</div>
                                        <div class="bg-secondary bg-opacity-10 p-3 rounded-3 small">
                                            @foreach($value as $subKey => $subVal)
                                                @if(!is_array($subVal))
                                                    @php $subLabel = $fieldLabels[$subKey] ?? str_replace('_', ' ', $subKey); @endphp
                                                    <div class="d-flex justify-content-between border-bottom border-secondary border-opacity-10 pb-2 mb-2 last-border-none">
                                                        <span class="text-muted">{{ mb_strtoupper($subLabel, 'UTF-8') }}</span>
                                                        <span class="fw-semibold">{{ $subVal }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </li>
                                @else
                                    <li class="list-group-item px-4 py-3 bg-transparent d-flex justify-content-between align-items-center">
                                        <span class="small text-muted text-uppercase fw-bold me-3">{{ $label }}</span>
                                        <span class="fw-medium text-end" style="word-break: break-word;">{{ $value }}</span>
                                    </li>
                                @endif
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Aprovar --}}
<div class="modal fade" id="modalAprovar" tabindex="-1" aria-labelledby="modalAprovarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 text-start">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-success" id="modalAprovarLabel">Confirmar Aprovação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('nodal-verifications.approve', $verification['uuid']) }}" onsubmit="let btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.innerHTML = '<span class=\'spinner-border spinner-border-sm me-2\' role=\'status\' aria-hidden=\'true\'></span>Aprovando...';">
                @csrf
                <div class="modal-body py-4">
                    <p class="mb-3">Você está prestes a <strong>Aprovar</strong> a empresa {{ $verification['organization_name'] ?? '' }}.</p>
                    <div class="mb-3">
                        <label for="notes" class="form-label text-muted small">Notas Internas (Opcional)</label>
                        <textarea name="notes" id="notes" rows="3" class="form-control bg-transparent border-secondary border-opacity-25 text-white" placeholder="Ex: Tudo correto..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">Confirmar Aprovação</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Rejeitar --}}
<div class="modal fade" id="modalRejeitar" tabindex="-1" aria-labelledby="modalRejeitarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 text-start">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" id="modalRejeitarLabel">Rejeitar Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('nodal-verifications.reject', $verification['uuid']) }}" onsubmit="let btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.innerHTML = '<span class=\'spinner-border spinner-border-sm me-2\' role=\'status\' aria-hidden=\'true\'></span>Rejeitando...';">
                @csrf
                <div class="modal-body py-4">
                    <p class="mb-3">Por favor, informe o motivo da rejeição. <strong>A empresa receberá um e-mail com este motivo.</strong></p>
                    <div class="mb-3">
                        <label for="reason" class="form-label fw-semibold text-danger">Motivo da Rejeição *</label>
                        <textarea name="reason" id="reason" rows="4" class="form-control bg-transparent border-danger border-opacity-50 text-white" required placeholder="Ex: O CNPJ do documento não condiz com o cadastrado."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Rejeitar Documento</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
