<ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link @if (request()->is('/*')) active @endif"
           aria-current="page"
           href="{{ route('home.index') }}">Home</a>
    </li>
    
    @can('kid-list')
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle @if (request()->is('kids*')) active @endif"
               href="#"
               id="kidsDropdown"
               role="button"
               data-bs-toggle="dropdown"
               aria-expanded="false">
                Crianças
            </a>
            <ul class="dropdown-menu" aria-labelledby="kidsDropdown">
                <li>
                    <a class="dropdown-item @if (request()->is('kids') && !request()->is('kids/trash')) active @endif"
                       href="{{ route('kids.index') }}">
                        <i class="bi bi-list"></i> Listar Crianças
                    </a>
                </li>
                @can('kid-create')
                    <li>
                        <a class="dropdown-item @if (request()->is('kids/create')) active @endif"
                           href="{{ route('kids.create') }}">
                            <i class="bi bi-plus-lg"></i> Nova Criança
                        </a>
                    </li>
                @endcan
                @can('kid-list-all')
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item @if (request()->is('kids/trash')) active @endif"
                           href="{{ route('kids.trash') }}">
                            <i class="bi bi-trash"></i> Lixeira
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcan

    @can('user-list')
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle @if (request()->is('users*')) active @endif"
               href="#"
               id="usersDropdown"
               role="button"
               data-bs-toggle="dropdown"
               aria-expanded="false">
                Usuários
            </a>
            <ul class="dropdown-menu" aria-labelledby="usersDropdown">
                <li>
                    <a class="dropdown-item @if (request()->is('users') && !request()->is('users/trash')) active @endif"
                       href="{{ route('users.index') }}">
                        <i class="bi bi-list"></i> Listar Usuários
                    </a>
                </li>
                @can('user-create')
                    <li>
                        <a class="dropdown-item @if (request()->is('users/create')) active @endif"
                           href="{{ route('users.create') }}">
                            <i class="bi bi-plus-lg"></i> Novo Usuário
                        </a>
                    </li>
                @endcan
                @can('user-list-all')
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item @if (request()->is('users/trash')) active @endif"
                           href="{{ route('users.trash') }}">
                            <i class="bi bi-trash"></i> Lixeira
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcan

    @can('checklist-list')
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle @if (request()->is('checklists*')) active @endif"
               href="#"
               id="checklistsDropdown"
               role="button"
               data-bs-toggle="dropdown"
               aria-expanded="false">
                Checklists
            </a>
            <ul class="dropdown-menu" aria-labelledby="checklistsDropdown">
                <li>
                    <a class="dropdown-item @if (request()->is('checklists') && !request()->is('checklists/trash')) active @endif"
                       href="{{ route('checklists.index') }}">
                        <i class="bi bi-list"></i> Listar Checklists
                    </a>
                </li>
                @can('checklist-list-all')
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item @if (request()->is('checklists/trash')) active @endif"
                           href="{{ route('checklists.trash') }}">
                            <i class="bi bi-trash"></i> Lixeira
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcan

    @can('role-list')
        <li class="nav-item">
            <a class="nav-link @if (request()->is('roles*')) active @endif"
               aria-current="page"
               href="{{ route('roles.index') }}">Permissões</a>
        </li>
    @endcan

    @can('competence-list')
        <li class="nav-item">
            <a class="nav-link @if (request()->is('competences*')) active @endif"
               aria-current="page"
               href="{{ route('competences.index') }}">Competências</a>
        </li>
    @endcan

    @can('document-list')
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle @if (request()->is('documents*')) active @endif"
               href="#"
               id="documentsDropdown"
               role="button"
               data-bs-toggle="dropdown"
               aria-expanded="false">
                <i class="bi bi-file-earmark-text"></i> Documentos
            </a>
            <ul class="dropdown-menu" aria-labelledby="documentsDropdown">
                <li>
                    <a class="dropdown-item @if (request()->is('documents') && !request()->is('documents/history*')) active @endif"
                       href="{{ route('documentos.index') }}">
                        <i class="bi bi-plus-circle"></i> Gerar Documentos
                    </a>
                </li>
                <li>
                    <a class="dropdown-item @if (request()->is('documents/history*')) active @endif"
                       href="{{ route('documentos.history') }}">
                        <i class="bi bi-clock-history"></i> Histórico de Documentos
                    </a>
                </li>
            </ul>
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
