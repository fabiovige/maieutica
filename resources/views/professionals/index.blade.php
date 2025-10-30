@extends('layouts.app')

@section('title')
    Profissionais
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-person-vcard"></i> Profissionais
    </li>
@endsection

@section('actions')
    @can('professional-create')
        <a href="{{ route('professionals.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Novo Profissional
        </a>
    @endcan
@endsection

@section('content')
    <!-- Filtro de Busca -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('professionals.index') }}" class="row g-3">
                <div class="col-md-10">
                    <label for="search" class="form-label">
                        <i class="bi bi-search"></i> Buscar Profissional
                    </label>
                    <input type="text"
                           class="form-control"
                           id="search"
                           name="search"
                           placeholder="Buscar por nome, email, especialidade ou registro..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        @if(request('search'))
                            <a href="{{ route('professionals.index') }}" class="btn btn-secondary" title="Limpar filtro">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(request('search'))
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Exibindo resultados da busca por "<strong>{{ request('search') }}</strong>".
            <strong>{{ $professionals->total() }}</strong> profissional(is) encontrado(s).
        </div>
    @endif

    @if ($professionals->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Nenhum profissional encontrado.
        </div>
    @else
        <table class="table table-hover table-bordered align-middle mt-3">
        <thead>
            <tr>
                <th style="width: 60px;" class="text-center align-middle">ID</th>
                <th>Nome</th>
                <th>Especialidade</th>
                <th>Registro</th>
                <th>Contato</th>
                <th>Crianças</th>
                <th>Status</th>
                <th width="200">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($professionals as $professional)
                <tr>
                    <td class="text-center">{{ $professional->id }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            @if ($professional->user->first() && $professional->user->first()->avatar)
                                <img src="{{ asset('storage/' . $professional->user->first()->avatar) }}"
                                    class="rounded-circle me-2" width="40" height="40"
                                    alt="{{ $professional->user->first()->name }}">
                            @else
                                <div class="avatar-circle me-2">
                                    {{ $professional->user->first() ? substr($professional->user->first()->name, 0, 2) : 'NA' }}
                                </div>
                            @endif
                            {{ $professional->user->first() ? $professional->user->first()->name : 'Sem nome' }}
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-info">
                            {{ $professional->specialty->name }}
                        </span>
                    </td>
                    <td>{{ $professional->registration_number }}</td>
                    <td>
                        <div>{{ $professional->user->first() ? $professional->user->first()->email : 'N/D' }}</div>
                        <small
                            class="text-muted">{{ $professional->user->first() ? $professional->user->first()->phone : 'N/D' }}</small>
                    </td>
                    <td>
                        <span class="badge bg-info">
                            {{ $professional->kids->count() }} crianças
                        </span>
                    </td>
                    <td>
                        @if ($professional->user->first()?->allow)
                            <span class="badge bg-success">Ativo</span>
                        @else
                            <span class="badge bg-danger">Inativo</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group">
                            @can('professional-edit')
                                <a href="{{ route('professionals.edit', $professional->id) }}"
                                    class="btn btn-sm btn-primary me-2" title="Editar">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                            @endcan

                            @can('professional-deactivate')
                                @if ($professional->user->first()?->allow)
                                    <form action="{{ route('professionals.deactivate', $professional->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Tem certeza que deseja desativar este profissional?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Desativar">
                                            <i class="bi bi-person-x"></i> Desativar
                                        </button>
                                    </form>
                                @endif
                            @endcan

                            @can('professional-activate')
                                @if (!$professional->user->first()?->allow)
                                    <form action="{{ route('professionals.activate', $professional->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Tem certeza que deseja ativar este profissional?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success" title="Ativar">
                                            <i class="bi bi-person-check"></i> Ativar
                                        </button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex justify-content-end">
        {{ $professionals->links() }}
    </div>
    @endif
@endsection

@push('styles')
    <style>
        .avatar-circle {
            width: 40px;
            height: 40px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #495057;
        }

        .table td {
            vertical-align: middle;
        }
    </style>
@endpush
