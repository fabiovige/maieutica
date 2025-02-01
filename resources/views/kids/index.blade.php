@extends('layouts.app')

@section('title')
    Crianças
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-people"></i> Crianças
    </li>
@endsection

@section('actions')
    @can('create kids')
        <a href="{{ route('kids.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nova Criança
        </a>
    @endcan
@endsection

@section('content')


            @if($kids->isEmpty())
                <div class="alert alert-info">
                    Nenhuma criança cadastrada.
                </div>
            @else
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th style="width: 60px;" class="text-center">ID</th>
                            <th>Nome</th>
                            <th>Responsável</th>
                            <th>Data Nasc.</th>
                            <th>Idade</th>
                            <th class="text-center" style="width: 100px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kids as $kid)
                            <tr>
                                <td class="text-center">{{ $kid->id }}</td>
                                <td>{{ $kid->name }}</td>
                                <td>{{ $kid->responsible->name ?? 'N/D' }}</td>
                                <td>{{ $kid->birth_date ?? 'N/D' }}</td>
                                <td>{{ $kid->age ?? 'N/D' }}</td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                            Ações
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                                            @can('edit kids')
                                                <li><a class="dropdown-item" href="{{ route('kids.edit', $kid->id) }}"><i class="bi bi-pencil"></i> Editar</a></li>
                                            @endcan

                                            @can('view checklists')
                                            <li>
                                                <a class="dropdown-item" href="{{ route('checklists.index', ['kidId' => $kid->id]) }}">
                                                    <i class="bi bi-card-checklist"></i> Checklists
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('kids.radarChart2', ['kidId' => $kid->id, 'levelId' => 0, 'checklist' => null]) }}">
                                                    <i class="bi bi-clipboard-data"></i> Comparativo
                                                </a>
                                            </li>
                                            @endcan
                                            <li>
                                                <a class="dropdown-item" href="{{ route('kids.overview', ['kidId' => $kid->id]) }}">
                                                    <i class="bi bi-bar-chart"></i> Desenvolvimento
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="d-flex justify-content-end">
                    {{ $kids->links() }}
                </div>
            @endif



@endsection
