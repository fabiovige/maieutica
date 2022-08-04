<div class="col-12 mt-2 d-flex justify-content-between">
    <div>
        <span class="text-muted small">Cadastrado em: {{ $data->created_at->format('d/m/Y H:i') }}</span>,
        <span class="text-muted small"> por {{ \App\Models\User::find($data->created_by)->name }}</span>
        <span class="text-muted small">  ( {{ \App\Models\User::find($data->created_by)->role->name }} )  </span>
    </div>

    <div>
        <span class="text-muted small">Atualizado em: {{ $data->updated_at->format('d/m/Y H:i') }}</span>,
        <span class="text-muted small"> por {{ \App\Models\User::find($data->updated_by)->name ?? '' }}</span>
        <span class="text-muted small">  ( {{ \App\Models\User::find($data->updated_by)->role->name ?? '' }} ) </span>
    </div>
</div>
