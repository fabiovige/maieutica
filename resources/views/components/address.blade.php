<div class="row">
    <div class="col-12 mt-3">
        <h4>Endereço</h4>
    </div>
</div>

<!-- cep -->
<div class="row">
    <div class="mb-2 col-md-4">
        <label for="cep">
            <i class="bi bi-search"></i>
            Informe o CEP
        </label> <br>

        <div class="input-group">
            <input class="form-control @error('cep') is-invalid @enderror" type="text" name="cep" id="cep"
                   value="{{ old('cep', $model->postal_code ?? null) }}" maxlength="9" placeholder="00000-000">
        </div>
        <small class="form-text text-muted">Digite o CEP para preenchimento automático</small>

        @error('cep')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>

</div>

<div class="row">

    <div class="mb-2 col-md-6">
        <label for="logradouro">Logradouro</label> <br>
        <input class="form-control @error('logradouro') is-invalid @enderror" type="text"
        id="logradouro" name="logradouro"
        value="{{ old('logradouro', $model->street ?? null) }}" maxlength="50" readonly>
        @error('logradouro')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="mb-2 col-md-2">
        <label for="numero">Número</label> <br>
        <input class="form-control @error('numero') is-invalid @enderror" type="text"
        id="numero" name="numero"
        value="{{ old('numero', $model->number ?? null) }}" maxlength="10" placeholder="123">
        @error('numero')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="mb-2 col-md-4">
        <label for="complemento">Complemento (opcional)</label> <br>
        <input class="form-control @error('complemento') is-invalid @enderror" type="text"
        id="complemento" name="complemento" value="{{ old('complemento', $model->complement ?? null) }}"
        maxlength="50" placeholder="Apto 101">
        @error('complemento')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>

</div>

<div class="row">

    <div class="mb-2 col-md-4">
        <label for="bairro">Bairro</label> <br>
        <input class="form-control @error('bairro') is-invalid @enderror" type="text"
        id="bairro" name="bairro"
        value="{{ old('bairro', $model->neighborhood ?? null) }}" maxlength="50" readonly>
        @error('bairro')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>
    <div class="mb-2 col-md-4">
        <label for="cidade">Cidade</label> <br>
        <input class="form-control @error('cidade') is-invalid @enderror" type="text"
        id="cidade" name="cidade" value="{{ old('cidade', $model->city ?? null) }}" maxlength="50" readonly>
        @error('cidade')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>
    <div class="mb-2 col-md-4">
        <label for="estado">Estado</label> <br>
        <input class="form-control @error('estado') is-invalid @enderror" type="text"
        id="estado" name="estado" value="{{ old('estado', $model->state ?? null) }}" maxlength="50" readonly>
        @error('estado')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/cep-autocomplete.css') }}">
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="{{ asset('js/cep-autocomplete.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            if (typeof $.fn.mask !== 'undefined') {
                $('input[name="cep"]').mask('00000-000');
            }
        });
    </script>
@endpush
