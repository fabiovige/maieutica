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
                                <div class="col-md-12 py-2">
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

                    <div class="card-footer bg-transparent mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <x-button icon="check-lg" name="Salvar" type="submit" class="success"></x-button>
                                <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-lg"></i> Cancelar
                                </a>
                            </div>

                            @can('role-delete')
                                <button type="button" class="btn btn-danger" id="btnMoveToTrash">
                                    <i class="bi bi-trash"></i> Mover para Lixeira
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </form>

            <!-- Form separado para delete -->
            @can('role-delete')
            <form id="deleteForm" action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
            @endcan
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript" src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        // Confirmação para mover para lixeira
        $('#btnMoveToTrash').on('click', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Tem certeza?',
                html: '<strong>{{ $role->name }}</strong> será movido para a lixeira.<br><br>Esta ação pode ser revertida.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash"></i> Sim, mover para lixeira',
                cancelButtonText: '<i class="bi bi-x-lg"></i> Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostra loading
                    Swal.fire({
                        title: 'Processando...',
                        html: 'Movendo perfil para a lixeira',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Submete o form
                    document.getElementById('deleteForm').submit();
                }
            });
        });
    </script>
@endpush
