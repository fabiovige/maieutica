@extends('layouts.app')

@section('title')
    Pacientes - {{ $professional->user->first()->name ?? 'Profissional' }}
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('professionals.index') }}">
            <i class="bi bi-person-vcard"></i> Profissionais
        </a>
    </li>
    <li class="breadcrumb-item">
        {{ $professional->user->first()->name ?? 'Profissional' }}
    </li>
    <li class="breadcrumb-item active">
        <i class="bi bi-people"></i> Pacientes
    </li>
@endsection

@section('content')

    {{-- Resumo --}}
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="card border-0 shadow-sm" style="background:#e8f0fe; border-radius:12px;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#4285f4;">
                        <i class="bi bi-person-hearts text-white"></i>
                    </div>
                    <div>
                        <div class="small" style="color:#5f6368;">Criancas</div>
                        <div class="fs-4 fw-bold" style="color:#202124;">{{ $children->count() }}</div>
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
                        <div class="fs-4 fw-bold" style="color:#202124;">{{ $adults->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-children" data-bs-toggle="tab" data-bs-target="#pane-children" type="button" role="tab">
                <i class="bi bi-person-hearts text-primary"></i> Criancas
                <span class="badge bg-primary ms-1">{{ $children->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-adults" data-bs-toggle="tab" data-bs-target="#pane-adults" type="button" role="tab">
                <i class="bi bi-person" style="color:#7c3aed;"></i> Adultos
                <span class="badge ms-1" style="background:#7c3aed;">{{ $adults->count() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- Tab Criancas --}}
        <div class="tab-pane fade show active" id="pane-children" role="tabpanel">
            @if($children->isNotEmpty())
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th style="width:120px;">Idade</th>
                            <th style="width:80px;" class="text-center">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($children as $patient)
                            <tr>
                                <td>{{ $patient->name }}</td>
                                <td><small class="text-muted">{{ $patient->age ?? 'N/D' }}</small></td>
                                <td class="text-center">
                                    @can('kid-show')
                                        <a href="{{ route('kids.show', $patient->id) }}" class="btn btn-secondary btn-sm">
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
                    <i class="bi bi-info-circle"></i> Nenhuma crianca atribuida.
                </div>
            @endif
        </div>

        {{-- Tab Adultos --}}
        <div class="tab-pane fade" id="pane-adults" role="tabpanel">
            @if($adults->isNotEmpty())
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th style="width:120px;">Idade</th>
                            <th style="width:80px;" class="text-center">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($adults as $patient)
                            <tr>
                                <td>{{ $patient->name }}</td>
                                <td><small class="text-muted">{{ $patient->age ?? 'N/D' }}</small></td>
                                <td class="text-center">
                                    @can('kid-show')
                                        <a href="{{ route('kids.show', $patient->id) }}" class="btn btn-secondary btn-sm">
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
                    <i class="bi bi-info-circle"></i> Nenhum adulto atribuido.
                </div>
            @endif
        </div>
    </div>

@endsection
