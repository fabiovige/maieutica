@extends('layouts.guest')

@section('content')

<div style="text-align: center; margin-bottom: 70px;">
    <img src="{{ asset('images/logo_login.png') }} "
            class="elevation-0"
            alt="{{ config('app.name') }}"
            width="160px"
        />
</div>

<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div class="input-box">
        <span>E-mail</span>
        <input id="email" type="email" class="form-control " name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
    </div>

    <div class="input-box">
        <span>Senha</span>
        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="input-box">
        <span>Confirme a Senha</span>
        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
    </div>

    <div class="input-box">
        <button type="submit" class="btn btn-primary">
            {{ __('Reset Password') }}
        </button>
    </div>
</form>

<div class="d-flex flex-column align-items-center">
    <span class="small text-muted text-center">
        Todos os direitos reservados. {{ config('app.name') }} - {{ config('app.description') }}.
    </span>
</div>

@endsection
