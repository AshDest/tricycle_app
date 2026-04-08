<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relevé Mensuel - {{ $mois }}</title>
    <style>
        @page {
            margin: 15mm;
        }
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
        }
        .header {
            background: #1a237e;
            color: white;
            padding: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 9px;
            opacity: 0.9;
        }
        .info-box {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 3px solid #1a237e;
        }
        .info-box h3 {
            font-size: 11px;
            margin-bottom: 5px;
            color: #1a237e;
        }
        .stats-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .stats-grid td {
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }
        .stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #1a237e;
        }
        .stat-value.success { color: #2e7d32; }
        .stat-value.danger { color: #c62828; }
        .stat-value.info { color: #1565c0; }
        .stat-label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data th {
            background: #1a237e;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }
        table.data td {
            padding: 6px 5px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 9px;
        }
        table.data tr:nth-child(even) {
            background: #f9f9f9;
        }
        table.data tfoot td {
            font-weight: bold;
            background: #e8eaf6;
            border-top: 2px solid #1a237e;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #2e7d32; }
        .text-danger { color: #c62828; }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>RELEVÉ MENSUEL - {{ strtoupper($mois) }}</h1>
        <p>LATEM Sarl - Tricycle App | Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <!-- Infos propriétaire -->
    <div class="info-box">
        <h3>Propriétaire</h3>
        <p><strong>{{ $proprietaire->user->name ?? 'N/A' }}</strong></p>
        <p>{{ $proprietaire->raison_sociale ?? '' }}</p>
        <p>Période: {{ $mois }}</p>
    </div>

    <!-- Statistiques -->
    <table class="stats-grid">
        <tr>
            <td>
                <div class="stat-value success">{{ number_format($totalVersements) }} FC</div>
                <div class="stat-label">Total Versé</div>
            </td>
            <td>
                <div class="stat-value">{{ number_format($totalAttendu) }} FC</div>
                <div class="stat-label">Total Attendu</div>
            </td>
            <td>
                <div class="stat-value {{ $totalArrieres > 0 ? 'danger' : 'success' }}">{{ number_format($totalArrieres) }} FC</div>
                <div class="stat-label">Arriérés</div>
            </td>
            <td>
                <div class="stat-value info">{{ number_format($soldeDisponible) }} FC</div>
                <div class="stat-label">Solde Disponible</div>
            </td>
        </tr>
    </table>

    <!-- Détails par moto -->
    <table class="data">
        <thead>
            <tr>
                <th>Moto</th>
                <th>Motard</th>
                <th class="text-center">Nb Vers.</th>
                <th class="text-right">Versé</th>
                <th class="text-right">Attendu</th>
                <th class="text-right">Arriérés</th>
                <th class="text-center">Taux</th>
            </tr>
        </thead>
        <tbody>
            @forelse($versementsParMoto as $item)
            @php
                $taux = $item['attendu'] > 0 ? round(($item['total'] / $item['attendu']) * 100, 1) : 100;
            @endphp
            <tr>
                <td>
                    <strong>{{ $item['moto']->plaque_immatriculation ?? 'N/A' }}</strong><br>
                    <small style="color: #666;">{{ $item['moto']->numero_matricule ?? '' }}</small>
                </td>
                <td>{{ $item['moto']->motard->user->name ?? 'Non assigné' }}</td>
                <td class="text-center">{{ $item['nb_versements'] }}</td>
                <td class="text-right text-success">{{ number_format($item['total']) }} FC</td>
                <td class="text-right">{{ number_format($item['attendu']) }} FC</td>
                <td class="text-right {{ $item['arrieres'] > 0 ? 'text-danger' : '' }}">
                    {{ $item['arrieres'] > 0 ? number_format($item['arrieres']) . ' FC' : '-' }}
                </td>
                <td class="text-center">{{ $taux }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 20px;">Aucune donnée pour cette période</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($versementsParMoto) > 0)
        <tfoot>
            <tr>
                <td colspan="3"><strong>TOTAL</strong></td>
                <td class="text-right text-success">{{ number_format($totalVersements) }} FC</td>
                <td class="text-right">{{ number_format($totalAttendu) }} FC</td>
                <td class="text-right text-danger">{{ number_format($totalArrieres) }} FC</td>
                <td class="text-center">
                    @php
                        $tauxGlobal = $totalAttendu > 0 ? round(($totalVersements / $totalAttendu) * 100, 1) : 100;
                    @endphp
                    {{ $tauxGlobal }}%
                </td>
            </tr>
        </tfoot>
        @endif
    </table>

    @if($paiementsRecus > 0)
    <div class="info-box" style="background: #e8f5e9; border-color: #2e7d32;">
        <p><strong>Paiements reçus ce mois:</strong> {{ number_format($paiementsRecus) }} FC</p>
    </div>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        <p>Ce document est un relevé officiel généré par Tricycle App - LATEM Sarl</p>
        <p>Pour toute question, veuillez contacter l'administration.</p>
    </div>
</body>
</html>

