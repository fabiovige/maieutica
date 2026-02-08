<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('css/typography.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Fonte Nunito -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* ===== SIDEBAR LAYOUT ===== */
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed: 70px;
            --header-height: 60px;
            --sidebar-bg: #1e293b;
            --sidebar-text: #94a3b8;
            --sidebar-active: #3b82f6;
        }

        * { box-sizing: border-box; }

        /* Fonte global - FORÇAR em todo o sistema */
        html, body {
            margin: 0;
            padding: 0;
            font-family: 'Nunito', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.5;
            background: #f8fafc;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Forçar fonte em TODOS os elementos */
        *, *::before, *::after {
            font-family: inherit;
        }

        /* Garantir que o Bootstrap não sobrescreva */
        h1, h2, h3, h4, h5, h6, 
        p, span, div, a, button, 
        input, textarea, select, label,
        table, th, td, li {
            font-family: 'Nunito', system-ui, sans-serif !important;
        }

        /* Tamanhos sóbrios para headings */
        h1 { font-size: 1.5rem !important; font-weight: 600 !important; }
        h2 { font-size: 1.25rem !important; font-weight: 600 !important; }
        h3 { font-size: 1.125rem !important; font-weight: 600 !important; }
        h4 { font-size: 1rem !important; font-weight: 600 !important; }
        h5 { font-size: 0.9375rem !important; font-weight: 600 !important; }
        h6 { font-size: 0.875rem !important; font-weight: 600 !important; }

        /* Conteúdo principal */
        .page-content {
            font-family: 'Nunito', system-ui, sans-serif;
            font-size: 0.875rem;
        }

        .page-content h1 { font-size: 1.5rem !important; }
        .page-content h2 { font-size: 1.25rem !important; }
        .page-content h3 { font-size: 1.125rem !important; }
        .page-content p { font-size: 0.875rem !important; line-height: 1.6 !important; }

        .layout-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: width 0.3s ease, transform 0.3s ease;
            overflow: hidden;
        }

        /* Sidebar Collapsed (Desktop) */
        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }

        .sidebar.collapsed .sidebar-brand span,
        .sidebar.collapsed .menu-section,
        .sidebar.collapsed .menu-link span,
        .sidebar.collapsed .menu-badge,
        .sidebar.collapsed .user-details,
        .sidebar.collapsed .submenu-arrow,
        .sidebar.collapsed .sidebar-footer {
            display: none !important;
        }

        .sidebar.collapsed .menu-link {
            justify-content: center;
            padding: 12px;
        }

        .sidebar.collapsed .menu-link i {
            margin-right: 0;
            font-size: 1.3rem;
        }

        .sidebar.collapsed .sidebar-close {
            display: none !important;
        }

        .sidebar.collapsed .submenu {
            display: none !important;
        }

        /* Sidebar Header */
        .sidebar-header {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            justify-content: space-between;
        }

        .sidebar-brand {
            color: white;
            text-decoration: none;
            font-weight: 700;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            overflow: hidden;
            white-space: nowrap;
        }

        .sidebar-brand i {
            font-size: 1.4rem;
            color: #3b82f6;
            flex-shrink: 0;
        }

        /* Toggle Collapse Button (Desktop) */
        .sidebar-collapse-btn {
            background: rgba(255,255,255,0.1);
            border: none;
            color: #94a3b8;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .sidebar-collapse-btn:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .sidebar.collapsed .sidebar-collapse-btn i {
            transform: rotate(180deg);
        }

        /* Close Button (Mobile) */
        .sidebar-close {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px;
        }

        /* Menu */
        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 10px 0;
        }

        .sidebar-menu::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 2px;
        }

        .menu-section {
            padding: 15px 20px 5px;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            font-weight: 600;
            white-space: nowrap;
        }

        .menu-item {
            position: relative;
        }

        .menu-link {
            display: flex;
            align-items: center;
            padding: 10px 18px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 0.8125rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            cursor: pointer;
            white-space: nowrap;
        }

        .menu-link:hover {
            color: white;
            background: rgba(255,255,255,0.05);
        }

        .menu-link.active {
            color: white;
            background: rgba(59, 130, 246, 0.15);
            border-left-color: var(--sidebar-active);
        }

        .menu-link i {
            font-size: 1.1rem;
            width: 24px;
            margin-right: 12px;
            flex-shrink: 0;
            text-align: center;
        }

        .menu-badge {
            margin-left: auto;
            background: #ef4444;
            color: white;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 10px;
            flex-shrink: 0;
        }

        /* Submenu */
        .has-submenu .submenu-arrow {
            margin-left: auto;
            font-size: 0.75rem;
            transition: transform 0.3s;
            flex-shrink: 0;
        }

        .has-submenu.open .submenu-arrow {
            transform: rotate(180deg);
        }

        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: rgba(0,0,0,0.15);
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .submenu.open {
            max-height: 500px;
        }

        .submenu-link {
            display: flex;
            align-items: center;
            padding: 8px 18px 8px 55px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 0.75rem;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .submenu-link:hover {
            color: white;
            background: rgba(255,255,255,0.05);
        }

        .submenu-link.active {
            color: white;
            background: rgba(59, 130, 246, 0.1);
        }

        /* Footer */
        .sidebar-footer {
            padding: 12px 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow: hidden;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .user-avatar-placeholder {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }

        .user-details {
            min-width: 0;
            overflow: hidden;
        }

        .user-name {
            color: white;
            font-size: 0.8125rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            font-size: 0.6875rem;
            color: #64748b;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-collapsed);
        }

        /* ===== TOP HEADER ===== */
        .top-header {
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .menu-toggle {
            display: none;
            background: #f1f5f9;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 6px;
            font-size: 1.3rem;
            color: #475569;
            cursor: pointer;
            align-items: center;
            justify-content: center;
        }

        .header-breadcrumb .breadcrumb {
            margin: 0;
            padding: 0;
            background: none;
            font-size: 0.8125rem;
        }

        .header-breadcrumb .breadcrumb-item a {
            color: #64748b;
            text-decoration: none;
        }

        .header-breadcrumb .breadcrumb-item.active {
            color: #1e293b;
            font-weight: 500;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Profile Dropdown */
        .profile-dropdown .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: #f1f5f9;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .profile-dropdown .dropdown-menu {
            min-width: 220px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }

        /* ===== PAGE CONTENT ===== */
        .page-content {
            flex: 1;
            padding: 20px;
        }

        /* ===== FOOTER ===== */
        .app-footer {
            background: white;
            border-top: 1px solid #e2e8f0;
            padding: 15px 20px;
            font-size: 0.8rem;
            color: #64748b;
            display: flex;
            justify-content: space-between;
        }

        /* ===== OVERLAY ===== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1035;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        /* ===== RESPONSIVIDADE ===== */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px !important;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar.collapsed {
                transform: translateX(-100%);
                width: 280px !important;
            }

            .sidebar.collapsed.show {
                transform: translateX(0);
            }

            /* Forçar mostrar elementos em mobile */
            .sidebar .sidebar-brand span,
            .sidebar .menu-section,
            .sidebar .menu-link span,
            .sidebar .menu-badge,
            .sidebar .user-details,
            .sidebar .submenu-arrow,
            .sidebar .sidebar-footer {
                display: block !important;
            }

            .sidebar .menu-link {
                justify-content: flex-start !important;
                padding: 11px 18px !important;
            }

            .sidebar .menu-link i {
                margin-right: 12px !important;
                font-size: 1.1rem !important;
            }

            .sidebar-collapse-btn {
                display: none !important;
            }

            .sidebar-close {
                display: block;
            }

            .main-content {
                margin-left: 0 !important;
            }

            .menu-toggle {
                display: flex;
            }

            body.sidebar-open {
                overflow: hidden;
            }
        }

        @media (max-width: 576px) {
            .top-header {
                padding: 0 15px;
            }

            .page-content {
                padding: 15px;
            }

            .header-breadcrumb .breadcrumb-item:not(.active) {
                display: none;
            }

            .header-breadcrumb .breadcrumb-item.active::before {
                display: none;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="layout-wrapper">
        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('home.index') }}" class="sidebar-brand">
                    <span>{{ config('app.name') }}</span>
                </a>
                <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Recolher/Expandir menu">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button class="sidebar-close" id="sidebarClose">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <nav class="sidebar-menu">
                {{-- Dashboard --}}
                <div class="menu-item">
                    <a href="{{ route('home.index') }}" class="menu-link {{ request()->routeIs('home.index') ? 'active' : '' }}">
                        <i class="bi bi-house-door"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                @auth
                    {{-- Denver --}}
                    @if(auth()->user()->can('checklist-list') || auth()->user()->can('kid-list'))
                        <div class="menu-section">Denver</div>
                        @can('checklist-list')
                            <div class="menu-item">
                                <a href="{{ route('checklists.index') }}" class="menu-link {{ request()->routeIs('checklists.*') ? 'active' : '' }}">
                                    <i class="bi bi-list-check"></i>
                                    <span>Checklists</span>
                                </a>
                            </div>
                        @endcan
                        @can('kid-list')
                            <div class="menu-item">
                                <a href="{{ route('kids.index') }}" class="menu-link {{ request()->routeIs('kids.*') ? 'active' : '' }}">
                                    <i class="bi bi-people"></i>
                                    <span>Crianças</span>
                                </a>
                            </div>
                        @endcan
                    @endif

                    {{-- Prontuários --}}
                    @if(auth()->user()->can('medical-record-list') || auth()->user()->can('medical-record-view-own'))
                        <div class="menu-section">Prontuários</div>
                        <div class="menu-item">
                            <a href="{{ route('medical-records.index') }}" class="menu-link {{ request()->routeIs('medical-records.*') ? 'active' : '' }}">
                                <i class="bi bi-file-medical"></i>
                                <span>Evolução dos Casos</span>
                            </a>
                        </div>
                    @endif

                    {{-- Documentos --}}
                    @if(auth()->user()->can('document-list') || auth()->user()->can('document-list-all'))
                        <div class="menu-section">Documentos</div>
                        <div class="menu-item">
                            <a href="{{ url('documents') }}" class="menu-link {{ request()->routeIs('documents.index') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-plus"></i>
                                <span>Gerar Documentos</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a href="{{ route('documentos.history') }}" class="menu-link {{ request()->routeIs('documentos.history') ? 'active' : '' }}">
                                <i class="bi bi-clock-history"></i>
                                <span>Histórico</span>
                            </a>
                        </div>
                    @endif

                    {{-- Cadastros --}}
                    @if(auth()->user()->can('user-list') || auth()->user()->can('role-list') || auth()->user()->can('professional-list'))
                        <div class="menu-section">Cadastros</div>
                        @can('user-list')
                            <div class="menu-item">
                                <a href="{{ route('users.index') }}" class="menu-link {{ request()->routeIs('users.*') && !request()->routeIs('users.trash') ? 'active' : '' }}">
                                    <i class="bi bi-person"></i>
                                    <span>Usuários</span>
                                </a>
                            </div>
                        @endcan
                        @can('role-list')
                            <div class="menu-item">
                                <a href="{{ route('roles.index') }}" class="menu-link {{ request()->routeIs('roles.*') && !request()->routeIs('roles.trash') ? 'active' : '' }}">
                                    <i class="bi bi-shield-lock"></i>
                                    <span>Perfis</span>
                                </a>
                            </div>
                        @endcan
                        @can('professional-list')
                            <div class="menu-item">
                                <a href="{{ route('professionals.index') }}" class="menu-link {{ request()->routeIs('professionals.*') && !request()->routeIs('professionals.trash') ? 'active' : '' }}">
                                    <i class="bi bi-person-badge"></i>
                                    <span>Profissionais</span>
                                </a>
                            </div>
                        @endcan
                    @endif

                    {{-- Administração --}}
                    @if(auth()->user()->can('checklist-list-all') || auth()->user()->can('kid-list-all') || auth()->user()->can('user-list-all'))
                        <div class="menu-section">Administração</div>
                        
                        <div class="menu-item">
                            <a class="menu-link has-submenu {{ request()->routeIs('*.trash') ? 'active open' : '' }}" data-submenu="submenu-trash">
                                <i class="bi bi-trash"></i>
                                <span>Lixeira</span>
                                <i class="bi bi-chevron-down submenu-arrow"></i>
                            </a>
                            <ul class="submenu {{ request()->routeIs('*.trash') ? 'open' : '' }}" id="submenu-trash">
                                @can('checklist-list-all')
                                    <li>
                                        <a href="{{ route('checklists.trash') }}" class="submenu-link {{ request()->routeIs('checklists.trash') ? 'active' : '' }}">
                                            Checklists
                                            @php $count = App\Models\Checklist::onlyTrashed()->count(); @endphp
                                            @if($count > 0)<span class="menu-badge">{{ $count }}</span>@endif
                                        </a>
                                    </li>
                                @endcan
                                @can('kid-list-all')
                                    <li>
                                        <a href="{{ route('kids.trash') }}" class="submenu-link {{ request()->routeIs('kids.trash') ? 'active' : '' }}">
                                            Crianças
                                            @php $count = App\Models\Kid::onlyTrashed()->count(); @endphp
                                            @if($count > 0)<span class="menu-badge">{{ $count }}</span>@endif
                                        </a>
                                    </li>
                                @endcan
                                @can('user-list-all')
                                    <li>
                                        <a href="{{ route('users.trash') }}" class="submenu-link {{ request()->routeIs('users.trash') ? 'active' : '' }}">
                                            Usuários
                                            @php $count = App\Models\User::onlyTrashed()->count(); @endphp
                                            @if($count > 0)<span class="menu-badge">{{ $count }}</span>@endif
                                        </a>
                                    </li>
                                @endcan
                                @can('role-list-all')
                                    <li>
                                        <a href="{{ route('roles.trash') }}" class="submenu-link {{ request()->routeIs('roles.trash') ? 'active' : '' }}">
                                            Perfis
                                            @php $count = \App\Models\Role::onlyTrashed()->count(); @endphp
                                            @if($count > 0)<span class="menu-badge">{{ $count }}</span>@endif
                                        </a>
                                    </li>
                                @endcan
                                @can('professional-list-all')
                                    <li>
                                        <a href="{{ route('professionals.trash') }}" class="submenu-link {{ request()->routeIs('professionals.trash') ? 'active' : '' }}">
                                            Profissionais
                                            @php $count = \App\Models\Professional::onlyTrashed()->count(); @endphp
                                            @if($count > 0)<span class="menu-badge">{{ $count }}</span>@endif
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>

                        @if(auth()->user()->can('view logs'))
                            <div class="menu-item">
                                <a href="{{ route('log-viewer::dashboard') }}" class="menu-link {{ request()->routeIs('logs.*') ? 'active' : '' }}">
                                    <i class="bi bi-journal-text"></i>
                                    <span>Logs</span>
                                </a>
                            </div>
                        @endif
                    @endif
                @endauth
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    @if(auth()->user()->avatar && file_exists(public_path(auth()->user()->avatar)))
                        <img src="{{ asset(auth()->user()->avatar) }}" alt="Avatar" class="user-avatar">
                    @else
                        <div class="user-avatar-placeholder">
                            <i class="bi bi-person"></i>
                        </div>
                    @endif
                    <div class="user-details">
                        <div class="user-name" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</div>
                        <div class="user-role">{{ auth()->user()->roles->first()->name ?? 'Usuário' }}</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- OVERLAY MOBILE -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- TOP HEADER -->
            <header class="top-header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle" aria-label="Abrir menu">
                        <i class="bi bi-list"></i>
                    </button>
                    @hasSection('breadcrumb')
                        @yield('breadcrumb')
                    @else
                        <nav aria-label="breadcrumb" class="header-breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
                                @yield('breadcrumb-items')
                            </ol>
                        </nav>
                    @endif
                </div>
                <div class="header-right">
                    @yield('actions')
                    
                    <div class="dropdown profile-dropdown">
                        <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @if(auth()->user()->avatar && file_exists(public_path(auth()->user()->avatar)))
                                <img src="{{ asset(auth()->user()->avatar) }}" alt="" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">
                            @else
                                <i class="bi bi-person-circle" style="font-size: 1.2rem;"></i>
                            @endif
                            <span class="d-none d-md-inline" style="font-size: 0.85rem;">{{ auth()->user()->first_name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">
                                <div class="d-flex align-items-center gap-2">
                                    @if(auth()->user()->avatar && file_exists(public_path(auth()->user()->avatar)))
                                        <img src="{{ asset(auth()->user()->avatar) }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    @else
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight: 600;">{{ auth()->user()->name }}</div>
                                        <div style="font-size: 0.75rem; color: #64748b;">{{ auth()->user()->email }}</div>
                                    </div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Meu Perfil</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Sair</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <main class="page-content">
                @include('layouts.messages')
                @yield('content')
            </main>

            <!-- FOOTER -->
            <footer class="app-footer">
                <span>&copy; {{ date('Y') }} {{ config('app.name') }}</span>
                <span>v{{ config('app.version', '1.0') }}</span>
            </footer>
        </div>
    </div>

    <script src="{{ mix('js/app.js') }}"></script>
    <script>
        (function() {
            'use strict';
            
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const menuToggle = document.getElementById('menuToggle');
            const sidebarClose = document.getElementById('sidebarClose');
            const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
            
            // Detectar se é mobile
            const isMobile = () => window.innerWidth < 992;
            
            // Abrir sidebar (mobile)
            function openSidebar() {
                sidebar.classList.add('show');
                sidebarOverlay.classList.add('show');
                document.body.classList.add('sidebar-open');
            }
            
            // Fechar sidebar (mobile)
            function closeSidebar() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
            
            // Toggle collapse (desktop)
            function toggleCollapse() {
                if (isMobile()) return;
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            }
            
            // Event Listeners
            if (menuToggle) {
                menuToggle.addEventListener('click', openSidebar);
            }
            
            if (sidebarClose) {
                sidebarClose.addEventListener('click', closeSidebar);
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', closeSidebar);
            }
            
            if (sidebarCollapseBtn) {
                sidebarCollapseBtn.addEventListener('click', toggleCollapse);
            }
            
            // Restaurar estado colapsado (desktop)
            if (!isMobile() && localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
            }
            
            // Fechar sidebar ao redimensionar para desktop
            window.addEventListener('resize', function() {
                if (!isMobile()) {
                    closeSidebar();
                }
            });
            
            // ===== MENU ACCORDION (SANFONA) =====
            document.querySelectorAll('.has-submenu').forEach(function(trigger) {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const submenuId = this.getAttribute('data-submenu');
                    const submenu = document.getElementById(submenuId);
                    const isOpen = this.classList.contains('open');
                    
                    // Fechar outros submenus (opcional - comportamento acordeão)
                    // document.querySelectorAll('.has-submenu.open').forEach(function(other) {
                    //     if (other !== trigger) {
                    //         other.classList.remove('open');
                    //         const otherSubmenu = document.getElementById(other.getAttribute('data-submenu'));
                    //         if (otherSubmenu) otherSubmenu.classList.remove('open');
                    //     }
                    // });
                    
                    // Toggle atual
                    if (isOpen) {
                        this.classList.remove('open');
                        if (submenu) submenu.classList.remove('open');
                    } else {
                        this.classList.add('open');
                        if (submenu) submenu.classList.add('open');
                    }
                });
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
