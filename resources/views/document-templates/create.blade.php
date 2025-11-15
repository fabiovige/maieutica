@extends('layouts.app')

@section('title')
    Novo Template de Documento
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('document-templates.index') }}">
            <i class="bi bi-file-earmark-text"></i> Templates
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Novo Template</li>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-plus-lg"></i> Criar Novo Template</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('document-templates.store') }}" method="POST">
                @csrf

                <div class="row">
                    <!-- Nome -->
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">
                            <i class="bi bi-tag"></i> Nome do Template <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="Ex: Declaração de Atendimento"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tipo -->
                    <div class="col-md-3 mb-3">
                        <label for="type" class="form-label">
                            <i class="bi bi-file-earmark"></i> Tipo <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('type') is-invalid @enderror"
                                id="type"
                                name="type"
                                required>
                            <option value="">Selecione o tipo</option>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Versão -->
                    <div class="col-md-3 mb-3">
                        <label for="version" class="form-label">
                            <i class="bi bi-hash"></i> Versão
                        </label>
                        <input type="text"
                               class="form-control @error('version') is-invalid @enderror"
                               id="version"
                               name="version"
                               value="{{ old('version', '1.0') }}"
                               placeholder="1.0">
                        @error('version')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Descrição -->
                <div class="mb-3">
                    <label for="description" class="form-label">
                        <i class="bi bi-text-paragraph"></i> Descrição
                    </label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description"
                              name="description"
                              rows="2"
                              placeholder="Breve descrição do template...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Placeholders Disponíveis -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-braces"></i> Placeholders Disponíveis
                    </label>
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted small mb-2">
                                Copie e cole os placeholders abaixo no conteúdo HTML. Eles serão substituídos pelos dados reais na geração do documento.
                            </p>
                            <div class="accordion" id="placeholdersAccordion">
                                @foreach($placeholderCategories as $category => $placeholders)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ $category }}">
                                            <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $category }}">
                                                {{ ucfirst($category) }} ({{ count($placeholders) }})
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $category }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#placeholdersAccordion">
                                            <div class="accordion-body">
                                                <div class="row g-2">
                                                    @foreach($placeholders as $key => $label)
                                                        <div class="col-md-4">
                                                            <div class="input-group input-group-sm">
                                                                <input type="text"
                                                                       class="form-control form-control-sm font-monospace"
                                                                       value="{{ '{{' . $key . '}}' }}"
                                                                       readonly
                                                                       onclick="this.select()">
                                                                <span class="input-group-text">{{ $label }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conteúdo HTML -->
                <div class="mb-3">
                    <label for="html_content" class="form-label">
                        <i class="bi bi-code-slash"></i> Conteúdo HTML <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control @error('html_content') is-invalid @enderror"
                              id="html_content"
                              name="html_content"
                              rows="15">{{ old('html_content') }}</textarea>
                    @error('html_content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        Use os placeholders acima no formato <code>{{ '{{placeholder}}' }}</code>. Exemplo: <code>{{ '{{nome_crianca}}' }}</code>
                    </div>
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               id="is_active"
                               name="is_active"
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Template ativo
                        </label>
                    </div>
                </div>

                <!-- Botões -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Salvar Template
                    </button>
                    <a href="{{ route('document-templates.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#html_content',
        height: 500,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | code | fullscreen',
        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }'
    });
</script>
@endpush
