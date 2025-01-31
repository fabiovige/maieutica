@extends('layouts.app')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Crianças</li>
    </ol>
</nav>
@endsection

@section('button')
@can('create kids')
<a href="{{ route('kids.create') }}" class="btn btn-primary btn-sm">
    <i class="fas fa-plus"></i> Cadastrar criança
</a>
@endcan
@endsection

@section('content')
<div class="row">
    <div class="col-md-12 ">
        <h3>Crianças</h3>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Foto</th>
                    <th>Nome</th>
                    <th>Data de nascimento</th>
                    <th>Profissional</th>
                    <th>Responsáveis</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kids as $kid)
                <tr class="centered-column-vertically">
                    <td>{{ $kid->id }}</td>
                    <td>
                        @php
                        $photoUrl = '';
                        if ($kid->photo) {
                            $photoUrl = asset('images/kids/' . $kid->photo);
                        }
                        @endphp
                        <img src="{{ $photoUrl }}" class="rounded-img" style="width: 50px; height: 50px;">
                    </td>
                    <td>{{ $kid->name }}</td>
                    <td>{{ $kid->birth_date }}</td>
                    <td>{{ $kid->professional->name ?? 'N/A' }}</td>
                    <td>{{ $kid->responsible->name ?? 'N/A' }}</td>
                    <td>
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
</div>
@endsection
