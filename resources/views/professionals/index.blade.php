@extends('layouts.app')

@section('title')
    Profissionais
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profissionais</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Profissionais</h4>
                    @can('create users')
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Novo Profissional
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Especialidade</th>
                                    <th>Registro</th>
                                    <th>Contato</th>
                                    <th>Crianças</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($professionals as $professional)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($professional->user->avatar)
                                                <img src="{{ asset('storage/' . $professional->user->avatar) }}"
                                                     class="rounded-circle me-2"
                                                     width="40" height="40"
                                                     alt="{{ $professional->user->name }}">
                                            @else
                                                <div class="avatar-initials rounded-circle me-2">
                                                    {{ substr($professional->user->name, 0, 2) }}
                                                </div>
                                            @endif
                                            {{ $professional->user->name }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $professional->specialty->name }}
                                        </span>
                                    </td>
                                    <td>{{ $professional->registration_number }}</td>
                                    <td>
                                        <div>{{ $professional->email }}</div>
                                        <small class="text-muted">{{ $professional->phone }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ $professional->kids->count() }} crianças
                                        </span>
                                    </td>
                                    <td>
                                        @if($professional->allow)
                                            <span class="badge bg-success">Ativo</span>
                                        @else
                                            <span class="badge bg-danger">Inativo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('professionals.show', $professional->id) }}"
                                               class="btn btn-sm btn-info"
                                               title="Detalhes">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('edit users')
                                            <a href="{{ route('users.edit', $professional->id) }}"
                                               class="btn btn-sm btn-warning"
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-initials {
    width: 40px;
    height: 40px;
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #495057;
}
</style>
@endpush
