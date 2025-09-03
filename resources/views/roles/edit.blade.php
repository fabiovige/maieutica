@extends('layouts.app')

@section('title')
    Perfis
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('roles.index') }}">
            <i class="bi bi-person-vcard"></i> Perfis
        </a>
    </li>
    <li class="breadcrumb-item active">
        <i class="bi bi-pencil"></i> Editar
    </li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <form action="{{ route('roles.update', $role->id) }}" method="post">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header">
                        Editar papel
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-2">
                            <label>Nome</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                                placeholder="Nome do Papel" value="{{ $role->name }}">

                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <label>Permissões</label>

                        <div class="custom-control custom-checkbox">
                            <div class="form-check">
                                <input class="form-check-input permission-input" type="checkbox" id="checkAll"
                                    {{ count($permissions) == count($role->permissions) ? 'checked' : '' }}>
                                <label class="form-check-label" for="checkAll">
                                    Selecionar Todos
                                </label>
                            </div>
                        </div>

                        <div class="row">
                            @foreach ($permissions as $permission)
                                <div class="col-md-4 py-2">
                                    <div class="custom-control custom-checkbox">
                                        <div class="form-check">
                                            <input class="form-check-input permission-input" type="checkbox"
                                                name="permissions[]" id="customCheck{{ $permission->name }}"
                                                value="{{ $permission->name }}"
                                                {{ in_array($permission->name, $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="customCheck{{ $permission->name }}">
                                                {{ $permission->name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between gap-2">
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                        <x-button icon="check" name="Salvar" type="submit" class="success"></x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('includes.information-register', [
        'data' => $role,
        'action' => 'roles.destroy',
        'can' => 'remove roles',
    ])
@endsection

@push('scripts')
    <script type="text/javascript" src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script>
        // Selecionar/Deselecionar todas as permissões
        $("#checkAll").click(function() {
            $('input:checkbox').not(this).prop('checked', this.checked);
        });

        // Verifica se todas as permissões estão marcadas e ajusta o "Selecionar Todos"
        $('.permission-input').on('click', function() {
            if ($('.permission-input:checked').length === $('.permission-input').length - 1) {
                $('#checkAll').prop('checked', true);
            } else {
                $('#checkAll').prop('checked', false);
            }
        });
    </script>
@endpush
