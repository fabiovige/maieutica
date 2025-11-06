@extends('layouts.app')

@section('title')
    Visualizar Criança
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('kids.index') }}">
            <i class="bi bi-people"></i> Crianças
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        Visualizar - {{ $kid->name }}
    </li>
@endsection

@section('content')

    <!-- Componente com Informações da Criança -->
    <div class="row">
        <div class="col-12">
            <x-kid-info-card :kid="$kid" />
        </div>
    </div>

    <!-- Últimos Checklists -->
    @if($kid->checklists && $kid->checklists->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Últimos Checklists</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Data</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($kid->checklists as $checklist)
                                        <tr>
                                            <td>{{ $checklist->id }}</td>
                                            <td>{{ $checklist->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge {{ $checklist->situation === 'a' ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $checklist->situation_label }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('checklists.show', $checklist->id) }}"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye"></i> Ver
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Botões de Ação -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <a href="{{ route('kids.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                @can('kid-edit')
                    <a href="{{ route('kids.edit', $kid->id) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                @endcan
            </div>
        </div>
    </div>

@endsection
