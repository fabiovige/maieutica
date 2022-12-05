<ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link @if (request()->is('/*')) active @endif" aria-current="page"
            href="{{ route('home.index') }}">Home</a>
    </li>

    {{--    <li class="nav-item {{ Route::is('log-viewer::dashboard') ? 'active' : '' }}"> --}}
    {{--        <a href="{{ route('log-viewer::dashboard') }}" class="nav-link"> --}}
    {{--            <i class="fa fa-dashboard"></i> @lang('Dashboard') --}}
    {{--        </a> --}}
    {{--    </li> --}}

    {{-- <li class="nav-item {{ Route::is('log-viewer::logs.list') ? 'active' : '' }}">
        <a href="{{ route('log-viewer::logs.list') }}" class="nav-link">
            <i class="fa fa-archive"></i> @lang('Logs')
        </a>
    </li> --}}

    @can('kids.index', 'checklists.index', 'responsible.index')
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                Gerenciamento
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">

                @can('responsibles.index')
                    <li>
                        <a class="dropdown-item @if (request()->is('responsibles*')) active @endif" aria-current="page"
                            href="{{ route('responsibles.index') }}">Responsáveis</a>
                    </li>
                @endcan

                @can('kids.index')
                    <li>
                        <a class="dropdown-item @if (request()->is('kids*')) active @endif" aria-current="page"
                            href="{{ route('kids.index') }}">Crianças</a>
                    <li>
                    @endcan

                    @can('checklists.index')
                    <li>
                        <a class="dropdown-item @if (request()->is('kids*')) checklists @endif" aria-current="page"
                            href="{{ route('checklists.index') }}">Checklists</a>
                    <li>
                    @endcan
            </ul>
        </li>
    @endcan




    @can('users.index', 'roles.index')
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                Configurações
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">

                @can('users.index')
                    <li>
                        <a class="dropdown-item @if (request()->is('users*')) active @endif" aria-current="page"
                            href="{{ route('users.index') }}">Usuários</a>
                    </li>
                @endcan

                @can('roles.index')
                    <li>

                        <a class="dropdown-item @if (request()->is('roles*')) active @endif" aria-current="page"
                            href="{{ route('roles.index') }}">Papéis</a>

                    </li>
                @endcan

            </ul>
        </li>
    @endcan



</ul>
