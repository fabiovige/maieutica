@extends('layouts.app')

@section('title')
    Novo Prontuário Médico
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

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Novo Prontuário Médico</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('medical-records.store') }}" method="POST">
                @csrf

                {{-- Profissional (somente admin) --}}
                @can('medical-record-create-all')
                    <div class="mb-3">
                        <label for="professional_id" class="form-label">Profissional <span class="text-danger">*</span></label>
                        <select name="professional_id" id="professional_id" class="form-select @error('professional_id') is-invalid @enderror" required>
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
                        <small class="form-text text-muted">Selecione o profissional que está criando este prontuário</small>
                    </div>
                @endcan

                {{-- Tipo de Paciente --}}
                <div class="mb-3">
                    <label for="patient_type" class="form-label">Tipo de Paciente <span class="text-danger">*</span></label>
                    <select name="patient_type" id="patient_type" class="form-select @error('patient_type') is-invalid @enderror" required>
                        <option value="">Selecione o tipo de paciente</option>
                        <option value="App\Models\Kid" {{ old('patient_type') === 'App\Models\Kid' ? 'selected' : '' }}>Criança</option>
                        <option value="App\Models\User" {{ old('patient_type') === 'App\Models\User' ? 'selected' : '' }}>Adulto</option>
                    </select>
                    @error('patient_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Paciente (Criança) --}}
                <div class="mb-3 patient-select" id="kid-select" style="display: none;">
                    <label for="patient_id_kid" class="form-label">Criança <span class="text-danger">*</span></label>
                    <select name="patient_id_kid" id="patient_id_kid" class="form-select @error('patient_id') is-invalid @enderror">
                        <option value="">Selecione a criança</option>
                        @foreach($kids as $kid)
                            <option value="{{ $kid->id }}" {{ old('patient_id') == $kid->id ? 'selected' : '' }}>
                                {{ $kid->name }} - {{ $kid->age }}
                            </option>
                        @endforeach
                    </select>
                    @error('patient_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Paciente (Adulto) --}}
                <div class="mb-3 patient-select" id="user-select" style="display: none;">
                    <label for="patient_id_user" class="form-label">Paciente Adulto <span class="text-danger">*</span></label>
                    <select name="patient_id_user" id="patient_id_user" class="form-select @error('patient_id') is-invalid @enderror">
                        <option value="">Selecione o paciente</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('patient_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} - {{ $user->email }}
                            </option>
                        @endforeach
                    </select>
                    @error('patient_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <input type="hidden" name="patient_id" id="patient_id" value="{{ old('patient_id') }}">

                {{-- Data da Sessão --}}
                <div class="mb-3">
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
                    <small class="form-text text-muted">Selecione a data ou digite no formato dd/mm/aaaa</small>
                </div>

                {{-- Demanda --}}
                <div class="mb-3">
                    <label for="complaint" class="form-label">Demanda <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('complaint') is-invalid @enderror" 
                              id="complaint" 
                              name="complaint" 
                              rows="4" 
                              placeholder="Descreva a demanda apresentada pelo paciente..."
                              required>{{ old('complaint') }}</textarea>
                    @error('complaint')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Mínimo 10 caracteres</small>
                </div>

                {{-- Objetivo/Técnica --}}
                <div class="mb-3">
                    <label for="objective_technique" class="form-label">Objetivo e Técnica Utilizada <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('objective_technique') is-invalid @enderror" 
                              id="objective_technique" 
                              name="objective_technique" 
                              rows="4" 
                              placeholder="Descreva o objetivo da sessão e as técnicas utilizadas..."
                              required>{{ old('objective_technique') }}</textarea>
                    @error('objective_technique')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Mínimo 10 caracteres</small>
                </div>

                {{-- Registro de Evolução --}}
                <div class="mb-3">
                    <label for="evolution_notes" class="form-label">Registro de Evolução <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('evolution_notes') is-invalid @enderror" 
                              id="evolution_notes" 
                              name="evolution_notes" 
                              rows="6" 
                              placeholder="Descreva a evolução do paciente durante a sessão..."
                              required>{{ old('evolution_notes') }}</textarea>
                    @error('evolution_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Mínimo 10 caracteres</small>
                </div>

                {{-- Encaminhamento ou Encerramento --}}
                <div class="mb-3">
                    <label for="referral_closure" class="form-label">Encaminhamento ou Encerramento</label>
                    <textarea class="form-control @error('referral_closure') is-invalid @enderror" 
                              id="referral_closure" 
                              name="referral_closure" 
                              rows="4" 
                              placeholder="Se houver encaminhamento ou encerramento, descreva aqui... (opcional)">{{ old('referral_closure') }}</textarea>
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

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Configurar datepicker para data da sessão (máximo hoje)
        $('#session_date').datepicker('option', 'maxDate', 0);

        // Função para mostrar/ocultar selects de paciente
        function togglePatientSelect() {
            const patientType = $('#patient_type').val();
            
            // Esconder todos os selects
            $('.patient-select').hide();
            $('#patient_id_kid').prop('required', false);
            $('#patient_id_user').prop('required', false);
            
            // Mostrar o select apropriado
            if (patientType === 'App\\Models\\Kid') {
                $('#kid-select').show();
                $('#patient_id_kid').prop('required', true);
            } else if (patientType === 'App\\Models\\User') {
                $('#user-select').show();
                $('#patient_id_user').prop('required', true);
            }
        }

        // Executar ao carregar a página
        togglePatientSelect();

        // Executar ao mudar o tipo de paciente
        $('#patient_type').change(function() {
            togglePatientSelect();
            // Limpar seleção anterior
            $('#patient_id_kid').val('');
            $('#patient_id_user').val('');
            $('#patient_id').val('');
        });

        // Atualizar campo hidden quando selecionar criança
        $('#patient_id_kid').change(function() {
            $('#patient_id').val($(this).val());
        });

        // Atualizar campo hidden quando selecionar adulto
        $('#patient_id_user').change(function() {
            $('#patient_id').val($(this).val());
        });
    });
</script>
@endpush
