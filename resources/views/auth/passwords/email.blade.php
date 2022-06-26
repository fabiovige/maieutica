@extends('layouts.guest')

@section('content')
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-form-label text-md-start">Seu e-mail</label>

                            <div class="">
                                <input id="email" type="email"
                                       class="form-control " name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Send Password Reset Link') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <p class="text-muted small text-center mb-0 mt-4">
                        {{ config('app.name') }} - {{ config('app.description') }}
                        <br>
                        &copy; 2021 - {{ now()->format('Y') }}
                    </p>
@endsection
