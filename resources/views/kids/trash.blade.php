@extends('layouts.app')

@section('title')
    Lixeira de Crianças
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('kids.index') }}">
            <i class="bi bi-people"></i> Crianças
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-trash"></i> Lixeira
    </li>
@endsection

@section('actions')
    <a href="{{ route('kids.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar para Crianças
    </a>
@endsection

@section('content')

    <div class="alert alert-warning mb-3">
        <i class="bi bi-info-circle"></i>
        <strong>Lixeira:</strong> Crianças movidas para a lixeira ficam arquivadas. Você pode restaurá-las a qualquer momento.
    </div>

    @if ($kids->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-trash"></i> Nenhuma criança na lixeira.
        </div>
    @else
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th style="width: 60px;" class="text-center">ID</th>
                    <th style="width: 60px;" class="text-center">Foto</th>
                    <th>Nome</th>
                    <th>Responsável</th>
                    <th>Profissionais</th>
                    <th>Data Nasc.</th>
                    <th style="width: 180px;">Excluído em</th>
                    <th class="text-center" style="width: 120px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kids as $kid)
                    <tr class="table-danger">
                        <td class="text-center">{{ $kid->id }}</td>
                        <td class="text-center">
                            @if ($kid->photo)
                                <img src="{{ asset($kid->photo) }}"
                                    class="rounded-circle opacity-50"
                                    style="width: 40px; height: 40px; object-fit: cover;"
                                    alt="{{ $kid->name }}">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto opacity-50"
                                    style="width: 40px; height: 40px;">
                                    <i class="bi bi-person text-white"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            {{ $kid->name }}
                            <br>
                            <small class="text-muted">
                                <i class="bi bi-archive"></i> Arquivado
                            </small>
                        </td>
                        <td>{{ $kid->responsible->name ?? 'N/D' }}</td>
                        <td>
                            @if($kid->professionals && $kid->professionals->count() > 0)
                                @foreach($kid->professionals as $professional)
                                    <span class="badge bg-secondary text-white mb-1 opacity-75" title="{{ $professional->specialty->name ?? 'Sem especialidade' }}">
                                        <i class="bi bi-person-badge"></i>
                                        {{ $professional->user->first()->name ?? 'N/D' }}
                                        @if($professional->specialty)
                                            <small>({{ $professional->specialty->initial ?? $professional->specialty->name }})</small>
                                        @endif
                                    </span>
                                    @if(!$loop->last)<br>@endif
                                @endforeach
                            @else
                                <span class="text-muted"><i class="bi bi-dash"></i> Nenhum</span>
                            @endif
                        </td>
                        <td>{{ $kid->birth_date ?? 'N/D' }}</td>
                        <td>
                            <small>
                                {{ $kid->deleted_at->format('d/m/Y H:i:s') }}
                                <br>
                                <span class="text-muted">({{ $kid->deleted_at->diffForHumans() }})</span>
                            </small>
                        </td>
                        <td class="text-center">
                            @can('restore', $kid)
                                <button type="button" class="btn btn-sm btn-success btn-restore"
                                    data-kid-id="{{ $kid->id }}"
                                    data-kid-name="{{ $kid->name }}">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                </button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-end">
            {{ $kids->links() }}
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.btn-restore').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                const kidId = this.dataset.kidId;
                const kidName = this.dataset.kidName;

                Swal.fire({
                    title: 'Restaurar criança?',
                    html: `<strong>${kidName}</strong> será restaurado(a) da lixeira.`,
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
                            html: 'Restaurando criança',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Cria e submete o formulário
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/kids/${kidId}/restore`;

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
