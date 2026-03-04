<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Liste des Dépenses</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 0; padding: 15px; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header p { margin: 5px 0; color: #666; font-size: 9px; }
        .info-box { background: #f5f5f5; padding: 8px; margin-bottom: 15px; border-radius: 4px; }
        .info-box span { display: inline-block; margin-right: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; font-size: 9px; }
        th { background: #dc3545; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 8px; }
        .badge-primary { background: #007bff; color: white; }
        .footer { text-align: center; font-size: 8px; color: #666; margin-top: 15px; border-top: 1px solid #ddd; padding-top: 10px; }
        .total-row { background: #f8d7da !important; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LISTE DES DÉPENSES - LAVAGE</h1>
        <p>{{ $cleaner->user->name ?? 'Laveur' }} | Généré le {{ now()->format('d/m/Y à H:i') }}</p>
        @if($dateDebut || $dateFin)
        <p>Période: {{ $dateDebut ? \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') : 'Début' }} - {{ $dateFin ? \Carbon\Carbon::parse($dateFin)->format('d/m/Y') : 'Fin' }}</p>
        @endif
    </div>

    <div class="info-box">
        <span><strong>Nombre de dépenses:</strong> {{ $stats['count'] }}</span>
        <span><strong>Total dépensé:</strong> {{ number_format($stats['total']) }} FC</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>N° Dépense</th>
                <th>Date</th>
                <th>Catégorie</th>
                <th>Description</th>
                <th>Fournisseur</th>
                <th class="text-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse($depenses as $depense)
            <tr>
                <td>{{ $depense->numero_depense }}</td>
                <td>{{ $depense->date_depense?->format('d/m/Y') }}</td>
                <td>
                    <span class="badge badge-primary">
                        {{ $categories[$depense->categorie] ?? $depense->categorie }}
                    </span>
                </td>
                <td>{{ Str::limit($depense->description, 40) }}</td>
                <td>{{ $depense->fournisseur ?? '-' }}</td>
                <td class="text-right">{{ number_format($depense->montant) }} FC</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Aucune dépense trouvée</td>
            </tr>
            @endforelse
            @if($depenses->count() > 0)
            <tr class="total-row">
                <td colspan="5" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>{{ number_format($stats['total']) }} FC</strong></td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>OKAMI - Gestion des Motos-Tricycles | Document généré automatiquement</p>
    </div>
</body>
</html>

