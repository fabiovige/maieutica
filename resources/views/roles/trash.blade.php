@extends('layouts.app')

@section('title')
    Lixeira de Perfis
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('roles.index') }}">
            <i class="bi bi-shield-lock"></i> Perfis
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-trash"></i> Lixeira
    </li>
@endsection

@section('actions')
    <a href="{{ route('roles.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar para Perfis
    </a>
@endsection

@section('content')

    <div class="alert alert-warning mb-3">
        <i class="bi bi-info-circle"></i>
        <strong>Lixeira:</strong> Perfis movidos para a lixeira podem ser restaurados a qualquer momento.
    </div>

    @if ($roles->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-trash"></i> Nenhum perfil na lixeira.
        </div>
    @else
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th style="width: 60px;" class="text-center">ID</th>
                    <th>Nome</th>
                    <th>Permissões</th>
                    <th style="width: 180px;">Excluído em</th>
                    <th class="text-center" style="width: 120px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr class="table-danger">
                        <td class="text-center">{{ $role->id }}</td>
                        <td>{{ $role->name }}</td>
                        <td>
                            @foreach ($role->permissions as $permission)
                                <span class="badge bg-secondary">{{ $permission->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            <small>
                                {{ $role->deleted_at->format('d/m/Y H:i:s') }}
                                <br>
                                <span class="text-muted">({{ $role->deleted_at->diffForHumans() }})</span>
                            </small>
                        </td>
                        <td class="text-center">
                            @can('role-edit')
                                <button type="button" class="btn btn-sm btn-success btn-restore"
                                    data-role-id="{{ $role->id }}"
                                    data-role-name="{{ $role->name }}">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                </button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-end">
            {{ $roles->links() }}
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.btn-restore').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                const roleId = this.dataset.roleId;
                const roleName = this.dataset.roleName;

                Swal.fire({
                    title: 'Restaurar perfil?',
                    html: `<strong>${roleName}</strong> será restaurado.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-arrow-counterclockwise"></i> Sim, restaurar',
                    cancelButtonText: '<i class="bi bi-x-lg"></i> Cancelar',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Mostra loading
                        Swal.fire({
                            title: 'Processando...',
                            html: 'Restaurando perfil',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Cria e submete o formulário
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/roles/${roleId}/restore`;

                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';

                        form.appendChild(csrfToken);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
