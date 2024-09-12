<div class="row">
    <div class="col-12 mt-3">
        <h4>Endereço</h4>
    </div>
</div>

<!-- cep -->
<div class="row">
    <div class="mb-2 col-md-4">
        <label for="cep">Informe o cep</label> <br>

        <div class="input-group">
            <input class="form-control @error('cep') is-invalid @enderror" type="text" name="cep" id="cep" value="{{ old('cep') ??$model->cep ?? '' }}" maxlength="8" >
        </div>

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
        value="{{ old('logradouro') ?? $model->logradouro ?? '' }}" maxlength="50" >
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
        value="{{ old('numero') ?? $model->numero ?? '' }}" maxlength="10">
        @error('numero')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="mb-2 col-md-4">
        <label for="complemento">Complemento (opcional)</label> <br>
        <input class="form-control @error('complemento') is-invalid @enderror" type="text"
        id="complemento" name="complemento" value="{{ old('complemento') ?? $model->complemento ?? '' }}" maxlength="50" >
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
        value="{{ old('bairro') ?? $model->bairro ?? '' }}" maxlength="50"  >
        @error('bairro')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>
    <div class="mb-2 col-md-4">
        <label for="cidade">Cidade</label> <br>
        <input class="form-control @error('cidade') is-invalid @enderror" type="text"
        id="cidade" name="cidade" value="{{ old('cidade') ?? $model->cidade ?? '' }}" maxlength="50"  >
        @error('cidade')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>
    <div class="mb-2 col-md-4">
        <label for="estado">Estado</label> <br>
        <input class="form-control @error('estado') is-invalid @enderror" type="text"
        id="estado" name="estado" value="{{ old('estado') ?? $model->estado ?? '' }}" maxlength="50"  >
        @error('estado')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>
</div>



@push ('scripts')

    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>

    <script type="text/javascript">

        var zipCodeField = document.querySelector('#cep')
        //var submitButton = document.querySelector('#btnConsultarCep')

        var logradouro = document.querySelector('#logradouro')
        var bairro = document.querySelector('#bairro')
        var cidade = document.querySelector('#cidade')
        var estado = document.querySelector('#estado')
        var numero = document.querySelector('#numero')
        var complemento = document.querySelector('#complemento')


        //submitButton.addEventListener('click', run)

        // Evento para quando o usuário terminar de digitar o CEP
        zipCodeField.addEventListener('keyup', function(event) {
            var zipCode = zipCodeField.value;
            console.log(zipCode);
            // Executa a função se o CEP tiver exatamente 8 dígitos
            if (zipCode.length === 8) {
                run();  // Chama a função de busca de CEP
            }
        });

        function run(){
            var zipCode = zipCodeField.value

            if(zipCode.length < 8){
                zipCodeField.focus()
                return ;
            }
            axios
                .get('https://viacep.com.br/ws/' + zipCode + '/json/')
                .then(function(response) {
                    console.log(response.data)
                    logradouro.value = response.data.logradouro
                    bairro.value = response.data.bairro
                    cidade.value = response.data.localidade
                    estado.value = response.data.uf
                    numero.value = ''
                    complemento.value = ''
                    numero.focus()
                })
                .catch(function(error) {
                    console.log(error)
                })

        }
    </script>
@endpush
