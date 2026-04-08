<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Transactions Mobile Money' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 0; padding: 10px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header p { margin: 3px 0; color: #666; font-size: 9px; }
        .stats { display: table; width: 100%; margin-bottom: 15px; }
        .stat-box { display: table-cell; width: 25%; text-align: center; padding: 8px; background: #f5f5f5; }
        .stat-box h4 { margin: 0; font-size: 14px; }
        .stat-box p { margin: 2px 0 0; color: #666; font-size: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; font-size: 9px; }
        th { background: #f0f0f0; font-weight: bold; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .text-danger { color: #dc3545; }
        .text-success { color: #198754; }
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 8px; }
        .bg-danger { background: #dc3545; color: white; }
        .bg-success { background: #198754; color: white; }
        .footer { text-align: center; margin-top: 15px; font-size: 8px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title ?? 'Transactions Mobile Money' }}</h1>
        <p>LATEM Sarl - Kinshasa</p>
        <p>Période: {{ $periode ?? 'Toutes' }}</p>
        @if($collecteur)
        <p>Collecteur: {{ $collecteur->user->name ?? $collecteur->identifiant }}</p>
        @endif
    </div>
    <div class="stats">
        <div class="stat-box">
            <h4>{{ number_format($stats['soldeCaisse'] ?? $stats['soldeCaisseGlobal'] ?? 0) }} FC</h4>
            <p>Solde Caisse</p>
        </div>
        <div class="stat-box">
            <h4 class="text-danger">{{ number_format($stats['totalEnvois'] ?? 0) }} FC</h4>
            <p>Envois Mobile</p>
        </div>
        <div class="stat-box">
            <h4 class="text-success">{{ number_format($stats['totalRetraits'] ?? 0) }} FC</h4>
            <p>Retraits Mobile</p>
        </div>
        <div class="stat-box">
            <h4>{{ $transactions->count() }}</h4>
            <p>Transactions</p>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>N° Transaction</th>
                @if(!$collecteur)<th>Collecteur</th>@endif
                <th>Type</th>
                <th>Opérateur</th>
                <th>Téléphone</th>
                <th class="text-end">Montant</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
            <tr>
                <td>{{ $tx->numero_transaction }}</td>
                @if(!$collecteur)<td>{{ $tx->collecteur?->user?->name ?? 'N/A' }}</td>@endif
                <td class="text-center">
                    <span class="badge bg-{{ $tx->type === 'envoi' ? 'danger' : 'success' }}">
                        {{ $tx->type_label }}
                    </span>
                </td>
                <td>{{ $tx->operateur_label }}</td>
                <td>{{ $tx->numero_telephone }}</td>
                <td class="text-end text-{{ $tx->type === 'envoi' ? 'danger' : 'success' }}">
                    {{ $tx->type === 'envoi' ? '-' : '+' }}{{ number_format($tx->montant) }} FC
                </td>
                <td>{{ $tx->date_transaction->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $collecteur ? 6 : 7 }}" class="text-center">Aucune transaction</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
</body>
</html>
