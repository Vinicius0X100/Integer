<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Integer') }}</title>
    
    <!-- Script Anti-FOUC (Apple Theme System: Claro, Escuro e Automático) -->
    <script>
        (function() {
            try {
                const storedPref = localStorage.getItem('integer_theme_preference') || localStorage.getItem('theme') || 'auto';
                let effectiveTheme = storedPref;
                if (storedPref === 'auto') {
                    effectiveTheme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                document.documentElement.setAttribute('data-bs-theme', effectiveTheme);
                document.documentElement.setAttribute('data-theme-preference', storedPref);
            } catch (e) {}
        })();
    </script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/logo-black.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="dns-prefetch" href="//fonts.bunny.net">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
        :root {
            /* Integer Typography System - Inter (Google Fonts) */
            --integer-font: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            --apple-font: var(--integer-font);

            /* Apple Design System - Light Mode Variables */
            --apple-bg: #f5f5f7;
            --apple-sidebar-bg: rgba(255, 255, 255, 0.78);
            --apple-header-bg: rgba(255, 255, 255, 0.78);
            --apple-card-bg: rgba(255, 255, 255, 0.85);
            --apple-text: #1d1d1f;
            --apple-text-secondary: #86868b;
            --apple-text-tertiary: #98989d;
            --apple-border: rgba(0, 0, 0, 0.08);
            --apple-border-subtle: rgba(0, 0, 0, 0.04);
            --apple-blue: #0071e3;
            --apple-blue-hover: #0077ed;
            --apple-blue-subtle: rgba(0, 113, 227, 0.1);
            --apple-hover-bg: rgba(0, 0, 0, 0.04);
            --apple-hover-bg-subtle: rgba(0, 0, 0, 0.02);
            --apple-shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.03);
            --apple-shadow-md: 0 6px 24px rgba(0, 0, 0, 0.06);
            --apple-shadow-dropdown: 0 12px 36px rgba(0, 0, 0, 0.12);
            --apple-radius-sm: 8px;
            --apple-radius-md: 10px;
            --apple-radius-lg: 16px;
            --apple-radius-xl: 20px;
        }

        [data-bs-theme="dark"] {
            /* Apple Design System - Dark Mode Variables */
            --apple-bg: #000000;
            --apple-sidebar-bg: rgba(24, 24, 26, 0.82);
            --apple-header-bg: rgba(24, 24, 26, 0.82);
            --apple-card-bg: rgba(28, 28, 30, 0.82);
            --apple-text: #f5f5f7;
            --apple-text-secondary: rgba(235, 235, 245, 0.6);
            --apple-text-tertiary: rgba(235, 235, 245, 0.38);
            --apple-border: rgba(255, 255, 255, 0.09);
            --apple-border-subtle: rgba(255, 255, 255, 0.05);
            --apple-blue: #0a84ff;
            --apple-blue-hover: #409cff;
            --apple-blue-subtle: rgba(10, 132, 255, 0.16);
            --apple-hover-bg: rgba(255, 255, 255, 0.06);
            --apple-hover-bg-subtle: rgba(255, 255, 255, 0.03);
            --apple-shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
            --apple-shadow-md: 0 6px 24px rgba(0, 0, 0, 0.45);
            --apple-shadow-dropdown: 0 16px 40px rgba(0, 0, 0, 0.6);
        }

        /* --- Padronização Tipográfica Inter --- */
        body {
            font-family: var(--integer-font);
            font-weight: 400; /* Normal para leitura corrente */
            font-size: 0.94rem;
            line-height: 1.5;
            background-color: var(--apple-bg);
            color: var(--apple-text);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            letter-spacing: -0.011em;
            transition: background-color 0.25s cubic-bezier(0.16, 1, 0.3, 1), color 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            overflow-x: hidden;
        }

        /* Títulos e Cabeçalhos (Bold & SemiBold com tracking ajustado) */
        h1, .h1, h2, .h2, h3, .h3 {
            font-family: var(--integer-font);
            font-weight: 700; /* Bold */
            letter-spacing: -0.025em;
            color: var(--apple-text);
        }

        h4, .h4, h5, .h5, h6, .h6 {
            font-family: var(--integer-font);
            font-weight: 600; /* SemiBold */
            letter-spacing: -0.018em;
            color: var(--apple-text);
        }

        .main-header h5 {
            font-weight: 700; /* Bold para o título principal da página */
            letter-spacing: -0.02em;
        }

        /* Textos Leves (Light: 300) */
        small, .small, .form-text, .text-muted, .footer {
            font-weight: 300 !important; /* Light para subtítulos, legendas e textos de apoio */
            letter-spacing: -0.005em;
        }

        /* Utilitários de Peso de Fonte */
        .fw-light { font-weight: 300 !important; }
        .fw-normal { font-weight: 400 !important; }
        .fw-medium { font-weight: 500 !important; }
        .fw-semibold { font-weight: 600 !important; }
        .fw-bold { font-weight: 700 !important; }

        /* Overrides para consistência de Tema */
        .bg-light {
            background-color: var(--apple-hover-bg) !important;
        }
        
        .bg-white {
            background-color: var(--apple-card-bg) !important;
            color: var(--apple-text);
        }
        
        .text-dark {
            color: var(--apple-text) !important;
        }
        
        .text-muted {
            color: var(--apple-text-secondary) !important;
        }

        /* Tabelas no estilo Apple com Inter */
        .table {
            color: var(--apple-text);
            --bs-table-color: var(--apple-text);
            --bs-table-hover-color: var(--apple-text);
            --bs-table-bg: transparent;
            --bs-table-border-color: var(--apple-border);
            font-family: var(--integer-font);
        }

        .table th {
            font-weight: 600; /* SemiBold em cabeçalhos de tabela */
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--apple-text-secondary);
        }

        .table td {
            font-weight: 400; /* Normal para dados da tabela */
            font-size: 0.9rem;
        }

        .table-hover > tbody > tr {
            transition: background-color 0.15s ease;
        }

        .table-hover > tbody > tr:hover > * {
            --bs-table-accent-bg: var(--apple-hover-bg);
            color: var(--apple-text);
        }
        
        /* Formulários e Inputs no estilo Apple com Inter */
        .form-label {
            font-family: var(--integer-font);
            font-weight: 500; /* Medium para rótulos de campos */
            font-size: 0.88rem;
            letter-spacing: -0.01em;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            color: var(--apple-text);
            background-color: var(--apple-hover-bg);
            border: 1px solid var(--apple-border);
            border-radius: var(--apple-radius-md);
            font-family: var(--integer-font);
            font-weight: 400; /* Normal nos campos de entrada */
            font-size: 0.92rem;
            padding: 8px 14px;
            transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .form-control::placeholder {
            font-weight: 300; /* Light nos placeholders */
            color: var(--apple-text-tertiary);
        }

        .form-select option {
            background-color: var(--apple-card-bg);
            color: var(--apple-text);
            font-weight: 400;
        }

        .form-control:focus,
        .form-select:focus {
            color: var(--apple-text);
            background-color: transparent;
            border-color: var(--apple-blue);
            box-shadow: 0 0 0 3.5px var(--apple-blue-subtle);
            outline: none;
        }

        /* Botões no estilo Apple Flat Design com Inter */
        .btn {
            font-family: var(--integer-font);
            font-weight: 500; /* Medium para botões neutros e secundários */
            letter-spacing: -0.012em;
            border-radius: var(--apple-radius-md);
            padding: 8px 16px;
            transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid transparent;
        }

        .btn:active {
            transform: scale(0.975);
        }

        .btn-primary, .btn-danger, .btn-success {
            font-weight: 600; /* SemiBold/Bold nos botões de ação principal */
            letter-spacing: -0.015em;
        }

        .btn-primary {
            background-color: var(--apple-blue);
            border-color: transparent;
            color: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 113, 227, 0.25);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--apple-blue-hover);
            border-color: transparent;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(0, 113, 227, 0.35);
        }

        .btn-light {
            background-color: var(--apple-hover-bg);
            color: var(--apple-text);
            border-color: var(--apple-border);
            font-weight: 500;
        }

        .btn-light:hover,
        .btn-light:focus {
            background-color: var(--apple-hover-bg-subtle);
            color: var(--apple-text);
            border-color: var(--apple-border);
        }

        .btn-outline-secondary {
            border-color: var(--apple-border);
            color: var(--apple-text-secondary);
            font-weight: 500;
        }

        .btn-outline-secondary:hover,
        .btn-outline-secondary:focus {
            background-color: var(--apple-hover-bg);
            border-color: var(--apple-border);
            color: var(--apple-text);
        }

        .rounded-pill {
            border-radius: 980px !important;
        }

        /* Layout Structure */
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
            min-height: 100vh;
        }

        /* Sidebar no estilo macOS Finder / Settings */
        #sidebar {
            min-width: 260px;
            max-width: 260px;
            background-color: var(--apple-sidebar-bg);
            backdrop-filter: saturate(190%) blur(25px);
            -webkit-backdrop-filter: saturate(190%) blur(25px);
            border-right: 1px solid var(--apple-border);
            transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            position: fixed;
            height: 100vh;
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Scrollbar sutil e minimalista */
        #sidebar::-webkit-scrollbar {
            width: 4px;
        }
        #sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        #sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(128, 128, 128, 0.2);
            border-radius: 4px;
        }
        #sidebar::-webkit-scrollbar-thumb:hover {
            background-color: rgba(128, 128, 128, 0.4);
        }

        #sidebar.active {
            margin-left: -260px;
        }

        #sidebar .sidebar-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--apple-border);
        }

        #sidebar-logo {
            transition: opacity 0.2s ease;
        }

        #sidebar ul.components {
            padding: 14px 0;
        }

        #sidebar ul li a {
            padding: 8px 14px;
            font-size: 0.89rem;
            display: flex;
            align-items: center;
            color: var(--apple-text);
            text-decoration: none;
            border-radius: var(--apple-radius-sm);
            margin: 2px 10px;
            transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
            font-weight: 500;
        }

        #sidebar ul li a:hover {
            background-color: var(--apple-hover-bg);
            color: var(--apple-text);
        }

        #sidebar ul li a.active {
            background-color: var(--apple-blue-subtle);
            color: var(--apple-blue);
            font-weight: 600;
        }

        #sidebar ul li a.active i {
            color: var(--apple-blue);
        }

        #sidebar ul li a i {
            margin-right: 11px;
            font-size: 1.05rem;
            width: 20px;
            text-align: center;
            opacity: 0.88;
            flex-shrink: 0;
        }

        .sidebar-section-title {
            font-size: 0.68rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--apple-text-tertiary);
            padding: 14px 22px 4px 22px;
            display: block;
        }

        /* Submenus colapsáveis limpos */
        #sidebar .collapse-submenu {
            background-color: transparent !important;
            border-left: 1.5px solid var(--apple-border);
            border-radius: 0 !important;
            margin: 4px 10px 6px 24px !important;
            padding-left: 6px !important;
        }

        #sidebar .collapse-submenu li a {
            padding: 6px 12px !important;
            font-size: 0.84rem !important;
            border-radius: 7px !important;
            margin: 1px 0 !important;
        }

        /* Content */
        #content {
            width: 100%;
            margin-left: 260px;
            transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            min-width: 0;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        @media (max-width: 768px) {
            #sidebar {
                margin-left: -260px;
            }
            #sidebar.active {
                margin-left: 0;
            }
            #content {
                margin-left: 0;
                min-width: 0;
                width: 100%;
                max-width: 100vw;
            }
        }

        /* Navbar/Header no estilo macOS Toolbar */
        .main-header {
            background-color: var(--apple-header-bg);
            backdrop-filter: saturate(190%) blur(25px);
            -webkit-backdrop-filter: saturate(190%) blur(25px);
            border-bottom: 1px solid var(--apple-border);
            padding: 10px 20px;
            position: sticky;
            top: 0;
            z-index: 900;
            min-width: 0;
            min-height: 56px;
        }

        /* Cards no estilo Apple */
        .card {
            background-color: var(--apple-card-bg);
            backdrop-filter: saturate(190%) blur(25px);
            -webkit-backdrop-filter: saturate(190%) blur(25px);
            border: 1px solid var(--apple-border);
            border-radius: var(--apple-radius-lg);
            box-shadow: var(--apple-shadow-sm);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .card-header {
            border-bottom: 1px solid var(--apple-border);
            background-color: transparent;
        }

        /* Dropdowns no estilo Apple macOS */
        .dropdown-menu {
            background-color: var(--apple-card-bg);
            backdrop-filter: saturate(190%) blur(25px);
            -webkit-backdrop-filter: saturate(190%) blur(25px);
            border: 1px solid var(--apple-border);
            border-radius: 12px;
            box-shadow: var(--apple-shadow-dropdown);
            padding: 6px;
        }

        .dropdown-item {
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 0.88rem;
            color: var(--apple-text);
            transition: background-color 0.12s ease;
            display: flex;
            align-items: center;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: var(--apple-hover-bg);
            color: var(--apple-text);
        }

        .dropdown-item.active,
        .dropdown-item:active {
            background-color: var(--apple-blue-subtle);
            color: var(--apple-blue);
            font-weight: 500;
        }

        .dropdown-divider {
            border-color: var(--apple-border);
            margin: 6px 0;
        }

        /* Botão de controle de tema */
        .theme-selector-btn {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background-color: var(--apple-hover-bg);
            border: 1px solid var(--apple-border);
            color: var(--apple-text);
            transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
            padding: 0;
        }

        .theme-selector-btn:hover {
            background-color: var(--apple-hover-bg-subtle);
            color: var(--apple-text);
            border-color: var(--apple-border);
        }

        /* Auth Page Specific */
        .auth-wrapper {
            margin-left: 0 !important;
            background: linear-gradient(135deg, #0a1f44 0%, #040b19 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Footer */
        .footer {
            padding: 20px;
            text-align: center;
            color: var(--apple-text-secondary);
            font-size: 0.82rem;
            margin-top: auto;
        }

        /* Utility: w-sm-auto */
        @media (min-width: 576px) {
            .w-sm-auto { width: auto !important; }
        }

        /* Evita scrollbar horizontal dentro de table-responsive em mobile */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        @media (max-width: 575.98px) {
            .table-responsive {
                scrollbar-width: none;
                -ms-overflow-style: none;
            }
            .table-responsive::-webkit-scrollbar {
                display: none;
            }
        }
    </style>
</head>
<body>
    @guest
        <!-- Layout for Login/Guest -->
        <div id="app" class="auth-wrapper">
            <!-- Theme Dropdown for Guest -->
            <div class="position-absolute top-0 end-0 p-3">
                <div class="dropdown">
                    <button class="theme-selector-btn text-white-50 border-0" id="theme-toggle-guest" data-bs-toggle="dropdown" aria-expanded="false" title="Tema: Automático">
                        <i class="bi bi-circle-half theme-icon-current"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="theme-toggle-guest">
                        <li>
                            <button type="button" class="dropdown-item d-flex justify-content-between align-items-center" onclick="setThemePreference('light')">
                                <span><i class="bi bi-sun-fill me-2 text-warning"></i> Claro</span>
                                <i class="bi bi-check2 theme-check-light d-none"></i>
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item d-flex justify-content-between align-items-center" onclick="setThemePreference('dark')">
                                <span><i class="bi bi-moon-stars-fill me-2 text-primary"></i> Escuro</span>
                                <i class="bi bi-check2 theme-check-dark d-none"></i>
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item d-flex justify-content-between align-items-center" onclick="setThemePreference('auto')">
                                <span><i class="bi bi-circle-half me-2 text-info"></i> Automático (Sistema)</span>
                                <i class="bi bi-check2 theme-check-auto d-none"></i>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <main class="py-4 flex-grow-1 d-flex align-items-center justify-content-center">
                @yield('content')
            </main>
            <footer class="footer">
                &copy; {{ date('Y') }} Sacratech Softwares LTDA. Todos os direitos reservados.
            </footer>
        </div>
    @else
        <!-- Layout for Authenticated Users (Dashboard) -->
        <div class="wrapper">
            <!-- Sidebar -->
            <nav id="sidebar">
                <div class="sidebar-header d-flex align-items-center justify-content-between">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        <img src="{{ asset('img/logo-white.png') }}" id="sidebar-logo" alt="Integer" style="height: 32px; width: auto;">
                    </a>
                    <button type="button" id="sidebarCollapse" class="btn btn-link d-md-none text-reset p-0">
                        <i class="bi bi-x-lg fs-5"></i>
                    </button>
                </div>

                <ul class="list-unstyled components">
                    <li>
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('clientes.index') }}" class="{{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                            <i class="bi bi-people-fill"></i> Clientes
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('servicos.index') }}" class="{{ request()->routeIs('servicos.*') ? 'active' : '' }}">
                            <i class="bi bi-briefcase-fill"></i> Serviços
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('services_automations.index') }}" class="{{ request()->routeIs('services_automations.*') ? 'active' : '' }}">
                            <i class="bi bi-robot"></i> Serviços e Automações
                        </a>
                    </li>
                    <li class="mt-2">
                        <span class="sidebar-section-title">Gerenciamento</span>
                    </li>
                    <li class="mt-1">
                        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }} d-flex align-items-center">
                            @if(file_exists(public_path('img/sacratech-id.png')))
                                <img src="{{ asset('img/sacratech-id.png') }}" alt="Sacratech iD Logo" style="width: 20px; height: 20px; object-fit: contain; margin-right: 11px;">
                            @else
                                <i class="bi bi-people"></i> 
                            @endif
                            Usuários do Sacratech iD
                        </a>
                    </li>
                    <li class="mt-2">
                        <span class="sidebar-section-title">Controle de Acessos</span>
                    </li>
                    <li class="mt-1">
                        <a href="{{ route('sismatriz.index') }}" class="{{ request()->routeIs('sismatriz.*') ? 'active' : '' }} d-flex align-items-center">
                            @if(file_exists(public_path('img/sismatriz-ticket-logo.jpg')))
                                <img src="{{ asset('img/sismatriz-ticket-logo.jpg') }}" alt="Ticket Logo" style="width: 20px; height: 20px; object-fit: contain; margin-right: 11px;">
                            @else
                                <i class="bi bi-ticket-detailed-fill"></i> 
                            @endif
                            SisMatriz Ticket
                        </a>
                    </li>
                    <li class="mt-1">
                        <a href="#sismatrizSubmenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('sismatriz-main.*') || request()->routeIs('paroquias.*') ? 'true' : 'false' }}" class="dropdown-toggle d-flex align-items-center">
                            @if(file_exists(public_path('img/sismatriz-logo.png')))
                                <img src="{{ asset('img/sismatriz-logo.png') }}" alt="SisMatriz Logo" style="width: 20px; height: 20px; object-fit: contain; margin-right: 11px;">
                            @else
                                <i class="bi bi-building-fill"></i> 
                            @endif
                            SisMatriz
                        </a>
                        <ul class="collapse list-unstyled collapse-submenu {{ request()->routeIs('sismatriz-main.*') || request()->routeIs('paroquias.*') ? 'show' : '' }}" id="sismatrizSubmenu">
                            <li>
                                <a href="{{ route('sismatriz-main.index') }}" class="{{ request()->routeIs('sismatriz-main.index') || request()->routeIs('sismatriz-main.show') || request()->routeIs('sismatriz-main.create') || request()->routeIs('sismatriz-main.edit') ? 'active' : '' }}">
                                    Acessos e Usuários
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('sismatriz-main.metrics') }}" class="{{ request()->routeIs('sismatriz-main.metrics') ? 'active' : '' }}">
                                    Métricas e KPIs
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('paroquias.index') }}" class="{{ request()->routeIs('paroquias.*') ? 'active' : '' }}">
                                    Paróquias
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="mt-2">
                        <span class="sidebar-section-title">Monitoramento</span>
                    </li>
                    <li class="mt-1">
                        <a href="{{ route('system_health.index') }}" class="{{ request()->routeIs('system_health.*') ? 'active' : '' }}">
                            <i class="bi bi-activity"></i> Saúde dos Sistemas
                        </a>
                    </li>
                    <li class="mt-1">
                        <a href="{{ route('system_logs.index') }}" class="{{ request()->routeIs('system_logs.*') ? 'active' : '' }}">
                            <i class="bi bi-journal-text"></i> Relatórios do Sistema (LOGS)
                        </a>
                    </li>
                    <li class="mt-2">
                        <span class="sidebar-section-title">Marketing</span>
                    </li>
                    <li class="mt-1">
                        <a href="{{ route('campanhas_email.index') }}" class="{{ request()->routeIs('campanhas_email.*') ? 'active' : '' }}">
                            <i class="bi bi-envelope-paper-fill"></i> Campanhas de Email
                        </a>
                    </li>
                    @if(Auth::user()->papel === 'admin')
                    <li class="mt-2">
                        <span class="sidebar-section-title">Financeiro</span>
                    </li>
                    <li class="mt-1">
                        <a href="{{ route('nodal-billing.index') }}" class="{{ request()->routeIs('nodal-billing.*') ? 'active' : '' }}">
                            <i class="bi bi-receipt-cutoff"></i> Faturamento Nodal
                        </a>
                    </li>
                    <li class="mt-1">
                        <a href="{{ route('nodal-plans.index') }}" class="{{ request()->routeIs('nodal-plans.*') ? 'active' : '' }}">
                            <i class="bi bi-box-seam-fill"></i> Planos de Licenciamento
                        </a>
                    </li>
                    <li class="mt-2">
                        <span class="sidebar-section-title">Integrações</span>
                    </li>
                    <li class="mt-1">
                        <a href="#nodalSubmenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('nodal.*') || request()->routeIs('nodal-verifications.*') || request()->routeIs('nodal-billing.*') || request()->routeIs('nodal-plans.*') ? 'true' : 'false' }}" class="dropdown-toggle d-flex align-items-center">
                            @if(file_exists(public_path('img/Nodal-Icon.png')))
                                <img src="{{ asset('img/Nodal-Icon.png') }}" alt="Nodal" style="width: 20px; height: 20px; object-fit: contain; margin-right: 11px;">
                            @else
                                <i class="bi bi-building-check"></i>
                            @endif
                            Nodal
                        </a>
                        <ul class="collapse list-unstyled collapse-submenu {{ request()->routeIs('nodal.*') || request()->routeIs('nodal-verifications.*') || request()->routeIs('nodal-billing.*') || request()->routeIs('nodal-plans.*') ? 'show' : '' }}" id="nodalSubmenu">
                            <li>
                                <a href="{{ route('nodal.index') }}" class="{{ request()->routeIs('nodal.index') || request()->routeIs('nodal.create') || request()->routeIs('nodal.store') ? 'active' : '' }}">
                                    Empresas
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('nodal-plans.index') }}" class="{{ request()->routeIs('nodal-plans.*') ? 'active' : '' }}">
                                    Planos de Licenciamento
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('nodal-billing.index') }}" class="{{ request()->routeIs('nodal-billing.*') ? 'active' : '' }}">
                                    Faturamento Nodal
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('nodal.settings') }}" class="{{ request()->routeIs('nodal.settings') || request()->routeIs('nodal.save-settings') ? 'active' : '' }}">
                                    Configurações
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('nodal-verifications.index') }}" class="{{ request()->routeIs('nodal-verifications.*') ? 'active' : '' }}">
                                    Verificações KYC
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                </ul>
            </nav>

            <!-- Page Content -->
            <div id="content" class="d-flex flex-column min-vh-100">
                <header class="main-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center me-2 flex-grow-1 min-w-0" style="min-width: 0;">
                        <button type="button" id="sidebarCollapseBtn" class="btn btn-link text-reset me-2 me-sm-3 p-0 d-md-none flex-shrink-0">
                            <i class="bi bi-list fs-4"></i>
                        </button>
                        <h5 class="m-0 fw-semibold text-truncate" style="font-size: clamp(0.9rem, 2.5vw, 1.15rem); letter-spacing: -0.015em;">@yield('page-title', 'Dashboard')</h5>
                    </div>

                    <div class="d-flex align-items-center gap-2 gap-sm-3 flex-shrink-0">
                        <!-- Apple Theme Selector Dropdown -->
                        <div class="dropdown">
                            <button class="theme-selector-btn" id="theme-toggle-dash" data-bs-toggle="dropdown" aria-expanded="false" title="Tema: Automático">
                                <i class="bi bi-circle-half theme-icon-current"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="theme-toggle-dash">
                                <li>
                                    <button type="button" class="dropdown-item d-flex justify-content-between align-items-center" onclick="setThemePreference('light')">
                                        <span><i class="bi bi-sun-fill me-2 text-warning"></i> Claro</span>
                                        <i class="bi bi-check2 theme-check-light d-none text-primary"></i>
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item d-flex justify-content-between align-items-center" onclick="setThemePreference('dark')">
                                        <span><i class="bi bi-moon-stars-fill me-2 text-primary"></i> Escuro</span>
                                        <i class="bi bi-check2 theme-check-dark d-none text-primary"></i>
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item d-flex justify-content-between align-items-center" onclick="setThemePreference('auto')">
                                        <span><i class="bi bi-circle-half me-2 text-info"></i> Automático (Sistema)</span>
                                        <i class="bi bi-check2 theme-check-auto d-none text-primary"></i>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <!-- User Dropdown -->
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-reset" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white me-1 me-sm-2 flex-shrink-0" style="width: 32px; height: 32px; background: linear-gradient(135deg, #0071e3 0%, #0a84ff 100%); font-weight: 600; font-size: 0.85rem;">
                                    {{ substr(Auth::user()->nome ?? 'A', 0, 1) }}
                                </div>
                                <span class="d-none d-sm-inline text-truncate fw-medium" style="max-width: 140px; font-size: 0.9rem;">{{ Auth::user()->nome }} {{ Auth::user()->sobrenome }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" aria-labelledby="dropdownUser1">
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.index') }}">
                                        <i class="bi bi-person me-2"></i> Perfil
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                        <i class="bi bi-box-arrow-right me-2"></i> Sair
                                    </a>
                                </li>
                            </ul>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>

                        <!-- LOGO SACRATECH_ID -->
                        <div class="border-start ps-2 ps-sm-3 ms-1 ms-sm-2 d-flex align-items-center" title="Sacratech ID" style="border-color: var(--apple-border) !important;">
                             @if(file_exists(public_path('img/sacratech-id.png')))
                                <img src="{{ asset('img/sacratech-id.png') }}" alt="Sacratech ID" height="24" class="d-none d-md-block">
                                <img src="{{ asset('img/sacratech-id.png') }}" alt="Sacratech ID" height="18" class="d-md-none">
                             @else
                                <span class="badge bg-primary rounded-pill">Sacratech ID</span>
                             @endif
                        </div>
                    </div>
                </header>

                <main class="p-3 p-md-4 flex-grow-1" style="min-width: 0;">
                    @yield('content')
                </main>

                <footer class="footer mt-auto">
                    &copy; {{ date('Y') }} Sacratech Softwares LTDA. Todos os direitos reservados.
                </footer>
            </div>
        </div>
    @endguest

    <!-- Global Page Transition Overlay -->
    <div id="global-page-loader" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="z-index: 10000; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); transition: opacity 0.3s ease;">
        <div class="d-flex flex-column align-items-center justify-content-center h-100">
            <div class="spinner-border text-light" style="width: 2.8rem; height: 2.8rem; border-width: 2.5px;" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-3 text-white fw-medium fs-6" style="letter-spacing: -0.01em;">Carregando...</p>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg" style="background-color: var(--apple-card-bg); backdrop-filter: saturate(190%) blur(25px); -webkit-backdrop-filter: saturate(190%) blur(25px); border: 1px solid var(--apple-border);">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold" id="logoutModalLabel">Confirmar Saída</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4 px-4">
                    <p class="mb-0 text-muted">Você será desconectado do sistema e seu acesso automático será removido. Para acessar novamente, será necessário realizar o login manualmente com a opção "Lembrar-me" marcada, se desejar.</p>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" onclick="document.getElementById('logout-form').submit();">
                        Sair Agora
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Apple Theme & Layout Engine -->
    <script>
        // --- GERENCIADOR DE TEMAS APPLE (Claro, Escuro e Automático) ---
        const logoWhiteUrl = "{{ asset('img/logo-white.png') }}";
        const logoBlackUrl = "{{ asset('img/logo-black.png') }}";

        function getSystemTheme() {
            return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        function getStoredThemePreference() {
            return localStorage.getItem('integer_theme_preference') || localStorage.getItem('theme') || 'auto';
        }

        function setThemePreference(preference) {
            localStorage.setItem('integer_theme_preference', preference);
            localStorage.setItem('theme', preference); // Retrocompatibilidade
            applyTheme(preference);
        }

        function applyTheme(preference) {
            const html = document.documentElement;
            const effectiveTheme = preference === 'auto' ? getSystemTheme() : preference;
            
            html.setAttribute('data-bs-theme', effectiveTheme);
            html.setAttribute('data-theme-preference', preference);

            // Atualiza o logotipo da sidebar
            const sidebarLogo = document.getElementById('sidebar-logo');
            if (sidebarLogo) {
                sidebarLogo.src = effectiveTheme === 'dark' ? logoWhiteUrl : logoBlackUrl;
            }

            // Atualiza ícones dos botões de alternância
            const iconClass = preference === 'auto' 
                ? 'bi-circle-half' 
                : (preference === 'dark' ? 'bi-moon-stars-fill' : 'bi-sun-fill');

            const titleText = preference === 'auto'
                ? 'Tema: Automático (Sistema)'
                : (preference === 'dark' ? 'Tema: Escuro' : 'Tema: Claro');

            document.querySelectorAll('.theme-icon-current').forEach(el => {
                el.className = `bi ${iconClass} theme-icon-current`;
            });

            document.querySelectorAll('#theme-toggle-dash, #theme-toggle-guest').forEach(btn => {
                btn.setAttribute('title', titleText);
            });

            // Atualiza marcas de seleção dos dropdowns
            ['light', 'dark', 'auto'].forEach(t => {
                document.querySelectorAll(`.theme-check-${t}`).forEach(check => {
                    if (t === preference) {
                        check.classList.remove('d-none');
                    } else {
                        check.classList.add('d-none');
                    }
                });
            });
        }

        // Listener para alterações de tema no Sistema Operacional (Windows / macOS / Navegador)
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                const currentPref = getStoredThemePreference();
                if (currentPref === 'auto') {
                    applyTheme('auto');
                }
            });
        }

        // Inicializa o tema ao carregar
        document.addEventListener('DOMContentLoaded', () => {
            const initialPref = getStoredThemePreference();
            applyTheme(initialPref);
        });

        // --- GLOBAL PAGE LOADER ---
        document.addEventListener('DOMContentLoaded', () => {
            const globalLoader = document.getElementById('global-page-loader');
            
            const showLoader = () => {
                if (globalLoader) {
                    globalLoader.classList.remove('d-none');
                    globalLoader.style.opacity = '0';
                    setTimeout(() => { globalLoader.style.opacity = '1'; }, 10);
                }
            };

            const hideLoader = () => {
                if (globalLoader) {
                    globalLoader.style.opacity = '0';
                    setTimeout(() => { globalLoader.classList.add('d-none'); }, 300);
                }
            };

            // Links com navegação
            document.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (link) {
                    const href = link.getAttribute('href');
                    const target = link.getAttribute('target');
                    
                    if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:') || target === '_blank' || link.hasAttribute('download')) {
                        return;
                    }

                    if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) {
                        return;
                    }
                    
                    showLoader();
                }
            });

            // Formulários com envio
            document.addEventListener('submit', (e) => {
                const form = e.target;
                if (form.target === '_blank' || form.hasAttribute('data-no-loader') || form.classList.contains('no-loader') || e.defaultPrevented) return;
                
                showLoader();
            });
            
            window.addEventListener('pageshow', () => {
                hideLoader();
            });
            
            hideLoader();
        });

        // --- SIDEBAR TOGGLE ---
        const sidebar = document.getElementById('sidebar');
        const sidebarCollapse = document.getElementById('sidebarCollapse');
        const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');

        function toggleSidebar() {
            if (sidebar) sidebar.classList.toggle('active');
        }

        if (sidebarCollapse) sidebarCollapse.addEventListener('click', toggleSidebar);
        if (sidebarCollapseBtn) sidebarCollapseBtn.addEventListener('click', toggleSidebar);

        // Fecha a sidebar no mobile ao clicar fora
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && sidebar) {
                if (!sidebar.contains(e.target) && sidebarCollapseBtn && !sidebarCollapseBtn.contains(e.target)) {
                    if (sidebar.classList.contains('active')) {
                        sidebar.classList.remove('active');
                    }
                }
            }
        });

        // Tooltips Bootstrap
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    </script>

    @stack('scripts')
</body>
</html>
