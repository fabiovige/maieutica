<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title> {{ config('app.name') }} - {{ config('app.description') }}</title>

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    <div id="app" class="pagewrap">

        @include('layouts.navbar')
        <main class="py-2">
            <div class="container">
                @yield('breadcrumb')
                @include('flash::message')
                @yield('content')
            </div>
        </main>
        <footer >
            <div class="container">
                <div class="row  py-4 d-flex justify-content-center">
                    <p class="small text-center mb-0 mt-4">
                        {{ config('app.name') }} - {{ config('app.description') }}
                        <br>
                        &copy; 2021 - {{ now()->format('Y') }}
                        <br> {{ config('app.version') }} 
                    </p>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}" defer></script>

    @stack('scripts')

</body>
</html>
