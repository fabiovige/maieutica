<!-- Seção de Endereço -->
<div class="col-12">
    <h5 class="mb-3">
        <i class="bi bi-geo-alt"></i>
        {{ $title }}
        @if(!$required)
            <small class="text-muted">(opcional)</small>
        @endif
    </h5>
</div>

<div class="col-md-4">
    <label for="cep" class="form-label">
        <i class="bi bi-search"></i>
        CEP
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>
    <input type="text" 
           class="form-control @error('cep') is-invalid @enderror" 
           id="cep"
           name="cep" 
           value="{{ old('cep', $cep) }}" 
           placeholder="00000-000" 
           maxlength="9"
           {{ $required ? 'required' : '' }}>
    <small class="form-text text-muted">Digite o CEP para preenchimento automático</small>
    @error('cep')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-6">
    <label for="logradouro" class="form-label">
        Logradouro
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>
    <input type="text" 
           class="form-control @error('logradouro') is-invalid @enderror"
           id="logradouro" 
           name="logradouro" 
           value="{{ old('logradouro', $logradouro) }}" 
           readonly
           {{ $required ? 'required' : '' }}>
    @error('logradouro')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-2">
    <label for="numero" class="form-label">
        Número
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>
    <input type="text" 
           class="form-control @error('numero') is-invalid @enderror"
           id="numero" 
           name="numero" 
           value="{{ old('numero', $numero) }}" 
           placeholder="123"
           {{ $required ? 'required' : '' }}>
    @error('numero')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-4">
    <label for="complemento" class="form-label">Complemento</label>
    <input type="text" 
           class="form-control @error('complemento') is-invalid @enderror"
           id="complemento" 
           name="complemento" 
           value="{{ old('complemento', $complemento) }}"
           placeholder="Apto 101">
    @error('complemento')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-4">
    <label for="bairro" class="form-label">
        Bairro
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>
    <input type="text" 
           class="form-control @error('bairro') is-invalid @enderror"
           id="bairro" 
           name="bairro" 
           value="{{ old('bairro', $bairro) }}" 
           readonly
           {{ $required ? 'required' : '' }}>
    @error('bairro')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-3">
    <label for="cidade" class="form-label">
        Cidade
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>
    <input type="text" 
           class="form-control @error('cidade') is-invalid @enderror"
           id="cidade" 
           name="cidade" 
           value="{{ old('cidade', $cidade) }}" 
           readonly
           {{ $required ? 'required' : '' }}>
    @error('cidade')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-1">
    <label for="estado" class="form-label">
        UF
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>
    <input type="text" 
           class="form-control @error('estado') is-invalid @enderror"
           id="estado" 
           name="estado" 
           value="{{ old('estado', $estado) }}" 
           readonly
           {{ $required ? 'required' : '' }}>
    @error('estado')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<script>
// Inicializa o CEP autocomplete para este componente
document.addEventListener('DOMContentLoaded', function() {
    const cepInput = document.getElementById('cep');
    const logradouroInput = document.getElementById('logradouro');
    const bairroInput = document.getElementById('bairro');
    const cidadeInput = document.getElementById('cidade');
    const estadoInput = document.getElementById('estado');
    const numeroInput = document.getElementById('numero');
    const complementoInput = document.getElementById('complemento');

    if (!cepInput) return;

    function clearAddressFields() {
        if (logradouroInput) logradouroInput.value = '';
        if (bairroInput) bairroInput.value = '';
        if (cidadeInput) cidadeInput.value = '';
        if (estadoInput) estadoInput.value = '';
        if (numeroInput) numeroInput.value = '';
        if (complementoInput) complementoInput.value = '';
    }

    function disableAddressFields() {
        if (logradouroInput) {
            logradouroInput.removeAttribute('readonly');
            logradouroInput.setAttribute('readonly', 'readonly');
            logradouroInput.classList.add('auto-filled');
        }
        if (bairroInput) {
            bairroInput.removeAttribute('readonly');
            bairroInput.setAttribute('readonly', 'readonly');
            bairroInput.classList.add('auto-filled');
        }
        if (cidadeInput) {
            cidadeInput.removeAttribute('readonly');
            cidadeInput.setAttribute('readonly', 'readonly');
            cidadeInput.classList.add('auto-filled');
        }
        if (estadoInput) {
            estadoInput.removeAttribute('readonly');
            estadoInput.setAttribute('readonly', 'readonly');
            estadoInput.classList.add('auto-filled');
        }
    }

    function enableAddressFields() {
        if (logradouroInput) {
            logradouroInput.removeAttribute('readonly');
            logradouroInput.classList.remove('auto-filled');
        }
        if (bairroInput) {
            bairroInput.removeAttribute('readonly');
            bairroInput.classList.remove('auto-filled');
        }
        if (cidadeInput) {
            cidadeInput.removeAttribute('readonly');
            cidadeInput.classList.remove('auto-filled');
        }
        if (estadoInput) {
            estadoInput.removeAttribute('readonly');
            estadoInput.classList.remove('auto-filled');
        }
    }

    async function searchAddressByCep(cep) {
        try {
            cep = cep.replace(/\D/g, '');

            if (cep.length !== 8) {
                clearAddressFields();
                enableAddressFields();
                return;
            }

            disableAddressFields();

            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);

            if (!response.ok) {
                throw new Error('Erro na requisição');
            }

            const data = await response.json();

            if (data.erro === "true" || data.erro === true) {
                clearAddressFields();
                enableAddressFields();
                showToast('CEP não encontrado. Por favor, verifique o CEP informado.', 'error');
                return;
            }

            if (logradouroInput) logradouroInput.value = data.logradouro || '';
            if (bairroInput) bairroInput.value = data.bairro || '';
            if (cidadeInput) cidadeInput.value = data.localidade || '';
            if (estadoInput) estadoInput.value = data.uf || '';

            if (numeroInput) numeroInput.value = '';
            if (complementoInput) complementoInput.value = '';

            disableAddressFields();

            if (numeroInput) {
                setTimeout(() => {
                    numeroInput.focus();
                }, 100);
            }

        } catch (error) {
            clearAddressFields();
            enableAddressFields();
            showToast('Erro ao buscar CEP. Tente novamente.', 'error');
        }
    }

    // Event listeners
    cepInput.addEventListener('blur', function() {
        const cep = this.value;
        if (cep.length >= 8) {
            searchAddressByCep(cep);
        }
    });

    cepInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const cep = this.value;
            if (cep.length >= 8) {
                searchAddressByCep(cep);
            }
        }
    });

    cepInput.addEventListener('input', function() {
        const cep = this.value.replace(/\D/g, '');
        if (cep.length === 8) {
            setTimeout(() => {
                if (this.value.replace(/\D/g, '').length === 8) {
                    searchAddressByCep(this.value);
                }
            }, 500);
        }
    });

    // Máscara de CEP
    cepInput.addEventListener('input', function() {
        let cep = this.value.replace(/\D/g, '');
        if (cep.length <= 8) {
            if (cep.length > 5) {
                this.value = cep.substring(0, 5) + '-' + cep.substring(5);
            } else {
                this.value = cep;
            }
        }
    });

    // Inicializa campos como readonly se já há CEP preenchido
    if (cepInput.value && cepInput.value.replace(/\D/g, '').length >= 8) {
        disableAddressFields();
    } else {
        // Garante que campos de endereço comecem readonly
        disableAddressFields();
    }
});
</script>