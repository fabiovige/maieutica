<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} - {{ config('app.description') }} - by fabiovige</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sidebar.css') }}" rel="stylesheet">

    <!-- No head, após os outros links CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @stack('styles')

</head>

<body>
    <div class="layout-container">
        @auth
            @include('layouts.sidebar')
        @endauth

        <div class="main-content">
            <header class="main-header">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <a class="brand-link" href="{{ route('home.index') }}">
                                <strong>{{ config('app.name') }}</strong>
                            </a>
                            <div class="text-muted fw-light">|</div>
                            <nav aria-label="breadcrumb" class="mb-0">
                                <ol class="breadcrumb breadcrumb-modern mb-0">
                                    @if (request()->routeIs('home.index'))
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
                            @auth
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                                        @if(auth()->user()->avatar && file_exists(public_path(auth()->user()->avatar)))
                                            <img src="{{ asset(auth()->user()->avatar) }}"
                                                 alt="Avatar"
                                                 class="rounded-circle me-2"
                                                 style="width: 24px; height: 24px; object-fit: cover;">
                                        @else
                                            <i class="bi bi-person-circle me-2"></i>
                                        @endif
                                        {{ auth()->user()->name }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                                <i class="bi bi-person me-2"></i> Meu Perfil
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-box-arrow-right me-2"></i> Sair
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </header>

            <div class="content-header">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">@yield('title')</h5>
                        <div class="d-flex align-items-center">
                            @yield('actions')
                        </div>
                    </div>
                </div>
            </div>

            <main class="content-body">
                <div class="container-fluid">
                    @include('layouts.messages')
                    @yield('content')
                </div>
            </main>
            
            @include('layouts.footer')
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}"></script>

    @stack('scripts')

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-menu-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });
    </script>

    <!-- by fabiovige -->
</body>

</html>
