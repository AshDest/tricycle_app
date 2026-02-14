<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="author" content="New Technology Hub Sarl">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Tricycle App - Gestion de flotte de motos-tricycles">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}">
    <title>@yield('title', 'Connexion') | {{ config('app.name', 'Tricycle App') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 440px;
            padding: 1rem;
        }

        .auth-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .auth-header {
            text-align: center;
            padding: 2rem 2rem 1rem;
        }

        .auth-logo {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: #fff;
            font-size: 1.5rem;
        }

        .auth-header h4 {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .auth-header p {
            color: #64748b;
            font-size: 0.875rem;
        }

        .auth-body {
            padding: 1rem 2rem 2rem;
        }

        .auth-footer {
            padding: 1rem 2rem;
            background: #f8fafc;
            text-align: center;
            border-top: 1px solid #f1f5f9;
        }

        .form-control {
            padding: 0.625rem 0.875rem;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            font-size: 0.875rem;
        }

        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-label {
            font-weight: 500;
            font-size: 0.8125rem;
            color: #374151;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca, #6d28d9);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .input-group-text {
            border-radius: 0.5rem 0 0 0.5rem;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #94a3b8;
        }

        .input-group .form-control {
            border-radius: 0 0.5rem 0.5rem 0;
        }

        .auth-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #94a3b8;
            font-size: 0.8125rem;
            margin: 1.25rem 0;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }
    </style>

    @stack('styles')
    @livewireStyles
</head>

<body>
    <div class="auth-wrapper">
        {{ $slot }}

        <div class="text-center mt-3">
            <small class="text-white-50">&copy; {{ date('Y') }} New Technology Hub Sarl</small>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
    @livewireScripts
</body>

</html>
