<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Liste des Lavages</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 0; padding: 15px; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header p { margin: 5px 0; color: #666; font-size: 9px; }
        .info-box { background: #f5f5f5; padding: 8px; margin-bottom: 15px; border-radius: 4px; }
        .info-box span { display: inline-block; margin-right: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 5px 6px; text-align: left; font-size: 9px; }
        th { background: #343a40; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 8px; }
        .badge-success { background: #28a745; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-info { background: #17a2b8; color: white; }
        .footer { text-align: center; font-size: 8px; color: #666; margin-top: 15px; border-top: 1px solid #ddd; padding-top: 10px; }
        .stats-row { display: flex; justify-content: space-around; margin-bottom: 10px; }
        .stat-item { text-align: center; padding: 8px; background: #f0f0f0; border-radius: 4px; display: inline-block; margin-right: 10px; }
        .stat-value { font-size: 14px; font-weight: bold; color: #28a745; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LISTE DES LAVAGES</h1>
        <p>{{ $cleaner->user->name ?? 'Laveur' }} | Généré le {{ now()->format('d/m/Y à H:i') }}</p>
        @if($dateDebut || $dateFin)
        <p>Période: {{ $dateDebut ? \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') : 'Début' }} - {{ $dateFin ? \Carbon\Carbon::parse($dateFin)->format('d/m/Y') : 'Fin' }}</p>
        @endif
    </div>

    <div class="info-box">
        <span><strong>Total lavages:</strong> {{ $stats['total'] }}</span>
        <span><strong>Montant total:</strong> {{ number_format($stats['total_montant']) }} FC</span>
        <span><strong>Part Laveur:</strong> {{ number_format($stats['part_cleaner']) }} FC</span>
        <span><strong>Part OKAMI:</strong> {{ number_format($stats['part_okami']) }} FC</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>N° Lavage</th>
                <th>Date</th>
                <th>Type</th>
                <th>Moto/Plaque</th>
                <th>Source</th>
                <th class="text-right">Montant</th>
                <th class="text-right">Part Laveur</th>
                <th class="text-center">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lavages as $lavage)
            <tr>
                <td>{{ $lavage->numero_lavage }}</td>
                <td>{{ $lavage->date_lavage?->format('d/m/Y H:i') }}</td>
                <td>{{ ucfirst($lavage->type_lavage) }}</td>
                <td>
                    @if($lavage->is_externe)
                    {{ $lavage->plaque_externe ?? 'N/A' }}
                    @else
                    {{ $lavage->moto?->plaque_immatriculation ?? 'N/A' }}
                    @endif
                </td>
                <td>
                    <span class="badge {{ $lavage->is_externe ? 'badge-info' : 'badge-success' }}">
                        {{ $lavage->is_externe ? 'Externe' : 'Interne' }}
                    </span>
                </td>
                <td class="text-right">{{ number_format($lavage->montant) }} FC</td>
                <td class="text-right">{{ number_format($lavage->part_cleaner) }} FC</td>
                <td class="text-center">
                    <span class="badge {{ $lavage->statut_paiement === 'payé' ? 'badge-success' : 'badge-warning' }}">
                        {{ ucfirst($lavage->statut_paiement) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Aucun lavage trouvé</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>OKAMI - Gestion des Motos-Tricycles | Document généré automatiquement</p>
    </div>
</body>
</html>

