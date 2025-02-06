@extends('layouts.app')


@section('title')
    Perfis
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home.index')}}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index')}}">Perfis</a></li>
    <li class="breadcrumb-item active" aria-current="page">cadastro</li>

@endsection

@section('actions')
    @can('create roles')
        <a href="{{ route('roles.create') }}" class="btn btn-primary">

            <i class="bi bi-plus-lg"></i> Novo Perfil
        </a>
    @endcan
@endsection

@section('content')

    <div class="row">

        <div class="col-12">

            <form action="{{route('roles.store')}}" method="post">
                @csrf
                @method('POST')

                <div class="card">
                    <div class="card-header">
                        Cadastrar
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-2">
                            <label>Nome</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name"
                                   placeholder="Nome do Papel"
                                   value="{{old('name')}}">

                            @error('name')
                            <div class="invalid-feedback">{{$message}}</div>
                            @enderror
                        </div>

                        <label>Permissões</label>

                        <div class="custom-control custom-checkbox">

                            <div class="form-check">
                                <input class="form-check-input permission-input"
                                       type="checkbox"
                                       id="checkAll"
                                >
                                <label class="form-check-label" for="checkAll">
                                    Selecionar Todos
                                </label>
                            </div>

                        </div>
                        <div class="row">
                            @foreach($permissions as $permission)
                                <div class="col-md-4 py-2">
                                    <div class="custom-control custom-checkbox">
                                        <div class="form-check">
                                            <input class="form-check-input permission-input"
                                                type="checkbox"
                                                name="permissions[]"
                                                id="customCheck{{$permission->name}}"
                                                value="{{$permission->name}}"
                                                @if(is_array(old('permissions')) && in_array($permission->name, old('permissions'))) checked @endif
                                            >
                                            <label class="form-check-label" for="customCheck{{$permission->name}}">
                                                {{ $permission->name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end">
                        <x-button icon="check" name="Salvar" type="submit" class="success"></x-button>
                    </div>
                </div>

            </form>

        </div>

    </div>

@endsection

@push('scripts')
    <script type="text/javascript" src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script>
        // Selecionar/Deselecionar todas as permissões
        $("#checkAll").click(function () {
            $('input:checkbox').not(this).prop('checked', this.checked);
        });

        // Verifica se todas as permissões estão marcadas e ajusta o "Selecionar Todos"
        $('.permission-input').on('click', function() {
            if ($('.permission-input:checked').length === $('.permission-input').length) {
                $('#checkAll').prop('checked', true);
            } else {
                $('#checkAll').prop('checked', false);
            }
        });
    </script>
@endpush
