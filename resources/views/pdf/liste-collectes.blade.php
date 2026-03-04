<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Liste des Collectes</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 0; padding: 15px; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header p { margin: 5px 0; color: #666; font-size: 9px; }
        .info-box { background: #f5f5f5; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .info-box span { display: inline-block; margin-right: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; font-size: 9px; }
        th { background: #17a2b8; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 8px; }
        .badge-success { background: #28a745; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-danger { background: #dc3545; color: white; }
        .footer { text-align: center; font-size: 8px; color: #666; margin-top: 15px; border-top: 1px solid #ddd; padding-top: 10px; }
        .total-row { background: #d1ecf1 !important; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LISTE DES COLLECTES</h1>
        <p>{{ $collecteur->user->name ?? 'Collecteur' }} | Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="info-box">
        <span><strong>Total collectes:</strong> {{ $stats['count'] }}</span>
        <span><strong>Montant total:</strong> {{ number_format($stats['total_collecte']) }} FC</span>
        <span><strong>Partielles:</strong> {{ $stats['partielles'] }}</span>
        <span><strong>En litige:</strong> {{ $stats['litiges'] }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date/Heure</th>
                <th>Caissier</th>
                <th>Zone</th>
                <th class="text-right">Montant attendu</th>
                <th class="text-right">Montant collecté</th>
                <th class="text-center">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($collectes as $collecte)
            <tr>
                <td>{{ $collecte->created_at?->format('d/m/Y H:i') }}</td>
                <td>{{ $collecte->caissier->user->name ?? 'N/A' }}</td>
                <td>{{ $collecte->caissier->zone ?? '-' }}</td>
                <td class="text-right">{{ number_format($collecte->montant_attendu ?? 0) }} FC</td>
                <td class="text-right">{{ number_format($collecte->montant_collecte) }} FC</td>
                <td class="text-center">
                    @switch($collecte->statut)
                        @case('reussie')
                            <span class="badge badge-success">Réussie</span>
                            @break
                        @case('partielle')
                            <span class="badge badge-warning">Partielle</span>
                            @break
                        @case('en_litige')
                            <span class="badge badge-danger">Litige</span>
                            @break
                        @default
                            <span class="badge">{{ ucfirst($collecte->statut) }}</span>
                    @endswitch
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Aucune collecte trouvée</td>
            </tr>
            @endforelse
            @if($collectes->count() > 0)
            <tr class="total-row">
                <td colspan="4" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>{{ number_format($stats['total_collecte']) }} FC</strong></td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>OKAMI - Gestion des Motos-Tricycles | Document généré automatiquement</p>
    </div>
</body>
</html>

