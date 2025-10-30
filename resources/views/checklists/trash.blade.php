@extends('layouts.app')

@section('title')
    Lixeira de Checklists
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('checklists.index') }}">
            <i class="bi bi-card-checklist"></i> Checklists
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-trash"></i> Lixeira
    </li>
@endsection

@section('actions')
    <a href="{{ route('checklists.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar para Checklists
    </a>
@endsection

@section('content')

    <div class="alert alert-warning mb-3">
        <i class="bi bi-info-circle"></i>
        <strong>Lixeira:</strong> Checklists movidos para a lixeira ficam arquivados. Você pode restaurá-los a qualquer momento.
    </div>

    @if ($checklists->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-trash"></i> Nenhum checklist na lixeira.
        </div>
    @else
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th style="width: 60px;" class="text-center">ID</th>
                    <th>Criança</th>
                    <th>Status</th>
                    <th>Data de Criação</th>
                    <th>Desenvolvimento</th>
                    <th style="width: 180px;">Excluído em</th>
                    <th class="text-center" style="width: 120px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($checklists as $checklist)
                    <tr class="table-danger">
                        <td class="text-center">{{ $checklist->id }}</td>
                        <td>
                            {{ $checklist->kid->name }}
                            <br>
                            <small class="text-muted">
                                <i class="bi bi-archive"></i> Arquivado
                            </small>
                        </td>
                        <td>
                            <span class="badge {{ $checklist->situation_label === 'Aberto' ? 'bg-success' : 'bg-secondary' }} opacity-75">
                                {{ $checklist->situation_label }}
                            </span>
                        </td>
                        <td>{{ $checklist->created_at }}</td>
                        <td>
                            <div class="progress opacity-75" role="progressbar" aria-label="checklist{{ $checklist->id }}"
                                aria-valuenow="{{ $checklist->developmentPercentage }}" aria-valuemin="0"
                                aria-valuemax="100">
                                <div class="progress-bar" style="width: {{ $checklist->developmentPercentage }}%; background-color: {{ get_progress_color($checklist->developmentPercentage) }} !important">
                                </div>
                            </div>
                            {{ $checklist->developmentPercentage }}%
                        </td>
                        <td>
                            <small>
                                {{ $checklist->deleted_at->format('d/m/Y H:i:s') }}
                                <br>
                                <span class="text-muted">({{ $checklist->deleted_at->diffForHumans() }})</span>
                            </small>
                        </td>
                        <td class="text-center">
                            @can('restore', $checklist)
                                <button type="button" class="btn btn-sm btn-success btn-restore"
                                    data-checklist-id="{{ $checklist->id }}"
                                    data-kid-name="{{ $checklist->kid->name }}">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                </button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.btn-restore').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                const checklistId = this.dataset.checklistId;
                const kidName = this.dataset.kidName;

                Swal.fire({
                    title: 'Restaurar checklist?',
                    html: `O checklist da criança <strong>${kidName}</strong> será restaurado da lixeira.`,
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
                            html: 'Restaurando checklist',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Cria e submete o formulário
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/checklists/${checklistId}/restore`;

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
