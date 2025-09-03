@extends('layouts.app')

@section('title')
    Visualizar Profissional
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('professionals.index') }}">
            <i class="bi bi-person-vcard"></i> Profissionais
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        Visualizar
    </li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary">
                    <h5 class="mb-0 text-dark">
                        <i class="bi bi-person-vcard"></i> Informações do Profissional
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            @if ($professional->user->first() && $professional->user->first()->avatar)
                                <img src="{{ asset('storage/' . $professional->user->first()->avatar) }}"
                                    class="img-fluid rounded-circle" 
                                    alt="{{ $professional->user->first()->name }}">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto"
                                    style="width: 100px; height: 100px">
                                    <i class="bi bi-person text-white" style="font-size: 3rem;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-10">
                            <h4>{{ $professional->user->first() ? $professional->user->first()->name : 'Sem nome' }}</h4>
                            <p class="text-muted mb-0">
                                <span class="badge bg-info me-2">{{ $professional->specialty->name }}</span>
                                @if ($professional->user->first()?->allow)
                                    <span class="badge bg-success">Ativo</span>
                                @else
                                    <span class="badge bg-danger">Inativo</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Dados Pessoais</h6>
                            <dl class="row">
                                <dt class="col-sm-4">Nome:</dt>
                                <dd class="col-sm-8">{{ $professional->user->first() ? $professional->user->first()->name : 'N/D' }}</dd>

                                <dt class="col-sm-4">E-mail:</dt>
                                <dd class="col-sm-8">
                                    @if($professional->user->first())
                                        <a href="mailto:{{ $professional->user->first()->email }}">
                                            {{ $professional->user->first()->email }}
                                        </a>
                                    @else
                                        N/D
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Telefone:</dt>
                                <dd class="col-sm-8">
                                    @if($professional->user->first() && $professional->user->first()->phone)
                                        <a href="tel:{{ $professional->user->first()->phone }}">
                                            {{ $professional->user->first()->phone }}
                                        </a>
                                    @else
                                        N/D
                                    @endif
                                </dd>
                            </dl>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted">Dados Profissionais</h6>
                            <dl class="row">
                                <dt class="col-sm-4">Registro:</dt>
                                <dd class="col-sm-8">{{ $professional->registration_number }}</dd>

                                <dt class="col-sm-4">Especialidade:</dt>
                                <dd class="col-sm-8">{{ $professional->specialty->name }}</dd>

                                <dt class="col-sm-4">Status:</dt>
                                <dd class="col-sm-8">
                                    @if ($professional->user->first()?->allow)
                                        <span class="text-success"><i class="bi bi-check-circle"></i> Ativo</span>
                                    @else
                                        <span class="text-danger"><i class="bi bi-x-circle"></i> Inativo</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>

                    @if($professional->created_at || $professional->updated_at)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> Cadastrado em: {{ $professional->created_at ? $professional->created_at->format('d/m/Y H:i') : 'N/D' }}
                                    @if($professional->updated_at)
                                        | <i class="bi bi-pencil"></i> Última atualização: {{ $professional->updated_at->format('d/m/Y H:i') }}
                                    @endif
                                </small>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('professionals.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                        <div>
                            @can('edit professionals')
                                <a href="{{ route('professionals.edit', $professional->id) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                            @endcan
                            
                            @can('deactivate professionals')
                                @if ($professional->user->first()?->allow)
                                    <form action="{{ route('professionals.deactivate', $professional->id) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Tem certeza que deseja desativar este profissional?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-warning">
                                            <i class="bi bi-person-x"></i> Desativar
                                        </button>
                                    </form>
                                @endif
                            @endcan

                            @can('activate professionals')
                                @if (!$professional->user->first()?->allow)
                                    <form action="{{ route('professionals.activate', $professional->id) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Tem certeza que deseja ativar este profissional?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-person-check"></i> Ativar
                                        </button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-people"></i> Crianças Associadas
                    </h5>
                </div>
                <div class="card-body">
                    @if($professional->kids->count() > 0)
                        <div class="list-group">
                            @foreach($professional->kids->take(10) as $kid)
                                <a href="{{ route('kids.show', $kid->id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $kid->name }}</h6>
                                        <small>{{ $kid->age ?? 'N/D' }}</small>
                                    </div>
                                    <small>Responsável: {{ $kid->responsible->name ?? 'N/D' }}</small>
                                </a>
                            @endforeach
                        </div>
                        @if($professional->kids->count() > 10)
                            <div class="mt-2 text-center">
                                <small class="text-muted">
                                    E mais {{ $professional->kids->count() - 10 }} criança(s)
                                </small>
                            </div>
                        @endif
                    @else
                        <p class="text-muted text-center mb-0">
                            <i class="bi bi-info-circle"></i> Nenhuma criança associada
                        </p>
                    @endif
                </div>
                <div class="card-footer text-center">
                    <small class="text-muted">
                        Total: {{ $professional->kids->count() }} criança(s)
                    </small>
                </div>
            </div>

            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin'))
                <div class="card mt-3">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0 text-dark">
                            <i class="bi bi-shield-lock"></i> Informações do Usuário
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($professional->user->first())
                            <dl class="row mb-0">
                                <dt class="col-sm-4">ID:</dt>
                                <dd class="col-sm-8">{{ $professional->user->first()->id }}</dd>

                                <dt class="col-sm-4">Perfil:</dt>
                                <dd class="col-sm-8">
                                    @foreach($professional->user->first()->roles as $role)
                                        <span class="badge bg-secondary">{{ $role->name }}</span>
                                    @endforeach
                                </dd>

                                <dt class="col-sm-4">Último login:</dt>
                                <dd class="col-sm-8">
                                    {{ $professional->user->first()->last_login_at ? $professional->user->first()->last_login_at->format('d/m/Y H:i') : 'Nunca' }}
                                </dd>
                            </dl>
                        @else
                            <p class="text-muted mb-0">Sem usuário associado</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection