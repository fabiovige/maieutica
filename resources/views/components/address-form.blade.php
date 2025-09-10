{{-- DEBUG: Verificar dados recebidos pelo componente --}}
@if(config('app.debug'))
    <div class="col-12">
        <div class="alert alert-warning small mb-3">
            <strong>COMPONENT DEBUG:</strong>
            CEP: "{{ $cep }}" |
            Logradouro: "{{ $logradouro }}" |
            Numero: "{{ $numero }}" |
            Cidade: "{{ $cidade }}"
        </div>
    </div>
@endif

@if ($shouldRenderTitle())
    <!-- Seção de Endereço -->
    <div class="col-12">
        <h5 class="mb-3">
            <i class="bi bi-geo-alt"></i>
            {{ $title }}
            @if (!$required)
                <small class="text-muted">(opcional)</small>
            @endif
        </h5>
    </div>
@endif

<!-- Linha 1: CEP -->
<div class="row">
    <div class="col-12 col-md-2">
        <label for="cep" class="form-label">
            <i class="bi bi-search"></i>
            CEP
            {!! $getRequiredIndicator() !!}
        </label>
        <input type="text" class="form-control @error('cep') is-invalid @enderror" id="cep" name="cep"
            value="{{ old('cep', $getCepFormatted()) }}" placeholder="00000-000" maxlength="9"
            autocomplete="postal-code" aria-describedby="cep-help" {{ $getRequiredAttribute() }}>
        @error('cep')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>


    <!-- Linha 2: Logradouro, Número, Complemento -->
    <div class="col-12 col-md-4">
        <label for="logradouro" class="form-label">
            Logradouro
            {!! $getRequiredIndicator() !!}
        </label>
        <input type="text" class="form-control address-field @error('logradouro') is-invalid @enderror"
            id="logradouro" name="logradouro" value="{{ old('logradouro', $logradouro) }}" autocomplete="address-line1"
            readonly {{ $getRequiredAttribute() }}>
        @error('logradouro')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>


    <div class="col-6 col-md-2">
        <label for="numero" class="form-label">
            Número
            {!! $getRequiredIndicator() !!}
        </label>
        <input type="text" class="form-control @error('numero') is-invalid @enderror" id="numero" name="numero"
            value="{{ old('numero', $numero) }}" placeholder="123" autocomplete="address-line2"
            {{ $getRequiredAttribute() }}>
        @error('numero')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>


    <div class="col-6 col-md-4">
        <label for="complemento" class="form-label">Complemento</label>
        <input type="text" class="form-control @error('complemento') is-invalid @enderror" id="complemento"
            name="complemento" value="{{ old('complemento', $complemento) }}" placeholder="Apto 101">
        @error('complemento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <!-- Linha 3: Bairro, Cidade, Estado -->
    <div class="col-12 col-md-4">
        <label for="bairro" class="form-label">
            Bairro
            {!! $getRequiredIndicator() !!}
        </label>
        <input type="text" class="form-control address-field @error('bairro') is-invalid @enderror" id="bairro"
            name="bairro" value="{{ old('bairro', $bairro) }}" autocomplete="address-level2" readonly
            {{ $getRequiredAttribute() }}>
        @error('bairro')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-8 col-md-6">
        <label for="cidade" class="form-label">
            Cidade
            {!! $getRequiredIndicator() !!}
        </label>
        <input type="text" class="form-control address-field @error('cidade') is-invalid @enderror" id="cidade"
            name="cidade" value="{{ old('cidade', $cidade) }}" autocomplete="address-level2" readonly
            {{ $getRequiredAttribute() }}>
        @error('cidade')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>


    <div class="col-4 col-md-2">
        <label for="estado" class="form-label">
            UF
            {!! $getRequiredIndicator() !!}
        </label>
        <input type="text" class="form-control address-field @error('estado') is-invalid @enderror" id="estado"
            name="estado" value="{{ old('estado', $estado) }}" autocomplete="address-level1" readonly
            {{ $getRequiredAttribute() }}>
        @error('estado')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@once
    @push('styles')
        <style>
            .auto-filled {
                background-color: #f8f9fa !important;
                border-color: #dee2e6;
            }

            input[data-loading="true"] {
                background-image: url("data:image/svg+xml,%3csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3e%3cg fill='none' fill-rule='evenodd'%3e%3cg fill='%236c757d' fill-rule='nonzero'%3e%3cpath d='M10 3C6.13401 3 3 6.13401 3 10s3.13401 7 7 7 7-3.13401 7-7-3.13401-7-7-7zM10 15c-2.76142 0-5-2.23858-5-5s2.23858-5 5-5 5 2.23858 5 5-2.23858 5-5 5z'/%3e%3cpath d='M10 1C8.68678 1 7.60322 2.08356 7.60322 3.39678s1.08356 2.39678 2.39678 2.39678 2.39678-1.08356 2.39678-2.39678S11.31322 1 10 1z'%3e%3canimateTransform attributeName='transform' type='rotate' values='0 10 10;360 10 10' dur='1s' repeatCount='indefinite'/%3e%3c/path%3e%3c/g%3e%3c/g%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right 10px center;
                background-size: 20px;
            }

            .address-field.loading {
                background: linear-gradient(90deg, #f8f9fa 25%, #ffffff 50%, #f8f9fa 75%) !important;
                background-size: 200% 100% !important;
                animation: subtle-loading 1.5s ease-in-out infinite !important;
                border-color: #198754 !important;
                color: #6c757d !important;
            }

            .address-field.loading::placeholder {
                color: #6c757d !important;
                font-style: italic !important;
            }

            @keyframes subtle-loading {
                0% {
                    background-position: -200% 0;
                }

                100% {
                    background-position: 200% 0;
                }
            }

            .address-field-disabled {
                background-color: #f8f9fa;
                cursor: not-allowed;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="{{ asset('js/address-cep.js') }}?v={{ time() }}"></script>
    @endpush
@endonce
