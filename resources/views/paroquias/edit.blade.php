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
                                                        : 'https://backend.sismatriz.online/uploads/paroquias/' . $paroquia->foto
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

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
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
    function previewImage(input) {
        const dropZone = document.getElementById('dropZone');
        const previewContainer = document.getElementById('imagePreviewContainer');
        const preview = document.getElementById('imagePreview');
        const content = document.getElementById('dropZoneContent');

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

    function clearImage(event) {
        event.preventDefault();
        event.stopPropagation();
        
        const input = document.getElementById('foto');
        const dropZone = document.getElementById('dropZone');
        const previewContainer = document.getElementById('imagePreviewContainer');
        const content = document.getElementById('dropZoneContent');
        const preview = document.getElementById('imagePreview');

        input.value = '';
        preview.src = '';
        previewContainer.classList.add('d-none');
        content.classList.remove('opacity-0');
        dropZone.style.borderStyle = 'dashed';
        dropZone.style.borderColor = 'var(--apple-border)';
        dropZone.style.backgroundColor = 'rgba(0,0,0,0.02)';
    }

    // Drag and Drop Visual Feedback
    const dropZone = document.getElementById('dropZone');
    
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

    // Handle dropped files
    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        const input = document.getElementById('foto');
        
        if (files && files.length > 0) {
            input.files = files;
            previewImage(input);
        }
    }

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
</script>
@endpush
