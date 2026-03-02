@extends('layouts.app')

@section('page-title', 'Editar Paróquia - SisMatriz')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="mb-0 fw-bold">Editar Paróquia</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('paroquias.update', $paroquia->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="foto" class="form-label">Foto da Paróquia</label>
                                <div class="position-relative">
                                    <div class="drop-zone p-5 rounded-4 text-center position-relative transition-all" id="dropZone" style="border: 2px {{ $paroquia->foto ? 'solid' : 'dashed' }} var(--apple-border); background-color: rgba(0,0,0,0.02); transition: all 0.3s ease;">
                                        <input type="file" class="form-control position-absolute top-0 start-0 w-100 h-100 opacity-0 z-2 cursor-pointer" id="foto" name="foto" accept="image/*" onchange="previewImage(this)">
                                        
                                        <div class="d-flex flex-column align-items-center justify-content-center {{ $paroquia->foto ? 'opacity-0' : '' }}" id="dropZoneContent">
                                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary mb-3">
                                                <i class="bi bi-cloud-arrow-up-fill fs-3"></i>
                                            </div>
                                            <h6 class="fw-bold mb-1">Arraste e solte a imagem aqui</h6>
                                            <p class="small text-muted mb-0">ou clique para selecionar do computador</p>
                                        </div>

                                        <div id="imagePreviewContainer" class="{{ $paroquia->foto ? '' : 'd-none' }} position-absolute top-0 start-0 w-100 h-100 bg-white rounded-4 d-flex align-items-center justify-content-center z-1 overflow-hidden" style="background-color: var(--apple-card-bg) !important;">
                                            @php
                                                $imageUrl = $paroquia->foto ? (
                                                    file_exists(public_path('uploads/paroquias/' . $paroquia->foto)) 
                                                        ? asset('uploads/paroquias/' . $paroquia->foto) 
                                                        : 'https://central.sismatriz.online/storage/uploads/paroquias/' . $paroquia->foto
                                                ) : '';
                                            @endphp
                                            <img src="{{ $imageUrl }}" alt="Preview" id="imagePreview" class="h-100 w-100" style="object-fit: contain;">
                                            <button type="button" class="btn btn-sm btn-dark rounded-circle position-absolute top-0 end-0 m-3 z-3 shadow-sm" onclick="clearImage(event)" title="Remover imagem">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-text mt-2">Deixe em branco para manter a foto atual.</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="name" class="form-label">Nome da Paróquia <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $paroquia->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="paroco" class="form-label">Pároco</label>
                                <input type="text" class="form-control @error('paroco') is-invalid @enderror" id="paroco" name="paroco" value="{{ old('paroco', $paroquia->paroco) }}">
                                @error('paroco')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="slug" class="form-label">Slug (URL Amigável)</label>
                                <input type="text" class="form-control bg-light" id="slug" value="{{ $paroquia->slug ?? '' }}" readonly>
                                <div class="form-text">Este campo é gerado automaticamente e não pode ser alterado.</div>
                            </div>

                            <!-- Novos campos do Pároco -->
                            <div class="col-12">
                                <label for="paroco_foto" class="form-label">Foto do Pároco</label>
                                <div class="position-relative">
                                    <div class="drop-zone p-5 rounded-4 text-center position-relative transition-all" id="dropZoneParoco" style="border: 2px {{ $paroquia->paroco_foto ? 'solid' : 'dashed' }} var(--apple-border); background-color: rgba(0,0,0,0.02); transition: all 0.3s ease;">
                                        <input type="file" class="form-control position-absolute top-0 start-0 w-100 h-100 opacity-0 z-2 cursor-pointer" id="paroco_foto" name="paroco_foto" accept="image/*" onchange="previewImageParoco(this)">
                                        
                                        <div class="d-flex flex-column align-items-center justify-content-center {{ $paroquia->paroco_foto ? 'opacity-0' : '' }}" id="dropZoneContentParoco">
                                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary mb-3">
                                                <i class="bi bi-person-bounding-box fs-3"></i>
                                            </div>
                                            <h6 class="fw-bold mb-1">Arraste e solte a foto do pároco aqui</h6>
                                            <p class="small text-muted mb-0">ou clique para selecionar do computador</p>
                                        </div>

                                        <div id="imagePreviewContainerParoco" class="{{ $paroquia->paroco_foto ? '' : 'd-none' }} position-absolute top-0 start-0 w-100 h-100 bg-white rounded-4 d-flex align-items-center justify-content-center z-1 overflow-hidden" style="background-color: var(--apple-card-bg) !important;">
                                            @php
                                                $parocoImageUrl = $paroquia->paroco_foto ? (
                                                    file_exists(public_path('uploads/paroquias/' . $paroquia->paroco_foto)) 
                                                        ? asset('uploads/paroquias/' . $paroquia->paroco_foto) 
                                                        : 'https://central.sismatriz.online/storage/uploads/paroquias/' . $paroquia->paroco_foto
                                                ) : '';
                                            @endphp
                                            <img src="{{ $parocoImageUrl }}" alt="Preview" id="imagePreviewParoco" class="h-100 w-100" style="object-fit: contain;">
                                            <button type="button" class="btn btn-sm btn-dark rounded-circle position-absolute top-0 end-0 m-3 z-3 shadow-sm" onclick="clearImageParoco(event)" title="Remover imagem">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-text mt-2">Deixe em branco para manter a foto atual.</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="paroco_email" class="form-label">Email do Pároco</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                    <input type="email" class="form-control border-start-0 @error('paroco_email') is-invalid @enderror" id="paroco_email" name="paroco_email" value="{{ old('paroco_email', $paroquia->paroco_email) }}" placeholder="email@exemplo.com">
                                </div>
                                @error('paroco_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="paroco_ordenacao" class="form-label">Data de Ordenação</label>
                                <input type="date" class="form-control @error('paroco_ordenacao') is-invalid @enderror" id="paroco_ordenacao" name="paroco_ordenacao" value="{{ old('paroco_ordenacao', optional($paroquia->paroco_ordenacao)->format('Y-m-d')) }}">
                                @error('paroco_ordenacao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="paroco_aniversario" class="form-label">Data de Aniversário</label>
                                <input type="date" class="form-control @error('paroco_aniversario') is-invalid @enderror" id="paroco_aniversario" name="paroco_aniversario" value="{{ old('paroco_aniversario', optional($paroquia->paroco_aniversario)->format('Y-m-d')) }}">
                                @error('paroco_aniversario')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="paroco_mensagem" class="form-label">Mensagem do Pároco</label>
                                <textarea class="form-control @error('paroco_mensagem') is-invalid @enderror" id="paroco_mensagem" name="paroco_mensagem" rows="3" placeholder="Digite uma mensagem...">{{ old('paroco_mensagem', $paroquia->paroco_mensagem) }}</textarea>
                                @error('paroco_mensagem')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email da Paróquia</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $paroquia->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">Telefone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $paroquia->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label">Endereço</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address', $paroquia->address) }}">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="diocese" class="form-label">Diocese</label>
                                <input type="text" class="form-control @error('diocese') is-invalid @enderror" id="diocese" name="diocese" value="{{ old('diocese', $paroquia->diocese) }}">
                                @error('diocese')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="region" class="form-label">Região</label>
                                <input type="text" inputmode="numeric" class="form-control @error('region') is-invalid @enderror" id="region" name="region" value="{{ old('region', $paroquia->region) }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                @error('region')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="0" {{ old('status', $paroquia->status) == '0' ? 'selected' : '' }}>Ativa</option>
                                    <option value="1" {{ old('status', $paroquia->status) == '1' ? 'selected' : '' }}>Inativa</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Redes Sociais -->
                            <div class="col-12 mt-4">
                                <h6 class="fw-bold mb-3"><i class="bi bi-share me-2"></i>Redes Sociais</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="facebook" class="form-label">Facebook</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-facebook text-primary"></i></span>
                                            <input type="url" class="form-control border-start-0 @error('facebook') is-invalid @enderror" id="facebook" name="facebook" value="{{ old('facebook', $paroquia->facebook) }}" placeholder="https://facebook.com/...">
                                        </div>
                                        @error('facebook')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="instagram" class="form-label">Instagram</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-instagram text-danger"></i></span>
                                            <input type="url" class="form-control border-start-0 @error('instagram') is-invalid @enderror" id="instagram" name="instagram" value="{{ old('instagram', $paroquia->instagram) }}" placeholder="https://instagram.com/...">
                                        </div>
                                        @error('instagram')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="twitter" class="form-label">X (Twitter)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-twitter-x"></i></span>
                                            <input type="url" class="form-control border-start-0 @error('twitter') is-invalid @enderror" id="twitter" name="twitter" value="{{ old('twitter', $paroquia->twitter) }}" placeholder="https://x.com/...">
                                        </div>
                                        @error('twitter')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="youtube" class="form-label">YouTube</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-youtube text-danger"></i></span>
                                            <input type="url" class="form-control border-start-0 @error('youtube') is-invalid @enderror" id="youtube" name="youtube" value="{{ old('youtube', $paroquia->youtube) }}" placeholder="https://youtube.com/...">
                                        </div>
                                        @error('youtube')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('paroquias.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Atualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // General Preview Function
    function previewImageGeneric(input, dropZoneId, previewContainerId, previewId, contentId) {
        const dropZone = document.getElementById(dropZoneId);
        const previewContainer = document.getElementById(previewContainerId);
        const preview = document.getElementById(previewId);
        const content = document.getElementById(contentId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                previewContainer.classList.remove('d-none');
                content.classList.add('opacity-0');
                dropZone.style.borderStyle = 'dashed';
                dropZone.style.borderColor = '#28a745';
                dropZone.style.backgroundColor = 'rgba(40, 167, 69, 0.05)';
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    // General Clear Function
    function clearImageGeneric(event, inputId, dropZoneId, previewContainerId, previewId, contentId) {
        event.preventDefault();
        event.stopPropagation();
        
        const input = document.getElementById(inputId);
        const dropZone = document.getElementById(dropZoneId);
        const previewContainer = document.getElementById(previewContainerId);
        const content = document.getElementById(contentId);
        const preview = document.getElementById(previewId);

        input.value = '';
        preview.src = '';
        previewContainer.classList.add('d-none');
        content.classList.remove('opacity-0');
        dropZone.style.borderStyle = 'dashed';
        dropZone.style.borderColor = 'var(--apple-border)';
        dropZone.style.backgroundColor = 'rgba(0,0,0,0.02)';
    }

    // Specific functions for Paroquia Foto
    function previewImage(input) {
        previewImageGeneric(input, 'dropZone', 'imagePreviewContainer', 'imagePreview', 'dropZoneContent');
    }

    function clearImage(event) {
        clearImageGeneric(event, 'foto', 'dropZone', 'imagePreviewContainer', 'imagePreview', 'dropZoneContent');
    }

    // Specific functions for Paroco Foto
    function previewImageParoco(input) {
        previewImageGeneric(input, 'dropZoneParoco', 'imagePreviewContainerParoco', 'imagePreviewParoco', 'dropZoneContentParoco');
    }

    function clearImageParoco(event) {
        clearImageGeneric(event, 'paroco_foto', 'dropZoneParoco', 'imagePreviewContainerParoco', 'imagePreviewParoco', 'dropZoneContentParoco');
    }

    // Drag and Drop Logic
    function setupDragAndDrop(dropZoneId, inputId, previewFunction) {
        const dropZone = document.getElementById(dropZoneId);
        const input = document.getElementById(inputId);

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('bg-primary');
            dropZone.classList.add('bg-opacity-10');
            dropZone.style.borderColor = 'var(--apple-blue)';
        }

        function unhighlight(e) {
            dropZone.classList.remove('bg-primary');
            dropZone.classList.remove('bg-opacity-10');
            dropZone.style.borderColor = 'var(--apple-border)';
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files && files.length > 0) {
                input.files = files;
                // Call the specific preview function passed as argument
                previewFunction(input);
            }
        }
    }

    // Initialize Drag and Drop for both zones
    document.addEventListener('DOMContentLoaded', function() {
        setupDragAndDrop('dropZone', 'foto', previewImage);
        setupDragAndDrop('dropZoneParoco', 'paroco_foto', previewImageParoco);
    });
</script>
@endpush
