<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reçu de Versement</title>
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
            width: 80mm;
            padding: 5mm;
        }

        .receipt {
            width: 100%;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #333;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .header h1 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .header p {
            font-size: 9px;
            color: #666;
        }

        .receipt-title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0;
            padding: 5px;
            background: #f5f5f5;
            border-radius: 3px;
        }

        .receipt-number {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }

        .info-label {
            display: table-cell;
            width: 40%;
            font-size: 9px;
            color: #666;
        }

        .info-value {
            display: table-cell;
            width: 60%;
            font-size: 10px;
            font-weight: 500;
            text-align: right;
        }

        .section {
            margin: 10px 0;
            padding: 8px 0;
            border-top: 1px dashed #ccc;
        }

        .section-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 6px;
        }

        .amount-box {
            background: #333;
            color: #fff;
            padding: 10px;
            text-align: center;
            margin: 10px 0;
            border-radius: 4px;
        }

        .amount-label {
            font-size: 9px;
            opacity: 0.8;
        }

        .amount-value {
            font-size: 18px;
            font-weight: bold;
            margin-top: 3px;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
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
            padding: 8px;
            border-radius: 4px;
            margin: 8px 0;
            text-align: center;
        }

        .arrieres-label {
            font-size: 8px;
            color: #856404;
        }

        .arrieres-value {
            font-size: 12px;
            font-weight: bold;
            color: #856404;
        }

        .footer {
            border-top: 1px dashed #333;
            padding-top: 10px;
            margin-top: 10px;
            text-align: center;
        }

        .footer p {
            font-size: 8px;
            color: #666;
            margin-bottom: 3px;
        }

        .footer .thank-you {
            font-size: 10px;
            font-weight: bold;
            margin-top: 8px;
        }

        .qr-placeholder {
            text-align: center;
            margin: 10px 0;
        }

        .datetime {
            text-align: center;
            font-size: 9px;
            color: #666;
            margin-bottom: 10px;
        }

        .divider {
            border-top: 1px dashed #ccc;
            margin: 8px 0;
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
        <div class="datetime">
            {{ $versement->created_at->format('d/m/Y à H:i') }}
        </div>

        <!-- Informations du motard -->
        <div class="section">
            <div class="section-title">Motard</div>
            <div class="info-row">
                <span class="info-label">Nom:</span>
                <span class="info-value">{{ $versement->motard->user->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">ID:</span>
                <span class="info-value">{{ $versement->motard->numero_identifiant ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Téléphone:</span>
                <span class="info-value">{{ $versement->motard->user->phone ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Informations de la moto -->
        <div class="section">
            <div class="section-title">Moto</div>
            <div class="info-row">
                <span class="info-label">Plaque:</span>
                <span class="info-value">{{ $versement->moto->plaque_immatriculation ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Matricule:</span>
                <span class="info-value">{{ $versement->moto->numero_matricule ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Montant versé -->
        <div class="amount-box">
            <div class="amount-label">MONTANT VERSÉ</div>
            <div class="amount-value">{{ number_format($versement->montant, 0, ',', ' ') }} FC</div>
        </div>

        <!-- Détails du versement -->
        <div class="section">
            <div class="section-title">Détails</div>
            <div class="info-row">
                <span class="info-label">Montant attendu:</span>
                <span class="info-value">{{ number_format($versement->montant_attendu ?? 0, 0, ',', ' ') }} FC</span>
            </div>
            <div class="info-row">
                <span class="info-label">Mode paiement:</span>
                <span class="info-value">{{ ucfirst(str_replace('_', ' ', $versement->mode_paiement ?? 'Cash')) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date versement:</span>
                <span class="info-value">{{ $versement->date_versement?->format('d/m/Y') ?? $versement->created_at->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Statut:</span>
                <span class="info-value">
                    @php
                        $statutClass = match($versement->statut) {
                            'paye' => 'status-paye',
                            'partiel', 'partiellement_paye' => 'status-partiel',
                            default => 'status-retard'
                        };
                        $statutLabel = match($versement->statut) {
                            'paye' => 'PAYÉ',
                            'partiel', 'partiellement_paye' => 'PARTIEL',
                            'en_retard' => 'EN RETARD',
                            default => strtoupper($versement->statut ?? 'N/A')
                        };
                    @endphp
                    <span class="status-badge {{ $statutClass }}">{{ $statutLabel }}</span>
                </span>
            </div>
        </div>

        <!-- Arriérés si existants -->
        @if(($versement->arrieres ?? 0) > 0)
        <div class="arrieres-box">
            <div class="arrieres-label">ARRIÉRÉS</div>
            <div class="arrieres-value">{{ number_format($versement->arrieres, 0, ',', ' ') }} FC</div>
        </div>
        @endif

        <div class="divider"></div>

        <!-- Informations du caissier -->
        <div class="info-row">
            <span class="info-label">Caissier:</span>
            <span class="info-value">{{ $versement->caissier->user->name ?? 'N/A' }}</span>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            <p>Ce reçu fait foi de paiement</p>
            <p>Conservez-le précieusement</p>
            <p class="thank-you">Merci de votre confiance!</p>
            <p style="margin-top: 10px; font-size: 7px;">
                Imprimé le {{ now()->format('d/m/Y à H:i') }}
            </p>
        </div>
    </div>
</body>
</html>

