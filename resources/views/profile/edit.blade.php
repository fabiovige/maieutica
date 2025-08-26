@extends('layouts.app')

@section('title')
    Meu Perfil
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-person"></i> Meu Perfil
    </li>
@endsection

@section('content')
    <div class="row">
        <!-- Adicionar antes dos outros cards -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Foto do Perfil</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            @if($user->avatar && file_exists(public_path($user->avatar)))
                                <img src="{{ asset($user->avatar) }}"
                                     alt="Avatar"
                                     class="rounded-circle img-thumbnail mb-3"
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mb-3 mx-auto"
                                     style="width: 150px; height: 150px;">
                                    <i class="bi bi-person text-white" style="font-size: 4rem;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="avatar" class="form-label">Escolha uma nova foto</label>
                                    <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                                           id="avatar" name="avatar" accept="image/*">
                                    <div class="form-text">Tamanho máximo: 1MB. Formatos aceitos: JPG, PNG, GIF.</div>
                                    @error('avatar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-cloud-upload"></i> Atualizar Foto
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dados Pessoais -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Dados Pessoais</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $user->name) }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" value="{{ $user->email }}" disabled>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="phone" class="form-label">Telefone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                            />

                        <div class="d-flex justify-content-start gap-2">
                            <x-button icon="check-lg" name="Salvar" type="submit" class="success"></x-button>
                            <a href="{{ route('home.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Alteração de Senha -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Alterar Senha</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Nova Senha</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
                                    <input type="password" class="form-control"
                                           id="password_confirmation" name="password_confirmation">
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-key"></i> Alterar Senha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            if (typeof $.fn.mask !== 'undefined') {
                $('input[name="phone"]').mask('(00) 00000-0000');
            }
        });
    </script>
@endpush

@inject('storage', 'Illuminate\Support\Facades\Storage')
