@if($hasResults())
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mt-4 gap-2">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">
            <div class="text-muted">
                {{ $getResultsText() }}
            </div>
            <div class="d-flex align-items-center">
                <label for="{{ $getPerPageSelectId() }}" class="form-label me-2 mb-0 text-muted small">
                    Itens por página:
                </label>
                <select 
                    class="form-select form-select-sm" 
                    id="{{ $getPerPageSelectId() }}" 
                    style="width: auto; min-width: 70px;" 
                    onchange="changePagination(this.value)"
                >
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}" {{ $getCurrentPerPage() == $option ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        @if($shouldShowPagination())
            <div class="align-self-end align-self-md-center">
                {{ $paginator->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
    function changePagination(perPageValue) {
        // Preserva todos os parâmetros atuais da URL
        var urlParams = new URLSearchParams(window.location.search);
        urlParams.set('per_page', perPageValue);
        urlParams.set('page', '1'); // Sempre vai para página 1 ao mudar per_page
        
        // Monta a URL final
        var newUrl = window.location.pathname + '?' + urlParams.toString();
        
        // Redireciona para a nova URL
        window.location.href = newUrl;
    }
    </script>
    @endpush
@else
    <div class="alert alert-info text-center mt-4">
        <i class="bi bi-info-circle"></i> {{ $getResultsText() }}
    </div>
@endif