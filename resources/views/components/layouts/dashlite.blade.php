<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="author" content="New Technology Hub Sarl">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Tricycle App - Système de gestion de flotte de motos-tricycles">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fav Icon -->
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}">

    <!-- Page Title -->
    <title>@yield('title', 'Dashboard') | {{ config('app.name', 'Tricycle App') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom App CSS -->
    @vite(['resources/css/app.css'])

    <style>
        /* ========================================
           TRICYCLE APP - PROFESSIONAL DASHBOARD
           ======================================== */

        :root {
            --sidebar-width: 270px;
            --sidebar-collapsed-width: 72px;
            --header-height: 68px;
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --primary-light: rgba(79, 70, 229, 0.1);
            --sidebar-bg: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            --sidebar-text: #94a3b8;
            --sidebar-active: #ffffff;
            --sidebar-heading: #64748b;
            --body-bg: #f1f5f9;
            --card-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
            --card-shadow-hover: 0 10px 25px rgba(0,0,0,0.1);
            --transition-fast: 0.15s ease;
            --transition-normal: 0.25s ease;
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        body {
            background-color: var(--body-bg);
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        .app-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1040;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform var(--transition-normal), width var(--transition-normal);
        }

        .app-sidebar::-webkit-scrollbar { width: 4px; }
        .app-sidebar::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 4px;
        }

        /* Sidebar Brand */
        .sidebar-brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            background: rgba(0,0,0,0.1);
        }

        .sidebar-brand a {
            color: #fff;
            font-size: 1.375rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-brand .brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), #7c3aed);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
        }

        .sidebar-brand img { max-height: 40px; }

        /* Sidebar Menu */
        .sidebar-content { padding: 0.75rem 0 2rem; }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; }

        .sidebar-heading {
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--sidebar-heading);
            padding: 1.25rem 1.5rem 0.625rem;
            margin-top: 0.5rem;
        }

        .sidebar-menu > li > a {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.75rem 1.5rem;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all var(--transition-fast);
            border-left: 3px solid transparent;
            margin: 2px 0;
        }

        .sidebar-menu > li > a:hover {
            color: var(--sidebar-active);
            background: rgba(255,255,255,0.05);
        }

        .sidebar-menu > li.active > a {
            color: var(--sidebar-active);
            background: rgba(79, 70, 229, 0.15);
            border-left-color: var(--primary-color);
        }

        .sidebar-menu > li > a i {
            width: 20px;
            text-align: center;
            font-size: 1.125rem;
            opacity: 0.9;
        }

        /* Submenus */
        .sidebar-menu .submenu {
            list-style: none;
            padding: 0;
            display: none;
            background: rgba(0,0,0,0.2);
            margin: 0 0.75rem;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .sidebar-menu .has-submenu.open .submenu { display: block; }

        .sidebar-menu .submenu a {
            display: block;
            padding: 0.5rem 1rem 0.5rem 2.75rem;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 0.8125rem;
            font-weight: 400;
            transition: all var(--transition-fast);
            position: relative;
        }

        .sidebar-menu .submenu a::before {
            content: '';
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--sidebar-heading);
            transition: all var(--transition-fast);
        }

        .sidebar-menu .submenu a:hover {
            color: var(--sidebar-active);
        }

        .sidebar-menu .submenu a:hover::before {
            background: var(--primary-color);
        }

        .submenu-icon {
            margin-left: auto;
            font-size: 0.625rem;
            transition: transform var(--transition-fast);
        }

        .has-submenu.open .submenu-icon { transform: rotate(180deg); }

        /* ===== HEADER ===== */
        .app-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            z-index: 1030;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            transition: left var(--transition-normal);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        /* ===== CONTENT ===== */
        .app-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 1.5rem;
            min-height: calc(100vh - var(--header-height));
            transition: margin-left var(--transition-normal);
        }

        /* ===== FOOTER ===== */
        .app-footer {
            margin-left: var(--sidebar-width);
            padding: 1rem 1.5rem;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            transition: margin-left var(--transition-normal);
            font-size: 0.8125rem;
            color: #64748b;
        }

        /* ===== BACKDROP ===== */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1035;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991.98px) {
            .app-sidebar { transform: translateX(-100%); }
            .app-sidebar.show { transform: translateX(0); }
            .app-header { left: 0; }
            .app-content { margin-left: 0; }
            .app-footer { margin-left: 0; }
            .sidebar-backdrop.show { display: block; }
        }

        /* ===== COMPONENTS ===== */

        /* Cards */
        .card {
            border: none;
            border-radius: 0.875rem;
            box-shadow: var(--card-shadow);
            transition: all var(--transition-normal);
        }

        .card:hover {
            box-shadow: var(--card-shadow-hover);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        /* Stats Cards */
        .stat-card {
            border-radius: 0.875rem;
            padding: 1.5rem;
            border: none;
            box-shadow: var(--card-shadow);
            background: #fff;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(79,70,229,0.03) 0%, transparent 70%);
            pointer-events: none;
        }

        .stat-card .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.375rem;
        }

        /* Tables */
        .table > :not(caption) > * > * {
            padding: 1rem 1rem;
            vertical-align: middle;
        }

        .table > thead > tr > th {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
            background: #f8fafc;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(79, 70, 229, 0.02);
        }

        /* Badges */
        .badge {
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.375rem 0.625rem;
            border-radius: 0.375rem;
        }

        .badge-soft-primary { background: rgba(79, 70, 229, 0.1); color: #4f46e5; }
        .badge-soft-success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .badge-soft-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .badge-soft-warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .badge-soft-info { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .badge-soft-secondary { background: rgba(100, 116, 139, 0.1); color: #64748b; }

        /* Buttons */
        .btn {
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            transition: all var(--transition-fast);
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
        }

        /* User Avatar */
        .user-avatar-sm {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .avatar-sm { width: 32px; height: 32px; font-size: 0.75rem; }

        /* Page Header */
        .page-header {
            margin-bottom: 1.75rem;
        }

        .page-header h4, .page-header .page-title {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
            color: #0f172a;
        }

        /* Form Styling */
        .form-control, .form-select {
            border-radius: 0.5rem;
            border-color: #e2e8f0;
            padding: 0.625rem 0.875rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .form-label {
            font-weight: 500;
            font-size: 0.875rem;
            color: #374151;
            margin-bottom: 0.375rem;
        }

        /* Search Input */
        .header-search {
            position: relative;
            max-width: 320px;
        }

        .header-search input {
            background: #f8fafc;
            border: 1px solid transparent;
            padding-left: 2.5rem;
            height: 42px;
        }

        .header-search input:focus {
            background: #fff;
            border-color: var(--primary-color);
        }

        .header-search .search-icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        /* Dropdown Improvements */
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
            border-radius: 0.75rem;
            padding: 0.5rem;
        }

        .dropdown-item {
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .dropdown-item:hover {
            background: #f1f5f9;
        }

        /* Notification Badge Pulse */
        .notification-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
    </style>

    @stack('styles')

    <!-- Livewire Styles -->
    @livewireStyles
</head>

<body>
    <!-- Sidebar -->
    @include('layouts.partials.sidebar')

    <!-- Sidebar Backdrop (mobile) -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- Header -->
    @include('layouts.partials.header')

    <!-- Main Content -->
    <div class="app-content">
        <!-- Flash Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{ $slot }}
    </div>

    <!-- Footer -->
    <div class="app-footer">
        @include('layouts.partials.footer')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Sidebar Toggle
        function toggleSidebar() {
            document.querySelector('.app-sidebar').classList.toggle('show');
            document.getElementById('sidebarBackdrop').classList.toggle('show');
        }

        // Submenu Toggle
        document.querySelectorAll('.toggle-submenu').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                this.closest('.has-submenu').classList.toggle('open');
            });
        });

        // Auto-open active submenus
        document.querySelectorAll('.has-submenu.active').forEach(function(el) {
            el.classList.add('open');
        });

        // Close sidebar on mobile when clicking outside
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.app-sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (window.innerWidth < 992 && sidebar.classList.contains('show')) {
                if (!sidebar.contains(e.target) && !e.target.closest('[onclick="toggleSidebar()"]')) {
                    sidebar.classList.remove('show');
                    backdrop.classList.remove('show');
                }
            }
        });

        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>

    @stack('scripts')

    <!-- Livewire Scripts -->
    @livewireScripts
</body>

</html>
