<ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link @if (request()->is('/*')) active @endif"
           aria-current="page"
           href="{{ route('home.index') }}">Home</a>
    </li>

    @can('kids.index')
        <li class="nav-item">
            <a class="nav-link @if (request()->is('kids*')) active @endif"
               aria-current="page"
               href="{{ route('kids.index') }}">Crianças</a>
        </li>
    @endcan

    @can('checklists.index')
        <li class="nav-item">
            <a class="nav-link @if (request()->is('checklists*')) active @endif"
               aria-current="page"
               href="{{ route('checklists.index') }}">Checklists</a>
        </li>
    @endcan

    @can('users.index')
        <li class="nav-item">
            <a class="nav-link @if (request()->is('users*')) active @endif"
               aria-current="page"
               href="{{ route('users.index') }}">Usuários</a>
        </li>
    @endcan

    @can('roles.index')
        <li class="nav-item">
            <a class="nav-link @if (request()->is('roles*')) active @endif"
               aria-current="page"
               href="{{ route('roles.index') }}">Permissões</a>
        </li>
    @endcan
</ul>
