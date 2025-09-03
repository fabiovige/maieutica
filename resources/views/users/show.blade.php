@extends('layouts.app')

@section('title')
    Visualizar Usuário
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('users.index') }}">
            <i class="bi bi-people"></i> Usuários
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
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-dark">
                        <i class="bi bi-person-circle"></i> Informações do Usuário
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            @if ($user->avatar && file_exists(public_path('images/avatars/' . $user->avatar)))
                                <img src="{{ asset('images/avatars/' . $user->avatar) }}" alt="{{ $user->name }}" 
                                     class="img-fluid rounded-circle" 
                                     style="object-fit: cover;">
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto"
                                     style="width: 100px; height: 100px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <span class="text-white fw-bold" style="font-size: 2rem;">
                                        {{ $user->getInitials() }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-10">
                            <h4>{{ $user->name }}</h4>
                            <p class="text-muted mb-0">
                                @foreach($user->roles as $role)
                                    <span class="badge bg-info me-2">{{ ucfirst($role->name) }}</span>
                                @endforeach
                                @if ($user->allow)
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
                                <dd class="col-sm-8">{{ $user->name }}</dd>

                                <dt class="col-sm-4">E-mail:</dt>
                                <dd class="col-sm-8">
                                    <a href="mailto:{{ $user->email }}">
                                        {{ $user->email }}
                                    </a>
                                </dd>

                                <dt class="col-sm-4">Telefone:</dt>
                                <dd class="col-sm-8">
                                    @if($user->phone)
                                        <a href="tel:{{ $user->phone }}">
                                            {{ $user->phone }}
                                        </a>
                                    @else
                                        N/D
                                    @endif
                                </dd>
                            </dl>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted">Informações da Conta</h6>
                            <dl class="row">
                                <dt class="col-sm-4">Perfis:</dt>
                                <dd class="col-sm-8">
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-secondary me-1">{{ ucfirst($role->name) }}</span>
                                    @endforeach
                                </dd>

                                <dt class="col-sm-4">Status:</dt>
                                <dd class="col-sm-8">
                                    @if ($user->allow)
                                        <span class="text-success"><i class="bi bi-check-circle"></i> Ativo</span>
                                    @else
                                        <span class="text-danger"><i class="bi bi-x-circle"></i> Inativo</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Último login:</dt>
                                <dd class="col-sm-8">
                                    {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca' }}
                                </dd>
                            </dl>
                        </div>
                    </div>

                    @if($user->created_at || $user->updated_at)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> Cadastrado em: {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/D' }}
                                    @if($user->updated_at)
                                        | <i class="bi bi-pencil"></i> Última atualização: {{ $user->updated_at->format('d/m/Y H:i') }}
                                    @endif
                                </small>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                        <div>
                            @can('edit users')
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-dark">
                        <i class="bi bi-shield-lock"></i> Permissões
                    </h6>
                </div>
                <div class="card-body">
                    @if($user->getAllPermissions()->count() > 0)
                        <div class="row">
                            @foreach($user->getAllPermissions()->take(20) as $permission)
                                <div class="col-12 mb-1">
                                    <small><i class="bi bi-check-circle text-success"></i> {{ $permission->name }}</small>
                                </div>
                            @endforeach
                        </div>
                        @if($user->getAllPermissions()->count() > 20)
                            <div class="mt-2 text-center">
                                <small class="text-muted">
                                    E mais {{ $user->getAllPermissions()->count() - 20 }} permissão(ões)
                                </small>
                            </div>
                        @endif
                    @else
                        <p class="text-muted text-center mb-0">
                            <i class="bi bi-info-circle"></i> Nenhuma permissão atribuída
                        </p>
                    @endif
                </div>
                <div class="card-footer text-center">
                    <small class="text-muted">
                        Total: {{ $user->getAllPermissions()->count() }} permissão(ões)
                    </small>
                </div>
            </div>

            @if($user->hasRole(['professional']))
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 text-dark">
                            <i class="bi bi-person-vcard"></i> Dados Profissionais
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $professional = $user->professionals->first();
                        @endphp
                        @if($professional)
                            <dl class="row mb-0">
                                <dt class="col-sm-5">Registro:</dt>
                                <dd class="col-sm-7">{{ $professional->registration_number }}</dd>

                                <dt class="col-sm-5">Especialidade:</dt>
                                <dd class="col-sm-7">{{ $professional->specialty->name }}</dd>

                                <dt class="col-sm-5">Crianças:</dt>
                                <dd class="col-sm-7">{{ $professional->kids->count() }}</dd>
                            </dl>
                            <div class="mt-2">
                                <a href="{{ route('professionals.show', $professional->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver Perfil Profissional
                                </a>
                            </div>
                        @else
                            <p class="text-muted mb-0">Perfil profissional não encontrado</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection