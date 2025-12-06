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

                    @if(auth()->user()->can('checklist-list') || auth()->user()->can('kid-list'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('checklists.*') || request()->routeIs('kids.*') ? 'active' : '' }}"
                               href="#"
                               id="denverDropdown"
                               role="button"
                               data-bs-toggle="dropdown"
                               aria-expanded="false">
                                <i class="bi bi-clipboard-check"></i> Denver
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="denverDropdown">
                                @can('checklist-list')
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('checklists.*') ? 'active' : '' }}"
                                           href="{{ route('checklists.index') }}">
                                            <i class="bi bi-list-check"></i> Checklists
                                        </a>
                                    </li>
                                @endcan

                                @can('kid-list')
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('kids.*') ? 'active' : '' }}"
                                           href="{{ route('kids.index') }}">
                                            <i class="bi bi-people"></i> Crianças
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif

                    @if(auth()->user()->can('user-list') || auth()->user()->can('role-list') || auth()->user()->can('professional-list') || auth()->user()->can('checklist-list-all') || auth()->user()->can('kid-list-all') || auth()->user()->can('user-list-all') || auth()->user()->can('role-list-all') || auth()->user()->can('professional-list-all'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('professionals.*') || request()->routeIs('*.trash') ? 'active' : '' }}"
                               href="#"
                               id="cadastroDropdown"
                               role="button"
                               data-bs-toggle="dropdown"
                               aria-expanded="false">
                                <i class="bi bi-folder-plus"></i> Cadastro
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="cadastroDropdown">
                                @can('user-list')
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('users.*') && !request()->routeIs('users.trash') ? 'active' : '' }}"
                                           href="{{ route('users.index') }}">
                                            <i class="bi bi-person"></i> Usuários
                                        </a>
                                    </li>
                                @endcan

                                @can('role-list')
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('roles.*') && !request()->routeIs('roles.trash') ? 'active' : '' }}"
                                           href="{{ route('roles.index') }}">
                                            <i class="bi bi-shield-lock"></i> Perfis
                                        </a>
                                    </li>
                                @endcan

                                @can('professional-list')
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('professionals.*') && !request()->routeIs('professionals.trash') ? 'active' : '' }}"
                                           href="{{ route('professionals.index') }}">
                                            <i class="bi bi-person-badge"></i> Profissionais
                                        </a>
                                    </li>
                                @endcan

                                @if(auth()->user()->can('checklist-list-all') || auth()->user()->can('kid-list-all') || auth()->user()->can('user-list-all') || auth()->user()->can('role-list-all') || auth()->user()->can('professional-list-all'))
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <h6 class="dropdown-header">
                                            <i class="bi bi-trash"></i> Lixeira
                                        </h6>
                                    </li>

                                    @can('checklist-list-all')
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('checklists.trash') ? 'active' : '' }}"
                                               href="{{ route('checklists.trash') }}">
                                                <i class="bi bi-list-check"></i> Checklists
                                                @php
                                                    $trashedChecklistsCount = App\Models\Checklist::onlyTrashed()->count();
                                                @endphp
                                                @if($trashedChecklistsCount > 0)
                                                    <span class="badge bg-danger ms-1">{{ $trashedChecklistsCount }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endcan

                                    @can('kid-list-all')
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('kids.trash') ? 'active' : '' }}"
                                               href="{{ route('kids.trash') }}">
                                                <i class="bi bi-people"></i> Crianças
                                                @php
                                                    $trashedKidsCount = App\Models\Kid::onlyTrashed()->count();
                                                @endphp
                                                @if($trashedKidsCount > 0)
                                                    <span class="badge bg-danger ms-1">{{ $trashedKidsCount }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endcan

                                    @can('user-list-all')
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('users.trash') ? 'active' : '' }}"
                                               href="{{ route('users.trash') }}">
                                                <i class="bi bi-person"></i> Usuários
                                                @php
                                                    $trashedUsersCount = App\Models\User::onlyTrashed()->count();
                                                @endphp
                                                @if($trashedUsersCount > 0)
                                                    <span class="badge bg-danger ms-1">{{ $trashedUsersCount }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endcan

                                    @can('role-list-all')
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('roles.trash') ? 'active' : '' }}"
                                               href="{{ route('roles.trash') }}">
                                                <i class="bi bi-shield-lock"></i> Perfis
                                                @php
                                                    $trashedRolesCount = \App\Models\Role::onlyTrashed()->count();
                                                @endphp
                                                @if($trashedRolesCount > 0)
                                                    <span class="badge bg-danger ms-1">{{ $trashedRolesCount }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endcan

                                    @can('professional-list-all')
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('professionals.trash') ? 'active' : '' }}"
                                               href="{{ route('professionals.trash') }}">
                                                <i class="bi bi-person-badge"></i> Profissionais
                                                @php
                                                    $trashedProfessionalsCount = \App\Models\Professional::onlyTrashed()->count();
                                                @endphp
                                                @if($trashedProfessionalsCount > 0)
                                                    <span class="badge bg-danger ms-1">{{ $trashedProfessionalsCount }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endcan
                                @endif
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

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('documents.*') ? 'active' : '' }}"
                           href="#"
                           id="documentoDropdown"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            <i class="bi bi-file-earmark-text"></i> Documento
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="documentoDropdown">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('documents.index') ? 'active' : '' }}"
                                   href="{{ url('documents') }}">
                                    <i class="bi bi-file-earmark-plus"></i> Geração de documentos
                                </a>
                            </li>
                        </ul>
                    </li>
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
