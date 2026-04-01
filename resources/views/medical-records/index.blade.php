@extends('layouts.app')

@section('title')
    Prontuários
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-file-medical"></i> Prontuários
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

    {{-- Filtros (não exibir para pacientes) --}}
    @if(!auth()->user()->can('medical-record-view-own') || auth()->user()->can('medical-record-list-all'))
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('medical-records.index') }}" class="row g-3" id="filter-form">
                {{-- Profissional --}}
                @can('medical-record-list-all')
                    <div class="col-md-3">
                        <label for="professional_id" class="form-label">Profissional</label>
                        <select name="professional_id" id="professional_id" class="form-select select2" data-placeholder="Todos os profissionais">
                            <option value="">Todos</option>
                            @foreach($professionals as $professional)
                                <option value="{{ $professional->id }}" {{ request('professional_id') == $professional->id ? 'selected' : '' }}>
                                    {{ $professional->user->first()->name ?? 'N/D' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endcan

                {{-- Paciente --}}
                <div class="col-md-3">
                    <label for="patient_id" class="form-label">Paciente</label>
                    <select name="patient_id" id="patient_id" class="form-select select2" data-placeholder="Todos os pacientes">
                        <option value="">Todos</option>
                        @if($kids->isNotEmpty())
                            <optgroup label="Crianças">
                                @foreach($kids as $kid)
                                    <option value="{{ $kid->id }}"
                                        data-type="App\Models\Kid"
                                        {{ request('patient_id') == $kid->id && request('filter_patient_type', 'App\Models\Kid') === 'App\Models\Kid' ? 'selected' : '' }}>
                                        {{ $kid->name }} ({{ $kid->age ?? 'N/D' }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if($userPatients->isNotEmpty())
                            <optgroup label="Adultos">
                                @foreach($userPatients as $user)
                                    <option value="{{ $user->id }}"
                                        data-type="App\Models\User"
                                        {{ request('patient_id') == $user->id && request('filter_patient_type') === 'App\Models\User' ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    <input type="hidden" name="filter_patient_type" id="filter_patient_type" value="{{ request('filter_patient_type', 'App\Models\Kid') }}">
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
                        @if(request()->hasAny(['professional_id', 'patient_id', 'date_start', 'date_end', 'search']))
                            <a href="{{ route('medical-records.index') }}" class="btn btn-secondary" title="Limpar filtros">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Lista de Prontuários --}}
    @if ($medicalRecords->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            @if(auth()->user()->can('medical-record-view-own') && !auth()->user()->can('medical-record-list-all'))
                Você ainda não possui prontuários registrados.
            @else
                Nenhum prontuário encontrado.
            @endif
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>DATA</th>
                                <th>PACIENTE</th>
                                <th>TIPO</th>
                                <th>PROFISSIONAL</th>
                                <th class="text-center">AÇÕES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($medicalRecords as $record)
                                <tr>
                                    {{-- Data da Sessão --}}
                                    <td>
                                        <span class="badge bg-info">{{ $record->session_date ? $record->session_date->format('d/m/Y') : 'N/D' }}</span>
                                    </td>

                                    {{-- Nome do Paciente com idade --}}
                                    <td>
                                        <strong>{{ $record->patient_name }}</strong>
                                        @if($record->patient && $record->patient_type === 'App\Models\Kid')
                                            <br><small class="text-muted">{{ $record->patient->age ?? '' }}</small>
                                        @endif
                                    </td>

                                    {{-- Tipo de Paciente --}}
                                    <td class="text-center">
                                        @if($record->patient_type === 'App\Models\Kid')
                                            <span class="badge bg-primary"><i class="bi bi-person-hearts"></i> Criança</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="bi bi-person"></i> Adulto</span>
                                        @endif
                                    </td>

                                    {{-- Profissional --}}
                                    <td>
                                        <div>{{ $record->creator->name ?? 'N/D' }}</div>
                                        @if($record->creator && $record->creator->professional && $record->creator->professional->first())
                                            <small class="text-muted">{{ $record->creator->professional->first()->full_registration }}</small>
                                        @endif
                                    </td>

                                    {{-- Ações --}}
                                    <td class="text-center">
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            @can('view', $record)
                                                <a href="{{ route('medical-records.show', $record) }}" class="btn btn-secondary btn-sm">Ver</a>
                                                <a href="{{ route('medical-records.pdf', $record) }}" class="btn btn-secondary btn-sm">PDF</a>
                                            @endcan
                                            @if(auth()->user()->can('medical-record-edit-all') || (int)$record->created_by === (int)auth()->id())
                                                @can('update', $record)
                                                    <a href="{{ route('medical-records.edit', $record) }}" class="btn btn-secondary btn-sm">Editar</a>
                                                @endcan
                                                @can('delete', $record)
                                                    <form action="{{ route('medical-records.destroy', $record) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja mover este prontuário para a lixeira?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-secondary btn-sm">Excluir</button>
                                                    </form>
                                                @endcan
                                            @endif
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
        // Atualizar filter_patient_type quando o paciente for selecionado no filtro
        $('#patient_id').on('change', function() {
            const selected = $(this).find('option:selected');
            const type = selected.data('type') || 'App\\Models\\Kid';
            $('#filter_patient_type').val(type);
        });
    });
</script>
@endpush
