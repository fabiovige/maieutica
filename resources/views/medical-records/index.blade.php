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

@push('styles')
<style>
    .record-card {
        border-radius: 12px !important;
        transition: box-shadow 0.2s ease, transform 0.15s ease;
    }
    .record-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.10) !important;
        transform: translateY(-1px);
    }
    .record-meta { font-size: 0.875rem; }
    @media (max-width: 575px) {
        .record-card .card-body { padding: 0.85rem 1rem !important; }
        .record-meta { font-size: 0.8125rem; }
    }
</style>
@endpush

@section('content')

    {{-- Filtros --}}
    @if(!auth()->user()->can('medical-record-view-own') || auth()->user()->can('medical-record-list-all'))
        <div class="card mb-3 border-0 shadow-sm" style="border-radius:12px;">
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
                            @if($kids->isNotEmpty())
                                <optgroup label="Crianças">
                                    @foreach($kids as $kid)
                                        <option value="{{ $kid->id }}" data-type="App\Models\Kid"
                                            {{ request('patient_id') == $kid->id && request('filter_patient_type', 'App\Models\Kid') === 'App\Models\Kid' ? 'selected' : '' }}>
                                            {{ $kid->name }} ({{ $kid->age ?? 'N/D' }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @if($userPatients->isNotEmpty())
                                <optgroup label="Adultos">
                                    @foreach($userPatients as $user)
                                        <option value="{{ $user->id }}" data-type="App\Models\User"
                                            {{ request('patient_id') == $user->id && request('filter_patient_type') === 'App\Models\User' ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                        <input type="hidden" name="filter_patient_type" id="filter_patient_type" value="{{ request('filter_patient_type', 'App\Models\Kid') }}">
                    </div>

                    <div class="col-auto d-flex align-items-end">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                            @if(request()->hasAny(['professional_id', 'patient_id']))
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

    {{-- Lista de Prontuários em Cards --}}
    <div class="d-flex flex-column gap-2">
        @forelse($medicalRecords as $record)
            <div class="card shadow-sm border-0 record-card">
                <div class="card-body py-3 px-4">
                    <div class="d-flex align-items-center gap-3">

                        {{-- Informações --}}
                        <div class="d-flex flex-wrap align-items-center gap-3 flex-grow-1 record-meta">

                            {{-- Data da sessão --}}
                            <span class="badge bg-info-subtle text-info-emphasis px-2 py-1">
                                <i class="bi bi-calendar3 me-1"></i>
                                {{ $record->session_date ? $record->session_date->format('d/m/Y') : 'N/D' }}
                            </span>

                            {{-- Paciente --}}
                            <span class="fw-semibold text-dark">{{ $record->patient_name }}</span>

                            {{-- Tipo --}}
                            @if($record->patient_type === 'App\Models\Kid')
                                <span class="badge bg-primary-subtle text-primary-emphasis">
                                    <i class="bi bi-person-hearts"></i> Criança
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                    <i class="bi bi-person"></i> Adulto
                                </span>
                            @endif

                            {{-- Profissional --}}
                            <span class="text-muted small">
                                <i class="bi bi-person-badge me-1"></i>{{ $record->creator->name ?? 'N/D' }}
                            </span>

                            {{-- Trecho da demanda --}}
                            @if($record->complaint)
                                <span class="text-muted small fst-italic text-truncate" style="max-width:280px;">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($record->complaint), 60) }}
                                </span>
                            @endif

                        </div>

                        {{-- Botão Ver --}}
                        @can('view', $record)
                            <div class="flex-shrink-0">
                                <a href="{{ route('medical-records.show', $record) }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                            </div>
                        @endcan

                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info border-0 shadow-sm" style="border-radius:12px;">
                <i class="bi bi-info-circle"></i>
                @if(auth()->user()->can('medical-record-view-own') && !auth()->user()->can('medical-record-list-all'))
                    Você ainda não possui prontuários registrados.
                @else
                    Nenhum prontuário encontrado.
                @endif
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    @if($medicalRecords->isNotEmpty())
        <div class="d-flex justify-content-end mt-3">
            {{ $medicalRecords->appends(request()->query())->links() }}
        </div>
    @endif

@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#patient_id').on('change', function () {
            const type = $(this).find('option:selected').data('type') || 'App\\Models\\Kid';
            $('#filter_patient_type').val(type);
        });
    });
</script>
@endpush
