@extends('layouts.app')

@section('title')
    Lixeira - Templates
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('document-templates.index') }}">
            <i class="bi bi-file-earmark-text"></i> Templates
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-trash"></i> Lixeira
    </li>
@endsection

@section('actions')
    <a href="{{ route('document-templates.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
@endsection

@section('content')

    @if ($templates->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            A lixeira está vazia.
        </div>
    @else
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Atenção:</strong> Templates na lixeira podem ser restaurados.
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;" class="text-center">ID</th>
                        <th>Nome</th>
                        <th style="width: 180px;">Tipo</th>
                        <th style="width: 180px;" class="text-center">Excluído em</th>
                        <th style="width: 120px;" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($templates as $template)
                        <tr>
                            <td class="text-center">{{ $template->id }}</td>
                            <td>
                                <strong>{{ $template->name }}</strong>
                                @if($template->description)
                                    <br><small class="text-muted">{{ Str::limit($template->description, 80) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ App\Models\DocumentTemplate::getDocumentTypes()[$template->type] ?? $template->type }}
                                </span>
                            </td>
                            <td class="text-center">
                                {{ $template->deleted_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-center">
                                @can('template-edit-all')
                                    <form action="{{ route('document-templates.restore', $template->id) }}"
                                          method="POST"
                                          style="display: inline;"
                                          onsubmit="return confirm('Deseja restaurar este template?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Restaurar">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Exibindo {{ $templates->firstItem() }} a {{ $templates->lastItem() }} de {{ $templates->total() }} templates
            </div>
            <div>
                {{ $templates->links() }}
            </div>
        </div>
    @endif

@endsection
