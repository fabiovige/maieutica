<div class="col-12 mt-2 d-flex justify-content-between">
    <div>
        <span class="text-muted small">Cadastrado em: {{ $data->created_at->format('d/m/Y H:i:s') }}</span>
    </div>

    <div>
        <span class="text-muted small">Última atualização: {{ $data->updated_at->format('d/m/Y H:i:s') }}</span>
    </div>
</div>
