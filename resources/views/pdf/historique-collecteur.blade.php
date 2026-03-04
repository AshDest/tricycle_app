<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Historique Collecteur</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 0; padding: 15px; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header p { margin: 5px 0; color: #666; font-size: 9px; }
        .info-box { background: #f5f5f5; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .info-box span { display: inline-block; margin-right: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; font-size: 9px; }
        th { background: #007bff; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 8px; }
        .badge-success { background: #28a745; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-info { background: #17a2b8; color: white; }
        .badge-secondary { background: #6c757d; color: white; }
        .footer { text-align: center; font-size: 8px; color: #666; margin-top: 15px; border-top: 1px solid #ddd; padding-top: 10px; }
        .total-row { background: #d4edda !important; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>HISTORIQUE DES TOURNEES</h1>
        <p>{{ $collecteur->user->name ?? 'Collecteur' }} | Genere le {{ now()->format('d/m/Y a H:i') }}</p>
        @if($dateFrom || $dateTo)
        <p>Periode: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'Debut' }} - {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : now()->format('d/m/Y') }}</p>
        @endif
    </div>
    <div class="info-box">
        <span><strong>Nombre de tournees:</strong> {{ $stats['nombre_tournees'] }}</span>
        <span><strong>Collectes effectuees:</strong> {{ $stats['nombre_collectes'] }}</span>
        <span><strong>Total collecte:</strong> {{ number_format($stats['total_collecte']) }} FC</span>
        <span><strong>Moyenne/tournee:</strong> {{ number_format($stats['moyenne_par_tournee']) }} FC</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>N Tournee</th>
                <th class="text-center">Collectes</th>
                <th class="text-right">Montant collecte</th>
                <th class="text-center">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tournees as $tournee)
            <tr>
                <td>{{ $tournee->date?->format('d/m/Y') }}</td>
                <td>{{ $tournee->numero ?? 'T-' . $tournee->id }}</td>
                <td class="text-center">{{ $tournee->collectes_count }}</td>
                <td class="text-right">{{ number_format($tournee->total_collecte) }} FC</td>
                <td class="text-center">
                    @switch($tournee->statut)
                        @case('terminee')
                            <span class="badge badge-success">Terminee</span>
                            @break
                        @case('en_cours')
                            <span class="badge badge-info">En cours</span>
                            @break
                        @case('confirmee')
                            <span class="badge badge-warning">Confirmee</span>
                            @break
                        @default
                            <span class="badge badge-secondary">{{ ucfirst($tournee->statut) }}</span>
                    @endswitch
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Aucune tournee trouvee</td>
            </tr>
            @endforelse
            @if($tournees->count() > 0)
            <tr class="total-row">
                <td colspan="2" class="text-right"><strong>TOTAUX</strong></td>
                <td class="text-center"><strong>{{ $stats['nombre_collectes'] }}</strong></td>
                <td class="text-right"><strong>{{ number_format($stats['total_collecte']) }} FC</strong></td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>
    <div class="footer">
        <p>OKAMI - Gestion des Motos-Tricycles | Document genere automatiquement</p>
    </div>
</body>
</html>
