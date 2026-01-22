<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Integer') }}</title>
    
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
    
    <style>
        @font-face {
            font-family: 'SF Pro Display';
            src: local('SF Pro Display'), local('SFProDisplay'), local('Segoe UI'), local('Roboto');
            font-weight: 400;
            font-style: normal;
        }
        
        @font-face {
            font-family: 'SF Pro Display';
            src: local('SF Pro Display Bold'), local('SFProDisplay-Bold'), local('Segoe UI Bold'), local('Roboto Bold');
            font-weight: 700;
            font-style: normal;
        }

        @font-face {
            font-family: 'SF Pro Display';
            src: local('SF Pro Display Medium'), local('SFProDisplay-Medium'), local('Segoe UI Semibold'), local('Roboto Medium');
            font-weight: 500;
            font-style: normal;
        }

        :root {
            /* Light Mode Variables */
            --apple-bg: #f5f5f7;
            --apple-sidebar-bg: rgba(255, 255, 255, 0.8);
            --apple-card-bg: rgba(255, 255, 255, 0.8);
            --apple-text: #1d1d1f;
            --apple-border: rgba(0,0,0,0.05);
            --apple-blue: #0071e3;
            --apple-font: "Inter", -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        [data-bs-theme="dark"] {
            /* Dark Mode Variables */
            --apple-bg: #000000;
            --apple-sidebar-bg: rgba(28, 28, 30, 0.8);
            --apple-card-bg: rgba(28, 28, 30, 0.8);
            --apple-text: #f5f5f7;
            --apple-border: rgba(255,255,255,0.1);
            --apple-blue: #0a84ff;
        }

        /* Dark Mode Overrides */
        [data-bs-theme="dark"] .bg-light {
            background-color: rgba(255,255,255,0.05) !important;
        }
        
        [data-bs-theme="dark"] .bg-white {
            background-color: var(--apple-card-bg) !important;
            color: var(--apple-text);
        }
        
        [data-bs-theme="dark"] .text-dark {
            color: var(--apple-text) !important;
        }
        
        [data-bs-theme="dark"] .text-muted {
            color: rgba(255,255,255,0.6) !important;
        }

        [data-bs-theme="dark"] .table {
            color: var(--apple-text);
            --bs-table-color: var(--apple-text);
            --bs-table-hover-color: var(--apple-text);
            --bs-table-bg: transparent;
            --bs-table-border-color: var(--apple-border);
        }

        [data-bs-theme="dark"] .table-hover > tbody > tr:hover > * {
            --bs-table-accent-bg: rgba(255,255,255,0.05);
            color: var(--apple-text);
        }
        
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            color: var(--apple-text);
            background-color: rgba(255,255,255,0.05);
            border-color: var(--apple-border);
        }
        
        [data-bs-theme="dark"] .form-control::placeholder {
            color: rgba(255,255,255,0.4);
        }

        [data-bs-theme="dark"] .form-select option {
            background-color: #1c1c1e;
            color: var(--apple-text);
        }
        
        [data-bs-theme="dark"] .btn-light {
            background-color: rgba(255,255,255,0.1);
            color: var(--apple-text);
            border-color: transparent;
        }

        [data-bs-theme="dark"] .btn-light:hover {
            background-color: rgba(255,255,255,0.2);
        }


        body {
            font-family: var(--apple-font);
            background-color: var(--apple-bg);
            color: var(--apple-text);
            -webkit-font-smoothing: antialiased;
            transition: background-color 0.3s ease, color 0.3s ease;
            overflow-x: hidden;
        }

        /* Layout Structure */
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
            min-height: 100vh;
        }

        /* Sidebar */
        #sidebar {
            min-width: 260px;
            max-width: 260px;
            background-color: var(--apple-sidebar-bg);
            backdrop-filter: saturate(180%) blur(20px);
            border-right: 1px solid var(--apple-border);
            transition: all 0.3s;
            position: fixed;
            height: 100vh;
            z-index: 1000;
        }

        #sidebar.active {
            margin-left: -260px;
        }

        #sidebar .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--apple-border);
        }

        #sidebar ul.components {
            padding: 20px 0;
        }

        #sidebar ul li a {
            padding: 12px 20px;
            font-size: 0.95rem;
            display: block;
            color: var(--apple-text);
            text-decoration: none;
            border-radius: 12px;
            margin: 0 10px;
            transition: background 0.2s;
            font-weight: 500;
        }

        #sidebar ul li a:hover, #sidebar ul li a.active {
            background-color: var(--apple-blue);
            color: #fff;
        }

        #sidebar ul li a i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        /* Content */
        #content {
            width: 100%;
            margin-left: 260px; /* Same as sidebar width */
            transition: all 0.3s;
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
            }
        }

        /* Navbar/Header */
        .main-header {
            background-color: var(--apple-sidebar-bg);
            backdrop-filter: saturate(180%) blur(20px);
            border-bottom: 1px solid var(--apple-border);
            padding: 10px 20px;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        /* Components */
        .card {
            background-color: var(--apple-card-bg);
            backdrop-filter: saturate(180%) blur(20px);
            border: 1px solid var(--apple-border);
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.04);
        }

        .btn-primary {
            background-color: var(--apple-blue);
            border-color: var(--apple-blue);
            border-radius: 980px;
        }

        .form-control {
            background-color: transparent;
            border: 1px solid var(--apple-border);
            color: var(--apple-text);
            border-radius: 10px;
        }
        
        .form-control:focus {
            background-color: transparent;
            color: var(--apple-text);
            border-color: var(--apple-blue);
            box-shadow: 0 0 0 4px rgba(0,113,227,0.15);
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
            color: var(--apple-text);
            opacity: 0.7;
            font-size: 0.85rem;
            margin-top: auto;
        }
    </style>
</head>
<body>
    @guest
        <!-- Layout for Login/Guest -->
        <div id="app" class="auth-wrapper">
             <!-- Theme Toggle for Guest -->
             <div class="position-absolute top-0 end-0 p-3">
                <button class="btn btn-sm btn-outline-secondary rounded-circle text-white-50 border-0" id="theme-toggle" title="Alternar Tema">
                    <i class="bi bi-moon-stars-fill"></i>
                </button>
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
                        <img src="{{ asset('img/logo-white.png') }}" id="sidebar-logo" alt="Integer" style="height: 35px; width: auto;">
                    </a>
                    <button type="button" id="sidebarCollapse" class="btn btn-link d-md-none text-reset">
                        <i class="bi bi-x-lg"></i>
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
                    <li class="mt-2">
                        <span class="px-4 text-uppercase small text-muted fw-bold" style="font-size: 0.75rem;">Gerenciamento</span>
                    </li>
                    <li class="mt-1">
                        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }} d-flex align-items-center">
                            @if(file_exists(public_path('img/sacratech-id.png')))
                                <img src="{{ asset('img/sacratech-id.png') }}" alt="Sacratech iD Logo" style="width: 20px; height: 20px; object-fit: contain; margin-right: 10px;">
                            @else
                                <i class="bi bi-people"></i> 
                            @endif
                            Usuários do Sacratech iD
                        </a>
                    </li>
                    <li class="mt-2">
                        <span class="px-4 text-uppercase small text-muted fw-bold" style="font-size: 0.75rem;">Controle de Acessos</span>
                    </li>
                    <li class="mt-1">
                        <a href="{{ route('sismatriz.index') }}" class="{{ request()->routeIs('sismatriz.*') ? 'active' : '' }} d-flex align-items-center">
                            @if(file_exists(public_path('img/sismatriz-ticket-logo.jpg')))
                                <img src="{{ asset('img/sismatriz-ticket-logo.jpg') }}" alt="Ticket Logo" style="width: 20px; height: 20px; object-fit: contain; margin-right: 10px;">
                            @else
                                <i class="bi bi-ticket-detailed-fill"></i> 
                            @endif
                            SisMatriz Ticket
                        </a>
                    </li>
                    <li class="mt-1">
                        <a href="{{ route('sismatriz-main.index') }}" class="{{ request()->routeIs('sismatriz-main.*') ? 'active' : '' }} d-flex align-items-center">
                            @if(file_exists(public_path('img/sismatriz-logo.png')))
                                <img src="{{ asset('img/sismatriz-logo.png') }}" alt="SisMatriz Logo" style="width: 20px; height: 20px; object-fit: contain; margin-right: 10px;">
                            @else
                                <i class="bi bi-building-fill"></i> 
                            @endif
                            SisMatriz
                        </a>
                    </li>
                    <li class="mt-2">
                        <span class="px-4 text-uppercase small text-muted fw-bold" style="font-size: 0.75rem;">Monitoramento</span>
                    </li>
                    <li class="mt-1">
                        <a href="{{ route('system_health.index') }}" class="{{ request()->routeIs('system_health.*') ? 'active' : '' }}">
                            <i class="bi bi-activity"></i> Saúde dos Sistemas
                        </a>
                    </li>
                    <!-- Add more menu items here -->
                </ul>
            </nav>

            <!-- Page Content -->
            <div id="content" class="d-flex flex-column min-vh-100">
                <header class="main-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <button type="button" id="sidebarCollapseBtn" class="btn btn-link text-reset me-3 d-md-none">
                            <i class="bi bi-list fs-4"></i>
                        </button>
                        <h5 class="m-0 fw-bold">@yield('page-title', 'Dashboard')</h5>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <!-- Theme Toggle -->
                        <button class="btn btn-link text-reset" id="theme-toggle-dash" title="Alternar Tema">
                            <i class="bi bi-moon-stars-fill"></i>
                        </button>

                        <!-- User Dropdown -->
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-reset" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white me-2" style="width: 32px; height: 32px;">
                                    {{ substr(Auth::user()->nome ?? 'A', 0, 1) }}
                                </div>
                                <span class="d-none d-sm-inline">{{ Auth::user()->nome }} {{ Auth::user()->sobrenome }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4" aria-labelledby="dropdownUser1">
                                <li><a class="dropdown-item" href="#">Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                        Sair
                                    </a>
                                </li>
                            </ul>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>

                        <!-- LOGO SACRATECH_ID -->
                        <div class="border-start ps-3 ms-2 d-flex align-items-center" title="Sacratech ID">
                             @if(file_exists(public_path('img/sacratech-id.png')))
                                <img src="{{ asset('img/sacratech-id.png') }}" alt="Sacratech ID" height="24" class="d-none d-md-block">
                                <img src="{{ asset('img/sacratech-id.png') }}" alt="Sacratech ID" height="20" class="d-md-none">
                             @else
                                <span class="badge bg-primary rounded-pill">Sacratech ID</span>
                             @endif
                        </div>
                    </div>
                </header>

                <main class="p-4 flex-grow-1">
                    @yield('content')
                </main>

                <footer class="footer mt-auto">
                    &copy; {{ date('Y') }} Sacratech Softwares LTDA. Todos os direitos reservados.
                </footer>
            </div>
        </div>
    @endguest

    <!-- Global Page Transition Overlay -->
    <div id="global-page-loader" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="z-index: 10000; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(4px); transition: opacity 0.3s ease;">
        <div class="d-flex flex-column align-items-center justify-content-center h-100">
            <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-3 text-white fw-medium fs-5">Carregando...</p>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg" style="background-color: var(--apple-card-bg); backdrop-filter: saturate(180%) blur(20px);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="logoutModalLabel">Confirmar Saída</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="mb-0 text-muted">Você será desconectado do sistema e seu acesso automático será removido. Para acessar novamente, será necessário realizar o login manualmente com a opção "Lembrar-me" marcada, se desejar.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
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

    <!-- Theme & Sidebar Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Global Page Loader Logic
            const globalLoader = document.getElementById('global-page-loader');
            
            const showLoader = () => {
                if (globalLoader) {
                    globalLoader.classList.remove('d-none');
                    // Force opacity transition if needed
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

            // Handle Link Clicks
            document.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (link) {
                    const href = link.getAttribute('href');
                    const target = link.getAttribute('target');
                    
                    // Ignore:
                    // 1. Links without href or empty href
                    // 2. Links starting with # (anchors)
                    // 3. Links starting with javascript:
                    // 4. External links (optional, but good practice to keep loader if we want transition there too, usually strictly internal)
                    // 5. Links with target="_blank"
                    // 6. Download links
                    
                    if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:') || target === '_blank' || link.hasAttribute('download')) {
                        return;
                    }

                    // Check if it's a modifier key click (ctrl/cmd + click)
                    if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) {
                        return;
                    }
                    
                    showLoader();
                }
            });

            // Handle Form Submits
            document.addEventListener('submit', (e) => {
                const form = e.target;
                // If form has target="_blank", don't show loader
                if (form.target === '_blank') return;
                
                showLoader();
            });
            
            // Hide loader when page is fully loaded (bfcache support)
            window.addEventListener('pageshow', (event) => {
                hideLoader();
            });
            
            // Initial hide
            hideLoader();
        });

        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarCollapse = document.getElementById('sidebarCollapse');
        const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
        const content = document.getElementById('content');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
        }

        if (sidebarCollapse) {
            sidebarCollapse.addEventListener('click', toggleSidebar);
        }
        
        if (sidebarCollapseBtn) {
            sidebarCollapseBtn.addEventListener('click', toggleSidebar);
        }

        // Close sidebar on mobile when clicking outside
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !sidebarCollapseBtn.contains(e.target) && !sidebar.classList.contains('active')) {
                    // In mobile, active means hidden (margin-left: -260px is default, active is 0)
                    // Wait, css says: #sidebar { margin-left: -260px } #sidebar.active { margin-left: 0 }
                    // So if it contains active, it is VISIBLE.
                    if (sidebar.classList.contains('active')) {
                        sidebar.classList.remove('active');
                    }
                }
            }
        });

        // Theme Toggle
        const themeToggle = document.getElementById('theme-toggle');
        const themeToggleDash = document.getElementById('theme-toggle-dash');
        const html = document.documentElement;
        
        function toggleTheme() {
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        }

        function updateThemeIcon(theme) {
            const iconClass = theme === 'dark' ? 'bi-moon-stars-fill' : 'bi-sun-fill';
            if (themeToggle) themeToggle.innerHTML = `<i class="bi ${iconClass}"></i>`;
            if (themeToggleDash) themeToggleDash.innerHTML = `<i class="bi ${iconClass}"></i>`;
        }

        if (themeToggle) themeToggle.addEventListener('click', toggleTheme);
        if (themeToggleDash) themeToggleDash.addEventListener('click', toggleTheme);

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-bs-theme', savedTheme);
        updateThemeIcon(savedTheme);
        
        // Enable Tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>

    @stack('scripts')
</body>
</html>
