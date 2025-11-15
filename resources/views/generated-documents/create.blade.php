@extends('layouts.app')

@section('title')
    Gerar Documento
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('generated-documents.index') }}">
            <i class="bi bi-file-pdf"></i> Documentos Gerados
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Gerar Documento</li>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-file-earmark-plus"></i> Gerar Novo Documento</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('generated-documents.generate') }}" method="POST" id="generateForm">
                @csrf

                <div class="row">
                    <!-- Criança -->
                    <div class="col-md-6 mb-3">
                        <label for="kid_id" class="form-label">
                            <i class="bi bi-person"></i> Criança <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('kid_id') is-invalid @enderror"
                                id="kid_id"
                                name="kid_id"
                                required>
                            <option value="">Selecione uma criança</option>
                            @foreach($kids as $kidOption)
                                <option value="{{ $kidOption->id }}" {{ (old('kid_id', $kid?->id) == $kidOption->id) ? 'selected' : '' }}>
                                    {{ $kidOption->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('kid_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Template -->
                    <div class="col-md-6 mb-3">
                        <label for="template_id" class="form-label">
                            <i class="bi bi-file-earmark-text"></i> Template <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('template_id') is-invalid @enderror"
                                id="template_id"
                                name="template_id"
                                required>
                            <option value="">Selecione um template</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                    {{ $template->name }} ({{ App\Models\DocumentTemplate::getDocumentTypes()[$template->type] ?? $template->type }})
                                </option>
                            @endforeach
                        </select>
                        @error('template_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Checklist (Opcional) -->
                <div class="mb-3">
                    <label for="checklist_id" class="form-label">
                        <i class="bi bi-list-check"></i> Checklist (Opcional)
                    </label>
                    <select class="form-select @error('checklist_id') is-invalid @enderror"
                            id="checklist_id"
                            name="checklist_id">
                        <option value="">Nenhum checklist selecionado</option>
                    </select>
                    @error('checklist_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        Selecione um checklist se o documento precisar de dados de avaliação específicos.
                    </div>
                </div>

                <!-- Dados Personalizados (Opcional) -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-pencil"></i> Dados Personalizados (Opcional)
                            <button type="button" class="btn btn-sm btn-link float-end" data-bs-toggle="collapse" data-bs-target="#customDataCollapse">
                                <i class="bi bi-chevron-down"></i> Expandir
                            </button>
                        </h6>
                    </div>
                    <div id="customDataCollapse" class="collapse">
                        <div class="card-body">
                            <p class="text-muted small">
                                Preencha apenas os campos que deseja sobrescrever. Campos vazios usarão dados automáticos.
                            </p>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label for="custom_dias_semana" class="form-label small">Dias da Semana</label>
                                    <input type="text" class="form-control form-control-sm" id="custom_dias_semana" name="custom_data[dias_semana]" placeholder="Ex: Segundas e Quartas">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="custom_horarios" class="form-label small">Horários</label>
                                    <input type="text" class="form-control form-control-sm" id="custom_horarios" name="custom_data[horarios]" placeholder="Ex: 14h às 15h">
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label for="custom_solicitante" class="form-label small">Solicitante</label>
                                    <input type="text" class="form-control form-control-sm" id="custom_solicitante" name="custom_data[solicitante]" placeholder="Quem solicitou o documento">
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label for="custom_finalidade" class="form-label small">Finalidade</label>
                                    <input type="text" class="form-control form-control-sm" id="custom_finalidade" name="custom_data[finalidade]" placeholder="Finalidade do documento">
                                </div>
                                <div class="col-md-12">
                                    <label for="custom_observacoes" class="form-label small">Observações/Texto Livre</label>
                                    <textarea class="form-control form-control-sm" id="custom_observacoes" name="custom_data[observacoes]" rows="3" placeholder="Observações adicionais..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-file-earmark-plus"></i> Gerar Documento
                    </button>
                    <a href="{{ route('generated-documents.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    document.getElementById('kid_id').addEventListener('change', function() {
        const kidId = this.value;
        const checklistSelect = document.getElementById('checklist_id');

        // Limpar opções
        checklistSelect.innerHTML = '<option value="">Carregando...</option>';

        if (kidId) {
            // Buscar checklists da criança (via AJAX se necessário)
            // Por enquanto, vamos deixar vazio
            checklistSelect.innerHTML = '<option value="">Nenhum checklist disponível</option>';
        } else {
            checklistSelect.innerHTML = '<option value="">Selecione uma criança primeiro</option>';
        }
    });
</script>
@endpush
