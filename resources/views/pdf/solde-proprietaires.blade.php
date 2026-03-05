<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solde Propriétaires</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0d6efd;
        }
        .header h1 {
            font-size: 18px;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .stats-box {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            background: #f8f9fa;
        }
        .stats-box .stat {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            text-align: center;
            border-right: 1px solid #ddd;
        }
        .stats-box .stat:last-child {
            border-right: none;
        }
        .stats-box .stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #198754;
        }
        .stats-box .stat-label {
            font-size: 9px;
            color: #666;
            margin-top: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background: #0d6efd;
            color: white;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        td {
            font-size: 9px;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-success {
            color: #198754;
        }
        .text-muted {
            color: #6c757d;
        }
        .fw-bold {
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success {
            background: #d1e7dd;
            color: #0f5132;
        }
        .badge-secondary {
            background: #e2e3e5;
            color: #41464b;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SOLDE DES PROPRIÉTAIRES</h1>
        <p>Généré le {{ $dateGeneration->format('d/m/Y à H:i') }}</p>
        @if($filterAvecSolde)
        <p style="margin-top: 5px;"><strong>Filtre:</strong> Uniquement propriétaires avec solde disponible</p>
        @endif
    </div>

    <div class="stats-box">
        <div class="stat">
            <div class="stat-value">{{ number_format($totalSoldeDisponible, 0, ',', ' ') }} FC</div>
            <div class="stat-label">Total Solde Disponible</div>
        </div>
        <div class="stat">
            <div class="stat-value">{{ $proprietaires->count() }}</div>
            <div class="stat-label">Nombre de Propriétaires</div>
        </div>
        <div class="stat">
            <div class="stat-value">{{ $proprietairesAvecSolde }}</div>
            <div class="stat-label">Avec Solde Disponible</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Propriétaire</th>
                <th style="width: 15%;">Contact</th>
                <th style="width: 10%;" class="text-center">Motos</th>
                <th style="width: 15%;" class="text-right">Versements</th>
                <th style="width: 15%;" class="text-right">Paiements</th>
                <th style="width: 15%;" class="text-right">Solde</th>
            </tr>
        </thead>
        <tbody>
            @forelse($proprietaires as $index => $proprietaire)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $proprietaire->user->name ?? 'N/A' }}</strong>
                    @if($proprietaire->raison_sociale)
                    <br><small class="text-muted">{{ $proprietaire->raison_sociale }}</small>
                    @endif
                </td>
                <td>{{ $proprietaire->telephone ?? '-' }}</td>
                <td class="text-center">
                    <span class="badge {{ $proprietaire->motos_actives > 0 ? 'badge-success' : 'badge-secondary' }}">
                        {{ $proprietaire->motos_actives }}
                    </span>
                </td>
                <td class="text-right">{{ number_format($proprietaire->total_versements, 0, ',', ' ') }} FC</td>
                <td class="text-right">{{ number_format($proprietaire->total_paiements, 0, ',', ' ') }} FC</td>
                <td class="text-right fw-bold {{ $proprietaire->solde_disponible > 0 ? 'text-success' : '' }}">
                    {{ number_format($proprietaire->solde_disponible, 0, ',', ' ') }} FC
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted">Aucun propriétaire trouvé</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #e9ecef; font-weight: bold;">
                <td colspan="4" class="text-right">TOTAL</td>
                <td class="text-right">{{ number_format($proprietaires->sum('total_versements'), 0, ',', ' ') }} FC</td>
                <td class="text-right">{{ number_format($proprietaires->sum('total_paiements'), 0, ',', ' ') }} FC</td>
                <td class="text-right text-success">{{ number_format($totalSoldeDisponible, 0, ',', ' ') }} FC</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>New Technology Hub Sarl - Gestion des Motos Tricycles</p>
        <p>Document généré automatiquement - {{ $dateGeneration->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>

