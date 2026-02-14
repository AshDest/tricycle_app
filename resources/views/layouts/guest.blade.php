<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Tricycle App') }}</title>

        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <style>
            * { font-family: 'Inter', sans-serif; }

            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
            }

            .auth-wrapper {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem 1rem;
            }

            .auth-card {
                background: #fff;
                border-radius: 1rem;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                overflow: hidden;
                width: 100%;
                max-width: 420px;
            }

            .auth-header {
                padding: 2rem 2rem 1rem;
                text-align: center;
            }

            .auth-logo {
                width: 64px;
                height: 64px;
                background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
                border-radius: 1rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-size: 1.75rem;
                margin-bottom: 1rem;
            }

            .auth-title {
                font-weight: 700;
                font-size: 1.5rem;
                color: #1e293b;
                margin-bottom: 0.25rem;
            }

            .auth-subtitle {
                color: #64748b;
                font-size: 0.9375rem;
            }

            .auth-body {
                padding: 1.5rem 2rem 2rem;
            }

            .form-control {
                border-radius: 0.5rem;
                padding: 0.75rem 1rem;
                border-color: #e2e8f0;
            }

            .form-control:focus {
                border-color: #4f46e5;
                box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
            }

            .form-label {
                font-weight: 500;
                font-size: 0.875rem;
                color: #374151;
            }

            .btn-primary {
                background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
                border: none;
                border-radius: 0.5rem;
                padding: 0.75rem 1.5rem;
                font-weight: 600;
            }

            .btn-primary:hover {
                background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
            }

            .auth-footer {
                text-align: center;
                padding: 1rem 2rem 1.5rem;
                border-top: 1px solid #f1f5f9;
                background: #f8fafc;
            }
        </style>

        @livewireStyles
    </head>
    <body>
        <div class="auth-wrapper">
            <div class="auth-card">
                <div class="auth-header">
                    <a href="/" wire:navigate class="text-decoration-none">
                        <div class="auth-logo">
                            <i class="bi bi-bicycle"></i>
                        </div>
                    </a>
                    <h1 class="auth-title">Tricycle App</h1>
                    <p class="auth-subtitle">Gestion de flotte de motos-tricycles</p>
                </div>

                <div class="auth-body">
                    {{ $slot }}
                </div>
            </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        @livewireScripts
    </body>
</html>
