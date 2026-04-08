<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Commissions Mobile Money' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 0; padding: 10px; }
        .header { text-align: center; border-bottom: 2px solid #198754; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { margin: 0; font-size: 16px; color: #198754; }
        .header p { margin: 3px 0; color: #666; font-size: 9px; }
        .stats { display: table; width: 100%; margin-bottom: 15px; }
        .stat-box { display: table-cell; width: 33%; text-align: center; padding: 8px; background: #f5f5f5; }
        .stat-box h4 { margin: 0; font-size: 12px; }
        .stat-box p { margin: 2px 0 0; color: #666; font-size: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; font-size: 9px; }
        th { background: #198754; color: white; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #198754; }
        .text-primary { color: #0d6efd; }
        .text-warning { color: #ffc107; }
        .footer { text-align: center; margin-top: 15px; font-size: 8px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title ?? 'Commissions Mobile Money' }}</h1>
        <p>LATEM Sarl - Kinshasa</p>
        @if($collecteur)
        <p>Collecteur: {{ $collecteur->user->name ?? $collecteur->identifiant }}</p>
        @endif
    </div>
    <div class="stats">
        <div class="stat-box">
            <h4 class="text-success">{{ number_format($stats['totalCommissions'] ?? 0) }} FC</h4>
            <p>Total Commissions</p>
        </div>
        <div class="stat-box">
            <h4 class="text-primary">{{ number_format($stats['totalPartNth'] ?? 0) }} FC</h4>
            <p>Part LATEM (70%)</p>
        </div>
        <div class="stat-box">
            <h4 class="text-warning">{{ number_format($stats['totalPartOkami'] ?? 0) }} FC</h4>
            <p>Part OKAMI (30%)</p>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Référence</th>
                <th>Période</th>
                <th class="text-end">Montant Total</th>
                <th class="text-end">Part LATEM</th>
                <th class="text-end">Part OKAMI</th>
                <th class="text-center">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($commissions as $com)
            <tr>
                <td>{{ $com->numero_reference }}</td>
                <td>{{ $com->periode_label }}</td>
                <td class="text-end text-success">{{ number_format($com->montant_total) }} FC</td>
                <td class="text-end text-primary">{{ number_format($com->part_nth) }} FC</td>
                <td class="text-end text-warning">{{ number_format($com->part_okami) }} FC</td>
                <td class="text-center">{{ $com->statut_label }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center">Aucune commission</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
</body>
</html>
