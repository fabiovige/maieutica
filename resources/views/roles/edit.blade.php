@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Papéis</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar</li>
        </ol>
    </nav>
@endsection

@section('content')

    <div class="row">
        <div class="col-12">

            <form action="{{route('roles.update', $role->id)}}" method="post">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header">
                        Editar papel
                    </div>
                    <div class="card-body">

                        <div class="form-group mb-2">
                            <label>Nome</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="Ex.: Administrador" value="{{ $role->name }}">

                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Check Todos -->
                        <div class="custom-control custom-checkbox">
                            <label>Permissões</label>
                            <div class="form-check">
                                <input class="form-check-input permission-input"
                                       type="checkbox"
                                       id="checkAll"
                                    {{ (count($permissions) == count($role->permissions)) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="checkAll">
                                    Selecionar Todos
                                </label>
                            </div>
                        </div>

                        <!-- Lista de permissões -->
                        <div class="row">
                            <div class="col-12">
                                Permissões Disponíveis
                                @foreach ($permissions as $permission)
                                    <div class="form-check">
                                        <input class="form-check-input permission-input"
                                                type="checkbox"
                                                name="permissions[]"
                                                id="customCheck{{ $permission->name }}"
                                                value="{{ $permission->name }}"
                                                {{ in_array($permission->name, $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label" for="customCheck{{ $permission->name }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end">
                        <x-button icon="check" name="Salvar" type="submit" class="success"></x-button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    @include('includes.information-register', ['data' => $role, 'action'=> 'roles.destroy', 'can' => 'remove roles'])

@endsection

@push('scripts')
    <script type="text/javascript" src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script>
        $("#checkAll").click(function () {
            $('input:checkbox').not(this).prop('checked', this.checked);
        });
    </script>
@endpush
