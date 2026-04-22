@extends('layouts.app')

@section('title')
    Visualizar Paciente
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('kids.index') }}">
            <i class="bi bi-people"></i> Pacientes
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        Visualizar - {{ $kid->name }}
    </li>
@endsection

@section('content')

    <!-- Componente com Informações da Criança -->
    <div class="row">
        <div class="col-12">
            <x-kid-info-card :kid="$kid" />
        </div>
    </div>

    <!-- Últimos Checklists (apenas para crianças) -->
    @if(!$kid->is_adult && $kid->checklists && $kid->checklists->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title-custom mb-0"><i class="bi bi-clipboard-check"></i> Últimos Checklists</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Data</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($kid->checklists as $checklist)
                                        <tr>
                                            <td>{{ $checklist->id }}</td>
                                            <td>{{ $checklist->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge {{ $checklist->situation === 'a' ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $checklist->situation_label }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('checklists.show', $checklist->id) }}"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye"></i> Ver
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Prontuários -->
    @if(isset($medicalRecords))
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title-custom mb-0"><i class="bi bi-file-medical"></i> Prontuários</h3>
                        @can('medical-record-create')
                            <a href="{{ route('medical-records.create', ['patient_id' => $kid->id]) }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg"></i> Novo Prontuário
                            </a>
                        @endcan
                    </div>
                    <div class="card-body">
                        @if($medicalRecords->total() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Data da Sessão</th>
                                            <th>Demanda</th>
                                            <th>Profissional</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($medicalRecords as $record)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-info">{{ $record->session_date ? $record->session_date->format('d/m/Y') : 'N/D' }}</span>
                                                </td>
                                                <td>{{ \Illuminate\Support\Str::limit($record->complaint, 60) }}</td>
                                                <td>{{ $record->creator->name ?? 'N/D' }}</td>
                                                <td>
                                                    <a href="{{ route('medical-records.show', $record) }}"
                                                       class="btn btn-sm btn-primary">
                                                        <i class="bi bi-eye"></i> Ver
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">
                                    Exibindo {{ $medicalRecords->firstItem() }}–{{ $medicalRecords->lastItem() }} de {{ $medicalRecords->total() }}
                                </small>
                                {{ $medicalRecords->onEachSide(1)->appends(request()->query())->links() }}
                            </div>
                        @else
                            <p class="text-muted mb-0"><i class="bi bi-info-circle"></i> Nenhum prontuário registrado para este paciente.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Botões de Ação -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <a href="{{ route('kids.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                @can('kid-edit')
                    <a href="{{ route('kids.edit', $kid->id) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                @endcan
            </div>
        </div>
    </div>

@endsection
