@extends('layouts.app')

@section('title', 'Releases')

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-tag"></i> Releases
    </li>
@endsection

@section('content')
    @if($releases->isEmpty())
        <div class="alert alert-info">
            Nenhum release registrado.
        </div>
    @else
        @foreach($releases as $release)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary fs-6 me-2">{{ $release->version }}</span>
                        <strong>{{ $release->title }}</strong>
                    </div>
                    <small class="text-muted">{{ $release->release_date->format('d/m/Y') }}</small>
                </div>
                <div class="card-body">
                    @if($release->description)
                        <p class="text-muted mb-3">{{ $release->description }}</p>
                    @endif

                    @foreach($release->items as $category => $entries)
                        <h5 class="mt-3 mb-2">
                            @switch($category)
                                @case('layout')
                                    <i class="bi bi-layout-sidebar text-primary"></i> Layout e Interface
                                    @break
                                @case('tipografia')
                                    <i class="bi bi-fonts text-info"></i> Tipografia e Design
                                    @break
                                @case('emails')
                                    <i class="bi bi-envelope text-warning"></i> E-mails
                                    @break
                                @case('bugs')
                                    <i class="bi bi-bug text-danger"></i> Correcoes de Bugs
                                    @break
                                @case('features')
                                    <i class="bi bi-stars text-success"></i> Novas Funcionalidades
                                    @break
                                @case('docs')
                                    <i class="bi bi-file-text text-secondary"></i> Documentacao
                                    @break
                                @default
                                    <i class="bi bi-dot"></i> {{ ucfirst($category) }}
                            @endswitch
                        </h5>
                        <ul class="list-group list-group-flush mb-2">
                            @foreach($entries as $entry)
                                <li class="list-group-item border-0 py-1 ps-4">
                                    <i class="bi bi-check2 text-success me-1"></i> {{ $entry }}
                                </li>
                            @endforeach
                        </ul>
                    @endforeach

                    @if($release->commits && count($release->commits) > 0)
                        <hr>
                        <h5 class="mb-2"><i class="bi bi-git text-dark"></i> Commits</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 100px;">Hash</th>
                                        <th>Descricao</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($release->commits as $commit)
                                        <tr>
                                            <td><code>{{ $commit['hash'] }}</code></td>
                                            <td>{{ $commit['message'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
@endsection
