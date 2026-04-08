<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Commissions & Bénéfices' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 0; padding: 10px; }
        .header { text-align: center; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { margin: 0; font-size: 16px; color: #0d6efd; }
        .header p { margin: 3px 0; color: #666; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; font-size: 9px; }
        th { background: #0d6efd; color: white; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #198754; }
        .footer { text-align: center; margin-top: 15px; font-size: 8px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>LATEM Sarl - Administration</p>
        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
    <table>
        <thead>
            <tr>
                @if($type === 'commissions')
                <th>Collecteur</th>
                <th>Période</th>
                <th class="text-end">Total</th>
                <th class="text-end">LATEM (70%)</th>
                <th class="text-end">OKAMI (30%)</th>
                <th class="text-center">Statut</th>
                @else
                <th>Collecteur</th>
                <th>Type</th>
                <th>Période</th>
                <th class="text-end">Bénéfice</th>
                <th class="text-center">Statut</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                @if($type === 'commissions')
                <td>{{ $item->collecteur->user->name ?? 'N/A' }}</td>
                <td>{{ $item->periode_label }}</td>
                <td class="text-end text-success">{{ number_format($item->montant_total) }} FC</td>
                <td class="text-end">{{ number_format($item->part_nth) }} FC</td>
                <td class="text-end">{{ number_format($item->part_okami) }} FC</td>
                <td class="text-center">{{ $item->statut_label }}</td>
                @else
                <td>{{ $item->collecteur->user->name ?? 'N/A' }}</td>
                <td>{{ $item->type_saisie_label }}</td>
                <td>{{ $item->periode_label }}</td>
                <td class="text-end text-success">{{ number_format($item->benefice) }} FC</td>
                <td class="text-center">{{ $item->statut_label }}</td>
                @endif
            </tr>
            @empty
            <tr><td colspan="{{ $type === 'commissions' ? 6 : 5 }}" class="text-center">Aucune donnée</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">
        <p>Total: {{ $type === 'commissions' ? number_format($items->sum('montant_total')) : number_format($items->sum('benefice')) }} FC</p>
    </div>
</body>
</html>
