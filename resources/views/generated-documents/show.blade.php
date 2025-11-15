@extends('layouts.app')

@section('title')
    Documento Gerado
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('generated-documents.index') }}">
            <i class="bi bi-file-pdf"></i> Documentos Gerados
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Documento #{{ $generatedDocument->id }}</li>
@endsection

@section('actions')
    @can('document-download')
        <a href="{{ route('generated-documents.download', $generatedDocument) }}" class="btn btn-success">
            <i class="bi bi-download"></i> Baixar PDF
        </a>
    @endcan
@endsection

@section('content')

    <!-- Visualizador de PDF -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-pdf"></i> Pré-visualização do Documento</h5>
                    <div>
                        @can('document-download')
                            <a href="{{ route('generated-documents.download', $generatedDocument) }}" class="btn btn-sm btn-success">
                                <i class="bi bi-download"></i> Baixar PDF
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($pdfBase64)
                        <embed
                            src="data:application/pdf;base64,{{ $pdfBase64 }}"
                            type="application/pdf"
                            style="width: 100%; height: 800px; border: none;"
                            title="Visualização do PDF">
                        <div class="text-center p-3 bg-light">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Se o PDF não carregar, <a href="{{ route('generated-documents.download', $generatedDocument) }}">clique aqui para baixar</a>.
                            </small>
                        </div>
                    @else
                        <div class="text-center p-5">
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                            <p class="mt-3">Arquivo PDF não encontrado.</p>
                            <p class="text-muted">O arquivo pode ter sido removido ou está temporariamente indisponível.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informações do Documento -->
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações do Documento</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th style="width: 200px;"><i class="bi bi-file-earmark-text"></i> Template:</th>
                                <td>
                                    <a href="{{ route('document-templates.show', $generatedDocument->documentTemplate) }}">
                                        {{ $generatedDocument->documentTemplate->name }}
                                    </a>
                                    <br>
                                    <small class="text-muted">
                                        <span class="badge bg-secondary">
                                            {{ App\Models\DocumentTemplate::getDocumentTypes()[$generatedDocument->documentTemplate->type] ?? $generatedDocument->documentTemplate->type }}
                                        </span>
                                    </small>
                                </td>
                            </tr>
                            <tr>
                                <th><i class="bi bi-person"></i> Criança:</th>
                                <td>
                                    <a href="{{ route('kids.show', $generatedDocument->kid) }}">
                                        {{ $generatedDocument->kid->name }}
                                    </a>
                                </td>
                            </tr>
                            @if($generatedDocument->checklist)
                                <tr>
                                    <th><i class="bi bi-list-check"></i> Checklist:</th>
                                    <td>
                                        <a href="{{ route('checklists.show', $generatedDocument->checklist) }}">
                                            Checklist #{{ $generatedDocument->checklist->id }} - {{ $generatedDocument->checklist->created_at->format('d/m/Y') }}
                                        </a>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th><i class="bi bi-person-circle"></i> Gerado por:</th>
                                <td>{{ $generatedDocument->user->name }}</td>
                            </tr>
                            <tr>
                                <th><i class="bi bi-calendar"></i> Data de Geração:</th>
                                <td>{{ $generatedDocument->generated_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th><i class="bi bi-file-earmark-pdf"></i> Arquivo:</th>
                                <td>
                                    <code>{{ $generatedDocument->file_name }}</code>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Dados Utilizados -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-database"></i> Dados Utilizados na Geração</h5>
                </div>
                <div class="card-body">
                    @if($generatedDocument->data_used)
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Campo</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($generatedDocument->data_used as $key => $value)
                                        @if(!empty($value))
                                            <tr>
                                                <td><code>{!! '{{' . e($key) . '}}' !!}</code></td>
                                                <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Nenhum dado registrado.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ações Laterais -->
        <div class="col-md-4">
            <!-- Ações Rápidas -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Ações Rápidas</h5>
                </div>
                <div class="list-group list-group-flush">
                    @can('document-download')
                        <a href="{{ route('generated-documents.download', $generatedDocument) }}"
                           class="list-group-item list-group-item-action">
                            <i class="bi bi-download text-success"></i> Baixar PDF
                        </a>
                    @endcan

                    @can('document-generate')
                        <a href="{{ route('generated-documents.create', ['kid_id' => $generatedDocument->kid_id]) }}"
                           class="list-group-item list-group-item-action">
                            <i class="bi bi-file-earmark-plus text-primary"></i> Gerar Novo Documento para esta Criança
                        </a>
                    @endcan

                    <a href="{{ route('generated-documents.index', ['kid_id' => $generatedDocument->kid_id]) }}"
                       class="list-group-item list-group-item-action">
                        <i class="bi bi-list text-info"></i> Ver Todos os Documentos desta Criança
                    </a>

                    @can('document-delete')
                        <form action="{{ route('generated-documents.destroy', $generatedDocument) }}"
                              method="POST"
                              onsubmit="return confirm('Tem certeza que deseja excluir este documento? O arquivo PDF será removido permanentemente.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="list-group-item list-group-item-action text-danger">
                                <i class="bi bi-trash"></i> Excluir Documento
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

            <!-- Informações Adicionais -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações Adicionais</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Template Versão:</strong>
                        <span class="badge bg-info">v{{ $generatedDocument->documentTemplate->version }}</span>
                    </p>
                    <p class="mb-2">
                        <strong>Status do Template:</strong>
                        <span class="badge {{ $generatedDocument->documentTemplate->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $generatedDocument->documentTemplate->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </p>
                    <hr>
                    <p class="text-muted small mb-0">
                        Este documento foi gerado automaticamente pelo sistema usando dados da criança, profissional e checklist selecionados.
                    </p>
                </div>
            </div>
        </div>
    </div>

@endsection
