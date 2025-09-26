@extends('layouts.app')

@section('title', 'Logs de Auditoria LGPD')

@section('breadcrumb-items')
<li class="breadcrumb-item active">Auditoria LGPD</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Logs de Auditoria LGPD</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('audit.stats') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-chart-bar"></i> Estatísticas
                        </a>
                        <a href="{{ route('audit.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                           class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> Exportar CSV
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filtros -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="user_id" class="form-label">Usuário</label>
                            <select name="user_id" id="user_id" class="form-select">
                                <option value="">Todos os usuários</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="action" class="form-label">Ação</label>
                            <select name="action" id="action" class="form-select">
                                <option value="">Todas as ações</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                        {{ $action }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="resource" class="form-label">Recurso</label>
                            <select name="resource" id="resource" class="form-select">
                                <option value="">Todos os recursos</option>
                                @foreach($resources as $resource)
                                    <option value="{{ $resource }}" {{ request('resource') == $resource ? 'selected' : '' }}>
                                        {{ $resource }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="start_date" class="form-label">Data Início</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                   value="{{ request('start_date') }}">
                        </div>

                        <div class="col-md-2">
                            <label for="end_date" class="form-label">Data Fim</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                   value="{{ request('end_date') }}">
                        </div>

                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                            </div>
                        </div>
                    </form>

                    <!-- Tabela de Logs -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Usuário</th>
                                    <th>Ação</th>
                                    <th>Recurso</th>
                                    <th>IP</th>
                                    <th>Detalhes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($auditLogs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        @if($log->user)
                                            {{ $log->user->name }}
                                        @else
                                            <span class="text-muted">Sistema</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge
                                            @switch($log->action)
                                                @case('CREATE') bg-success @break
                                                @case('UPDATE') bg-warning @break
                                                @case('DELETE') bg-danger @break
                                                @case('READ') bg-info @break
                                                @default bg-secondary
                                            @endswitch
                                        ">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $log->resource }}
                                        @if($log->resource_id)
                                            <small class="text-muted">#{{ $log->resource_id }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $log->ip_address }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('audit.show', $log) }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Nenhum log encontrado para os filtros aplicados.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <div class="d-flex justify-content-center">
                        {{ $auditLogs->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change
    const filters = document.querySelectorAll('#user_id, #action, #resource');
    filters.forEach(filter => {
        filter.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>
@endpush