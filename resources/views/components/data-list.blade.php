@if($isEmpty())
    @if($hasFiltersApplied)
        <div class="alert alert-warning">
            <i class="bi bi-search"></i> {{ $emptyWithFiltersMessage }}
            @if($clearFiltersUrl)
                <a href="{{ $clearFiltersUrl }}" class="alert-link">Limpar filtros</a>
            @endif
        </div>
    @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> {{ $emptyMessage }}
        </div>
    @endif
@else
    <!-- Mobile View -->
    <div class="d-lg-none">
        @foreach($data as $item)
            <div class="card mb-3">
                <div class="card-body">
                    @foreach($columns as $column)
                        @if($column['label'])
                            <div class="row mb-2">
                                <div class="col-5 text-muted small">{{ $column['label'] }}:</div>
                                <div class="col-7">{!! $getColumnValue($item, $column) !!}</div>
                            </div>
                        @endif
                    @endforeach
                    
                    @if($hasActions())
                        <div class="d-flex justify-content-end gap-1 mt-3">
                            @foreach($actions as $action)
                                @if($shouldShowAction($action, $item))
                                    <a href="{{ $getActionUrl($action, $item) }}" 
                                       class="btn btn-sm {{ $action['class'] }}"
                                       title="{{ $action['label'] }}"
                                       @foreach($action['attributes'] as $attr => $value) {{ $attr }}="{{ $value }}" @endforeach>
                                        <i class="{{ $action['icon'] }}"></i>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Desktop View -->
    <div class="d-none d-lg-block">
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        @foreach($columns as $column)
                            <th class="{{ $column['class'] }}">
                                {{ $column['label'] }}
                                @if($column['sortable'])
                                    <i class="bi bi-arrow-down-up ms-1 text-muted"></i>
                                @endif
                            </th>
                        @endforeach
                        
                        @if($hasActions())
                            <th style="width: 120px;" class="text-center">Ações</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                        <tr>
                            @foreach($columns as $column)
                                <td class="{{ $column['class'] }}">
                                    {!! $getColumnValue($item, $column) !!}
                                </td>
                            @endforeach
                            
                            @if($hasActions())
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        @foreach($actions as $action)
                                            @if($shouldShowAction($action, $item))
                                                @if($action['type'] === 'delete')
                                                    <button type="button" 
                                                            class="btn btn-sm {{ $action['class'] }}"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal"
                                                            data-url="{{ $getActionUrl($action, $item) }}"
                                                            data-name="{{ $getColumnValue($item, $columns[0] ?? []) }}"
                                                            title="{{ $action['label'] }}"
                                                            @foreach($action['attributes'] as $attr => $value) {{ $attr }}="{{ $value }}" @endforeach>
                                                        <i class="{{ $action['icon'] }}"></i>
                                                    </button>
                                                @else
                                                    <a href="{{ $getActionUrl($action, $item) }}" 
                                                       class="btn btn-sm {{ $action['class'] }}"
                                                       title="{{ $action['label'] }}"
                                                       @foreach($action['attributes'] as $attr => $value) {{ $attr }}="{{ $value }}" @endforeach>
                                                        <i class="{{ $action['icon'] }}"></i>
                                                    </a>
                                                @endif
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($hasActions() && in_array('delete', array_column($actions, 'type')))
        <!-- Modal de Confirmação de Exclusão -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Tem certeza de que deseja excluir <strong id="deleteItemName"></strong>?</p>
                        <p class="text-muted small">Esta ação não pode ser desfeita.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <form id="deleteForm" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Excluir</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteModal = document.getElementById('deleteModal');
            const deleteForm = document.getElementById('deleteForm');
            const deleteItemName = document.getElementById('deleteItemName');

            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const url = button.getAttribute('data-url');
                    const name = button.getAttribute('data-name');
                    
                    deleteForm.action = url;
                    deleteItemName.textContent = name;
                });
            }
        });
        </script>
        @endpush
    @endif
@endif