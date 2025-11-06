@extends('layouts.app')

@section('title')
    Lixeira de Profissionais
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('professionals.index') }}">
            <i class="bi bi-person-vcard"></i> Profissionais
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-trash"></i> Lixeira
    </li>
@endsection

@section('actions')
    <a href="{{ route('professionals.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar para Profissionais
    </a>
@endsection

@section('content')

    <div class="alert alert-warning mb-3">
        <i class="bi bi-info-circle"></i>
        <strong>Lixeira:</strong> Profissionais movidos para a lixeira podem ser restaurados a qualquer momento.
    </div>

    @if ($professionals->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-trash"></i> Nenhum profissional na lixeira.
        </div>
    @else
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th style="width: 60px;" class="text-center">ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Especialidade</th>
                    <th>Registro</th>
                    <th style="width: 180px;">Excluído em</th>
                    <th class="text-center" style="width: 120px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($professionals as $professional)
                    <tr class="table-danger">
                        <td class="text-center">{{ $professional->id }}</td>
                        <td>{{ $professional->user->first()->name ?? 'N/A' }}</td>
                        <td>{{ $professional->user->first()->email ?? 'N/A' }}</td>
                        <td>{{ $professional->specialty->name ?? 'N/A' }}</td>
                        <td>{{ $professional->registration_number }}</td>
                        <td>
                            <small>
                                {{ $professional->deleted_at->format('d/m/Y H:i:s') }}
                                <br>
                                <span class="text-muted">({{ $professional->deleted_at->diffForHumans() }})</span>
                            </small>
                        </td>
                        <td class="text-center">
                            @can('professional-edit')
                                <button type="button" class="btn btn-sm btn-success btn-restore"
                                    data-professional-id="{{ $professional->id }}"
                                    data-professional-name="{{ $professional->user->first()->name ?? 'Profissional' }}">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                </button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-end">
            {{ $professionals->links() }}
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.btn-restore').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                const professionalId = this.dataset.professionalId;
                const professionalName = this.dataset.professionalName;

                Swal.fire({
                    title: 'Restaurar profissional?',
                    html: `<strong>${professionalName}</strong> será restaurado.`,
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
                            html: 'Restaurando profissional',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Cria e submete o formulário
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/professionals/${professionalId}/restore`;

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
