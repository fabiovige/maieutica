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
                    <th>Checklists</th>
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
                        if ($kid->photo) {
                        $photoUrl = asset('storage/' . $kid->photo);
                        } else {
                        $randomAvatarNumber = rand(1, 13);
                        $photoUrl = asset('storage/kids_avatars/avatar' . $randomAvatarNumber . '.png');
                        }
                        @endphp
                        <img src="{{ $photoUrl }}" class="rounded-img" style="width: 50px; height: 50px;">
                    </td>
                    <td>{{ $kid->name }}</td>
                    <td>{{ $kid->birth_date }}</td>
                    <td>
                        <a href="{{ route('checklists.index', ['kid_id' => $kid->id]) }}">
                            Ver checklists
                        </a>
                    </td>
                    <td>{{ $kid->professional->name ?? 'N/A' }}</td>
                    <td>{{ $kid->responsible->name ?? 'N/A' }}</td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                Ações
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                @can('view kids')
                                    <li><a class="dropdown-item" href="{{ route('kids.eye', $kid->id) }}"><i class="bi bi-eye"></i> Visualizar</a></li>
                                @endcan

                                @can('edit kids')
                                    <li><a class="dropdown-item" href="{{ route('kids.edit', $kid->id) }}"><i class="bi bi-pencil"></i> Editar</a></li>
                                @endcan

                                @can('view checklists')
                                <li>
                                    <a class="dropdown-item" href="{{ route('kids.radarChart2', ['kidId' => $kid->id, 'levelId' => 1, 'checklist' => null]) }}">
                                        <i class="bi bi-check2-square"></i> Análise Geral
                                    </a>
                                </li>
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