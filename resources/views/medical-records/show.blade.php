@extends('layouts.app')

@section('title')
    Prontuário Médico
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('medical-records.index') }}">
            <i class="bi bi-file-medical"></i> Prontuários
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Visualizar</li>
@endsection

@section('actions')
    <div class="d-flex gap-2">
        @can('view', $medicalRecord)
            <a href="{{ route('medical-records.pdf', $medicalRecord) }}" class="btn btn-outline-primary">
                <i class="bi bi-file-pdf"></i> Download PDF
            </a>
        @endcan

        @can('update', $medicalRecord)
            <a href="{{ route('medical-records.edit', $medicalRecord) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> Editar
            </a>
        @endcan

        @can('delete', $medicalRecord)
            <form action="{{ route('medical-records.destroy', $medicalRecord) }}"
                method="POST"
                class="d-inline"
                onsubmit="return confirm('Tem certeza que deseja mover este prontuário para a lixeira?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i> Excluir
                </button>
            </form>
        @endcan
    </div>
@endsection

@section('content')

    {{-- Informações do Paciente --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="bi bi-person"></i> Informações do Paciente</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Tipo:</strong>
                        @if($medicalRecord->patient_type === 'App\Models\Kid')
                            <span class="badge bg-info">Criança</span>
                        @else
                            <span class="badge bg-secondary">Adulto</span>
                        @endif
                    </div>
                    <div class="mb-2">
                        <strong>Nome:</strong> {{ $medicalRecord->patient_name }}
                    </div>

                    @if($medicalRecord->patient_type === 'App\Models\Kid' && $medicalRecord->patient)
                        <div class="mb-2">
                            <strong>Idade:</strong> {{ $medicalRecord->patient->age ?? 'N/D' }}
                        </div>
                        <div class="mb-2">
                            <strong>Nascimento:</strong> {{ $medicalRecord->patient->birth_date ?? 'N/D' }}
                        </div>
                        @if($medicalRecord->patient->responsible)
                            <div class="mb-2">
                                <strong>Responsável:</strong> {{ $medicalRecord->patient->responsible->name }}
                            </div>
                        @endif
                        @if($medicalRecord->patient->professionals && $medicalRecord->patient->professionals->count() > 0)
                            <div class="mb-2">
                                <strong>Profissionais:</strong>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach($medicalRecord->patient->professionals as $professional)
                                        <span class="badge bg-info text-dark">
                                            {{ $professional->user->first()->name ?? 'N/D' }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif

                    @if($medicalRecord->patient_type === 'App\Models\User' && $medicalRecord->patient)
                        <div class="mb-2">
                            <strong>Email:</strong> {{ $medicalRecord->patient->email ?? 'N/D' }}
                        </div>
                        @if($medicalRecord->patient->phone)
                            <div class="mb-2">
                                <strong>Telefone:</strong> {{ $medicalRecord->patient->phone }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informações da Sessão</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Data da Sessão:</strong>
                        <span class="badge bg-info">{{ $medicalRecord->session_date }}</span>
                    </div>
                    <div class="mb-2">
                        <strong>Profissional:</strong> {{ $medicalRecord->creator->name ?? 'N/D' }}
                        @if($medicalRecord->creator && $medicalRecord->creator->professional && $medicalRecord->creator->professional->first())
                            <br>
                            <small class="text-muted">CRP: {{ $medicalRecord->creator->professional->first()->registration_number }}</small>
                        @endif
                    </div>
                    <div class="mb-2">
                        <strong>Criado em:</strong> {{ $medicalRecord->created_at ? $medicalRecord->created_at->format('d/m/Y H:i') : 'N/D' }}
                    </div>
                    @if($medicalRecord->updated_at && $medicalRecord->updated_at != $medicalRecord->created_at)
                        <div class="mb-2">
                            <strong>Última atualização:</strong> {{ $medicalRecord->updated_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Conteúdo do Prontuário --}}
    <div class="card mb-3">
        <div class="card-header bg-secondary text-white">
            <h6 class="mb-0"><i class="bi bi-file-text"></i> Demanda</h6>
        </div>
        <div class="card-body">
            <p class="mb-0" style="white-space: pre-wrap;">{{ $medicalRecord->complaint }}</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-secondary text-white">
            <h6 class="mb-0"><i class="bi bi-bullseye"></i> Objetivo e Técnica Utilizada</h6>
        </div>
        <div class="card-body">
            <p class="mb-0" style="white-space: pre-wrap;">{{ $medicalRecord->objective_technique }}</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-secondary text-white">
            <h6 class="mb-0"><i class="bi bi-journal-text"></i> Registro de Evolução</h6>
        </div>
        <div class="card-body">
            <p class="mb-0" style="white-space: pre-wrap;">{{ $medicalRecord->evolution_notes }}</p>
        </div>
    </div>

    @if($medicalRecord->referral_closure)
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="bi bi-arrow-right-circle"></i> Encaminhamento ou Encerramento</h6>
            </div>
            <div class="card-body">
                <p class="mb-0" style="white-space: pre-wrap;">{{ $medicalRecord->referral_closure }}</p>
            </div>
        </div>
    @endif

    {{-- Botão Voltar --}}
    <div class="mt-3">
        <a href="{{ route('medical-records.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar para Lista
        </a>
    </div>

@endsection
