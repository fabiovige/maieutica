<div class="row">
    <div class="col-md-6 mt-2">
        <span class="text-muted small">Cadastrado em: {{ $data->created_at->format('d/m/Y H:i:s') }}</span>
    </div>
    <div class="col-md-6 mt-2">
        <span class="text-muted small d-flex justify-content-end">Última atualização: {{ $data->updated_at->format('d/m/Y H:i:s') }}</span>
    </div>
</div>

@can($action)
    <div class="row">
        <div class="col-md-12 mt-5">
            <form class="d-flex justify-content-start" action="{{ route($action, $data->id) }}" name="form-delete" method="post">
                @csrf
                @method('DELETE')
                <x-button icon="trash" name="Enviar para lixeira" type="submit" class="danger form-delete"></x-button>
            </form>
        </div>
    </div>
@endcan
