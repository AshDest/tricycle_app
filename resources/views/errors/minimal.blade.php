<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur - Tricycle App</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e293b;
        }
        .container { text-align: center; padding: 2rem; }
        .code { font-size: 5rem; font-weight: 800; color: #4f46e5; }
        h1 { font-size: 1.5rem; margin: 0.5rem 0; }
        p { color: #64748b; margin-bottom: 2rem; }
        a {
            background: #4f46e5; color: #fff; padding: 0.75rem 2rem;
            border-radius: 0.5rem; text-decoration: none; font-weight: 600;
        }
        a:hover { background: #4338ca; }
    </style>
</head>
<body>
    <div class="container">
        <div class="code">{{ $exception->getStatusCode() ?? 'Erreur' }}</div>
        <h1>{{ $exception->getMessage() ?: 'Une erreur est survenue' }}</h1>
        <p>Veuillez réessayer ou retourner au tableau de bord.</p>
        <a href="{{ url('/dashboard') }}">Tableau de bord</a>
    </div>
</body>
</html>

