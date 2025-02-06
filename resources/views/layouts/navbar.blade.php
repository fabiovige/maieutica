<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #d7e2ec;">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home.index') }}">
            <strong>{{ config('app.name') }}</strong>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            @auth
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home.index') ? 'active' : '' }}" href="{{ route('home.index') }}">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>

                    @if(auth()->user()->can('view checklists'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('checklists.*') ? 'active' : '' }}" href="{{ route('checklists.index') }}">
                                <i class="bi bi-list-check"></i> Checklists
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->can('view kids'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('kids.*') ? 'active' : '' }}" href="{{ route('kids.index') }}">
                                <i class="bi bi-people"></i> Crianças
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->hasRole('admin') || auth()->user()->can('view competences'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('competences.*') ? 'active' : '' }}" href="{{ route('competences.index') }}">
                                <i class="bi bi-bookmark-star"></i> Competências
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->can('view users'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                <i class="bi bi-person"></i> Usuários
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->can('view roles'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                                <i class="bi bi-shield-lock"></i> Perfis
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->hasRole('admin') && auth()->user()->can('view logs'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('logs.*') ? 'active' : '' }}" href="{{ route('log-viewer::dashboard') }}">
                                <i class="bi bi-journal-text"></i> Logs
                            </a>
                        </li>
                    @endif
                </ul>

                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-link text-dark text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            {{ auth()->user()->name }} ({{ auth()->user()->roles->first()->name }})
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if(auth()->user()->can('edit profile'))
                                <li>
                                    <a href="{{ route('users.edit', auth()->id()) }}" class="dropdown-item">
                                        <i class="bi bi-person"></i> Perfil
                                    </a>
                                </li>
                            @endif
                            <li>
                                <form method="POST" action="{{ route('sair') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right"></i> Sair
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            @endauth
        </div>
    </div>
</nav>
