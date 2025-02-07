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
                <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mt-3">
                    <thead>
                        <tr>
                            <th style="width: 60px;" class="text-center align-middle">ID</th>
                            <th style="width: 60px;" class="text-center align-middle">Foto</th>
                            <th class="align-middle">Nome</th>
                            <th class="align-middle">Responsável</th>
                            <th class="align-middle">Data Nasc.</th>
                            <th class="align-middle">Idade</th>
                            <th width="200">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kids as $kid)
                            <tr>
                                <td class="text-center align-middle">{{ $kid->id }}</td>
                                <td class="text-center align-middle">
                                    <div class="d-flex align-items-center">
                                        @if($kid->photo)
                                            <img src="{{ asset($kid->photo) }}"
                                                 class="rounded-circle me-2"
                                                 width="40" height="40"
                                                 alt="{{ $kid->name }}">
                                        @else
                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto"
                                                 style="width: 40px; height: 40px;">
                                               <i class="bi bi-person text-white"></i>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="align-middle">{{ $kid->name }}</td>
                                <td class="align-middle">{{ $kid->responsible->name ?? 'N/D' }}</td>
                                <td class="align-middle">{{ $kid->birth_date ?? 'N/D' }}</td>
                                <td class="align-middle">{{ $kid->age ?? 'N/D' }}</td>
                                <td class="align-middle">
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
                </div>
                <div class="d-flex justify-content-end">
                    {{ $kids->links() }}
                </div>
            @endif



@endsection
