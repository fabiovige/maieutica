@extends('layouts.guest')

@section('content')


                    <div style="text-align: center; margin-bottom: 70px;">
                        <img src="{{ asset('images/logo_login.png') }} "
                                class="elevation-0"
                                alt="{{ config('app.name') }}"
                                width="160px"
                            />
                    </div>

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="col-md-12 d-flex align-items-center justify-content-center">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif
                        </div>

                        <div class="input-box">
                            <span>E-mail</span>
                            <input type="email" name="email" required autocomplete="email" autofocus>
                        </div>

                        <div class="input-box d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Send Password Reset Link') }}
                            </button>
                        </div>
                    </form>

                    <div class="d-flex flex-column align-items-center">
                        <span class="small text-muted text-center">
                            Todos os direitos reservados. {{ config('app.name') }} - {{ config('app.description') }}.
                        </span>
                    </div>
@endsection
