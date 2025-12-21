@extends('layouts.app')

@section('title')
    Histórico de Documentos Gerados
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('documentos.index') }}">Documentos</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Histórico</li>
@endsection

@section('content')

    <!-- Filtro de Busca -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('documentos.history') }}" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">
                        <i class="bi bi-search"></i> Buscar Documento
                    </label>
                    <input type="text"
                           class="form-control"
                           id="search"
                           name="search"
                           placeholder="Buscar por ID, paciente ou gerado por..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-4">
                    <label for="model_type" class="form-label">
                        <i class="bi bi-filter"></i> Tipo de Documento
                    </label>
                    <select class="form-select" id="model_type" name="model_type">
                        <option value="">Todos os tipos</option>
                        <option value="1" {{ request('model_type') == '1' ? 'selected' : '' }}>Declaração - Modelo 1</option>
                        <option value="2" {{ request('model_type') == '2' ? 'selected' : '' }}>Declaração Simplificada - Modelo 2</option>
                        <option value="3" {{ request('model_type') == '3' ? 'selected' : '' }}>Laudo Psicológico - Modelo 3</option>
                        <option value="4" {{ request('model_type') == '4' ? 'selected' : '' }}>Parecer Psicológico - Modelo 4</option>
                        <option value="5" {{ request('model_type') == '5' ? 'selected' : '' }}>Relatório Multiprofissional - Modelo 5</option>
                        <option value="6" {{ request('model_type') == '6' ? 'selected' : '' }}>Relatório Psicológico - Modelo 6</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        @if(request('search') || request('model_type'))
                            <a href="{{ route('documentos.history') }}" class="btn btn-secondary" title="Limpar filtros">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(request('search') || request('model_type'))
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Exibindo resultados da busca.
            <strong>{{ $documents->total() }}</strong> documento(s) encontrado(s).
        </div>
    @endif

    @if ($documents->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Nenhum documento encontrado.
        </div>
    @else
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history"></i> Histórico de Documentos Gerados
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;" class="text-center">ID</th>
                                <th>Tipo</th>
                                <th>Paciente/Usuário</th>
                                <th>Profissional</th>
                                <th>Gerado Por</th>
                                <th style="width: 150px;">Data Geração</th>
                                <th style="width: 120px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($documents as $doc)
                                <tr>
                                    <td class="text-center">{{ $doc->id }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $doc->model_type_name }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($doc->documentable)
                                            <i class="bi bi-person"></i> {{ $doc->documentable->name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($doc->professional && $doc->professional->user->first())
                                            <i class="bi bi-person-badge"></i> {{ $doc->professional->user->first()->name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($doc->generatedBy)
                                            <i class="bi bi-person-check"></i> {{ $doc->generatedBy->name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($doc->generated_at)
                                            <i class="bi bi-calendar-event"></i>
                                            {{ $doc->generated_at->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('documentos.download', $doc->id) }}"
                                           class="btn btn-sm btn-primary"
                                           target="_blank"
                                           title="Download PDF">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $documents->appends(request()->query())->links() }}
        </div>
    @endif
@endsection

