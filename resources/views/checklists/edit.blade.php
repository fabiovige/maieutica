@extends('layouts.app') @section('title') Editar Checklist @endsection
@section('breadcrumb-items')
<li class="breadcrumb-item">
    <a href="{{ route('checklists.index') }}">
        <i class="bi bi-card-checklist"></i> Checklists
    </a>
</li>
<li class="breadcrumb-item active" aria-current="page">Editar</li>
@endsection @section('content')
<div class="row">
    <div class="col-md-12">
        <form
            action="{{ route('checklists.update', $checklist->id) }}"
            method="POST"
        >
            @csrf @method('PUT') @if(request('kidId'))
            <input type="hidden" name="kidId" value="{{ request('kidId') }}" />
            @endif
            <div class="card">
                <div class="card-header">
                    <h3>Checklist Id: {{ $checklist->id }}</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="row">
                            <input type="hidden" name="kid_id" value="{{ $checklist->kid_id }}" />
                            <div class="col">
                                <label for="kid_name">Criança</label> <br />
                                <input
                                    class="form-control bg-light"
                                    type="text"
                                    id="kid_name"
                                    value="{{ $checklist->kid->name }}"
                                    readonly
                                />
                            </div>
                            <div class="col">
                                <label for="kid_birth_date">Data de nascimento</label>
                                <br />
                                <input
                                    class="form-control bg-light"
                                    type="text"
                                    id="kid_birth_date"
                                    value="{{ $checklist->kid->birth_date }}"
                                    readonly
                                />
                            </div>
                            <div class="col">
                                <label for="created_at">Data de criação</label>
                                <br />
                                <input
                                    disabled
                                    class="form-control bg-light"
                                    type="text"
                                    name="created_at"
                                    value="{{ $checklist->created_at->format('d/m/Y') }}"
                                    readonly
                                />
                            </div>
                            <div class="col">
                                <input
                                    type="hidden"
                                    name="level"
                                    value="{{ $checklist->level }}"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-2">
                        <label for="description">Descrição</label>
                        <textarea
                            class="form-control @error('description') is-invalid @enderror"
                            name="description"
                            rows="3"
                            >{{ old('description') ?? $checklist->description }}</textarea
                        >
                        @error('description')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group mt-2">
                        <label for="situation">Situação</label>
                        <select
                            class="form-control"
                            id="situation"
                            name="situation"
                        >
                            <option value="a" {{ $checklist->
                                situation === 'a' ? 'selected' : '' }}>Aberto
                            </option>
                            <option value="f" {{ $checklist->
                                situation === 'f' ? 'selected' : '' }}>Fechado
                            </option>
                        </select>
                    </div>
                </div>
                <div class="card-footer bg-transparent mt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2">
                            <x-button
                                icon="check-lg"
                                name="Salvar"
                                type="submit"
                                class="success"
                            ></x-button>
                            <a
                                href="{{ route('checklists.index', ['kidId' => $checklist->kid_id]) }}"
                                class="btn btn-secondary"
                            >
                                <i class="bi bi-x-lg"></i> Cancelar
                            </a>
                        </div>

                        @can('checklist-delete')
                            <button type="button" class="btn btn-danger" id="btn-delete-checklist"
                                data-checklist-id="{{ $checklist->id }}"
                                data-kid-name="{{ $checklist->kid->name }}">
                                <i class="bi bi-trash"></i> Mover para Lixeira
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Script para mover para lixeira
        document.getElementById('btn-delete-checklist')?.addEventListener('click', function(e) {
            e.preventDefault();

            const checklistId = this.dataset.checklistId;
            const kidName = this.dataset.kidName;

            Swal.fire({
                title: 'Mover para lixeira?',
                html: `O checklist da criança <strong>${kidName}</strong> será movido para a lixeira.<br><br>Você poderá restaurar depois se necessário.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash"></i> Sim, mover para lixeira',
                cancelButtonText: '<i class="bi bi-x-lg"></i> Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostra loading
                    Swal.fire({
                        title: 'Processando...',
                        html: 'Movendo checklist para lixeira',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Cria e submete o formulário
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/checklists/${checklistId}`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';

                    form.appendChild(csrfToken);
                    form.appendChild(methodField);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    </script>
@endpush
