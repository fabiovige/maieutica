@extends('layouts.app')

@section('title')
    Lixeira - Prontuários Médicos
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('medical-records.index') }}">
            <i class="bi bi-file-medical"></i> Prontuários
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Lixeira</li>
@endsection

@section('actions')
    <a href="{{ route('medical-records.index') }}" class="btn btn-primary">
        <i class="bi bi-arrow-left"></i> Voltar para Lista
    </a>
@endsection

@section('content')

    @if ($medicalRecords->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Nenhum prontuário na lixeira.
        </div>
    @else
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-trash"></i> Prontuários Excluídos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Paciente</th>
                                <th>Demanda</th>
                                <th>Excluído por</th>
                                <th>Excluído em</th>
                                <th class="text-end" style="width: 100px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($medicalRecords as $record)
                                <tr>
                                    {{-- Data da Sessão --}}
                                    <td>
                                        <span class="badge bg-primary">{{ $record->session_date }}</span>
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

                                    {{-- Demanda (truncada) --}}
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 300px;" 
                                              title="{{ $record->complaint }}">
                                            {{ Str::limit($record->complaint, 50) }}
                                        </span>
                                    </td>

                                    {{-- Excluído por --}}
                                    <td>{{ $record->deleter->name ?? 'N/D' }}</td>

                                    {{-- Excluído em --}}
                                    <td>{{ $record->deleted_at ? $record->deleted_at->format('d/m/Y H:i') : 'N/D' }}</td>

                                    {{-- Ações --}}
                                    <td class="text-end">
                                        @can('restore', $record)
                                            <form action="{{ route('medical-records.restore', $record->id) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Tem certeza que deseja restaurar este prontuário?');">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm" title="Restaurar">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                                </button>
                                            </form>
                                        @endcan
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
            {{ $medicalRecords->links() }}
        </div>
    @endif

@endsection
