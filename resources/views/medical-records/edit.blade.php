@extends('layouts.app')

@section('title')
    Editar Prontuário Médico
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('medical-records.index') }}">
            <i class="bi bi-file-medical"></i> Prontuários
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Editar</li>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-pencil"></i> Editar Prontuário Médico</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('medical-records.update', $medicalRecord) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Tipo de Paciente (readonly) --}}
                <div class="mb-3">
                    <label for="patient_type" class="form-label">Tipo de Paciente</label>
                    <input type="text" class="form-control" value="{{ $medicalRecord->patient_type_name }}" readonly>
                    <input type="hidden" name="patient_type" value="{{ $medicalRecord->patient_type }}">
                </div>

                {{-- Paciente (readonly) --}}
                <div class="mb-3">
                    <label for="patient_name" class="form-label">Paciente</label>
                    <input type="text" class="form-control" value="{{ $medicalRecord->patient_name }}" readonly>
                    <input type="hidden" name="patient_id" value="{{ $medicalRecord->patient_id }}">
                </div>

                {{-- Profissional (readonly) --}}
                <div class="mb-3">
                    <label for="professional_name" class="form-label">Profissional Responsável</label>
                    <input type="text" class="form-control"
                           value="{{ $medicalRecord->creator->name ?? 'N/D' }}@if($medicalRecord->creator && $medicalRecord->creator->professional && $medicalRecord->creator->professional->first()) - CRP: {{ $medicalRecord->creator->professional->first()->registration_number }}@endif"
                           readonly>
                    <small class="form-text text-muted">Profissional que criou este prontuário</small>
                </div>

                {{-- Data da Sessão --}}
                <div class="mb-3">
                    <label for="session_date" class="form-label">Data da Sessão <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control datepicker @error('session_date') is-invalid @enderror"
                           id="session_date"
                           name="session_date"
                           placeholder="dd/mm/aaaa"
                           value="{{ old('session_date', $medicalRecord->session_date) }}"
                           required
                           autocomplete="off">
                    @error('session_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Selecione a data ou digite no formato dd/mm/aaaa</small>
                </div>

                {{-- Demanda --}}
                <div class="mb-3">
                    <label for="complaint" class="form-label">Demanda <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('complaint') is-invalid @enderror" 
                              id="complaint" 
                              name="complaint" 
                              rows="4" 
                              required>{{ old('complaint', $medicalRecord->complaint) }}</textarea>
                    @error('complaint')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Objetivo/Técnica --}}
                <div class="mb-3">
                    <label for="objective_technique" class="form-label">Objetivo e Técnica Utilizada <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('objective_technique') is-invalid @enderror" 
                              id="objective_technique" 
                              name="objective_technique" 
                              rows="4" 
                              required>{{ old('objective_technique', $medicalRecord->objective_technique) }}</textarea>
                    @error('objective_technique')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Registro de Evolução --}}
                <div class="mb-3">
                    <label for="evolution_notes" class="form-label">Registro de Evolução <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('evolution_notes') is-invalid @enderror" 
                              id="evolution_notes" 
                              name="evolution_notes" 
                              rows="6" 
                              required>{{ old('evolution_notes', $medicalRecord->evolution_notes) }}</textarea>
                    @error('evolution_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Encaminhamento ou Encerramento --}}
                <div class="mb-3">
                    <label for="referral_closure" class="form-label">Encaminhamento ou Encerramento</label>
                    <textarea class="form-control @error('referral_closure') is-invalid @enderror" 
                              id="referral_closure" 
                              name="referral_closure" 
                              rows="4">{{ old('referral_closure', $medicalRecord->referral_closure) }}</textarea>
                    @error('referral_closure')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Botões --}}
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Atualizar Prontuário
                    </button>
                    <a href="{{ route('medical-records.show', $medicalRecord) }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Configurar datepicker para data da sessão (máximo hoje)
        $('#session_date').datepicker('option', 'maxDate', 0);
    });
</script>
@endpush
