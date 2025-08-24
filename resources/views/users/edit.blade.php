@extends('layouts.app')

@section('title')
    Editar Usuário
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('users.index') }}">
            <i class="bi bi-person"></i> Usuários
        </a>
    </li>
    <li class="breadcrumb-item active">
        Editar
    </li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-12">
            <form action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nome</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $user->name) }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email', $user->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="role_id" class="form-label">Perfil</label>
                                <select class="form-select @error('role_id') is-invalid @enderror" id="role_id"
                                    name="role_id">
                                    <option value="">Selecione...</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ old('role_id', $user->roles->first()->id ?? '') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">
                                    Telefone <small class="text-muted">(opcional)</small>
                                </label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                    placeholder="(11) 99999-9999" maxlength="15">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input @error('allow') is-invalid @enderror"
                                        id="allow" name="allow" value="1"
                                        {{ old('allow', $user->allow) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow">
                                        Liberado para acessar o sistema
                                    </label>
                                    @error('allow')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Seção de Endereço -->
                            <div class="col-12">
                                <h5 class="mb-3">
                                    <i class="bi bi-geo-alt"></i>
                                    Endereço
                                </h5>
                            </div>

                            <div class="col-md-4">
                                <label for="cep" class="form-label">
                                    <i class="bi bi-search"></i>
                                    CEP
                                </label>
                                <input type="text" class="form-control @error('cep') is-invalid @enderror" id="cep"
                                    name="cep" value="{{ old('cep', $user->postal_code) }}" placeholder="00000-000" maxlength="9">
                                <small class="form-text text-muted">Digite o CEP para preenchimento automático</small>
                                @error('cep')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="logradouro" class="form-label">Logradouro</label>
                                <input type="text" class="form-control @error('logradouro') is-invalid @enderror"
                                    id="logradouro" name="logradouro" value="{{ old('logradouro', $user->street) }}" readonly>
                                @error('logradouro')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="numero" class="form-label">Número</label>
                                <input type="text" class="form-control @error('numero') is-invalid @enderror"
                                    id="numero" name="numero" value="{{ old('numero', $user->number) }}" placeholder="123">
                                @error('numero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="complemento" class="form-label">Complemento</label>
                                <input type="text" class="form-control @error('complemento') is-invalid @enderror"
                                    id="complemento" name="complemento"
                                    value="{{ old('complemento', $user->complement) }}" placeholder="Apto 101">
                                @error('complemento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="bairro" class="form-label">Bairro</label>
                                <input type="text" class="form-control @error('bairro') is-invalid @enderror"
                                    id="bairro" name="bairro" value="{{ old('bairro', $user->neighborhood) }}" readonly>
                                @error('bairro')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="cidade" class="form-label">Cidade</label>
                                <input type="text" class="form-control @error('cidade') is-invalid @enderror"
                                    id="cidade" name="cidade" value="{{ old('cidade', $user->city) }}" readonly>
                                @error('cidade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-1">
                                <label for="estado" class="form-label">UF</label>
                                <input type="text" class="form-control @error('estado') is-invalid @enderror"
                                    id="estado" name="estado" value="{{ old('estado', $user->state) }}" readonly>
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                    <div class="card-footer bg-transparent mt-4">
                        <div class="d-flex justify-content-start gap-2">
                            <x-button icon="check-lg" name="Salvar" type="submit" class="success"></x-button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg"></i> Cancelar
                            </a>
                        </div>
                    </div>
            </form>
        </div>
    </div>
@endsection

    <script>
        setTimeout(function() {
            // CEP Autocomplete
            const cepInput = document.getElementById('cep');
            if (cepInput) {
                cepInput.addEventListener('input', function() {
                    let cep = this.value.replace(/\D/g, '');
                    
                    if (cep.length === 8) {
                        fetch('https://viacep.com.br/ws/' + cep + '/json/')
                            .then(response => response.json())
                            .then(data => {
                                if (!data.erro) {
                                    document.getElementById('logradouro').value = data.logradouro || '';
                                    document.getElementById('bairro').value = data.bairro || '';
                                    document.getElementById('cidade').value = data.localidade || '';
                                    document.getElementById('estado').value = data.uf || '';
                                    document.getElementById('numero').focus();
                                }
                            })
                            .catch(error => {
                                console.error('Erro ao buscar CEP:', error);
                            });
                    }
                });
            }

            // Máscara de telefone
            const phoneInput = document.getElementById('phone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function() {
                    let phone = this.value.replace(/\D/g, '');
                    
                    if (phone.length <= 11) {
                        if (phone.length <= 2) {
                            this.value = phone;
                        } else if (phone.length <= 7) {
                            this.value = `(${phone.substring(0, 2)}) ${phone.substring(2)}`;
                        } else {
                            this.value = `(${phone.substring(0, 2)}) ${phone.substring(2, 7)}-${phone.substring(7, 11)}`;
                        }
                    }
                });

                // Permitir apenas números
                phoneInput.addEventListener('keypress', function(e) {
                    if (!/\d/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'Tab') {
                        e.preventDefault();
                    }
                });
            }
        }, 500);
    </script>
