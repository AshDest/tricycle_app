<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Solde OKAMI</title>
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
            border-bottom: 2px solid #ffc107;
        }
        .header h1 {
            font-size: 18px;
            color: #ffc107;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .periode {
            text-align: center;
            background: #fff3cd;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .periode strong {
            color: #856404;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stats-row {
            display: table-row;
        }
        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 5px;
            vertical-align: top;
        }
        .stat-inner {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            background: #f8f9fa;
        }
        .stat-value {
            font-size: 14px;
            font-weight: bold;
        }
        .stat-value.success { color: #198754; }
        .stat-value.danger { color: #dc3545; }
        .stat-value.warning { color: #ffc107; }
        .stat-value.info { color: #0dcaf0; }
        .stat-label {
            font-size: 8px;
            color: #666;
            margin-top: 3px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #333;
            padding: 5px 10px;
            background: #ffc107;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 5px 8px;
            text-align: left;
        }
        th {
            background: #6c757d;
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
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .text-warning { color: #856404; }
        .text-muted { color: #6c757d; }
        .fw-bold { font-weight: bold; }
        .summary-box {
            background: #f8f9fa;
            border: 2px solid #ffc107;
            padding: 15px;
            margin-bottom: 15px;
        }
        .summary-box h3 {
            font-size: 12px;
            color: #856404;
            margin-bottom: 10px;
            text-align: center;
        }
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        .summary-row > div {
            display: table-cell;
            padding: 5px;
        }
        .summary-row .label {
            width: 60%;
        }
        .summary-row .value {
            width: 40%;
            text-align: right;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>RAPPORT SOLDE OKAMI</h1>
        <p>Généré le {{ $dateGeneration->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="periode">
        <strong>Période:</strong> Du {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
        ({{ ucfirst($periodeFilter) }})
    </div>

    <!-- Statistiques principales -->
    <div class="stats-grid">
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-inner">
                    <div class="stat-value success">{{ number_format($totalEntreesOkami, 0, ',', ' ') }} FC</div>
                    <div class="stat-label">Total Entrées OKAMI</div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-inner">
                    <div class="stat-value danger">{{ number_format($totalSortiesOkami, 0, ',', ' ') }} FC</div>
                    <div class="stat-label">Total Sorties</div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-inner">
                    <div class="stat-value {{ $soldeNetPeriode >= 0 ? 'success' : 'danger' }}">{{ number_format($soldeNetPeriode, 0, ',', ' ') }} FC</div>
                    <div class="stat-label">Solde Net Période</div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-inner">
                    <div class="stat-value warning">{{ number_format($paiementsEnAttenteOkami, 0, ',', ' ') }} FC</div>
                    <div class="stat-label">En Attente</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Détail des entrées -->
    <div class="summary-box">
        <h3>Détail des Entrées OKAMI</h3>
        <div class="summary-row">
            <div class="label">Versements collectés</div>
            <div class="value text-success">{{ number_format($totalPartOkamiVersements, 0, ',', ' ') }} FC</div>
        </div>
        <div class="summary-row">
            <div class="label">Part OKAMI sur lavages (20%)</div>
            <div class="value text-success">{{ number_format($totalPartOkamiLavages, 0, ',', ' ') }} FC</div>
        </div>
        <div class="summary-row" style="border-top: 1px solid #ddd; padding-top: 5px;">
            <div class="label fw-bold">Total Entrées</div>
            <div class="value text-success fw-bold">{{ number_format($totalEntreesOkami, 0, ',', ' ') }} FC</div>
        </div>
    </div>

    <!-- Stats par semaine -->
    @if(count($statsParSemaine) > 0)
    <div class="section">
        <div class="section-title">Statistiques par Semaine</div>
        <table>
            <thead>
                <tr>
                    <th>Semaine</th>
                    <th>Période</th>
                    <th class="text-right">Versements</th>
                    <th class="text-right">Lavages</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statsParSemaine as $stat)
                <tr>
                    <td>{{ $stat['semaine'] }}</td>
                    <td>{{ $stat['debut'] }} - {{ $stat['fin'] }}</td>
                    <td class="text-right">{{ number_format($stat['versements'], 0, ',', ' ') }} FC</td>
                    <td class="text-right">{{ number_format($stat['lavages'], 0, ',', ' ') }} FC</td>
                    <td class="text-right fw-bold text-success">{{ number_format($stat['total'], 0, ',', ' ') }} FC</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Derniers versements -->
    @if($derniersVersements->count() > 0)
    <div class="section">
        <div class="section-title">Derniers Versements avec Part OKAMI</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Motard</th>
                    <th>Moto</th>
                    <th class="text-right">Montant Total</th>
                    <th class="text-right">Part OKAMI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($derniersVersements as $versement)
                <tr>
                    <td>{{ $versement->date_versement->format('d/m/Y') }}</td>
                    <td>{{ $versement->motard?->user?->name ?? 'N/A' }}</td>
                    <td>{{ $versement->moto?->plaque_immatriculation ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($versement->montant, 0, ',', ' ') }} FC</td>
                    <td class="text-right fw-bold text-warning">{{ number_format($versement->part_okami, 0, ',', ' ') }} FC</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Derniers paiements OKAMI -->
    @if($derniersPaiements->count() > 0)
    <div class="section">
        <div class="section-title">Derniers Paiements depuis Caisse OKAMI</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Bénéficiaire</th>
                    <th class="text-right">Montant</th>
                </tr>
            </thead>
            <tbody>
                @foreach($derniersPaiements as $paiement)
                <tr>
                    <td>{{ $paiement->date_paiement?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $paiement->proprietaire?->user?->name ?? $paiement->beneficiaire_externe ?? 'N/A' }}</td>
                    <td class="text-right fw-bold text-danger">{{ number_format($paiement->total_paye, 0, ',', ' ') }} FC</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>New Technology Hub Sarl - Gestion des Motos Tricycles</p>
        <p>Document généré automatiquement - {{ $dateGeneration->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>

