@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@push('styles')
<style>
    .stat-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.10) !important;
    }
    .stat-icon {
        width: 46px;
        height: 46px;
        min-width: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }
    .chart-card {
        border-radius: 12px;
    }
    .ranking-item {
        border-radius: 8px;
        transition: background 0.15s;
    }
    .ranking-item:hover { background: #f8fafc; }
    .rank-badge {
        width: 28px;
        height: 28px;
        min-width: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
    }
</style>
@endpush

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Home</li>
        </ol>
    </nav>
@endsection

@section('content')

    {{-- Dashboard para Pacientes --}}
    @if(isset($isPatient) && $isPatient)
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 h-100 shadow-sm stat-card" style="background:#e8f0fe;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:#4285f4;">
                            <i class="bi bi-file-medical-fill text-white"></i>
                        </div>
                        <div>
                            <div class="small text-muted">Meus Prontuários</div>
                            <div class="fs-4 fw-bold">{{ $totalMedicalRecords }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-8">
                <div class="card border-0 h-100 shadow-sm stat-card">
                    <div class="card-body p-4">
                        <h3 class="mb-2"><i class="bi bi-person-circle text-primary"></i> Bem-vindo(a), {{ auth()->user()->name }}!</h3>
                        <p class="text-muted mb-3">Este é seu espaço para acompanhar suas consultas e evolução do tratamento.</p>
                        <a href="{{ route('medical-records.index') }}" class="btn btn-primary">
                            <i class="bi bi-eye"></i> Ver Todos os Prontuários
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if($latestMedicalRecords->isNotEmpty())
            <div class="card shadow-sm border-0" style="border-radius:12px;">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Últimos Registros</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Data da Consulta</th>
                                    <th>Profissional</th>
                                    <th class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestMedicalRecords as $record)
                                    <tr>
                                        <td><i class="bi bi-calendar3 text-muted me-1"></i>{{ $record->session_date_formatted ?? 'N/D' }}</td>
                                        <td><i class="bi bi-person-badge text-primary me-1"></i>{{ $record->creator->name ?? 'N/D' }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('medical-records.pdf', $record) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="bi bi-file-pdf"></i> Ver PDF
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info border-0 shadow-sm" style="border-radius:12px;">
                <i class="bi bi-info-circle"></i> Você ainda não possui prontuários registrados.
            </div>
        @endif

    @else
    {{-- Dashboard Admin / Profissional / Responsável --}}

        {{-- ── Stat Cards ── --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl">
                <div class="card border-0 shadow-sm stat-card h-100" style="background:#fef3c7;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:#d97706;">
                            <i class="bi bi-person-lines-fill text-white"></i>
                        </div>
                        <div>
                            <div class="small" style="color:#5f6368;">Total Pacientes</div>
                            <div class="fs-4 fw-bold" style="color:#202124;">{{ $totalKids }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl">
                <div class="card border-0 shadow-sm stat-card h-100" style="background:#e8f0fe;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:#4285f4;">
                            <i class="bi bi-people-fill text-white"></i>
                        </div>
                        <div>
                            <div class="small" style="color:#5f6368;">Crianças</div>
                            <div class="fs-4 fw-bold" style="color:#202124;">{{ $totalChildren }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl">
                <div class="card border-0 shadow-sm stat-card h-100" style="background:#f3e8fe;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:#7c3aed;">
                            <i class="bi bi-person-fill text-white"></i>
                        </div>
                        <div>
                            <div class="small" style="color:#5f6368;">Adultos</div>
                            <div class="fs-4 fw-bold" style="color:#202124;">{{ $totalAdults }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl">
                <div class="card border-0 shadow-sm stat-card h-100" style="background:#e6f4ea;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:#34a853;">
                            <i class="bi bi-clipboard2-check-fill text-white"></i>
                        </div>
                        <div>
                            <div class="small" style="color:#5f6368;">Checklists</div>
                            <div class="fs-4 fw-bold" style="color:#202124;">{{ $totalChecklists }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Listas de Crianças ── --}}
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-with-checklists" data-bs-toggle="tab" data-bs-target="#pane-with-checklists" type="button" role="tab">
                    <i class="bi bi-clipboard2-check text-success"></i> Crianças com Checklists
                    <span class="badge bg-success ms-1">{{ $kidsWithChecklists->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-without-checklists" data-bs-toggle="tab" data-bs-target="#pane-without-checklists" type="button" role="tab">
                    <i class="bi bi-clipboard2-x text-warning"></i> Crianças sem Checklists
                    <span class="badge bg-warning text-dark ms-1">{{ $kidsWithoutChecklists->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-adults" data-bs-toggle="tab" data-bs-target="#pane-adults" type="button" role="tab">
                    <i class="bi bi-person-fill text-purple"></i> Pacientes Adultos
                    <span class="badge bg-purple ms-1" style="background-color:#7c3aed !important;">{{ $adultPatients->count() }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content">
            {{-- Tab Crianças COM checklists --}}
            <div class="tab-pane fade show active" id="pane-with-checklists" role="tabpanel">
                @if($kidsWithChecklists->isNotEmpty())
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th style="width:120px;">Idade</th>
                                <th style="width:140px;">Progresso</th>
                                <th style="width:120px;" class="text-center">Checklists</th>
                                <th style="width:80px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kidsWithChecklists as $kid)
                                <tr>
                                    <td>{{ $kid->name }}</td>
                                    <td><small class="text-muted">{{ $kid->age ?? 'N/D' }}</small></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:6px;border-radius:4px;">
                                                <div class="progress-bar"
                                                     style="width:{{ $kid->progress }}%; background-color:{{ get_progress_color($kid->progress) }} !important;"
                                                     role="progressbar"
                                                     aria-valuenow="{{ $kid->progress }}"
                                                     aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="small fw-bold" style="color:{{ get_progress_color($kid->progress) }};">{{ $kid->progress }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">{{ $kid->checklists_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        @can('kid-show')
                                            <a href="{{ route('kids.show', $kid->id) }}" class="btn btn-secondary btn-sm">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-light mt-3 mb-0">
                        <i class="bi bi-info-circle"></i> Nenhuma criança com checklist.
                    </div>
                @endif
            </div>

            {{-- Tab Crianças SEM checklists --}}
            <div class="tab-pane fade" id="pane-without-checklists" role="tabpanel">
                @if($kidsWithoutChecklists->isNotEmpty())
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th style="width:120px;">Idade</th>
                                <th style="width:80px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kidsWithoutChecklists as $kid)
                                <tr>
                                    <td>{{ $kid->name }}</td>
                                    <td><small class="text-muted">{{ $kid->age ?? 'N/D' }}</small></td>
                                    <td class="text-center">
                                        @can('kid-show')
                                            <a href="{{ route('kids.show', $kid->id) }}" class="btn btn-secondary btn-sm">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-light mt-3 mb-0">
                        <i class="bi bi-check-circle text-success"></i> Todas as crianças possuem checklists!
                    </div>
                @endif
            </div>

            {{-- Tab Pacientes Adultos --}}
            <div class="tab-pane fade" id="pane-adults" role="tabpanel">
                @if($adultPatients->isNotEmpty())
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th style="width:120px;">Idade</th>
                                <th style="width:80px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($adultPatients as $adult)
                                <tr>
                                    <td>{{ $adult->name }}</td>
                                    <td><small class="text-muted">{{ $adult->age ?? 'N/D' }}</small></td>
                                    <td class="text-center">
                                        @can('kid-show')
                                            <a href="{{ route('kids.show', $adult->id) }}" class="btn btn-secondary btn-sm">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-light mt-3 mb-0">
                        <i class="bi bi-info-circle"></i> Nenhum paciente adulto cadastrado.
                    </div>
                @endif
            </div>
        </div>

    @endif

@endsection

