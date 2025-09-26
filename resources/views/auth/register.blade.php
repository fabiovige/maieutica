@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
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
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                <div id="password-match" class="form-text" style="display: none;">
                                    <i class="bi bi-check text-success"></i> As senhas coincidem
                                </div>
                                <div id="password-no-match" class="form-text text-danger" style="display: none;">
                                    <i class="bi bi-x text-danger"></i> As senhas não coincidem
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
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
                    const username = $('#name').val().toLowerCase();
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
                const confirmation = $('#password-confirm').val();

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

            // Re-validate when name changes (for username requirement)
            $('#name').on('input', function() {
                const password = $('#password').val();
                if (password.length > 0) {
                    updatePasswordStrength(password);
                }
            });

            $('#password-confirm').on('input', checkPasswordMatch);

            // Form submission validation
            $('form').on('submit', function(e) {
                const password = $('#password').val();
                const confirmation = $('#password-confirm').val();

                if (password.length === 0) {
                    e.preventDefault();
                    alert('Por favor, digite a senha.');
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
