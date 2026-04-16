@extends('layouts.app')

@section('title')
    Editar Prontuário
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
            <h3 class="card-title-custom mb-0"><i class="bi bi-pencil"></i> Editar Prontuário</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('medical-records.update', $medicalRecord) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="patient_type" value="{{ $medicalRecord->patient_type }}">

                {{-- Linha 1: Paciente, Data --}}
                <div class="row">
                    {{-- Paciente --}}
                    <div class="col-md-6 mb-3">
                        <label for="patient_id" class="form-label">
                            Paciente
                            @if($medicalRecord->patient && $medicalRecord->patient->is_adult)
                                <span class="badge bg-secondary ms-1"><i class="bi bi-person"></i> Adulto</span>
                            @else
                                <span class="badge bg-primary ms-1"><i class="bi bi-person-hearts"></i> Criança</span>
                            @endif
                            <span class="text-danger">*</span>
                        </label>
                        <select name="patient_id" id="patient_id" class="form-select select2 @error('patient_id') is-invalid @enderror" data-placeholder="Selecione o paciente">
                            <option value="">Selecione o paciente</option>
                            @if($medicalRecord->patient && $medicalRecord->patient->is_adult)
                                @foreach($userPatients as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id', $medicalRecord->patient_id) == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->name }} ({{ $patient->age ?? 'Idade N/D' }})
                                    </option>
                                @endforeach
                            @else
                                @foreach($kids as $kid)
                                    <option value="{{ $kid->id }}" {{ old('patient_id', $medicalRecord->patient_id) == $kid->id ? 'selected' : '' }}>
                                        {{ $kid->name }} ({{ $kid->age ?? 'Idade N/D' }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('patient_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Data da Sessão --}}
                    <div class="col-md-6 mb-3">
                        <label for="session_date" class="form-label">Data da Sessão <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control datepicker @error('session_date') is-invalid @enderror"
                               id="session_date"
                               name="session_date"
                               placeholder="dd/mm/aaaa"
                               value="{{ old('session_date', $medicalRecord->session_date_formatted) }}"
                               autocomplete="off"
                               required>
                        @error('session_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Profissional (readonly) --}}
                <div class="mb-3">
                    <label class="form-label">Profissional</label>
                    <input type="text" class="form-control bg-light"
                           value="{{ $medicalRecord->creator->name ?? 'N/D' }}@if($medicalRecord->creator && $medicalRecord->creator->professional && $medicalRecord->creator->professional->first()) — {{ $medicalRecord->creator->professional->first()->full_registration }}@endif"
                           readonly>
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
                              required>{{ old('complaint', $medicalRecord->complaint) }}</textarea>
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
                              required>{{ old('objective_technique', $medicalRecord->objective_technique) }}</textarea>
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
                              required>{{ old('evolution_notes', $medicalRecord->evolution_notes) }}</textarea>
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
        $('#session_date').datepicker('option', 'maxDate', 0);
    });
</script>
@endpush
