@extends('layouts.app')

@section('title')
    Documentos Gerados
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-file-pdf"></i> Documentos Gerados
    </li>
@endsection

@section('actions')
    @can('document-generate')
        <a href="{{ route('generated-documents.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Gerar Documento
        </a>
    @endcan
@endsection

@section('content')

    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('generated-documents.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="kid_id" class="form-label">
                        <i class="bi bi-person"></i> Criança
                    </label>
                    <select class="form-select" id="kid_id" name="kid_id">
                        <option value="">Todas as crianças</option>
                        @foreach($kids as $kid)
                            <option value="{{ $kid->id }}" {{ request('kid_id') == $kid->id ? 'selected' : '' }}>
                                {{ $kid->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="template_id" class="form-label">
                        <i class="bi bi-file-earmark-text"></i> Template
                    </label>
                    <select class="form-select" id="template_id" name="template_id">
                        <option value="">Todos os templates</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" {{ request('template_id') == $template->id ? 'selected' : '' }}>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="date_from" class="form-label">
                        <i class="bi bi-calendar"></i> Data De
                    </label>
                    <input type="date"
                           class="form-control"
                           id="date_from"
                           name="date_from"
                           value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label for="date_to" class="form-label">
                        <i class="bi bi-calendar"></i> Data Até
                    </label>
                    <input type="date"
                           class="form-control"
                           id="date_to"
                           name="date_to"
                           value="{{ request('date_to') }}">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                        @if(request()->hasAny(['kid_id', 'template_id', 'date_from', 'date_to']))
                            <a href="{{ route('generated-documents.index') }}" class="btn btn-secondary" title="Limpar filtros">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(request()->hasAny(['kid_id', 'template_id', 'date_from', 'date_to']))
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Exibindo resultados filtrados.
            <strong>{{ $documents->total() }}</strong> documento(s) encontrado(s).
        </div>
    @endif

    @if ($documents->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Nenhum documento gerado encontrado.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;" class="text-center">ID</th>
                        <th>Criança</th>
                        <th>Template</th>
                        <th style="width: 150px;">Gerado por</th>
                        <th style="width: 130px;" class="text-center">Data</th>
                        <th style="width: 150px;" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($documents as $document)
                        <tr>
                            <td class="text-center">{{ $document->id }}</td>
                            <td>
                                <i class="bi bi-person"></i> <strong>{{ $document->kid->name }}</strong>
                            </td>
                            <td>
                                <i class="bi bi-file-earmark-text"></i> {{ $document->documentTemplate->name }}
                                <br>
                                <small class="text-muted">
                                    <span class="badge bg-secondary">
                                        {{ App\Models\DocumentTemplate::getDocumentTypes()[$document->documentTemplate->type] ?? $document->documentTemplate->type }}
                                    </span>
                                </small>
                            </td>
                            <td>
                                <small>
                                    <i class="bi bi-person-circle"></i> {{ $document->user->name }}
                                </small>
                            </td>
                            <td class="text-center">
                                {{ $document->generated_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    @can('document-show')
                                        <a href="{{ route('generated-documents.show', $document) }}"
                                           class="btn btn-sm btn-info"
                                           title="Visualizar">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endcan

                                    @can('document-download')
                                        <a href="{{ route('generated-documents.download', $document) }}"
                                           class="btn btn-sm btn-success"
                                           title="Baixar PDF">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    @endcan

                                    @can('document-delete')
                                        <form action="{{ route('generated-documents.destroy', $document) }}"
                                              method="POST"
                                              style="display: inline;"
                                              onsubmit="return confirm('Tem certeza que deseja excluir este documento?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Exibindo {{ $documents->firstItem() }} a {{ $documents->lastItem() }} de {{ $documents->total() }} documentos
            </div>
            <div>
                {{ $documents->appends(request()->query())->links() }}
            </div>
        </div>
    @endif

@endsection
