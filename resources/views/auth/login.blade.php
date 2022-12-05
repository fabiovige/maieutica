@extends('layouts.guest')

@section('content')
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="row mb-2">
                                <label for="email" class="col-form-label text-md-start">E-mail</label>

                                <div class="">

                                    <input id="email" type="email" maxlength="150"  class="form-control" name="email" value="{{ old('email') ?? 'ricardo@gmail.com' }}" required autocomplete="email"
                                    autofocus>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <label for="password" class="col-form-label text-md-start">Senha</label>

                                <div class="">
                                    <input id="password" maxlength="32" type="password"
                                           class="form-control" value="password"
                                           name="password" required autocomplete="current-password">
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember"
                                               id="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">
                                            {{ __('Remember Me') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="d-flex align-items-center">
                                    <button type="submit" class="w-100 btn btn-primary ">
                                        <i class="bi bi-arrow-right-short"></i> {{ __('Login') }}
                                    </button>
                                </div>
                            </div>
                        </form>

                        </div>
                        <div class="card-footer text-center">
                            @if (Route::has('password.request'))
                                <a class="btn btn-link" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                    <a/>
                            @endif
                        </div>

@endsection
