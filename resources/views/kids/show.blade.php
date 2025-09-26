@extends('layouts.app')

@section('title')
    Visualizar Criança
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('kids.index') }}">
            <i class="bi bi-people"></i> Crianças
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        Visualizar
    </li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 mb-3">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-dark">
                        <i class="bi bi-person"></i> Informações da Criança
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            @if ($kid->photo)
                                <img src="{{ asset($kid->photo) }}" class="img-fluid rounded-circle" width="100"
                                    height="100" alt="{{ safe_attribute($kid->name) }}" style="object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-info d-flex align-items-center justify-content-center mx-auto"
                                    style="width: 100px; height: 100px">
                                    <i class="bi bi-person text-white" style="font-size: 3rem;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-10">
                            <h4>{{ $kid->name }}</h4>
                            <p class="text-muted mb-0">
                                @if ($kid->age)
                                    <span class="badge bg-info me-2">{{ $kid->age }}</span>
                                @endif
                                @if ($kid->birth_date)
                                    <span class="badge bg-secondary me-2">{{ $kid->birth_date_formatted }}</span>
                                @endif
                                <span class="badge bg-success">{{ $kid->checklists->count() }} checklists</span>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Dados Pessoais</h6>
                            <dl class="row">
                                <dt class="col-sm-4">Nome:</dt>
                                <dd class="col-sm-8">{{ $kid->name }}</dd>

                                <dt class="col-sm-4">Data Nasc.:</dt>
                                <dd class="col-sm-8">{{ $kid->birth_date_formatted }}</dd>

                                <dt class="col-sm-4">Idade:</dt>
                                <dd class="col-sm-8">{{ $kid->age ?? 'N/D' }}</dd>

                                @if ($kid->gender)
                                    <dt class="col-sm-4">Gênero:</dt>
                                    <dd class="col-sm-8">{{ $kid->gender_formatted }}</dd>
                                @endif
                            </dl>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted">Responsável Legal</h6>
                            <dl class="row">
                                <dt class="col-sm-4">Nome:</dt>
                                <dd class="col-sm-8">
                                    @if ($kid->responsible)
                                        {{ $kid->responsible->name }}
                                    @else
                                        N/D
                                    @endif
                                </dd>

                                @if ($kid->responsible && $kid->responsible->email)
                                    <dt class="col-sm-4">E-mail:</dt>
                                    <dd class="col-sm-8">
                                        <a href="mailto:{{ $kid->responsible->email }}">
                                            {{ $kid->responsible->email }}
                                        </a>
                                    </dd>
                                @endif

                                @if ($kid->responsible && $kid->responsible->phone)
                                    <dt class="col-sm-4">Telefone:</dt>
                                    <dd class="col-sm-8">
                                        <a href="tel:{{ $kid->responsible->phone }}">
                                            {{ $kid->responsible->phone }}
                                        </a>
                                    </dd>
                                @endif
                            </dl>

                            <h6 class="text-muted mt-3">Profissional Responsável</h6>
                            <dl class="row">
                                @php
                                    $mainProfessional = $kid->professionals->first();
                                @endphp
                                @if ($mainProfessional)
                                    <dt class="col-sm-4">Nome:</dt>
                                    <dd class="col-sm-8">{{ $mainProfessional->user->first()->name ?? 'N/D' }}</dd>

                                    <dt class="col-sm-4">Especialidade:</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge bg-info">{{ $mainProfessional->specialty->name }}</span>
                                    </dd>

                                    <dt class="col-sm-4">Registro:</dt>
                                    <dd class="col-sm-8">{{ $mainProfessional->registration_number }}</dd>

                                    @if ($mainProfessional->user->first() && $mainProfessional->user->first()->email)
                                        <dt class="col-sm-4">E-mail:</dt>
                                        <dd class="col-sm-8">
                                            <a href="mailto:{{ $mainProfessional->user->first()->email }}">
                                                {{ $mainProfessional->user->first()->email }}
                                            </a>
                                        </dd>
                                    @endif
                                @else
                                    <dt class="col-sm-4">Status:</dt>
                                    <dd class="col-sm-8">
                                        <span class="text-muted">Nenhum profissional associado</span>
                                    </dd>
                                @endif
                            </dl>
                        </div>
                    </div>

                    @if ($kid->address)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-muted">Endereço</h6>
                                <p>{{ $kid->address }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($kid->created_at || $kid->updated_at)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> Cadastrado em:
                                    {{ $kid->created_at ? $kid->created_at->format('d/m/Y H:i') : 'N/D' }}
                                    @if ($kid->updated_at)
                                        | <i class="bi bi-pencil"></i> Última atualização:
                                        {{ $kid->updated_at->format('d/m/Y H:i') }}
                                    @endif
                                </small>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('kids.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                        <div>
                            @can('edit kids')
                                <a href="{{ route('kids.edit', $kid->id) }}" class="btn btn-primary me-2">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-dark">
                        <i class="bi bi-card-checklist"></i> Checklists
                    </h6>
                </div>
                <div class="card-body">
                    @if ($kid->checklists->count() > 0)
                        <div class="list-group">
                            @foreach ($kid->checklists->take(10) as $checklist)
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Checklist #{{ $checklist->id }}</h6>
                                        <small>{{ $checklist->created_at->format('d/m/Y') }}</small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span
                                            class="badge {{ $checklist->situation_label === 'Aberto' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $checklist->situation_label }}
                                        </span>
                                        <small class="text-muted">{{ $checklist->developmentPercentage }}%
                                            concluído</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if ($kid->checklists->count() > 10)
                            <div class="mt-2 text-center">
                                <small class="text-muted">
                                    E mais {{ $kid->checklists->count() - 10 }} checklist(s)
                                </small>
                            </div>
                        @endif
                    @else
                        <p class="text-muted text-center mb-0">
                            <i class="bi bi-info-circle"></i> Nenhum checklist criado
                        </p>
                    @endif
                </div>
                <div class="card-footer text-center">
                    <small class="text-muted">
                        Total: {{ $kid->checklists->count() }} checklist(s)
                    </small>
                    <div class="mt-2">
                        <a href="{{ route('checklists.index', ['kidId' => $kid->id]) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-list"></i> Ver Todos
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
