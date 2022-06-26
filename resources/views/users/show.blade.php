@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Usuários</a></li>
            <li class="breadcrumb-item active" aria-current="page">Gerenciar</li>
        </ol>
    </nav>
@endsection

@section('content')

    <div class="row">

        <div class="col-12 ">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="h5">{{ $user->name }} </div>
                    <div><a href="{{route('users.index')}}" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Voltar </a></div>
                </div>
                <div class="card-body">
                    <div class="h6"> E-mail: {{ $user->email }} </div>
                    <div class="h6">Papél: @if($user->role)
                    <div class="badge  bg-dark">{{ $user->role->name }}</div>@else <div class="badge bg-info">Sem papél associado!</div> @endif</div>
                    <div class="h5 mt-2">Resursos adicionados:</div>
                        @if($user->role)
                        @foreach($user->role->resources()->orderBy('name')->get() as $resource)

                            <i class="bi bi-check-circle"></i> {{$resource->name}} ({{$resource->ability}}) <br>

                        @endforeach
                        @endif
                </div>
                <div class="card-footer d-flex justify-content-between">
                    @can('users.edit')
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">
                        <i class="bi bi-pencil-square"></i> Editar</a>
                    @endcan

                    @can('users.destroy')
                        <form action="{{ route('users.destroy', $user->id) }}" name="form-delete" method="post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-warning form-delete">
                                <i class="bi bi-trash3"></i> Enviar para lixeira</button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>

        @include('includes.information-register', ['data' => $user])

    </div>



@endsection

