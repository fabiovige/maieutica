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
        Visualizar - {{ $kid->name }}
    </li>
@endsection

@section('content')

    <!-- Card com Informações Básicas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-circle"></i> Informações da Criança</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Foto -->
                        <div class="col-md-3 text-center mb-3">
                            @if ($kid->photo)
                                <img src="{{ asset($kid->photo) }}"
                                     class="rounded-circle img-thumbnail"
                                     width="150"
                                     height="150"
                                     alt="{{ $kid->name }}"
                                     style="object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto text-white"
                                     style="width: 150px; height: 150px; font-size: 48px;">
                                    {{ substr($kid->name, 0, 2) }}
                                </div>
                            @endif
                        </div>

                        <!-- Dados Pessoais -->
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-person"></i> Nome:</strong><br>
                                    {{ $kid->name }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-calendar"></i> Data de Nascimento:</strong><br>
                                    {{ $kid->birth_date ?? 'N/D' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-hourglass-split"></i> Idade:</strong><br>
                                    {{ $kid->age ?? 'N/D' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-gender-ambiguous"></i> Gênero:</strong><br>
                                    {{ $kid->gender ?? 'N/D' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informações do Responsável -->
                    <hr>
                    <h6><i class="bi bi-person-badge"></i> Responsável</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Nome:</strong><br>
                            {{ $kid->responsible->name ?? 'N/D' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Email:</strong><br>
                            {{ $kid->responsible->email ?? 'N/D' }}
                        </div>
                        @if($kid->responsible && $kid->responsible->phone)
                        <div class="col-md-6 mb-3">
                            <strong>Telefone:</strong><br>
                            {{ $kid->responsible->phone }}
                        </div>
                        @endif
                    </div>

                    <!-- Profissionais Vinculados -->
                    <hr>
                    <h6><i class="bi bi-people"></i> Profissionais Vinculados</h6>
                    @if($kid->professionals && $kid->professionals->count() > 0)
                        <div class="row">
                            @foreach($kid->professionals as $professional)
                                <div class="col-md-6 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body p-2">
                                            <strong>{{ $professional->user->first()->name ?? 'N/D' }}</strong><br>
                                            <small class="text-muted">
                                                {{ $professional->specialty->name ?? 'Sem especialidade' }}
                                                @if($professional->registration_number)
                                                    - Registro: {{ $professional->registration_number }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Nenhum profissional vinculado</p>
                    @endif

                    <!-- Últimos Checklists -->
                    @if($kid->checklists && $kid->checklists->count() > 0)
                        <hr>
                        <h6><i class="bi bi-clipboard-check"></i> Últimos Checklists</h6>
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
                    @endif
                </div>
                <div class="card-footer d-flex justify-content-between">
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
    </div>

@endsection
