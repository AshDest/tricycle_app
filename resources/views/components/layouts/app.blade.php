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

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --sidebar-width: 250px;
            --main-bg: #f5f6f7;
            --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            --border-color: #e3e6f0;
            --primary-color: #2466d0;
        }

        * {
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--main-bg);
            display: flex;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background-color: #fff;
            border-right: 1px solid var(--border-color);
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 4px rgba(0,0,0,0.05);
        }

        .sidebar-brand {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            text-align: center;
        }

        .sidebar-brand a {
            text-decoration: none;
            display: inline-block;
        }

        .sidebar-brand img {
            max-height: 50px;
            width: auto;
        }

        .sidebar-content {
            padding: 20px 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu > li {
            margin: 0;
        }

        .sidebar-menu > li.sidebar-heading {
            padding: 15px 20px 5px;
            font-size: 0.75rem;
            font-weight: 700;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 10px;
        }

        .sidebar-menu > li:first-child.sidebar-heading {
            margin-top: 0;
        }

        .sidebar-menu > li > a {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #495057;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .sidebar-menu > li > a:hover,
        .sidebar-menu > li.active > a {
            background-color: #f8f9fa;
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }

        .sidebar-menu > li > a i {
            min-width: 18px;
            text-align: center;
        }

        .sidebar-menu > li > a span {
            flex-grow: 1;
        }

        .submenu-icon {
            transition: transform 0.3s ease;
            font-size: 0.75rem;
        }

        .sidebar-menu .submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            background-color: #f8f9fa;
            display: none;
        }

        .sidebar-menu .submenu > li {
            margin: 0;
        }

        .sidebar-menu .submenu > li > a {
            padding: 8px 20px 8px 50px;
            font-size: 0.9rem;
            border-left: 3px solid transparent;
            color: #495057;
            text-decoration: none;
            display: block;
            transition: all 0.3s ease;
        }

        .sidebar-menu .submenu > li > a:hover {
            background-color: #e9ecef;
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .header {
            background-color: #fff;
            border-bottom: 1px solid var(--border-color);
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .header-search {
            flex-grow: 1;
        }

        .header-search .input-group {
            max-width: 400px;
        }

        .header-tools {
            margin-left: auto;
        }

        .header .btn-link {
            border: none;
            background: none;
            padding: 0.5rem;
            cursor: pointer;
        }

        .header .btn-link:hover {
            opacity: 0.7;
        }

        .user-avatar {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .footer {
            background-color: #fff;
            border-top: 1px solid var(--border-color);
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .card {
            border: 1px solid var(--border-color);
            box-shadow: var(--card-shadow);
            border-radius: 0.35rem;
        }

        .table {
            margin-bottom: 0;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #999;
            font-size: 0.95rem;
        }

        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }

        .badge {
            padding: 0.35em 0.65em;
            font-weight: 500;
        }

        /* Dropdown styling */
        .dropdown-menu {
            border: 1px solid var(--border-color);
            box-shadow: var(--card-shadow);
            border-radius: 0.35rem;
        }

        .dropdown-item {
            padding: 0.75rem 1rem;
            color: #495057;
        }

        .dropdown-item:hover,
        .dropdown-item.active {
            background-color: #f8f9fa;
            color: var(--primary-color);
        }

        .dropdown-item i {
            margin-right: 0.5rem;
            min-width: 18px;
        }

        /* Scrollbar styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #d3d3d3;
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #999;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                max-height: 60vh;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }

            .main-content {
                margin-left: 0;
            }

            .content {
                padding: 15px;
            }

            .sidebar-menu > li > a {
                padding-left: 15px;
            }

            .page-title {
                font-size: 1.25rem;
            }

            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            .header {
                flex-wrap: wrap;
            }

            .header-search {
                flex-basis: 100%;
                margin-bottom: 10px;
            }
        }
    </style>

    @stack('styles')

    <!-- Livewire Styles -->
    @livewireStyles
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        @include('layouts.partials.sidebar')
    </div>

    <!-- Main Content -->
    <div class="main-content flex-grow-1">
        <!-- Header -->
        <div class="header">
            @include('layouts.partials.header')
        </div>

        <!-- Content Area -->
        <div class="content">
            {{ $slot }}
        </div>

        <!-- Footer -->
        <div class="footer">
            @include('layouts.partials.footer')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Submenu toggle functionality
            const toggleMenuItems = document.querySelectorAll('.toggle-submenu');

            toggleMenuItems.forEach(toggleLink => {
                toggleLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    const parentLi = this.closest('li');

                    // Toggle the active class on the parent
                    parentLi.classList.toggle('active');

                    // Toggle display of the submenu
                    const submenu = parentLi.querySelector('.submenu');
                    if (submenu) {
                        submenu.style.display = parentLi.classList.contains('active') ? 'block' : 'none';
                    }

                    // Rotate the chevron icon
                    const chevron = toggleLink.querySelector('.submenu-icon');
                    if (chevron) {
                        chevron.style.transform = parentLi.classList.contains('active') ? 'rotate(180deg)' : 'rotate(0deg)';
                    }
                });
            });

            // Set initial display state based on active class
            document.querySelectorAll('.has-submenu.active').forEach(activeItem => {
                const submenu = activeItem.querySelector('.submenu');
                if (submenu) {
                    submenu.style.display = 'block';
                }
                const chevron = activeItem.querySelector('.submenu-icon');
                if (chevron) {
                    chevron.style.transform = 'rotate(180deg)';
                }
            });

            // Hide all other submenus by default
            document.querySelectorAll('.has-submenu:not(.active) .submenu').forEach(submenu => {
                submenu.style.display = 'none';
            });
        });
    </script>

    @stack('scripts')

    <!-- Livewire Scripts -->
    @livewireScripts
</body>

</html>
