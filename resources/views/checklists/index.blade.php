@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checklists</li>
        </ol>
    </nav>
@endsection

@section('button')
    @can('create checklists')
        <a href="{{ route('checklists.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Cadastrar novo checklist
        </a>
    @endcan
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h3>Checklists</h3>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Criança</th>
                        <th>Nível</th>
                        <th>Data de criação</th>
                        <th style="width: 100px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($checklists as $checklist)
                        <tr>
                            <td>{{ $checklist->id }}</td>
                            <td>{{ $checklist->kid->name }}</td>
                            <td>{{ $checklist->level }}</td>
                            <td>{{ $checklist->created_at }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        Ações
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        @can('view checklists')
                                            <li><a class="dropdown-item" href="{{ route('checklists.show', $checklist->id) }}"><i class="bi bi-eye"></i> Visualizar</a></li>
                                        @endcan
                                        @can('edit checklists')
                                            <li><a class="dropdown-item" href="{{ route('checklists.edit', $checklist->id) }}"><i class="bi bi-pencil"></i> Anotações</a></li>
                                        @endcan
                                        @can('fill checklists')
                                            <li><a class="dropdown-item" href="{{ route('checklists.fill', $checklist->id) }}"><i class="bi bi-check2-square"></i> Avaliação</a></li>
                                        @endcan
                                        @can('fill checklists')
                                            <li><a class="dropdown-item" href="{{ route('kids.showPlane', $checklist->kid->id) }}"><i class="bi bi-check2-square"></i> Planos</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
