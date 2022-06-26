<div class="col-12 mt-2 d-flex justify-content-between">
    <iv>
        <small class="text-muted">Cadastrado em {{ $data->created_at->format('d/m/Y H:i') }}</small>
        <small class="text-muted"> {{ \App\Models\User::find($data->created_by)->name }}</small>
        <small class="text-muted">  {{ \App\Models\User::find($data->created_by)->role->name }}  </small>
    </iv>

    <div>
        <small class="text-muted">Última atualização em {{ $data->updated_at->format('d/m/Y H:i') }}</small>

        <small class="text-muted"> {{ \App\Models\User::find($data->updated_by)->name ?? '' }}</small>
        <small class="text-muted">  {{ \App\Models\User::find($data->updated_by)->role->name ?? '' }} </small>
    </div>
</div>
