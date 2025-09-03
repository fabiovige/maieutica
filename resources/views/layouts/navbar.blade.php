@inject('storage', 'Illuminate\Support\Facades\Storage')

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

                    @can('view competences')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('competences.*') ? 'active' : '' }}" href="{{ route('competences.index') }}">
                                <i class="bi bi-bookmark-star"></i> Competências
                            </a>
                        </li>
                    @endcan

                    @if(auth()->user()->can('view users'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                <i class="bi bi-person"></i> Usuários
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->can('list roles'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                                <i class="bi bi-shield-lock"></i> Perfis
                            </a>
                        </li>
                    @endif

                    @can('view logs')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('logs.*') ? 'active' : '' }}" href="{{ route('log-viewer::dashboard') }}">
                                <i class="bi bi-journal-text"></i> Logs
                            </a>
                        </li>
                    @endcan

                    @if(auth()->user()->can('view professionals'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('professionals.*') ? 'active' : '' }}" href="{{ route('professionals.index') }}">
                                <i class="bi bi-person-badge"></i> Profissionais
                            </a>
                        </li>
                    @endif
                </ul>

                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-link text-dark text-decoration-none dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                            @if(auth()->user()->avatar && file_exists(public_path(auth()->user()->avatar)))
                                <img src="{{ asset(auth()->user()->avatar) }}"
                                     alt="Avatar"
                                     class="rounded-circle me-2"
                                     style="width: 32px; height: 32px; object-fit: cover;">
                            @else
                                <i class="bi bi-person-circle me-2"></i>
                            @endif
                            {{ auth()->user()->name }} ({{ auth()->user()->roles->first()->name }})
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                    <i class="bi bi-person"></i> Meu Perfil
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
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
