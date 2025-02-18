                        </a>
                    </li>
                    <!-- Profissionais -->
                    <!-- Usuários -->

                    <!-- Competências -->
                    @can('view competences')
                    <li class="nav-item">
                        <a href="{{ route('competences.index') }}" class="nav-link {{ request()->routeIs('competences.*') ? 'active' : '' }}">
                            <i class="bi bi-list-check"></i>
                            <span class="nav-link-text">Competências</span>
                        </a>
                    </li>
                    @endcan

                    <!-- Profissionais -->
                    @can('view users')
                    <li class="nav-item">
                        <a href="{{ route('professionals.index') }}" class="nav-link {{ request()->routeIs('professionals.*') ? 'active' : '' }}">
                            <i class="bi bi-person-vcard"></i>
                            <span class="nav-link-text">Profissionais</span>
                        </a>
                    </li>
                    @endcan

                    <!-- Checklists -->
                    @can('view checklists')
                    <li class="nav-item">
