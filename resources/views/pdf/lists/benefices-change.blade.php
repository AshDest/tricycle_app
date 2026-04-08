<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Bénéfices de Change' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 0; padding: 10px; }
        .header { text-align: center; border-bottom: 2px solid #0dcaf0; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { margin: 0; font-size: 16px; color: #0dcaf0; }
        .header p { margin: 3px 0; color: #666; font-size: 9px; }
        .stats { display: table; width: 100%; margin-bottom: 15px; }
        .stat-box { display: table-cell; width: 25%; text-align: center; padding: 6px; background: #f5f5f5; }
        .stat-box h4 { margin: 0; font-size: 11px; }
        .stat-box p { margin: 2px 0 0; color: #666; font-size: 7px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; font-size: 9px; }
        th { background: #0dcaf0; color: white; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #198754; }
        .footer { text-align: center; margin-top: 15px; font-size: 8px; color: #666; }
        .total-row { background: #e3f2fd; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title ?? 'Bénéfices de Change' }}</h1>
        <p>LATEM Sarl - Kinshasa</p>
        <p>Période: {{ $periode ?? 'Toutes' }}</p>
        @if($collecteur)
        <p>Collecteur: {{ $collecteur->user->name ?? $collecteur->identifiant }}</p>
        @endif
    </div>
    <div class="stats">
        <div class="stat-box">
            <h4>{{ number_format($stats['totalJournalier'] ?? 0) }} FC</h4>
            <p>Journalier</p>
        </div>
        <div class="stat-box">
            <h4>{{ number_format($stats['totalHebdo'] ?? 0) }} FC</h4>
            <p>Hebdomadaire</p>
        </div>
        <div class="stat-box">
            <h4>{{ number_format($stats['totalMensuel'] ?? 0) }} FC</h4>
            <p>Mensuel</p>
        </div>
        <div class="stat-box">
            <h4 class="text-success">{{ number_format($stats['totalPeriode'] ?? 0) }} FC</h4>
            <p>Total</p>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Référence</th>
                <th>Type</th>
                <th>Période</th>
                <th class="text-end">Montant Caissier</th>
                <th class="text-end">Solde Caisse</th>
                <th class="text-end">Bénéfice</th>
                <th class="text-center">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($benefices as $b)
            <tr>
                <td>{{ $b->numero_reference }}</td>
                <td>{{ $b->type_saisie_label }}</td>
                <td>{{ $b->periode_label }}</td>
                <td class="text-end">{{ $b->montant_recu_caissier ? number_format($b->montant_recu_caissier) . ' FC' : '-' }}</td>
                <td class="text-end">{{ $b->solde_general_caisse ? number_format($b->solde_general_caisse) . ' FC' : '-' }}</td>
                <td class="text-end text-success">{{ number_format($b->benefice) }} FC</td>
                <td class="text-center">{{ $b->statut_label }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center">Aucun bénéfice</td></tr>
            @endforelse
            @if($benefices->count() > 0)
            <tr class="total-row">
                <td colspan="5" class="text-end">TOTAL:</td>
                <td class="text-end text-success">{{ number_format($benefices->sum('benefice')) }} FC</td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>
    <div class="footer">
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
</body>
</html>
