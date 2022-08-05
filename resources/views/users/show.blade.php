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

@section('button')
    <x-button href="{{route('users.index', $user->id)}}" icon="arrow-left" name="Voltar" type="link" class="dark"></x-button>
@endsection

@section('content')

    <div class="row">

        <div class="col-12 ">
            <div class="card">
                <div class="card-header">
                    {{ __('visualizar') }}
                </div>
                <div class="card-body">
                    Usuário: {{ $user->name }} <br>
                    E-mail: {{ $user->email }} <br>
                    Papél: {{ $user->role->name }} <br>
                    <div class="mt-2">Permissões:</div>
                    @if($user->role)
                        @foreach($user->role->abilities()->orderBy('name')->get() as $ability)
                            <i class="bi bi-check-circle"></i> {{$ability->name}} ({{$ability->ability}}) <br>
                        @endforeach
                    @endif
                </div>
                <div class="card-footer d-flex justify-content-between">
                    @can('users.update')
                        <x-button href="{{route('users.edit', $user->id)}}" icon="pencil" name="Editar" type="link" class="dark"></x-button>
                    @endcan

                    @can('users.destroy')
                        <form action="{{ route('users.destroy', $user->id) }}" name="form-delete" method="post">
                            @csrf
                            @method('DELETE')
                            <x-button icon="trash" name="Enviar para lixeira" type="submit" class="danger form-delete"></x-button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>

        @include('includes.information-register', ['data' => $user])

    </div>



@endsection

