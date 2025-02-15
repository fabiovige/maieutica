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
    @can('create professionals')
        <a href="{{ route('professionals.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Novo Profissional
        </a>
    @endcan
@endsection

@section('content')
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
                        <span class="badge bg-primary">
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
                            @can('edit professionals')
                                <a href="{{ route('professionals.edit', $professional->id) }}"
                                    class="btn btn-sm btn-primary me-2" title="Editar">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                            @endcan

                            @can('deactivate professionals')
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

                            @can('activate professionals')
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
