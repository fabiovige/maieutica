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

                    @if(auth()->user()->can('checklist-list'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('checklists.*') ? 'active' : '' }}" href="{{ route('checklists.index') }}">
                                <i class="bi bi-list-check"></i> Checklists
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->can('kid-list'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('kids.*') ? 'active' : '' }}"
                               href="#"
                               id="kidsDropdown"
                               role="button"
                               data-bs-toggle="dropdown"
                               aria-expanded="false">
                                <i class="bi bi-people"></i> Crianças
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="kidsDropdown">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('kids.index') ? 'active' : '' }}"
                                       href="{{ route('kids.index') }}">
                                        <i class="bi bi-people"></i> Lista de Crianças
                                    </a>
                                </li>
                                @if(auth()->user()->can('kid-edit') || auth()->user()->can('kid-list-all'))
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('kids.trash') ? 'active' : '' }}"
                                           href="{{ route('kids.trash') }}">
                                            <i class="bi bi-trash"></i> Lixeira
                                            @php
                                                $trashedKidsCount = App\Models\Kid::onlyTrashed()->count();
                                            @endphp
                                            @if($trashedKidsCount > 0)
                                                <span class="badge bg-danger ms-1">{{ $trashedKidsCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if(auth()->user()->can('user-list'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('users.*') ? 'active' : '' }}"
                               href="#"
                               id="usersDropdown"
                               role="button"
                               data-bs-toggle="dropdown"
                               aria-expanded="false">
                                <i class="bi bi-person"></i> Usuários
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="usersDropdown">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('users.index') ? 'active' : '' }}"
                                       href="{{ route('users.index') }}">
                                        <i class="bi bi-people"></i> Lista de Usuários
                                    </a>
                                </li>
                                @if(auth()->user()->can('user-edit') || auth()->user()->can('user-list-all'))
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('users.trash') ? 'active' : '' }}"
                                           href="{{ route('users.trash') }}">
                                            <i class="bi bi-trash"></i> Lixeira
                                            @php
                                                $trashedCount = App\Models\User::onlyTrashed()->count();
                                            @endphp
                                            @if($trashedCount > 0)
                                                <span class="badge bg-danger ms-1">{{ $trashedCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if(auth()->user()->can('role-list'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                               href="#"
                               id="rolesDropdown"
                               role="button"
                               data-bs-toggle="dropdown"
                               aria-expanded="false">
                                <i class="bi bi-shield-lock"></i> Perfis
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="rolesDropdown">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('roles.index') ? 'active' : '' }}"
                                       href="{{ route('roles.index') }}">
                                        <i class="bi bi-shield-lock"></i> Lista de Perfis
                                    </a>
                                </li>
                                @can('role-list')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('roles.trash') ? 'active' : '' }}"
                                           href="{{ route('roles.trash') }}">
                                            <i class="bi bi-trash"></i> Lixeira
                                            @php
                                                $trashedRolesCount = \App\Models\Role::onlyTrashed()->count();
                                            @endphp
                                            @if($trashedRolesCount > 0)
                                                <span class="badge bg-danger ms-1">{{ $trashedRolesCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif

                    @if(auth()->user()->can('view logs'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('logs.*') ? 'active' : '' }}" href="{{ route('log-viewer::dashboard') }}">
                                <i class="bi bi-journal-text"></i> Logs
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->can('professional-list'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('professionals.*') ? 'active' : '' }}"
                               href="#"
                               id="professionalsDropdown"
                               role="button"
                               data-bs-toggle="dropdown"
                               aria-expanded="false">
                                <i class="bi bi-person-badge"></i> Profissionais
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="professionalsDropdown">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('professionals.index') ? 'active' : '' }}"
                                       href="{{ route('professionals.index') }}">
                                        <i class="bi bi-person-badge"></i> Lista de Profissionais
                                    </a>
                                </li>
                                @can('professional-list')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('professionals.trash') ? 'active' : '' }}"
                                           href="{{ route('professionals.trash') }}">
                                            <i class="bi bi-trash"></i> Lixeira
                                            @php
                                                $trashedProfessionalsCount = \App\Models\Professional::onlyTrashed()->count();
                                            @endphp
                                            @if($trashedProfessionalsCount > 0)
                                                <span class="badge bg-danger ms-1">{{ $trashedProfessionalsCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endcan
                            </ul>
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
