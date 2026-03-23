@extends('layouts.app')

@section('title')
    Novo Prontuário
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('medical-records.index') }}">
            <i class="bi bi-file-medical"></i> Prontuários
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Novo</li>
@endsection

@section('content')

    <div class="row">
        {{-- Formulário principal --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title-custom mb-0"><i class="bi bi-plus-circle"></i> Novo Prontuário</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('medical-records.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="patient_type" value="App\Models\Kid">

                        {{-- Linha 1: Profissional (admin), Paciente, Data --}}
                        <div class="row">
                            {{-- Profissional (somente admin) --}}
                            @can('medical-record-create-all')
                                <div class="col-md-4 mb-3">
                                    <label for="professional_id" class="form-label">Profissional <span class="text-danger">*</span></label>
                                    <select name="professional_id" id="professional_id" class="form-select select2 @error('professional_id') is-invalid @enderror" data-placeholder="Selecione o profissional" required>
                                        <option value="">Selecione o profissional</option>
                                        @foreach($professionals as $professional)
                                            <option value="{{ $professional->id }}" {{ old('professional_id') == $professional->id ? 'selected' : '' }}>
                                                {{ $professional->user->first()->name ?? 'N/D' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('professional_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endcan

                            {{-- Paciente --}}
                            <div class="col-md-{{ auth()->user()->can('medical-record-create-all') ? '4' : '6' }} mb-3">
                                <label for="patient_id" class="form-label">Paciente <span class="text-danger">*</span></label>
                                <select name="patient_id" id="patient_id" class="form-select select2 @error('patient_id') is-invalid @enderror" data-placeholder="Selecione o paciente" required>
                                    <option value="">Selecione o paciente</option>
                                    @foreach($kids as $kid)
                                        <option value="{{ $kid->id }}" {{ old('patient_id') == $kid->id ? 'selected' : '' }}>
                                            {{ $kid->name }} ({{ $kid->age ?? 'Idade N/D' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Data da Sessão --}}
                            <div class="col-md-{{ auth()->user()->can('medical-record-create-all') ? '4' : '6' }} mb-3">
                                <label for="session_date" class="form-label">Data da Sessão <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control datepicker @error('session_date') is-invalid @enderror"
                                       id="session_date"
                                       name="session_date"
                                       placeholder="dd/mm/aaaa"
                                       value="{{ old('session_date') }}"
                                       required
                                       autocomplete="off">
                                @error('session_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Divisor --}}
                        <hr class="my-4">

                        {{-- Demanda / Queixa --}}
                        <div class="mb-3">
                            <label for="complaint" class="form-label fw-bold">Demanda / Queixa <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('complaint') is-invalid @enderror"
                                      id="complaint"
                                      name="complaint"
                                      rows="4"
                                      placeholder="Descreva a demanda ou queixa apresentada pelo paciente..."
                                      required>{{ old('complaint') }}</textarea>
                            @error('complaint')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Objetivo / Técnica Utilizada --}}
                        <div class="mb-3">
                            <label for="objective_technique" class="form-label fw-bold">Objetivo / Técnica Utilizada <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('objective_technique') is-invalid @enderror"
                                      id="objective_technique"
                                      name="objective_technique"
                                      rows="4"
                                      placeholder="Descreva o objetivo da sessão e as técnicas utilizadas..."
                                      required>{{ old('objective_technique') }}</textarea>
                            @error('objective_technique')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Registro de Evolução --}}
                        <div class="mb-3">
                            <label for="evolution_notes" class="form-label fw-bold">Registro de Evolução <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('evolution_notes') is-invalid @enderror"
                                      id="evolution_notes"
                                      name="evolution_notes"
                                      rows="6"
                                      placeholder="Descreva a evolução do paciente durante a sessão..."
                                      required>{{ old('evolution_notes') }}</textarea>
                            @error('evolution_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Encaminhamento / Encerramento --}}
                        <div class="mb-3">
                            <label for="referral_closure" class="form-label fw-bold">Encaminhamento / Encerramento <small class="text-muted">(opcional)</small></label>
                            <textarea class="form-control @error('referral_closure') is-invalid @enderror"
                                      id="referral_closure"
                                      name="referral_closure"
                                      rows="4"
                                      placeholder="Se houver encaminhamento ou encerramento, descreva aqui...">{{ old('referral_closure') }}</textarea>
                            @error('referral_closure')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Botões --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvar Prontuário
                            </button>
                            <a href="{{ route('medical-records.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Painel lateral: Histórico do paciente --}}
        <div class="col-lg-4">
            <div class="card" id="history-panel">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="bi bi-clock-history"></i> Registros Anteriores</h6>
                </div>
                <div class="card-body" id="history-content">
                    <p class="text-muted mb-0">
                        <i class="bi bi-info-circle"></i> Selecione um paciente para ver o histórico de prontuários.
                    </p>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Configurar datepicker para data da sessão (máximo hoje)
        $('#session_date').datepicker('option', 'maxDate', 0);

        // Carregar histórico ao selecionar paciente
        $('#patient_id').on('change', function() {
            const patientId = $(this).val();
            const $content = $('#history-content');

            if (!patientId) {
                $content.html('<p class="text-muted mb-0"><i class="bi bi-info-circle"></i> Selecione um paciente para ver o histórico de prontuários.</p>');
                return;
            }

            $content.html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Carregando...</div>');

            $.get('{{ route("medical-records.patient-history") }}', { patient_id: patientId })
                .done(function(records) {
                    if (records.length === 0) {
                        $content.html('<p class="text-muted mb-0"><i class="bi bi-info-circle"></i> Nenhum prontuário anterior para este paciente.</p>');
                        return;
                    }

                    let html = '<div class="list-group list-group-flush">';
                    records.forEach(function(record) {
                        html += '<a href="' + record.url + '" target="_blank" class="list-group-item list-group-item-action px-0 py-2">';
                        html += '<div class="d-flex justify-content-between align-items-start">';
                        html += '<div>';
                        html += '<span class="badge bg-info mb-1">' + record.session_date + '</span>';
                        html += '<p class="mb-0 small text-truncate" style="max-width: 250px;">' + record.complaint + '</p>';
                        html += '</div>';
                        html += '<small class="text-muted">' + record.creator_name.split(' ')[0] + '</small>';
                        html += '</div>';
                        html += '</a>';
                    });
                    html += '</div>';
                    html += '<div class="mt-2 text-muted small"><i class="bi bi-box-arrow-up-right"></i> Clique para abrir em nova aba</div>';

                    $content.html(html);
                })
                .fail(function() {
                    $content.html('<p class="text-danger mb-0"><i class="bi bi-exclamation-triangle"></i> Erro ao carregar histórico.</p>');
                });
        });

        // Disparar se já houver paciente selecionado (old value)
        if ($('#patient_id').val()) {
            $('#patient_id').trigger('change');
        }
    });
</script>
@endpush
