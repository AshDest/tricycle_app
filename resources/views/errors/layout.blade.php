<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Tricycle App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #eef2ff;
            --dark: #1e293b;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 50%, #f1f5f9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark);
        }
        .error-container {
            text-align: center;
            max-width: 540px;
            padding: 2rem;
        }
        .error-icon {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 3rem;
        }
        .error-code {
            font-size: 5rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
            margin-bottom: 0.5rem;
            letter-spacing: -2px;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
        }
        .error-message {
            font-size: 1rem;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .btn-back {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 0.625rem;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
        }
        .btn-back:hover {
            background: #4338ca;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
        }
        .btn-secondary-back {
            background: transparent;
            color: #64748b;
            border: 1.5px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            border-radius: 0.625rem;
            font-weight: 500;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            margin-left: 0.75rem;
        }
        .btn-secondary-back:hover {
            background: #f8fafc;
            color: var(--dark);
            border-color: #cbd5e1;
        }
        .brand {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #94a3b8;
            font-size: 0.85rem;
        }
        .brand i { color: var(--primary); }
        .decoration {
            position: fixed;
            border-radius: 50%;
            opacity: 0.05;
            background: var(--primary);
        }
        .decoration-1 { width: 400px; height: 400px; top: -100px; right: -100px; }
        .decoration-2 { width: 300px; height: 300px; bottom: -80px; left: -80px; }
    </style>
</head>
<body>
    <div class="decoration decoration-1"></div>
    <div class="decoration decoration-2"></div>

    <div class="error-container">
        @yield('content')

        <div class="mt-4">
            <a href="{{ url('/dashboard') }}" class="btn-back">
                <i class="bi bi-house-door"></i> Tableau de bord
            </a>
            <a href="javascript:history.back()" class="btn-secondary-back">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="brand">
        <i class="bi bi-shield-check"></i> Tricycle App &mdash; New Technology Hub Sarl
    </div>
</body>
</html>

