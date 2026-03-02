<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Répartition Hebdomadaire</title>
    <style>
        @page { margin: 10mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            background: #1a237e;
            color: white;
            padding: 12px;
            margin-bottom: 15px;
        }
        .header h1 { font-size: 14px; margin-bottom: 3px; }
        .header p { font-size: 8px; opacity: 0.9; }
        .info-box {
            background: #e3f2fd;
            border-left: 3px solid #1565c0;
            padding: 8px;
            margin-bottom: 15px;
            font-size: 8px;
        }
        .stats-grid {
            width: 100%;
            margin-bottom: 15px;
        }
        .stats-grid td {
            width: 25%;
            padding: 8px;
            text-align: center;
            border: 1px solid #e0e0e0;
            background: #f5f5f5;
        }
        .stat-value { font-size: 12px; font-weight: bold; }
        .stat-value.primary { color: #1565c0; }
        .stat-value.success { color: #2e7d32; }
        .stat-value.warning { color: #f57c00; }
        .stat-value.info { color: #0097a7; }
        .stat-label { font-size: 7px; color: #666; text-transform: uppercase; }
        .repartition-box {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            border: 1px solid #ddd;
            margin-bottom: 15px;
        }
        .repartition-header {
            padding: 6px;
            font-weight: bold;
            font-size: 9px;
        }
        .repartition-header.proprietaire { background: #fff3e0; color: #e65100; }
        .repartition-header.okami { background: #e0f7fa; color: #00838f; }
        .repartition-body { padding: 8px; text-align: center; }
        .repartition-body .amount { font-size: 11px; font-weight: bold; }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.data th {
            background: #1a237e;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
        }
        table.data td {
            padding: 5px 4px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 8px;
        }
        table.data tr:nth-child(even) { background: #f9f9f9; }
        table.data tfoot td {
            font-weight: bold;
            background: #e8eaf6;
            border-top: 2px solid #1a237e;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #2e7d32; }
        .text-warning { color: #f57c00; }
        .text-info { color: #0097a7; }
        .text-danger { color: #c62828; }
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 7px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>RAPPORT DE RÉPARTITION HEBDOMADAIRE</h1>
        <p>New Technology Hub Sarl - Tricycle App | Période: {{ $resume['periode']['debut'] }} au {{ $resume['periode']['fin'] }}</p>
    </div>

    <!-- Info système -->
    <div class="info-box">
        <strong>Système de répartition:</strong> Semaine = 6 jours |
        Part Propriétaires = 5 jours (83.33%) |
        Part OKAMI = 1 jour (16.67%)
    </div>

    <!-- Statistiques globales -->
    <table class="stats-grid">
        <tr>
            <td>
                <div class="stat-value primary">{{ $resume['nb_motos_actives'] }}</div>
                <div class="stat-label">Motos Actives</div>
            </td>
            <td>
                <div class="stat-value info">{{ number_format($resume['total_attendu']) }} FC</div>
                <div class="stat-label">Total Attendu</div>
            </td>
            <td>
                <div class="stat-value success">{{ number_format($resume['total_verse']) }} FC</div>
                <div class="stat-label">Total Versé</div>
            </td>
            <td>
                <div class="stat-value {{ $resume['taux_recouvrement'] >= 80 ? 'success' : 'warning' }}">{{ $resume['taux_recouvrement'] }}%</div>
                <div class="stat-label">Taux Recouvrement</div>
            </td>
        </tr>
    </table>

    <!-- Répartition -->
    <div style="margin-bottom: 15px;">
        <div class="repartition-box" style="margin-right: 2%;">
            <div class="repartition-header proprietaire">
                <i>●</i> PART PROPRIÉTAIRES (5/6)
            </div>
            <div class="repartition-body">
                <div>Attendue: <span class="amount">{{ number_format($resume['repartition_attendue']['part_proprietaires']) }} FC</span></div>
                <div>Réelle: <span class="amount text-warning">{{ number_format($resume['repartition_verse']['part_proprietaires']) }} FC</span></div>
            </div>
        </div>
        <div class="repartition-box">
            <div class="repartition-header okami">
                <i>●</i> PART OKAMI (1/6)
            </div>
            <div class="repartition-body">
                <div>Attendue: <span class="amount">{{ number_format($resume['repartition_attendue']['part_okami']) }} FC</span></div>
                <div>Réelle: <span class="amount text-info">{{ number_format($resume['repartition_verse']['part_okami']) }} FC</span></div>
            </div>
        </div>
    </div>

    <!-- Détails par propriétaire -->
    <table class="data">
        <thead>
            <tr>
                <th>Propriétaire</th>
                <th class="text-center">Motos</th>
                <th class="text-right">Attendu</th>
                <th class="text-right">Versé</th>
                <th class="text-right">Part Propriétaire</th>
                <th class="text-right">Part OKAMI</th>
                <th class="text-center">Écart</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detailsProprietaires as $detail)
            <tr>
                <td>{{ $detail['proprietaire_nom'] }}</td>
                <td class="text-center">{{ $detail['nb_motos'] }}</td>
                <td class="text-right">{{ number_format($detail['total_attendu']) }} FC</td>
                <td class="text-right text-success">{{ number_format($detail['total_verse']) }} FC</td>
                <td class="text-right text-warning">{{ number_format($detail['total_part_proprietaire']) }} FC</td>
                <td class="text-right text-info">{{ number_format($detail['total_part_okami']) }} FC</td>
                <td class="text-center {{ $detail['ecart'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $detail['ecart'] >= 0 ? '+' : '' }}{{ number_format($detail['ecart']) }} FC
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Aucune donnée</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($detailsProprietaires) > 0)
        <tfoot>
            <tr>
                <td>TOTAL</td>
                <td class="text-center">{{ $resume['nb_motos_actives'] }}</td>
                <td class="text-right">{{ number_format($resume['total_attendu']) }} FC</td>
                <td class="text-right text-success">{{ number_format($resume['total_verse']) }} FC</td>
                <td class="text-right text-warning">{{ number_format($resume['repartition_verse']['part_proprietaires']) }} FC</td>
                <td class="text-right text-info">{{ number_format($resume['repartition_verse']['part_okami']) }} FC</td>
                <td class="text-center {{ $resume['ecart'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $resume['ecart'] >= 0 ? '+' : '' }}{{ number_format($resume['ecart']) }} FC
                </td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Pied de page -->
    <div class="footer">
        <p>Document généré le {{ $dateExport }} | Tricycle App - New Technology Hub Sarl</p>
    </div>
</body>
</html>

