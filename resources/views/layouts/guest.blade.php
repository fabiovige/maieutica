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

<div id="app" class="pagewrap">

    <main class="py-2 d-flex align-items-center justify-content-center">
        <div class="div">

            @section('content')
                @error('email')
                <div class="alert alert-warning text-center" style="width:400px;">
                    {{ $message }}
                </div>
                @enderror
                <div class="card " style="width:400px;">
                    <div class="card-header text-center">
                        <img src="{{ asset('images/logo_login.png') }} "
                             class="elevation-0"
                             alt="{{ config('app.name') }}"
                             width="180px"
                        />
                    </div>
                    <div class="card-body">
                        @yield('content')

                    </div>
    </main>
    <footer>
        <div class="container">
            <div class="row  py-4 d-flex justify-content-center">
                <p class="small text-center mb-0 mt-4">
                    {{ config('app.name') }} - {{ config('app.description') }}
                    <br>
                    &copy; 2021 - {{ now()->format('Y') }}
                </p>
            </div>

    </footer>
</div>


<!-- Scripts -->
<script src="{{ mix('js/app.js') }}" defer></script>

@stack('scripts')
</body>
</html>
