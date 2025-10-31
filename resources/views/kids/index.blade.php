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
    @can('kid-create')
        <a href="{{ route('kids.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nova Criança
        </a>
    @endcan
@endsection

@section('content')

    <!-- Filtro de Busca -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('kids.index') }}" class="row g-3">
                <div class="col-md-10">
                    <label for="search" class="form-label">
                        <i class="bi bi-search"></i> Buscar Criança
                    </label>
                    <input type="text"
                           class="form-control"
                           id="search"
                           name="search"
                           placeholder="Buscar por nome, responsável ou profissional..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        @if(request('search'))
                            <a href="{{ route('kids.index') }}" class="btn btn-secondary" title="Limpar filtro">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(request('search'))
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Exibindo resultados da busca por "<strong>{{ request('search') }}</strong>".
            <strong>{{ $kids->total() }}</strong> criança(s) encontrada(s).
        </div>
    @endif

    @if ($kids->isEmpty())
        <div class="alert alert-info">
            Nenhuma criança cadastrada.
        </div>
    @else
<table class="table table-hover table-bordered align-middle mt-3">
    <thead>
        <tr>
            <th style="width: 60px" class="text-center align-middle">ID</th>
            <th style="width: 60px" class="text-center align-middle">Foto</th>
            <th class="align-middle">Nome</th>
            <th class="align-middle">Responsável</th>
            <th class="align-middle">Profissionais</th>
            <th class="align-middle">Data Nasc.</th>
            <th class="align-middle">Idade</th>
            <th width="100">Ações</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($kids as $kid)
        <tr>
            <td class="text-center align-middle">{{ $kid->id }}</td>
            <td class="text-center align-middle">
                <div class="d-flex align-items-center">
                    @if ($kid->photo)
                    <img
                        src="{{ asset($kid->photo) }}"
                        class="rounded-circle me-2"
                        width="40"
                        height="40"
                        alt="{{ $kid->name }}"
                    />
                    @else
                    <div
                        class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto"
                        style="width: 40px; height: 40px"
                    >
                        <i class="bi bi-person text-white"></i>
                    </div>
                    @endif
                </div>
            </td>
            <td class="align-middle">{{ $kid->name }}</td>
            <td class="align-middle">{{ $kid->responsible->name ?? 'N/D' }}</td>
            <td class="align-middle">
                @if($kid->professionals && $kid->professionals->count() > 0)
                    @foreach($kid->professionals as $professional)
                        <span class="badge bg-info text-dark mb-1" title="{{ $professional->specialty->name ?? 'Sem especialidade' }}">
                            <i class="bi bi-person-badge"></i>
                            {{ $professional->user->first()->name ?? 'N/D' }}
                            @if($professional->specialty)
                                <small>({{ $professional->specialty->initial ?? $professional->specialty->name }})</small>
                            @endif
                        </span>
                        @if(!$loop->last)<br>@endif
                    @endforeach
                @else
                    <span class="text-muted"><i class="bi bi-dash"></i> Nenhum</span>
                @endif
            </td>
            <td class="align-middle">{{ $kid->birth_date ?? 'N/D' }}</td>
            <td class="align-middle">{{ $kid->age ?? 'N/D' }}</td>
            <td class="align-middle">
                <div class="dropdown">
                    <button
                        class="btn btn-sm btn-secondary dropdown-toggle"
                        type="button"
                        id="dropdownMenuButton"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        Ações
                    </button>
                    <ul
                        class="dropdown-menu"
                        aria-labelledby="dropdownMenuButton"
                    >
                        @if(auth()->user()->can('kid-show') || auth()->user()->id === $kid->responsible_id)
                        <li>
                            <a
                                class="dropdown-item"
                                href="{{ route('kids.show', $kid->id) }}"
                            >
                                <i class="bi bi-eye"></i> Visualizar
                            </a>
                        </li>
                        @endif

                        @can('kid-edit')
                        <li>
                            <a
                                class="dropdown-item"
                                href="{{ route('kids.edit', $kid->id) }}"
                                ><i class="bi bi-pencil"></i> Editar</a
                            >
                        </li>
                        @endcan

                        @if(auth()->user()->can('checklist-list') || auth()->user()->id === $kid->responsible_id)
                        <li>
                            <a
                                class="dropdown-item"
                                href="{{ route('checklists.index', ['kidId' => $kid->id]) }}"
                            >
                                <i class="bi bi-card-checklist"></i> Checklists
                            </a>
                        </li>
                        @endif

                        @if(auth()->user()->can('kid-list') || auth()->user()->id === $kid->responsible_id)
                        <li>
                            <a
                                class="dropdown-item"
                                href="{{ route('kids.radarChart2', ['kidId' => $kid->id, 'levelId' => 0]) }}"
                            >
                                <i class="bi bi-clipboard-data"></i> Comparativo
                            </a>
                        </li>

                        <li>
                            <a
                                class="dropdown-item"
                                href="{{ route('kids.overview', ['kidId' => $kid->id]) }}"
                            >
                                <i class="bi bi-bar-chart"></i> Desenvolvimento
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
        <div class="d-flex justify-content-end">
            {{ $kids->appends(request()->query())->links() }}
        </div>
    @endif
@endsection
