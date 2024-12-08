<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} - {{ config('app.description') }} - by fabiovige</title>

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}"
          rel="stylesheet">

    @stack('styles')

</head>

<body>
    <div class="pagewrap">

        @include('layouts.navbar')
        <main class="py-2" style="margin-bottom: 70px;">
            <div class="container">
                <div class="row mt-1">
                    <div class="col-md-6 text-start">
                        @yield('breadcrumb')
                    </div>
                    <div class="col-md-6 text-end">
                        @yield('button')
                    </div>
                </div>

                @include('flash::message')
                @yield('content')
            </div>
        </main>
        <footer>
            <div class="container-fluid" style="background-color: #d7e2ec; height: 40px; position: fixed; bottom: 0; width: 100%;">
                <div class="row py-2 d-flex justify-content-between align-items-center">
                    <div class="col-auto">
                        <p class="small mb-0">
                            &copy; {{ now()->format('Y') }} {{ config('app.name') }} - {{ config('app.description') }}
                        </p>
                    </div>
                    <div class="col-auto">
                        <p class="small mb-0 text-right">
                            <span class="badge text-bg-secondary">versão: {{ config('app.version') }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}"></script>

    @stack('scripts')

    <!-- by fabiovige -->
</body>

</html>
