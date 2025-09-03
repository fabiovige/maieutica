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

                            <x-address-form 
                                :cep="old('cep', $user->postal_code)"
                                :logradouro="old('logradouro', $user->street)"
                                :numero="old('numero', $user->number)"
                                :complemento="old('complemento', $user->complement)"
                                :bairro="old('bairro', $user->neighborhood)"
                                :cidade="old('cidade', $user->city)"
                                :estado="old('estado', $user->state)"
                                title="Endereço"
                                :required="false"
                            />
                        </div>

                    </div>
                    <div class="card-footer bg-transparent mt-4">
                        <div class="d-flex justify-content-between gap-2">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>
                            <x-button icon="check-lg" name="Salvar" type="submit" class="success"></x-button>
                        </div>
                    </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Máscara de telefone
    document.addEventListener('DOMContentLoaded', function() {
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

            phoneInput.addEventListener('keypress', function(e) {
                if (!/\d/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'Tab') {
                    e.preventDefault();
                }
            });
        }
    });
</script>
@endpush
