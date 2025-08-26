@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">Debug - Teste Componentes</div>
    <div class="card-body">
        <h5>1. Teste DataFilter</h5>
        <x-data-filter
            :filters="[
                [
                    'type' => 'text',
                    'name' => 'search',
                    'placeholder' => 'Digite algo...',
                    'value' => '',
                    'class' => 'col-md-6'
                ]
            ]"
            action-route="checklists.index"
            :hidden-fields="[]"
            :total-results="10"
            entity-name="checklist"
        />

        <hr>

        <h5>2. Dados Recebidos</h5>
        <pre>
@if(isset($checklists))
Checklists count: {{ $checklists->count() }}
Has items: {{ $checklists->isNotEmpty() ? 'yes' : 'no' }}
@if($checklists->isNotEmpty())
First item: {{ json_encode($checklists->first()->toArray(), JSON_PRETTY_PRINT) }}
@endif
@else
No checklists variable
@endif
        </pre>

        <hr>

        <h5>3. Teste DataList Simples</h5>
        @if(isset($checklists) && $checklists->isNotEmpty())
            <x-data-list
                :data="$checklists"
                :columns="[
                    ['label' => 'ID', 'attribute' => 'id'],
                    ['label' => 'Situação', 'attribute' => 'situation']
                ]"
                :actions="[]"
                empty-message="Nenhum item encontrado"
            />
        @else
            <p>Sem dados para testar DataList</p>
        @endif

        <hr>

        <h5>4. Teste DataPagination</h5>
        @if(isset($checklists) && method_exists($checklists, 'links'))
            <x-data-pagination :paginator="$checklists" />
        @else
            <p>Sem paginação para testar</p>
        @endif
    </div>
</div>

@endsection