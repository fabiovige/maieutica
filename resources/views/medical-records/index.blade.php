@extends('layouts.app')

@section('title')
    Prontuários Médicos
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-file-medical"></i> Prontuários Médicos
    </li>
@endsection

@section('actions')
    <div class="d-flex gap-2">
        @can('medical-record-create')
            <a href="{{ route('medical-records.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Novo Prontuário
            </a>
        @endcan

        @can('medical-record-list-all')
            <a href="{{ route('medical-records.trash') }}" class="btn btn-secondary">
                <i class="bi bi-trash"></i> Lixeira
            </a>
        @endcan
    </div>
@endsection

@section('content')

    {{-- Filtros --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('medical-records.index') }}" class="row g-3" id="filter-form">
                {{-- Profissional --}}
                @can('medical-record-list-all')
                    <div class="col-md-3">
                        <label for="professional_id" class="form-label">Profissional</label>
                        <select name="professional_id" id="professional_id" class="form-select">
                            <option value="">Todos</option>
                            @foreach($professionals as $professional)
                                <option value="{{ $professional->id }}" {{ request('professional_id') == $professional->id ? 'selected' : '' }}>
                                    {{ $professional->user->first()->name ?? 'N/D' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endcan

                {{-- Tipo de Paciente --}}
                <div class="col-md-2">
                    <label for="patient_type" class="form-label">Tipo Paciente</label>
                    <select name="patient_type" id="patient_type" class="form-select">
                        <option value="">Todos</option>
                        <option value="App\Models\Kid" {{ request('patient_type') === 'App\Models\Kid' ? 'selected' : '' }}>Criança</option>
                        <option value="App\Models\User" {{ request('patient_type') === 'App\Models\User' ? 'selected' : '' }}>Adulto</option>
                    </select>
                </div>

                {{-- Paciente --}}
                <div class="col-md-3">
                    <label for="patient_id" class="form-label">Paciente</label>
                    <select name="patient_id" id="patient_id" class="form-select">
                        <option value="">Todos</option>
                        @if(request('patient_type') === 'App\Models\Kid')
                            @foreach($kids as $kid)
                                <option value="{{ $kid->id }}" {{ request('patient_id') == $kid->id ? 'selected' : '' }}>
                                    {{ $kid->name }}
                                </option>
                            @endforeach
                        @elseif(request('patient_type') === 'App\Models\User')
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('patient_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                {{-- Data Início --}}
                <div class="col-md-2">
                    <label for="date_start" class="form-label">Data Início</label>
                    <input type="text" class="form-control datepicker" id="date_start" name="date_start"
                           placeholder="dd/mm/aaaa" value="{{ request('date_start') }}" autocomplete="off">
                </div>

                {{-- Data Fim --}}
                <div class="col-md-2">
                    <label for="date_end" class="form-label">Data Fim</label>
                    <input type="text" class="form-control datepicker" id="date_end" name="date_end"
                           placeholder="dd/mm/aaaa" value="{{ request('date_end') }}" autocomplete="off">
                </div>

                {{-- Busca --}}
                <div class="col-md-4">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="search" name="search"
                           placeholder="Buscar em demanda ou evolução..." value="{{ request('search') }}">
                </div>

                {{-- Botões --}}
                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        @if(request()->hasAny(['professional_id', 'patient_type', 'patient_id', 'date_start', 'date_end', 'search']))
                            <a href="{{ route('medical-records.index') }}" class="btn btn-secondary" title="Limpar filtros">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Lista de Prontuários --}}
    @if ($medicalRecords->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Nenhum prontuário encontrado.
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Paciente</th>
                                <th>Profissional</th>
                                <th>Criado em</th>
                                <th class="text-end" style="width: 150px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($medicalRecords as $record)
                                <tr>
                                    {{-- Data da Sessão --}}
                                    <td>
                                        <span class="badge bg-info">{{ $record->session_date }}</span>
                                    </td>

                                    {{-- Tipo de Paciente --}}
                                    <td>
                                        @if($record->patient_type === 'App\Models\Kid')
                                            <span class="badge bg-info">Criança</span>
                                        @else
                                            <span class="badge bg-secondary">Adulto</span>
                                        @endif
                                    </td>

                                    {{-- Nome do Paciente --}}
                                    <td>
                                        <strong>{{ $record->patient_name }}</strong>
                                    </td>

                                    {{-- Profissional --}}
                                    <td>
                                        <div>{{ $record->creator->name ?? 'N/D' }}</div>
                                        @if($record->creator && $record->creator->professional && $record->creator->professional->first())
                                            <small class="text-muted">CRP: {{ $record->creator->professional->first()->registration_number }}</small>
                                        @endif
                                    </td>

                                    {{-- Criado em --}}
                                    <td>{{ $record->created_at ? $record->created_at->format('d/m/Y H:i') : 'N/D' }}</td>

                                    {{-- Ações --}}
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            @can('view', $record)
                                                <a href="{{ route('medical-records.show', $record) }}"
                                                   class="btn btn-primary btn-sm" title="Visualizar">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endcan

                                            @can('update', $record)
                                                <a href="{{ route('medical-records.edit', $record) }}"
                                                   class="btn btn-warning btn-sm" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan

                                            @can('delete', $record)
                                                <form action="{{ route('medical-records.destroy', $record) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Tem certeza que deseja mover este prontuário para a lixeira?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Excluir">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Paginação --}}
        <div class="d-flex justify-content-end mt-3">
            {{ $medicalRecords->appends(request()->query())->links() }}
        </div>
    @endif

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Dados dos pacientes (Kids e Users)
        const kids = @json($kids);
        const users = @json($users);

        // Atualizar dropdown de pacientes quando tipo mudar
        $('#patient_type').on('change', function() {
            const patientType = $(this).val();
            const $patientSelect = $('#patient_id');

            // Limpar opções atuais (exceto "Todos")
            $patientSelect.find('option:not(:first)').remove();

            if (patientType === 'App\\Models\\Kid') {
                // Adicionar Kids
                kids.forEach(function(kid) {
                    $patientSelect.append(
                        $('<option></option>').val(kid.id).text(kid.name)
                    );
                });
            } else if (patientType === 'App\\Models\\User') {
                // Adicionar Users
                users.forEach(function(user) {
                    $patientSelect.append(
                        $('<option></option>').val(user.id).text(user.name)
                    );
                });
            }
        });
    });
</script>
@endpush
