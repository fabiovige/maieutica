<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>

<div class="pagewrap">

    <main class="d-flex align-items-center justify-content-center ">
        @error('email')
        <div class="alert alert-warning text-center" style="width:400px;">
            {{ $message }}
        </div>
        @enderror
        <div class="card shadow" style="width:400px;" >
            <div class="card-header text-center">
                <img src="{{ asset('images/logo_login.png') }} "
                     class="elevation-0"
                     alt="{{ config('app.name') }}"
                     width="120px"
                />
            </div>
            <div class="card-body">
                @yield('content')
            </div>
        </div>
    </main>

</div>


<!-- Scripts -->
<script src="{{ mix('js/app.js') }}" defer></script>

@stack('scripts')
</body>
</html>
