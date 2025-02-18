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

    <!-- No head, após os outros links CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @stack('styles')

</head>

<body>
    <div class="pagewrap">

        @include('layouts.navbar')

        <div class="container mt-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <h4 class="mb-0 fw-bold">@yield('title')</h4>
                    <div class="text-muted fw-light">|</div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb breadcrumb-modern mb-0">
                            @if(request()->routeIs('home.index'))
                                <li class="breadcrumb-item active">
                                    <i class="bi bi-house-door"></i> Home
                                </li>
                            @else
                                <li class="breadcrumb-item">
                                    <a href="{{ route('home.index') }}">
                                        <i class="bi bi-house-door"></i> Home
                                    </a>
                                </li>
                                @yield('breadcrumb-items')
                            @endif
                        </ol>
                    </nav>
                </div>
                <div class="d-flex align-items-center">
                    @yield('actions')
                </div>
            </div>
        </div>

        <main class="py-2">
            <div class="container">
                @include('layouts.messages')
                @yield('content')
            </div>
        </main>
        @include('layouts.footer')
    </div>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}"></script>

    @stack('scripts')

    <!-- by fabiovige -->
</body>

</html>
