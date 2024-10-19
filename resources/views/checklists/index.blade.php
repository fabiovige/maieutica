@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kids.index') }}">Crianças</a></li>
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
    <div class="row" id="app">
        <div class="col-md-12">
            
                @if (isset($kid))

                    <Resume :responsible="{{ $kid->responsible()->first() }}"
                        :professional="{{ $kid->professional()->first() }}" :kid="{{ $kid }}"
                        :checklist="{{ $kid->checklists()->count() }}" :plane="{{ $kid->planes()->count() }}"
                        :months="{{ $kid->months }}">
                    </Resume>

                @endif
            <h3>Checklists</h3>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Checklist ID</th>
                        @if (!isset($kid))<th>Criança</th>@endif
                        <th>Data de criação</th>
                        <th>Média Geral do Desenvolvimento</th>
                        <th style="width: 100px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($checklists as $checklist)
                        <tr>
                            <td>{{ $checklist->id }}</td>
                            @if (!isset($kid))<td>{{ $checklist->kid->name }}</td>@endif
                            <td>{{ $checklist->created_at }}</td>
                            <td>
                            
                                <div class="progress" role="progressbar" aria-label="checklist{{$checklist->id}}" aria-valuenow="{{$checklist->developmentPercentage}}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: {{$checklist->developmentPercentage}}%"></div>
                                </div>

                                {{ $checklist->developmentPercentage }}%
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        Ações
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
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
