{{--
    Componente padrão para dropdown de ações em tabelas
    
    Uso:
    @component('components.table-actions')
        @slot('items')
            <li><a class="dropdown-item" href="{{ route('model.edit', $id) }}">Editar</a></li>
            <li><a class="dropdown-item" href="{{ route('model.show', $id) }}">Visualizar</a></li>
        @endslot
    @endcomponent
--}}
<div class="dropdown">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" data-bs-auto-close="true" data-bs-boundary="viewport" data-bs-strategy="fixed" aria-expanded="false">
        Ações
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow">
        {{ $items ?? $slot }}
    </ul>
</div>
