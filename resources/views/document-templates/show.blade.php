@extends('layouts.app')

@section('title')
    Visualizar Template
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('document-templates.index') }}">
            <i class="bi bi-file-earmark-text"></i> Templates
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ $documentTemplate->name }}</li>
@endsection

@section('actions')
    @can('template-edit')
        <a href="{{ route('document-templates.edit', $documentTemplate) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
    @endcan
@endsection

@section('content')

    <div class="row">
        <!-- Informações do Template -->
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações do Template</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th style="width: 200px;"><i class="bi bi-tag"></i> Nome:</th>
                                <td><strong>{{ $documentTemplate->name }}</strong></td>
                            </tr>
                            <tr>
                                <th><i class="bi bi-file-earmark"></i> Tipo:</th>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ App\Models\DocumentTemplate::getDocumentTypes()[$documentTemplate->type] ?? $documentTemplate->type }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th><i class="bi bi-hash"></i> Versão:</th>
                                <td><span class="badge bg-info">v{{ $documentTemplate->version }}</span></td>
                            </tr>
                            <tr>
                                <th><i class="bi bi-toggle-on"></i> Status:</th>
                                <td>
                                    <span class="badge {{ $documentTemplate->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $documentTemplate->is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                            </tr>
                            @if($documentTemplate->description)
                                <tr>
                                    <th><i class="bi bi-text-paragraph"></i> Descrição:</th>
                                    <td>{{ $documentTemplate->description }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th><i class="bi bi-calendar"></i> Criado em:</th>
                                <td>{{ $documentTemplate->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th><i class="bi bi-clock-history"></i> Atualizado em:</th>
                                <td>{{ $documentTemplate->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Conteúdo HTML -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-code-slash"></i> Conteúdo HTML</h5>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code>{{ $documentTemplate->html_content }}</code></pre>
                </div>
            </div>
        </div>

        <!-- Estatísticas e Documentos Gerados -->
        <div class="col-md-4">
            <!-- Estatísticas -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Estatísticas</h5>
                </div>
                <div class="card-body text-center">
                    <h2 class="display-4 text-primary">{{ $documentTemplate->generatedDocuments->count() }}</h2>
                    <p class="text-muted mb-0">Documentos Gerados</p>
                </div>
            </div>

            <!-- Documentos Gerados Recentes -->
            @if($documentTemplate->generatedDocuments->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-file-pdf"></i> Documentos Recentes</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($documentTemplate->generatedDocuments->take(10) as $document)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $document->kid->name }}</h6>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i> {{ $document->generated_at->format('d/m/Y') }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-person"></i> {{ $document->user->name }}
                                        </small>
                                    </div>
                                    <div>
                                        @can('document-download')
                                            <a href="{{ route('generated-documents.download', $document) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Baixar">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($documentTemplate->generatedDocuments->count() > 10)
                        <div class="card-footer text-center">
                            <a href="{{ route('generated-documents.index', ['template_id' => $documentTemplate->id]) }}" class="btn btn-sm btn-link">
                                Ver todos os {{ $documentTemplate->generatedDocuments->count() }} documentos
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

@endsection
