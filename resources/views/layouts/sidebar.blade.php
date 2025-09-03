@inject('storage', 'Illuminate\Support\Facades\Storage')

<button class="mobile-menu-toggle d-md-none" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
</button>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="d-flex align-items-center">
            @if(auth()->user()->avatar && file_exists(public_path(auth()->user()->avatar)))
                <img src="{{ asset(auth()->user()->avatar) }}"
                     alt="Avatar"
                     class="rounded-circle me-2"
                     style="width: 40px; height: 40px; object-fit: cover;">
            @else
                <i class="bi bi-person-circle me-2" style="font-size: 40px;"></i>
            @endif
            <div>
                <div class="fw-bold text-truncate" style="max-width: 180px;">{{ auth()->user()->name }}</div>
                <small class="text-muted">{{ auth()->user()->roles->first()->name }}</small>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        @auth
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home.index') ? 'active' : '' }}" href="{{ route('home.index') }}">
                        <i class="bi bi-house-door"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                @if(auth()->user()->can('view checklists'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('checklists.*') ? 'active' : '' }}" href="{{ route('checklists.index') }}">
                            <i class="bi bi-list-check"></i>
                            <span>Checklists</span>
                        </a>
                    </li>
                @endif

                @if(auth()->user()->can('view kids'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('kids.*') ? 'active' : '' }}" href="{{ route('kids.index') }}">
                            <i class="bi bi-people"></i>
                            <span>Crianças</span>
                        </a>
                    </li>
                @endif

                @can('view competences')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('competences.*') ? 'active' : '' }}" href="{{ route('competences.index') }}">
                            <i class="bi bi-bookmark-star"></i>
                            <span>Competências</span>
                        </a>
                    </li>
                @endcan

                @if(auth()->user()->can('view users'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <i class="bi bi-person"></i>
                            <span>Usuários</span>
                        </a>
                    </li>
                @endif

                @if(auth()->user()->can('list roles'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                            <i class="bi bi-shield-lock"></i>
                            <span>Perfis</span>
                        </a>
                    </li>
                @endif

                @can('view logs')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('logs.*') ? 'active' : '' }}" href="{{ route('log-viewer::dashboard') }}">
                            <i class="bi bi-journal-text"></i>
                            <span>Logs</span>
                        </a>
                    </li>
                @endcan

                @if(auth()->user()->can('view professionals'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('professionals.*') ? 'active' : '' }}" href="{{ route('professionals.index') }}">
                            <i class="bi bi-person-badge"></i>
                            <span>Profissionais</span>
                        </a>
                    </li>
                @endif
            </ul>
        @endauth
    </nav>

</aside>