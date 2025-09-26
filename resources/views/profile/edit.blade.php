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
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-dark">Dados Pessoais</h6>
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
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between gap-2">
                        <a href="{{ route('home.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                        <x-button icon="check-lg" name="Salvar" type="submit" class="success"></x-button>
                    </div>
                </div>
                    </form>
            </div>
        </div>

        <!-- Alteração de Senha -->
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-dark">Alterar Senha</h6>
                </div>
                <div class="card-body">
                    <form id="password-form" action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Senha Atual</label>
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                           id="current_password" name="current_password" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Nova Senha</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                               id="password" name="password">
                                        <button class="btn btn-outline-secondary" type="button" id="toggle-password">
                                            <i class="bi bi-eye" id="toggle-icon"></i>
                                        </button>
                                    </div>
                                    <div id="password-strength" class="mt-2" style="display: none;">
                                        <div class="progress mb-2" style="height: 5px;">
                                            <div class="progress-bar" id="strength-bar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <div id="password-requirements" class="small">
                                            <div class="requirement" data-requirement="min_length">
                                                <i class="bi bi-x text-danger"></i> Mínimo 8 caracteres
                                            </div>
                                            <div class="requirement" data-requirement="uppercase">
                                                <i class="bi bi-x text-danger"></i> Pelo menos 1 letra maiúscula
                                            </div>
                                            <div class="requirement" data-requirement="lowercase">
                                                <i class="bi bi-x text-danger"></i> Pelo menos 1 letra minúscula
                                            </div>
                                            <div class="requirement" data-requirement="number">
                                                <i class="bi bi-x text-danger"></i> Pelo menos 1 número
                                            </div>
                                            <div class="requirement" data-requirement="special">
                                                <i class="bi bi-x text-danger"></i> Pelo menos 1 caractere especial (!@#$%^&*)
                                            </div>
                                            <div class="requirement" data-requirement="no_username">
                                                <i class="bi bi-x text-danger"></i> Não deve conter o nome do usuário
                                            </div>
                                        </div>
                                    </div>
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
                                    <div id="password-match" class="form-text" style="display: none;">
                                        <i class="bi bi-check text-success"></i> As senhas coincidem
                                    </div>
                                    <div id="password-no-match" class="form-text text-danger" style="display: none;">
                                        <i class="bi bi-x text-danger"></i> As senhas não coincidem
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        <button type="submit" form="password-form" class="btn btn-primary">
                            <i class="bi bi-key"></i> Alterar Senha
                        </button>
                    </div>
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

            // Toggle password visibility
            $('#toggle-password').click(function() {
                const passwordField = $('#password');
                const toggleIcon = $('#toggle-icon');

                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    toggleIcon.removeClass('bi-eye').addClass('bi-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    toggleIcon.removeClass('bi-eye-slash').addClass('bi-eye');
                }
            });

            // Password strength validation
            const requirements = {
                min_length: (password) => password.length >= 8,
                uppercase: (password) => /[A-Z]/.test(password),
                lowercase: (password) => /[a-z]/.test(password),
                number: (password) => /[0-9]/.test(password),
                special: (password) => /[!@#$%^&*]/.test(password),
                no_username: (password) => {
                    const username = '{{ strtolower(auth()->user()->name ?? '') }}';
                    return !username || password.toLowerCase().indexOf(username) === -1;
                }
            };

            function validatePassword(password) {
                const results = {};
                let strength = 0;

                Object.keys(requirements).forEach(key => {
                    results[key] = requirements[key](password);
                    if (results[key]) strength++;
                });

                return { results, strength };
            }

            function updatePasswordStrength(password) {
                const validation = validatePassword(password);
                const strengthPercentage = (validation.strength / Object.keys(requirements).length) * 100;

                // Update progress bar
                const strengthBar = $('#strength-bar');
                strengthBar.css('width', strengthPercentage + '%');

                // Update progress bar color
                if (strengthPercentage < 50) {
                    strengthBar.removeClass().addClass('progress-bar bg-danger');
                } else if (strengthPercentage < 80) {
                    strengthBar.removeClass().addClass('progress-bar bg-warning');
                } else {
                    strengthBar.removeClass().addClass('progress-bar bg-success');
                }

                // Update requirements
                Object.keys(validation.results).forEach(key => {
                    const requirement = $(`.requirement[data-requirement="${key}"]`);
                    const icon = requirement.find('i');

                    if (validation.results[key]) {
                        icon.removeClass('bi-x text-danger').addClass('bi-check text-success');
                        requirement.removeClass('text-muted').addClass('text-success');
                    } else {
                        icon.removeClass('bi-check text-success').addClass('bi-x text-danger');
                        requirement.removeClass('text-success').addClass('text-muted');
                    }
                });

                return validation.strength === Object.keys(requirements).length;
            }

            function checkPasswordMatch() {
                const password = $('#password').val();
                const confirmation = $('#password_confirmation').val();

                if (confirmation.length === 0) {
                    $('#password-match, #password-no-match').hide();
                    return;
                }

                if (password === confirmation) {
                    $('#password-match').show();
                    $('#password-no-match').hide();
                } else {
                    $('#password-match').hide();
                    $('#password-no-match').show();
                }
            }

            // Password input event handlers
            $('#password').on('input', function() {
                const password = $(this).val();

                if (password.length > 0) {
                    $('#password-strength').show();
                    updatePasswordStrength(password);
                } else {
                    $('#password-strength').hide();
                }

                checkPasswordMatch();
            });

            $('#password_confirmation').on('input', checkPasswordMatch);

            // Form submission validation
            $('#password-form').on('submit', function(e) {
                const password = $('#password').val();
                const confirmation = $('#password_confirmation').val();

                if (password.length === 0) {
                    e.preventDefault();
                    alert('Por favor, digite a nova senha.');
                    return;
                }

                if (password !== confirmation) {
                    e.preventDefault();
                    alert('As senhas não coincidem.');
                    return;
                }

                const validation = validatePassword(password);
                if (validation.strength < Object.keys(requirements).length) {
                    e.preventDefault();
                    alert('A senha não atende a todos os critérios de segurança.');
                    return;
                }
            });
        });
    </script>
@endpush

@inject('storage', 'Illuminate\Support\Facades\Storage')
