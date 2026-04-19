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

    {{-- Filtros --}}
    @if(!auth()->user()->can('medical-record-view-own') || auth()->user()->can('medical-record-list-all'))
        <div class="card mb-3" style="border-radius:12px; border:1px solid #e9ecef;">
            <div class="card-body">
                <form method="GET" action="{{ route('medical-records.index') }}" class="row g-3" id="filter-form">

                    @can('medical-record-list-all')
                        <div class="col-md-4">
                            <label for="professional_id" class="form-label">Profissional</label>
                            <select name="professional_id" id="professional_id" class="form-select select2" data-placeholder="Todos">
                                <option value="">Todos</option>
                                @foreach($professionals as $professional)
                                    <option value="{{ $professional->id }}" {{ request('professional_id') == $professional->id ? 'selected' : '' }}>
                                        {{ $professional->user->first()->name ?? 'N/D' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endcan

                    <div class="col">
                        <label for="patient_id" class="form-label">Paciente</label>
                        <select name="patient_id" id="patient_id" class="form-select select2" data-placeholder="Todos os pacientes">
                            <option value="">Todos</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                    {{ $patient->name }} ({{ $patient->age ?? 'N/D' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(request()->hasAny(['professional_id', 'patient_id']))
                        <div class="col-auto d-flex align-items-end">
                            <a href="{{ route('medical-records.index') }}" class="btn btn-secondary" title="Limpar filtros">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        </div>
                    @endif

                </form>
            </div>
        </div>
    @endif

    {{-- Lista de Prontuários em Tabela --}}
    @if($medicalRecords->isNotEmpty())
        <table class="table table-hover table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:110px;">Data</th>
                    <th>Paciente</th>
                    <th style="width:110px;">Tipo</th>
                    <th>Profissional</th>
                    <th>Demanda</th>
                    <th style="width:80px;" class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($medicalRecords as $record)
                    <tr>
                        <td>
                            <small class="text-muted">
                                {{ $record->session_date ? $record->session_date->format('d/m/Y') : 'N/D' }}
                            </small>
                        </td>
                        <td>{{ $record->patient_name }}</td>
                        <td>
                            @if($record->patient && $record->patient->is_adult)
                                <span class="badge" style="background:#f3e8fe; color:#7c3aed;">
                                    <i class="bi bi-person"></i> Adulto
                                </span>
                            @else
                                <span class="badge bg-primary-subtle text-primary-emphasis">
                                    <i class="bi bi-person-hearts"></i> Criança
                                </span>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ $record->creator->name ?? 'N/D' }}</small></td>
                        <td>
                            @if($record->complaint)
                                <small class="text-muted fst-italic">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($record->complaint), 80) }}
                                </small>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                        <td class="text-center">
                            @can('view', $record)
                                <a href="{{ route('medical-records.show', $record) }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center mt-3">
            {{ $medicalRecords->onEachSide(1)->appends(request()->query())->links() }}
        </div>
    @else
        <div class="alert alert-light mt-3 mb-0">
            <i class="bi bi-info-circle"></i>
            @if(auth()->user()->can('medical-record-view-own') && !auth()->user()->can('medical-record-list-all'))
                Você ainda não possui prontuários registrados.
            @else
                Nenhum prontuário encontrado.
            @endif
        </div>
    @endif

@endsection

@push('scripts')
<script>
    $(function () {
        $('#patient_id, #professional_id').on('change', function () {
            $('#filter-form').trigger('submit');
        });
    });
</script>
@endpush

