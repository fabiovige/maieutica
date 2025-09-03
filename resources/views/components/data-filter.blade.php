<!-- Filtros de busca -->
<div class="card mb-3">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <h6 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h6>
            @if($totalResults !== null && $totalResults > 0)
                <small class="text-muted ms-3">
                    {{ $totalResults }} {{ $totalResults == 1 ? $entityName . ' encontrado' : $entityName . 's encontrados' }}
                </small>
            @endif
        </div>
        <button
            class="btn btn-sm btn-outline-secondary"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#filterCollapse"
            aria-expanded="true"
            aria-controls="filterCollapse"
            id="filterToggleBtn"
            title="Expandir/Recolher Filtros"
        >
            <i class="bi bi-chevron-up" id="filterToggleIcon"></i>
        </button>
    </div>
    <div class="collapse show" id="filterCollapse">
        <div class="card-body">
            <form method="GET" action="{{ route($actionRoute) }}" id="filter-form">
                @foreach($hiddenFields as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach
                
                <div class="row g-3">
                    @foreach($filters as $filter)
                        <div class="{{ $filter['class'] }}">
                            @php
                                $filterType = \App\Enums\FilterType::from($filter['type']);
                                $inputClass = $filterType->getInputClass();
                                $wrapperClass = $filterType->getWrapperClass();
                            @endphp

                            @if($wrapperClass)
                                <div class="{{ $wrapperClass }}">
                            @endif

                            @switch($filterType)
                                @case(\App\Enums\FilterType::TEXT)
                                @case(\App\Enums\FilterType::DATE)
                                @case(\App\Enums\FilterType::NUMBER)
                                    @if($filter['label'])
                                        <label for="{{ $filter['name'] }}" class="form-label">{{ $filter['label'] }}</label>
                                    @endif
                                    <input
                                        type="{{ $filter['type'] }}"
                                        class="{{ $inputClass }}"
                                        id="{{ $filter['name'] }}"
                                        name="{{ $filter['name'] }}"
                                        value="{{ $filter['value'] }}"
                                        placeholder="{{ $filter['placeholder'] }}"
                                        @if($filter['required']) required @endif
                                        @foreach($filter['attributes'] as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach
                                    >
                                    @break

                                @case(\App\Enums\FilterType::SELECT)
                                    @if($filter['label'])
                                        <label for="{{ $filter['name'] }}" class="form-label">{{ $filter['label'] }}</label>
                                    @endif
                                    <select
                                        class="{{ $inputClass }}"
                                        id="{{ $filter['name'] }}"
                                        name="{{ $filter['name'] }}"
                                        @if($filter['required']) required @endif
                                        @foreach($filter['attributes'] as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach
                                    >
                                        @foreach($filter['options'] as $value => $label)
                                            <option value="{{ $value }}" {{ $filter['value'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @break

                                @case(\App\Enums\FilterType::CHECKBOX)
                                    <input
                                        type="checkbox"
                                        class="{{ $inputClass }}"
                                        id="{{ $filter['name'] }}"
                                        name="{{ $filter['name'] }}"
                                        value="1"
                                        {{ $filter['value'] ? 'checked' : '' }}
                                        @if($filter['required']) required @endif
                                        @foreach($filter['attributes'] as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach
                                    >
                                    @if($filter['label'])
                                        <label for="{{ $filter['name'] }}" class="form-check-label">{{ $filter['label'] }}</label>
                                    @endif
                                    @break

                                @case(\App\Enums\FilterType::RADIO)
                                    @foreach($filter['options'] as $value => $label)
                                        <input
                                            type="radio"
                                            class="{{ $inputClass }}"
                                            id="{{ $filter['name'] }}_{{ $value }}"
                                            name="{{ $filter['name'] }}"
                                            value="{{ $value }}"
                                            {{ $filter['value'] == $value ? 'checked' : '' }}
                                            @if($filter['required']) required @endif
                                            @foreach($filter['attributes'] as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach
                                        >
                                        <label for="{{ $filter['name'] }}_{{ $value }}" class="form-check-label">{{ $label }}</label>
                                        <br>
                                    @endforeach
                                    @break
                            @endswitch

                            @if($wrapperClass)
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2" title="Buscar">
                            <i class="bi bi-search"></i>
                        </button>
                        <a href="{{ route($actionRoute, $hiddenFields) }}" class="btn btn-outline-secondary" title="Limpar Filtros">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterToggleBtn = document.getElementById('filterToggleBtn');
    const filterToggleIcon = document.getElementById('filterToggleIcon');
    const filterCollapse = document.getElementById('filterCollapse');

    if (filterCollapse) {
        filterCollapse.addEventListener('shown.bs.collapse', function () {
            filterToggleIcon.classList.remove('bi-chevron-down');
            filterToggleIcon.classList.add('bi-chevron-up');
        });

        filterCollapse.addEventListener('hidden.bs.collapse', function () {
            filterToggleIcon.classList.remove('bi-chevron-up');
            filterToggleIcon.classList.add('bi-chevron-down');
        });
    }
});
</script>
@endpush