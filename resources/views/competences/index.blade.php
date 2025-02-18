@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Percentis</li>
        </ol>
    </nav>
@endsection


@section('title')
    Percentis
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-people"></i> Percentis
    </li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12 ">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Formulário de filtro -->
        <form method="GET" action="{{ route('competences.index') }}" class="mb-4" id="filter-form">
            <div class="row">
                <!-- Filtro por Level -->
                <div class="col-md-4">
                    <label for="level_id">Filtrar por Nível</label>
                    <select name="level_id" id="level_id" class="form-control">
                        <option value="">Selecione o Nível</option>
                        @foreach($levels as $level)
                        <option value="{{ $level->id }}" {{ session('level_id') == $level->id ? 'selected' : '' }}>
                            {{ $level->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Domain -->
                <div class="col-md-4">
                    <label for="domain_id">Filtrar por Domínio</label>
                    <select name="domain_id" id="domain_id" class="form-control">
                        <option value="">Selecione o Domínio</option>
                        @foreach($domains as $domain)
                        <option value="{{ $domain->id }}" {{ session('domain_id') == $domain->id ? 'selected' : '' }}>
                            {{ $domain->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Botão de Filtrar -->
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('competences.clearFilters') }}" class="btn btn-secondary ms-2">Limpar Filtros</a>
                </div>
            </div>
        </form>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descrição</th>
                    <th>Detalhe</th>
                    <th>Nível</th>
                    <th>Domínio</th>
                    <th nowrap colspan="4">Percentis (25%, 50%, 75%, 90%)</th>
                    @can('edit competences')<th></th>@endcan
                    <th nowrap>Atualização em</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($competences as $com)
                <form action="{{ route('competences.update', $com->id) }}" id="form_{{$com->id}}" method="post">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $com->id }}" />
                    <tr class="centered-column-vertically">
                        <td>{{ $com->id }}</td>
                        <td>{{ $com->description }}</td>
                        <td>
                            <span class="btn btn-info btn-sm" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="{{ $com->description_detail }}">
                                <i class="bi bi-eye"></i>
</span>
                        </td>
                        <td nowrap>{{ $com->level->name }}</td>
                        <td nowrap>{{ $com->domain->name }} - {{ $com->domain->initial }}</td>
                        <td nowrap><input type="text" value="{{ $com->percentil_25 }}" name="percentil_25" maxlength="2" class="col-8" /></td>
                        <td nowrap><input type="text" value="{{ $com->percentil_50 }}" name="percentil_50" maxlength="2" class="col-8" /></td>
                        <td nowrap><input type="text" value="{{ $com->percentil_75 }}" name="percentil_75" maxlength="2" class="col-8" /></td>
                        <td nowrap><input type="text" value="{{ $com->percentil_90 }}" name="percentil_90" maxlength="2" class="col-8" /></td>
                        @can('edit competences')
                        <td>

                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </td>
                        @endcan
                        <td nowrap>{{ $com->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </form>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const levelSelect = document.getElementById('level_id');
        const domainSelect = document.getElementById('domain_id');

        levelSelect.addEventListener('change', function() {
            const levelId = this.value;

            // Limpa o dropdown de domínios
            domainSelect.innerHTML = '<option value="">Selecione o Domínio</option>';

            if (levelId) {
                // Faz a requisição AJAX para buscar os domínios relacionados ao nível selecionado
                fetch(`/competences/domains-by-level/${levelId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(domain => {
                            const option = document.createElement('option');
                            option.value = domain.id;
                            option.text = domain.name;
                            domainSelect.appendChild(option);
                        });

                        // Se houver um domínio selecionado na sessão, re-seleciona após carregar
                        const selectedDomain = '{{ session("domain_id") }}';
                        if (selectedDomain) {
                            domainSelect.value = selectedDomain;
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar domínios:', error);
                        alert('Ocorreu um erro ao buscar os domínios.');
                    });
            }
        });

        // Trigger change event on page load if a level is already selected
        @if(session('level_id'))
        levelSelect.dispatchEvent(new Event('change'));
        @endif
    });

    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl, {
        trigger: 'hover' // Alteração do trigger para 'hover'
    }));

</script>
@endpush
