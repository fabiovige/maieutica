@extends('layouts.app')

@section('title')
    Crianças
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-people"></i> Crianças
    </li>
@endsection

@section('actions')
    @can('create kids')
        <a href="{{ route('kids.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nova Criança
        </a>
    @endcan
@endsection

@section('content')


            @if($kids->isEmpty())
                <div class="alert alert-info">
                    Nenhuma criança cadastrada.
                </div>
            @else
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th style="width: 60px;" class="text-center">ID</th>
                            <th>Nome</th>
                            <th>Responsável</th>
                            <th>Data Nasc.</th>
                            <th>Idade</th>
                            <th class="text-center" style="width: 100px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kids as $kid)
                            <tr>
                                <td class="text-center">{{ $kid->id }}</td>
                                <td>{{ $kid->name }}</td>
                                <td>{{ $kid->responsible->name ?? 'N/D' }}</td>
                                <td>{{ $kid->birth_date ?? 'N/D' }}</td>
                                <td>{{ $kid->age ?? 'N/D' }}</td>
                                <td class="text-center">
                                    <div class="btn-group gap-2" role="group">
                                        @can('view kids')
                                            <button type="button"
                                                    onclick="window.location.href='{{ route('kids.overview', $kid->id) }}'"
                                                    class="btn btn-info"
                                                    title="Comparativo">
                                                <i class="bi bi-bar-chart"></i>
                                            </button>
                                            <button type="button"
                                                    onclick="window.location.href='{{ route('analysis.level', ['id' => $kid->id, 'level' => 0]) }}'"
                                                    class="btn btn-warning"
                                                    title="Análise">
                                                <i class="bi bi-graph-down"></i>
                                            </button>
                                            <button type="button"
                                                    onclick="window.location.href='{{ route('kids.overview', $kid->id) }}'"
                                                    class="btn btn-dark"
                                                    title="Desenvolvimento">
                                                <i class="bi bi-graph-up-arrow"></i>
                                            </button>
                                            <button type="button"
                                                    onclick="window.location.href='{{ route('checklists', ['kidId' => $kid->id]) }}'"
                                                    class="btn btn-success"
                                                    title="Checklist">
                                                <i class="bi bi-check2-square"></i>
                                            </button>
                                            <button type="button"
                                                    onclick="window.location.href='{{ route('kids.development', $kid->id) }}'"
                                                    class="btn btn-primary"
                                                    title="Desenvolvimento">
                                                <i class="bi bi-graph-up"></i>
                                            </button>
                                        @endcan
                                        @can('edit kids')
                                            <button type="button"
                                                    onclick="window.location.href='{{ route('kids.edit', $kid->id) }}'"
                                                    class="btn btn-secondary"
                                                    title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endcan
                                        @can('remove kids')
                                            <form action="{{ route('kids.destroy', $kid->id) }}"
                                                  method="POST"
                                                  style="display: contents;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-danger"
                                                        onclick="return confirm('Tem certeza que deseja excluir?')"
                                                        title="Excluir">
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

                <div class="d-flex justify-content-end">
                    {{ $kids->links() }}
                </div>
            @endif



@endsection
