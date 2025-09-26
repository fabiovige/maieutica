@extends('layouts.app')

@section('title', 'Detalhes do Log de Auditoria')

@section('breadcrumb-items')
<li class="breadcrumb-item"><a href="{{ route('audit.index') }}">Auditoria LGPD</a></li>
<li class="breadcrumb-item active">Log #{{ $auditLog->id }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Log de Auditoria #{{ $auditLog->id }}</h4>
                    <a href="{{ route('audit.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Informações Básicas</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Data/Hora</th>
                                    <td>{{ $auditLog->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Usuário</th>
                                    <td>
                                        @if($auditLog->user)
                                            {{ $auditLog->user->name }}
                                            <small class="text-muted">(ID: {{ $auditLog->user->id }})</small>
                                        @else
                                            <span class="text-muted">Sistema</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Ação</th>
                                    <td>
                                        <span class="badge
                                            @switch($auditLog->action)
                                                @case('CREATE') bg-success @break
                                                @case('UPDATE') bg-warning @break
                                                @case('DELETE') bg-danger @break
                                                @case('read') bg-info @break
                                                @default bg-secondary
                                            @endswitch
                                        ">
                                            {{ $auditLog->action }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Recurso</th>
                                    <td>
                                        {{ $auditLog->resource }}
                                        @if($auditLog->resource_id)
                                            <small class="text-muted">(ID: {{ $auditLog->resource_id }})</small>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5>Informações de Rede</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Endereço IP</th>
                                    <td>{{ $auditLog->ip_address }}</td>
                                </tr>
                                <tr>
                                    <th>User Agent</th>
                                    <td>
                                        @if($auditLog->user_agent)
                                            <small>{{ Str::limit($auditLog->user_agent, 100) }}</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            @if($auditLog->context)
                            <h5>Contexto</h5>
                            <div class="alert alert-info">
                                {{ $auditLog->context }}
                            </div>
                            @endif
                        </div>
                    </div>

                    @if($auditLog->data_before || $auditLog->data_after)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Dados Alterados</h5>

                            <div class="row">
                                @if($auditLog->data_before)
                                <div class="col-md-6">
                                    <h6>Dados Anteriores</h6>
                                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($auditLog->data_before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                </div>
                                @endif

                                @if($auditLog->data_after)
                                <div class="col-md-6">
                                    <h6>Dados Posteriores</h6>
                                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($auditLog->data_after, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                </div>
                                @endif
                            </div>

                            @if($auditLog->data_before && $auditLog->data_after)
                            <div class="mt-3">
                                <h6>Resumo das Alterações</h6>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Campo</th>
                                                <th>Valor Anterior</th>
                                                <th>Valor Novo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($auditLog->data_after as $key => $newValue)
                                                @if(isset($auditLog->data_before[$key]) && $auditLog->data_before[$key] !== $newValue)
                                                <tr>
                                                    <td><strong>{{ $key }}</strong></td>
                                                    <td>
                                                        <span class="text-danger">
                                                            {{ is_string($auditLog->data_before[$key]) ? $auditLog->data_before[$key] : json_encode($auditLog->data_before[$key]) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="text-success">
                                                            {{ is_string($newValue) ? $newValue : json_encode($newValue) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection