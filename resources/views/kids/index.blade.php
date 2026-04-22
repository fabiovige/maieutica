@extends('layouts.app')

@section('title')
    Pacientes
@endsection


@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-people"></i> Pacientes
    </li>
@endsection

@section('actions')
    @can('kid-create')
        <a href="{{ route('kids.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Novo Paciente
        </a>
    @endcan
@endsection

@section('content')

    @php
        $totalChildren = $childrenWithChecklists->total() + $childrenWithoutChecklists->total();
        $totalAdults   = $adults->total();
        $totalAll      = $totalChildren + $totalAdults;
        $activeTab     = request('tab', 'with');
    @endphp

    {{-- Filtro de Busca --}}
    <div class="card mb-3" style="border-radius:12px; border:1px solid #e9ecef;">
        <div class="card-body">
            <form method="GET" action="{{ route('kids.index') }}" class="row g-3" id="search-form">
                <input type="hidden" name="tab" id="search-tab" value="{{ $activeTab }}">
                <div class="col-md-10">
                    <label for="search" class="form-label">
                        <i class="bi bi-search"></i> Buscar Paciente
                    </label>
                    <input type="text"
                           class="form-control"
                           id="search"
                           name="search"
                           placeholder="Buscar por nome, responsável ou profissional..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        @if(request('search'))
                            <a href="{{ route('kids.index') }}" class="btn btn-secondary" title="Limpar filtro">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(request('search'))
        <div class="alert alert-info border-0 shadow-sm" style="border-radius:12px;">
            <i class="bi bi-info-circle"></i>
            Exibindo resultados para "<strong>{{ request('search') }}</strong>".
            <strong>{{ $totalAll }}</strong> paciente(s) encontrado(s).
        </div>
    @endif

    {{-- Resumo --}}
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="card border-0 shadow-sm" style="background:#e8f0fe; border-radius:12px;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#4285f4;">
                        <i class="bi bi-person-hearts text-white"></i>
                    </div>
                    <div>
                        <div class="small" style="color:#5f6368;">Crianças</div>
                        <div class="fs-4 fw-bold" style="color:#202124;">{{ $totalChildren }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card border-0 shadow-sm" style="background:#f3e8fe; border-radius:12px;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#7c3aed;">
                        <i class="bi bi-person text-white"></i>
                    </div>
                    <div>
                        <div class="small" style="color:#5f6368;">Adultos</div>
                        <div class="fs-4 fw-bold" style="color:#202124;">{{ $totalAdults }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'with' ? 'active' : '' }}" id="tab-with" data-bs-toggle="tab" data-bs-target="#pane-with" type="button" role="tab" data-tab="with">
                <i class="bi bi-clipboard2-check text-success"></i> Crianças com Checklists
                <span class="badge bg-success ms-1">{{ $childrenWithChecklists->total() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'without' ? 'active' : '' }}" id="tab-without" data-bs-toggle="tab" data-bs-target="#pane-without" type="button" role="tab" data-tab="without">
                <i class="bi bi-clipboard2-x text-warning"></i> Crianças sem Checklists
                <span class="badge bg-warning text-dark ms-1">{{ $childrenWithoutChecklists->total() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'adults' ? 'active' : '' }}" id="tab-adults" data-bs-toggle="tab" data-bs-target="#pane-adults" type="button" role="tab" data-tab="adults">
                <i class="bi bi-person-fill" style="color:#7c3aed;"></i> Pacientes Adultos
                <span class="badge ms-1" style="background:#7c3aed;">{{ $totalAdults }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- Tab Crianças COM checklists --}}
        <div class="tab-pane fade {{ $activeTab === 'with' ? 'show active' : '' }}" id="pane-with" role="tabpanel">
            @if($childrenWithChecklists->isNotEmpty())
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px;"></th>
                            <th>Nome</th>
                            <th style="width:120px;">Idade</th>
                            <th style="width:180px;">Progresso</th>
                            <th style="width:110px;" class="text-center">Checklists</th>
                            <th style="width:80px;" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($childrenWithChecklists as $kid)
                            <tr>
                                <td>
                                    @if($kid->photo)
                                        <img src="{{ asset($kid->photo) }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover;" alt="{{ $kid->name }}">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center" style="width:32px;height:32px;border-radius:50%;background:#e2e8f0;color:#94a3b8;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $kid->name }}</td>
                                <td><small class="text-muted">{{ $kid->age ?? 'N/D' }}</small></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:6px;border-radius:4px;">
                                            <div class="progress-bar"
                                                 role="progressbar"
                                                 style="width:{{ $kid->progress_percentage ?? 0 }}%; background-color:{{ get_progress_color($kid->progress_percentage ?? 0) }} !important;"
                                                 aria-valuenow="{{ $kid->progress_percentage ?? 0 }}"
                                                 aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <span class="small fw-semibold" style="min-width:36px;">{{ $kid->progress_percentage ?? 0 }}%</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">{{ $kid->checklists_count }}</span>
                                </td>
                                <td class="text-center">
                                    @if(auth()->user()->can('kid-show') || auth()->user()->id === $kid->responsible_id)
                                        <a href="{{ route('kids.show', $kid->id) }}" class="btn btn-secondary btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    {{ $childrenWithChecklists->onEachSide(1)->appends(array_merge(request()->query(), ['tab' => 'with']))->links() }}
                </div>
            @else
                <div class="alert alert-light mt-3 mb-0">
                    <i class="bi bi-info-circle"></i> Nenhuma criança com checklist.
                </div>
            @endif
        </div>

        {{-- Tab Crianças SEM checklists --}}
        <div class="tab-pane fade {{ $activeTab === 'without' ? 'show active' : '' }}" id="pane-without" role="tabpanel">
            @if($childrenWithoutChecklists->isNotEmpty())
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px;"></th>
                            <th>Nome</th>
                            <th style="width:120px;">Idade</th>
                            <th style="width:80px;" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($childrenWithoutChecklists as $kid)
                            <tr>
                                <td>
                                    @if($kid->photo)
                                        <img src="{{ asset($kid->photo) }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover;" alt="{{ $kid->name }}">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center" style="width:32px;height:32px;border-radius:50%;background:#e2e8f0;color:#94a3b8;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $kid->name }}</td>
                                <td><small class="text-muted">{{ $kid->age ?? 'N/D' }}</small></td>
                                <td class="text-center">
                                    @if(auth()->user()->can('kid-show') || auth()->user()->id === $kid->responsible_id)
                                        <a href="{{ route('kids.show', $kid->id) }}" class="btn btn-secondary btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    {{ $childrenWithoutChecklists->onEachSide(1)->appends(array_merge(request()->query(), ['tab' => 'without']))->links() }}
                </div>
            @else
                <div class="alert alert-light mt-3 mb-0">
                    <i class="bi bi-check-circle text-success"></i> Todas as crianças possuem checklists.
                </div>
            @endif
        </div>

        {{-- Tab Pacientes Adultos --}}
        <div class="tab-pane fade {{ $activeTab === 'adults' ? 'show active' : '' }}" id="pane-adults" role="tabpanel">
            @if($adults->isNotEmpty())
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px;"></th>
                            <th>Nome</th>
                            <th style="width:120px;">Idade</th>
                            <th style="width:80px;" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($adults as $kid)
                            <tr>
                                <td>
                                    @if($kid->photo)
                                        <img src="{{ asset($kid->photo) }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover;" alt="{{ $kid->name }}">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center" style="width:32px;height:32px;border-radius:50%;background:#e2e8f0;color:#94a3b8;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $kid->name }}</td>
                                <td><small class="text-muted">{{ $kid->age ?? 'N/D' }}</small></td>
                                <td class="text-center">
                                    @if(auth()->user()->can('kid-show') || auth()->user()->id === $kid->responsible_id)
                                        <a href="{{ route('kids.show', $kid->id) }}" class="btn btn-secondary btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    {{ $adults->onEachSide(1)->appends(array_merge(request()->query(), ['tab' => 'adults']))->links() }}
                </div>
            @else
                <div class="alert alert-light mt-3 mb-0">
                    <i class="bi bi-info-circle"></i> Nenhum adulto encontrado.
                </div>
            @endif
        </div>
    </div>

@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function(tab) {
        tab.addEventListener('shown.bs.tab', function(e) {
            document.getElementById('search-tab').value = e.target.dataset.tab || 'with';
        });
    });
</script>
@endpush
