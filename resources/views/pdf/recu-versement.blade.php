<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reçu de Versement</title>
    <style>
        @page {
            margin: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
            width: 72mm;
            margin: 0 auto;
            padding: 3mm;
        }
        .receipt {
            width: 100%;
            text-align: center;
        }
        .header {
            border-bottom: 1px dashed #333;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .header h1 {
            font-size: 13px;
            font-weight: bold;
        }
        .header p {
            font-size: 8px;
            color: #666;
        }
        .receipt-title {
            font-size: 11px;
            font-weight: bold;
            margin: 8px 0;
            padding: 4px;
            background: #f0f0f0;
        }
        .receipt-number {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .datetime {
            font-size: 8px;
            color: #666;
            margin-bottom: 8px;
        }
        .section {
            text-align: left;
            margin: 6px 0;
            padding-top: 6px;
            border-top: 1px dashed #ccc;
        }
        .section-title {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 4px;
            text-align: center;
        }
        table.info {
            width: 100%;
            border-collapse: collapse;
        }
        table.info td {
            padding: 2px 0;
            font-size: 9px;
        }
        table.info td.label {
            color: #666;
            width: 45%;
        }
        table.info td.value {
            text-align: right;
            font-weight: 500;
        }
        .amount-box {
            background: #222;
            color: #fff;
            padding: 8px;
            margin: 8px 0;
            text-align: center;
        }
        .amount-label {
            font-size: 8px;
            opacity: 0.8;
        }
        .amount-value {
            font-size: 16px;
            font-weight: bold;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
        }
        .status-paye {
            background: #d4edda;
            color: #155724;
        }
        .status-partiel {
            background: #fff3cd;
            color: #856404;
        }
        .status-retard {
            background: #f8d7da;
            color: #721c24;
        }
        .arrieres-box {
            background: #fff3cd;
            padding: 6px;
            margin: 6px 0;
            text-align: center;
        }
        .arrieres-label {
            font-size: 7px;
            color: #856404;
        }
        .arrieres-value {
            font-size: 11px;
            font-weight: bold;
            color: #856404;
        }
        .footer {
            border-top: 1px dashed #333;
            padding-top: 8px;
            margin-top: 8px;
            text-align: center;
        }
        .footer p {
            font-size: 7px;
            color: #666;
            margin: 2px 0;
        }
        .footer .thank-you {
            font-size: 9px;
            font-weight: bold;
            margin-top: 6px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- En-tête -->
        <div class="header">
            <h1>TRICYCLE APP</h1>
            <p>New Technology Hub Sarl</p>
            <p>Kinshasa, RDC</p>
        </div>

        <!-- Titre du reçu -->
        <div class="receipt-title">REÇU DE VERSEMENT</div>

        <!-- Numéro de reçu -->
        <div class="receipt-number">N° {{ $versement->numero_recu ?? sprintf('RV-%s-%06d', date('Ymd'), $versement->id) }}</div>

        <!-- Date et heure -->
        <div class="datetime">{{ $versement->created_at->format('d/m/Y à H:i') }}</div>

        <!-- Informations du motard -->
        <div class="section">
            <div class="section-title">Motard</div>
            <table class="info">
                <tr>
                    <td class="label">Nom</td>
                    <td class="value">{{ $versement->motard->user->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">ID</td>
                    <td class="value">{{ $versement->motard->numero_identifiant ?? 'N/A' }}</td>
                </tr>
                @if($versement->motard_secondaire_id && $versement->motardSecondaire)
                <tr>
                    <td class="label">Conducteur</td>
                    <td class="value">{{ $versement->motardSecondaire->user->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label" colspan="2" style="text-align:center; font-size: 7px; color: #856404;">
                        (Remplaçant du motard titulaire)
                    </td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Informations de la moto -->
        <div class="section">
            <div class="section-title">Moto</div>
            <table class="info">
                <tr>
                    <td class="label">Plaque</td>
                    <td class="value">{{ $versement->moto->plaque_immatriculation ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Matricule</td>
                    <td class="value">{{ $versement->moto->numero_matricule ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <!-- Montant versé -->
        <div class="amount-box">
            <div class="amount-label">MONTANT VERSÉ</div>
            <div class="amount-value">{{ number_format($versement->montant, 0, ',', ' ') }} FC</div>
        </div>

        <!-- Détails du versement -->
        <div class="section">
            <div class="section-title">Détails</div>
            <table class="info">
                @if($versement->semaine_debut && $versement->semaine_fin)
                <tr>
                    <td class="label">Semaine civile</td>
                    <td class="value">{{ $versement->semaine_debut->format('d/m') }} - {{ $versement->semaine_fin->format('d/m') }}</td>
                </tr>
                <tr>
                    <td class="label">N° Semaine</td>
                    <td class="value">{{ $versement->numero_semaine ?? $versement->semaine_debut->weekOfYear }}/{{ $versement->semaine_debut->year }}</td>
                </tr>
                <tr>
                    <td class="label">Jours</td>
                    <td class="value">Lundi - Samedi (6 jours)</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Attendu</td>
                    <td class="value">{{ number_format($versement->montant_attendu ?? 0, 0, ',', ' ') }} FC</td>
                </tr>
                <tr>
                    <td class="label">Mode</td>
                    <td class="value">{{ ucfirst(str_replace('_', ' ', $versement->mode_paiement ?? 'Cash')) }}</td>
                </tr>
                <tr>
                    <td class="label">Date</td>
                    <td class="value">{{ $versement->date_versement?->format('d/m/Y') ?? $versement->created_at->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Statut</td>
                    <td class="value">
                        @php
                            $statutClass = match($versement->statut) {
                                'payé' => 'status-paye',
                                'partiellement_payé' => 'status-partiel',
                                default => 'status-retard'
                            };
                            $statutLabel = match($versement->statut) {
                                'payé' => 'PAYÉ',
                                'partiellement_payé' => 'PARTIEL',
                                'en_retard' => 'EN RETARD',
                                default => strtoupper(str_replace('_', ' ', $versement->statut ?? 'N/A'))
                            };
                        @endphp
                        <span class="status-badge {{ $statutClass }}">{{ $statutLabel }}</span>
                    </td>
                </tr>
            </table>
        </div>


        <!-- Arriérés si existants -->
        @if(($versement->arrieres ?? 0) > 0)
        <div class="arrieres-box">
            <div class="arrieres-label">ARRIÉRÉS DU JOUR</div>
            <div class="arrieres-value">{{ number_format($versement->arrieres, 0, ',', ' ') }} FC</div>
        </div>
        @endif

        <!-- Caissier -->
        <div class="section">
            <table class="info">
                <tr>
                    <td class="label">Caissier</td>
                    <td class="value">{{ $versement->caissier->user->name ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            <p>Ce reçu fait foi de paiement</p>
            <p>Conservez-le précieusement</p>
            <p class="thank-you">Merci!</p>
            <p style="margin-top: 6px;">{{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>
</html>

