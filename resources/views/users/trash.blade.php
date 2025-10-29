@extends('layouts.app')

@section('title')
    Lixeira de Usuários
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('users.index') }}">
            <i class="bi bi-people"></i> Usuários
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-trash"></i> Lixeira
    </li>
@endsection

@section('actions')
    <a href="{{ route('users.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar para Usuários
    </a>
@endsection

@section('content')

    <div class="alert alert-warning mb-3">
        <i class="bi bi-info-circle"></i>
        <strong>Lixeira:</strong> Usuários movidos para a lixeira ficam desativados e não podem acessar o sistema. Você pode restaurá-los a qualquer momento.
    </div>

    @if ($users->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-trash"></i> Nenhum usuário na lixeira.
        </div>
    @else
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th style="width: 60px;" class="text-center">ID</th>
                    <th style="width: 80px;" class="text-center">Avatar</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Perfil</th>
                    <th style="width: 180px;">Excluído em</th>
                    <th class="text-center" style="width: 120px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="table-danger">
                        <td class="text-center">{{ $user->id }}</td>
                        <td class="text-center">
                            @if ($user->avatar)
                                <img src="{{ asset('images/avatar/' . $user->avatar) }}" alt="{{ $user->name }}"
                                    class="rounded-circle opacity-50" style="width: 40px; height: 40px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto text-white opacity-50"
                                    style="width: 40px; height: 40px; font-size: 16px;">
                                    {{ substr($user->name, 0, 2) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            {{ $user->name }}
                            <br>
                            <small class="text-muted">
                                <i class="bi bi-ban"></i> Desativado
                            </small>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach ( $user->getRoleNames() as $role )
                                <span class="badge text-bg-secondary">{{ $role }}</span>
                            @endforeach
                        </td>
                        <td>
                            <small>
                                {{ $user->deleted_at->format('d/m/Y H:i:s') }}
                                <br>
                                <span class="text-muted">({{ $user->deleted_at->diffForHumans() }})</span>
                            </small>
                        </td>
                        <td class="text-center">
                            @can('restore', $user)
                                <button type="button" class="btn btn-sm btn-success btn-restore"
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                </button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-end">
            {{ $users->links() }}
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.btn-restore').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                const userId = this.dataset.userId;
                const userName = this.dataset.userName;

                Swal.fire({
                    title: 'Restaurar usuário?',
                    html: `<strong>${userName}</strong> será restaurado e <strong>reativado</strong>.<br><br>O usuário poderá acessar o sistema novamente.`,
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
                            html: 'Restaurando usuário',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Cria e submete o formulário
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/users/${userId}/restore`;

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
