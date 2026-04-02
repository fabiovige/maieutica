@extends('layouts.app')

@section('title')
    Prontuário
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('medical-records.index') }}">
            <i class="bi bi-file-medical"></i> Prontuários
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Visualizar</li>
@endsection

@section('content')

    {{-- Header: paciente + sessão + ações --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
        <div class="card-body px-4 py-3">
            <div class="d-flex align-items-start gap-3 flex-wrap">

                {{-- Info principal --}}
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="fw-semibold fs-5 text-dark">{{ $medicalRecord->patient_name }}</span>
                        @if($medicalRecord->patient_type === 'App\Models\Kid')
                            <span class="badge bg-primary-subtle text-primary-emphasis">
                                <i class="bi bi-person-hearts"></i> Criança
                            </span>
                            @if($medicalRecord->patient)
                                <span class="text-muted small">{{ $medicalRecord->patient->age ?? '' }}</span>
                            @endif
                        @else
                            <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                <i class="bi bi-person"></i> Adulto
                            </span>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap gap-3 text-muted small">
                        <span><i class="bi bi-calendar3 me-1"></i>Sessão: <strong>{{ $medicalRecord->session_date ? $medicalRecord->session_date->format('d/m/Y') : 'N/D' }}</strong></span>
                        <span><i class="bi bi-person-badge me-1"></i>{{ $medicalRecord->creator->name ?? 'N/D' }}</span>
                        @if($medicalRecord->creator && $medicalRecord->creator->professional?->first())
                            <span class="text-muted">{{ $medicalRecord->creator->professional->first()->full_registration }}</span>
                        @endif
                        <span><i class="bi bi-clock me-1"></i>{{ $medicalRecord->created_at?->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                {{-- Botão Voltar --}}
                <div class="flex-shrink-0">
                    <a href="{{ route('medical-records.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>

        {{-- Rodapé: ações --}}
        <div class="card-footer bg-transparent border-top px-4 py-2 d-flex flex-wrap gap-2">
            @can('view', $medicalRecord)
                <a href="{{ route('medical-records.pdf', $medicalRecord) }}" class="btn btn-primary btn-sm" target="_blank">
                    <i class="bi bi-file-pdf"></i> Download PDF
                </a>
            @endcan
            @if(auth()->user()->can('medical-record-edit-all') || (int)$medicalRecord->created_by === (int)auth()->id())
                @can('update', $medicalRecord)
                    <a href="{{ route('medical-records.edit', $medicalRecord) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                @endcan
                @can('delete', $medicalRecord)
                    <form action="{{ route('medical-records.destroy', $medicalRecord) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Mover este prontuário para a lixeira?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash"></i> Excluir
                        </button>
                    </form>
                @endcan
            @endif
        </div>
    </div>

    {{-- Conteúdo do Prontuário --}}
    <div class="d-flex flex-column gap-3 mb-4">

        <div class="card border-0 shadow-sm" style="border-radius:12px;">
            <div class="card-header bg-transparent border-bottom fw-semibold">
                <i class="bi bi-file-text text-primary me-2"></i>Demanda
            </div>
            <div class="card-body">
                <p class="mb-0" style="white-space:pre-wrap;">{{ $medicalRecord->complaint }}</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius:12px;">
            <div class="card-header bg-transparent border-bottom fw-semibold">
                <i class="bi bi-bullseye text-primary me-2"></i>Objetivo e Técnica Utilizada
            </div>
            <div class="card-body">
                <p class="mb-0" style="white-space:pre-wrap;">{{ $medicalRecord->objective_technique }}</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius:12px;">
            <div class="card-header bg-transparent border-bottom fw-semibold">
                <i class="bi bi-journal-text text-primary me-2"></i>Registro de Evolução
            </div>
            <div class="card-body">
                <p class="mb-0" style="white-space:pre-wrap;">{{ $medicalRecord->evolution_notes }}</p>
            </div>
        </div>

        @if($medicalRecord->referral_closure)
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-header bg-transparent border-bottom fw-semibold">
                    <i class="bi bi-arrow-right-circle text-primary me-2"></i>Encaminhamento ou Encerramento
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space:pre-wrap;">{{ $medicalRecord->referral_closure }}</p>
                </div>
            </div>
        @endif

    </div>

    {{-- Outros registros do mesmo paciente --}}
    @if(isset($patientRecords) && $patientRecords->count() > 0)
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-header bg-transparent border-bottom fw-semibold">
                <i class="bi bi-clock-history text-primary me-2"></i>
                Outros Registros deste Paciente ({{ $patientRecords->count() }})
            </div>
            <div class="card-body p-3">
                <div class="d-flex flex-column gap-2">
                    @foreach($patientRecords as $record)
                        <div class="d-flex align-items-center gap-3 px-2 py-2 rounded" style="background:#f8fafc;">
                            <span class="badge bg-info-subtle text-info-emphasis">
                                <i class="bi bi-calendar3 me-1"></i>{{ $record->session_date ? $record->session_date->format('d/m/Y') : 'N/D' }}
                            </span>
                            <span class="text-muted small text-truncate flex-grow-1">
                                {{ \Illuminate\Support\Str::limit(strip_tags($record->complaint), 80) }}
                            </span>
                            <span class="text-muted small">{{ $record->creator->name ?? 'N/D' }}</span>
                            <a href="{{ route('medical-records.show', $record) }}" class="btn btn-secondary btn-sm flex-shrink-0">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

@endsection
