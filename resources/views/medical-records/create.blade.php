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
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title-custom mb-0"><i class="bi bi-plus-circle"></i> Novo Prontuário</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('medical-records.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="patient_type" id="patient_type" value="App\Models\Kid">

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
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ old('patient_id', request('patient_id')) == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->name }} ({{ $patient->age ?? 'Idade N/D' }})
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
