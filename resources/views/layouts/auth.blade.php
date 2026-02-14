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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: rgba(79, 70, 229, 0.1);
            --secondary: #7c3aed;
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            background: #f8fafc;
            overflow-x: hidden;
        }

        /* ===== LEFT SIDE - BRANDING ===== */
        .auth-brand-side {
            display: none;
            width: 45%;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            position: relative;
            padding: 3rem;
            overflow: hidden;
        }

        @media (min-width: 992px) {
            .auth-brand-side {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
        }

        .auth-brand-side::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.15) 0%, transparent 60%);
            pointer-events: none;
        }

        .auth-brand-side::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -30%;
            width: 80%;
            height: 80%;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.1) 0%, transparent 60%);
            pointer-events: none;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            position: relative;
            z-index: 1;
        }

        .brand-logo-icon {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.5rem;
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.3);
        }

        .brand-logo-text {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.025em;
        }

        .brand-logo-text span {
            color: #a78bfa;
        }

        .brand-content {
            position: relative;
            z-index: 1;
        }

        .brand-content h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .brand-content h1 span {
            background: linear-gradient(135deg, #a78bfa, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-content p {
            font-size: 1.125rem;
            color: #94a3b8;
            line-height: 1.7;
            max-width: 400px;
        }

        .brand-features {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 2.5rem;
        }

        .brand-feature {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #cbd5e1;
            font-size: 0.9375rem;
        }

        .brand-feature i {
            width: 36px;
            height: 36px;
            background: rgba(79, 70, 229, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a78bfa;
            font-size: 1rem;
        }

        .brand-footer {
            position: relative;
            z-index: 1;
            color: #64748b;
            font-size: 0.8125rem;
        }

        .brand-footer a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.2s;
        }

        .brand-footer a:hover {
            color: #a78bfa;
        }

        /* ===== RIGHT SIDE - FORM ===== */
        .auth-form-side {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            background: #fff;
        }

        @media (min-width: 992px) {
            .auth-form-side {
                width: 55%;
                padding: 3rem 4rem;
            }
        }

        .auth-form-container {
            width: 100%;
            max-width: 420px;
        }

        /* Mobile Logo */
        .mobile-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 992px) {
            .mobile-brand {
                display: none;
            }
        }

        .mobile-brand .brand-logo-icon {
            width: 44px;
            height: 44px;
            font-size: 1.25rem;
        }

        .mobile-brand .brand-logo-text {
            color: #1e293b;
            font-size: 1.375rem;
        }

        /* Auth Card */
        .auth-card {
            background: #fff;
            border-radius: 1.25rem;
            overflow: hidden;
        }

        @media (max-width: 991.98px) {
            .auth-card {
                box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
                border: 1px solid #f1f5f9;
            }
        }

        .auth-header {
            text-align: center;
            padding: 0 0 1.5rem;
        }

        .auth-header h4 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            color: #64748b;
            font-size: 0.9375rem;
            margin: 0;
        }

        .auth-body {
            padding: 0;
        }

        @media (max-width: 991.98px) {
            .auth-header, .auth-body, .auth-footer {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
            .auth-header {
                padding-top: 1.5rem;
            }
            .auth-body {
                padding-bottom: 1.5rem;
            }
        }

        .auth-footer {
            padding: 1.25rem 0 0;
            text-align: center;
            border-top: 1px solid #f1f5f9;
            margin-top: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .auth-footer {
                background: #f8fafc;
                padding: 1.25rem 1.5rem;
                margin-top: 0;
            }
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            font-size: 0.8125rem;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.625rem;
            font-size: 0.9375rem;
            color: #1e293b;
            background: #fff;
            transition: all 0.2s ease;
        }

        .form-control:hover {
            border-color: #cbd5e1;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .form-control.is-invalid {
            border-color: #ef4444;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .input-group {
            position: relative;
        }

        .input-group .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.125rem;
            z-index: 2;
            pointer-events: none;
        }

        .input-group .form-control {
            padding-left: 2.75rem;
        }

        .input-group .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            z-index: 2;
            padding: 0;
            font-size: 1.125rem;
            transition: color 0.2s;
        }

        .input-group .toggle-password:hover {
            color: #64748b;
        }

        .form-text-error {
            display: block;
            color: #ef4444;
            font-size: 0.8125rem;
            margin-top: 0.375rem;
        }

        /* Remember & Forgot */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .form-check-label {
            font-size: 0.875rem;
            color: #475569;
            cursor: pointer;
        }

        .forgot-link {
            font-size: 0.875rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            border: none;
            border-radius: 0.625rem;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.35);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-submit .spinner-border {
            width: 1.25rem;
            height: 1.25rem;
            border-width: 2px;
        }

        /* Footer Link */
        .auth-footer-text {
            font-size: 0.9375rem;
            color: #64748b;
        }

        .auth-footer-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .auth-footer-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Mobile Footer */
        .mobile-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f1f5f9;
        }

        @media (min-width: 992px) {
            .mobile-footer {
                display: none;
            }
        }

        .mobile-footer p {
            font-size: 0.8125rem;
            color: #94a3b8;
        }

        /* Alert Styles */
        .alert {
            padding: 0.875rem 1rem;
            border-radius: 0.625rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #dc2626;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #059669;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-card {
            animation: fadeInUp 0.5s ease-out;
        }

        /* Decorative elements */
        .decoration-circle {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(124, 58, 237, 0.05));
            pointer-events: none;
        }

        .decoration-1 {
            width: 300px;
            height: 300px;
            top: -100px;
            right: -100px;
        }

        .decoration-2 {
            width: 200px;
            height: 200px;
            bottom: 50px;
            left: -50px;
        }
    </style>

    @stack('styles')
    @livewireStyles
</head>

<body>
    <!-- Left Side - Branding -->
    <div class="auth-brand-side">
        <div class="brand-logo">
            <div class="brand-logo-icon">
                <i class="bi bi-bicycle"></i>
            </div>
            <div class="brand-logo-text">Tricycle<span>App</span></div>
        </div>

        <div class="brand-content">
            <h1>Gérez votre flotte <span>en toute simplicité</span></h1>
            <p>Une solution complète pour la gestion de vos motos-tricycles, versements, et équipes sur le terrain.</p>

            <div class="brand-features">
                <div class="brand-feature">
                    <i class="bi bi-shield-check"></i>
                    <span>Suivi en temps réel des versements</span>
                </div>
                <div class="brand-feature">
                    <i class="bi bi-people"></i>
                    <span>Gestion des motards et collecteurs</span>
                </div>
                <div class="brand-feature">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Rapports et statistiques détaillés</span>
                </div>
                <div class="brand-feature">
                    <i class="bi bi-phone"></i>
                    <span>Accessible sur tous vos appareils</span>
                </div>
            </div>
        </div>

        <div class="brand-footer">
            <p>&copy; {{ date('Y') }} <a href="https://nth-sarl.com" target="_blank">New Technology Hub Sarl</a>. Tous droits réservés.</p>
        </div>
    </div>

    <!-- Right Side - Form -->
    <div class="auth-form-side">
        <div class="auth-form-container">
            <!-- Mobile Brand -->
            <div class="mobile-brand">
                <div class="brand-logo-icon">
                    <i class="bi bi-bicycle"></i>
                </div>
                <div class="brand-logo-text">Tricycle<span style="color: #7c3aed;">App</span></div>
            </div>

            {{ $slot }}

            <!-- Mobile Footer -->
            <div class="mobile-footer">
                <p>&copy; {{ date('Y') }} New Technology Hub Sarl</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle Password Visibility
        document.querySelectorAll('.toggle-password').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const input = this.closest('.input-group').querySelector('input');
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });
    </script>

    @stack('scripts')
    @livewireScripts
</body>

</html>
