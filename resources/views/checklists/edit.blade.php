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
            @csrf @method('PUT')
            <div class="card">
                <div class="card-header">
                    <h3>Checklist Id: {{ $checklist->id }}</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="row">
                            <div class="col">
                                <label for="name">Criança</label> <br />
                                <input
                                    disabled
                                    class="form-control"
                                    type="text"
                                    name="name"
                                    value="{{ $checklist->kid->name }}"
                                    readonly
                                />
                            </div>
                            <div class="col">
                                <label for="birth_date"
                                    >Data de nascimento</label
                                >
                                <br />
                                <input
                                    disabled
                                    class="form-control"
                                    type="text"
                                    name="birth_date"
                                    value="{{ $checklist->kid->birth_date }}"
                                    readonly
                                />
                            </div>
                            <div class="col">
                                <label for="created_at">Data de criação</label>
                                <br />
                                <input
                                    disabled
                                    class="form-control bg-ligth"
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
                <div class="card-footer d-flex justify-content-start gap-2">
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
            </div>
        </form>
    </div>
</div>

@include('includes.information-register', [ 'data' => $checklist, 'action' =>
'checklists.destroy', 'can' => 'remove checklists', ]) @endsection
