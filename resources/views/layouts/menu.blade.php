<ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link @if (request()->is('/*')) active @endif"
           aria-current="page"
           href="{{ route('home.index') }}">Home</a>
    </li>
    
    @can('list kids')
        <li class="nav-item">
            <a class="nav-link @if (request()->is('kids*')) active @endif"
               aria-current="page"
               href="{{ route('kids.index') }}">Crianças</a>
        </li>
    @endcan

    @can('list users')
    <li class="nav-item">
        <a class="nav-link @if (request()->is('users*')) active @endif"
           aria-current="page"
           href="{{ route('users.index') }}">Usuários</a>
    </li>
    @endcan

    @can('list checklists')
    <li class="nav-item">
        <a class="nav-link @if (request()->is('checklists*')) active @endif"
           aria-current="page"
           href="{{ route('checklists.index') }}">Checklists</a>
    </li>
    @endcan

    @can('list roles')
        <li class="nav-item">
            <a class="nav-link @if (request()->is('roles*')) active @endif"
               aria-current="page"
               href="{{ route('roles.index') }}">Permissões</a>
        </li>
    @endcan

    @can('list competences')
        <li class="nav-item">
            <a class="nav-link @if (request()->is('competences*')) active @endif"
               aria-current="page"
               href="{{ route('competences.index') }}">Competências</a>
        </li>
    @endcan

    <!-- Tutorial - Disponível para todos os usuários -->
    <li class="nav-item">
        <a class="nav-link @if (request()->is('tutorial*')) active @endif"
           aria-current="page"
           href="{{ route('tutorial.index') }}">
           <i class="bi bi-book me-1"></i>Tutorial
        </a>
    </li>
</ul>
