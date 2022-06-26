<ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link @if(request()->is('/*')) active @endif" aria-current="page"
           href="{{ route('home.index') }}">Home</a>
    </li>
    @can('kids.index')
    <li class="nav-item">
        <a class="nav-link @if(request()->is('kids*')) active @endif" aria-current="page"
           href="{{ route('kids.index') }}">Crianças</a>
    </li>
    @endcan
    @can('users.index')
    <li class="nav-item">
        <a class="nav-link @if(request()->is('users*')) active @endif" aria-current="page"
           href="{{ route('users.index') }}">Usuários</a>
    </li>
    @endcan

    @can('roles.index')
    <li class="nav-item">
        <a class="nav-link @if(request()->is('roles*')) active @endif" aria-current="page"
           href="{{ route('roles.index') }}">Papéis</a>
    </li>
    @endcan

{{--    <li class="nav-item {{ Route::is('log-viewer::dashboard') ? 'active' : '' }}">--}}
{{--        <a href="{{ route('log-viewer::dashboard') }}" class="nav-link">--}}
{{--            <i class="fa fa-dashboard"></i> @lang('Dashboard')--}}
{{--        </a>--}}
{{--    </li>--}}
{{--    <li class="nav-item {{ Route::is('log-viewer::logs.list') ? 'active' : '' }}">--}}
{{--        <a href="{{ route('log-viewer::logs.list') }}" class="nav-link">--}}
{{--            <i class="fa fa-archive"></i> @lang('Logs')--}}
{{--        </a>--}}
{{--    </li>--}}
</ul>
