<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Demandes de Paiement</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 0; padding: 15px; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header p { margin: 5px 0; color: #666; font-size: 9px; }
        .info-box { background: #f5f5f5; padding: 8px; margin-bottom: 15px; border-radius: 4px; }
        .info-box span { display: inline-block; margin-right: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 5px 6px; text-align: left; font-size: 9px; }
        th { background: #28a745; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 8px; }
        .badge-success { background: #28a745; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-danger { background: #dc3545; color: white; }
        .footer { text-align: center; font-size: 8px; color: #666; margin-top: 15px; border-top: 1px solid #ddd; padding-top: 10px; }
        .row-danger { background: #f8d7da !important; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DEMANDES DE PAIEMENT EN ATTENTE</h1>
        <p>{{ $collecteur->user->name ?? 'Collecteur' }} | Genere le {{ now()->format('d/m/Y a H:i') }}</p>
    </div>
    <div class="info-box">
        <span><strong>Total demandes:</strong> {{ $stats['total_demandes'] }}</span>
        <span><strong>Montant total:</strong> {{ number_format($stats['total_montant']) }} FC</span>
        <span><strong>Proprietaires:</strong> {{ $stats['demandes_proprietaire'] }}</span>
        <span><strong>OKAMI:</strong> {{ $stats['demandes_okami'] }}</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Source</th>
                <th>Beneficiaire</th>
                <th class="text-right">Solde dispo.</th>
                <th class="text-right">Demande</th>
                <th>Mode</th>
                <th class="text-center">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
            @php $peutPayer = $payment->total_du <= $payment->solde_disponible; @endphp
            <tr class="{{ !$peutPayer ? 'row-danger' : '' }}">
                <td>{{ $payment->date_demande?->format('d/m/Y') }}</td>
                <td>
                    @if($payment->source_caisse === 'okami')
                    <span class="badge badge-warning">OKAMI</span>
                    @else
                    <span class="badge badge-success">Proprietaire</span>
                    @endif
                </td>
                <td>
                    @if($payment->source_caisse === 'okami')
                    {{ $payment->beneficiaire_nom ?? 'N/A' }}
                    @else
                    {{ $payment->proprietaire->user->name ?? 'N/A' }}
                    @endif
                </td>
                <td class="text-right">{{ number_format($payment->solde_disponible) }} FC</td>
                <td class="text-right">{{ number_format($payment->total_du) }} FC</td>
                <td>{{ \App\Models\Payment::getModesPaiement()[$payment->mode_paiement] ?? $payment->mode_paiement }}</td>
                <td class="text-center">
                    @if($peutPayer)
                    <span class="badge badge-success">OK</span>
                    @else
                    <span class="badge badge-danger">Insuffisant</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Aucune demande en attente</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">
        <p>OKAMI - Gestion des Motos-Tricycles | Document genere automatiquement</p>
    </div>
</body>
</html>
