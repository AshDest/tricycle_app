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
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 64px;
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --sidebar-bg: #1e293b;
            --sidebar-text: #94a3b8;
            --sidebar-active: #ffffff;
            --sidebar-heading: #64748b;
        }

        * { font-family: 'Inter', sans-serif; }

        body {
            background-color: #f1f5f9;
            overflow-x: hidden;
        }

        /* Sidebar */
        .app-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1040;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .app-sidebar::-webkit-scrollbar { width: 4px; }
        .app-sidebar::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }

        .sidebar-brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            border-bottom: 1px solid #334155;
        }

        .sidebar-brand a {
            color: #fff;
            font-size: 1.25rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-brand img { max-height: 36px; }

        .sidebar-menu { list-style: none; padding: 0.75rem 0; margin: 0; }

        .sidebar-heading {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--sidebar-heading);
            padding: 1rem 1.25rem 0.5rem;
        }

        .sidebar-menu > li > a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 1.25rem;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 400;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar-menu > li > a:hover,
        .sidebar-menu > li.active > a {
            color: var(--sidebar-active);
            background: rgba(255,255,255,0.05);
            border-left-color: var(--primary-color);
        }

        .sidebar-menu > li > a i { width: 20px; text-align: center; font-size: 1rem; }

        .sidebar-menu .submenu {
            list-style: none;
            padding: 0;
            display: none;
            background: rgba(0,0,0,0.15);
        }

        .sidebar-menu .has-submenu.open .submenu { display: block; }

        .sidebar-menu .submenu a {
            display: block;
            padding: 0.45rem 1.25rem 0.45rem 3.25rem;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 0.8125rem;
            transition: color 0.2s;
        }

        .sidebar-menu .submenu a:hover { color: var(--sidebar-active); }

        .submenu-icon {
            margin-left: auto;
            font-size: 0.625rem;
            transition: transform 0.2s;
        }

        .has-submenu.open .submenu-icon { transform: rotate(180deg); }

        /* Header */
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
            transition: left 0.3s ease;
        }

        /* Content */
        .app-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 1.5rem;
            min-height: calc(100vh - var(--header-height));
            transition: margin-left 0.3s ease;
        }

        /* Footer */
        .app-footer {
            margin-left: var(--sidebar-width);
            padding: 1rem 1.5rem;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            transition: margin-left 0.3s ease;
        }

        /* Backdrop */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1035;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .app-sidebar { transform: translateX(-100%); }
            .app-sidebar.show { transform: translateX(0); }
            .app-header { left: 0; }
            .app-content { margin-left: 0; }
            .app-footer { margin-left: 0; }
            .sidebar-backdrop.show { display: block; }
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 1rem 1.25rem;
        }

        /* Stats Cards */
        .stat-card {
            border-radius: 0.75rem;
            padding: 1.25rem;
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.625rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        /* Tables */
        .table > :not(caption) > * > * { padding: 0.875rem 1rem; vertical-align: middle; }
        .table > thead > tr > th {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            border-bottom-width: 1px;
        }

        /* Badge */
        .badge { font-weight: 500; font-size: 0.75rem; }

        /* Buttons */
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); }
        .btn-primary:hover { background-color: var(--primary-hover); border-color: var(--primary-hover); }

        /* User avatar */
        .user-avatar-sm {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* Page header */
        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-header h4 {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        /* Form styling */
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15);
        }

        .form-label { font-weight: 500; font-size: 0.875rem; color: #374151; }
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
    </script>

    @stack('scripts')

    <!-- Livewire Scripts -->
    @livewireScripts
</body>

</html>
