<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reçu de Paiement</title>
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
            background: #28a745;
            color: #fff;
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
            background: #28a745;
            color: #fff;
            padding: 10px;
            text-align: center;
            margin: 10px 0;
            border-radius: 4px;
        }

        .amount-label {
            font-size: 9px;
            opacity: 0.9;
        }

        .amount-value {
            font-size: 18px;
            font-weight: bold;
            margin-top: 3px;
        }

        .mode-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            background: #f0f0f0;
            color: #333;
        }

        .mode-cash {
            background: #ffc107;
            color: #333;
        }

        .mode-mobile {
            background: #17a2b8;
            color: #fff;
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

        .signature-area {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            margin: 20px 10px 5px 10px;
        }

        .signature-label {
            text-align: center;
            font-size: 8px;
            color: #666;
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
        <div class="receipt-title">REÇU DE PAIEMENT PROPRIÉTAIRE</div>

        <!-- Numéro de reçu -->
        <div class="receipt-number">N° {{ $payment->numero_envoi ?? sprintf('PAY-%s-%06d', date('Ymd'), $payment->id) }}</div>

        <!-- Date et heure -->
        <div class="datetime">
            {{ $payment->date_paiement?->format('d/m/Y') ?? now()->format('d/m/Y') }} à {{ $payment->updated_at->format('H:i') }}
        </div>

        <!-- Informations du propriétaire -->
        <div class="section">
            <div class="section-title">Bénéficiaire</div>
            <div class="info-row">
                <span class="info-label">Nom:</span>
                <span class="info-value">{{ $payment->proprietaire->user->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Téléphone:</span>
                <span class="info-value">{{ $payment->proprietaire->telephone ?? $payment->proprietaire->user->phone ?? 'N/A' }}</span>
            </div>
            @if($payment->numero_compte)
            <div class="info-row">
                <span class="info-label">N° Compte:</span>
                <span class="info-value">{{ $payment->numero_compte }}</span>
            </div>
            @endif
        </div>

        <!-- Montant payé -->
        <div class="amount-box">
            <div class="amount-label">MONTANT PAYÉ</div>
            <div class="amount-value">{{ number_format($payment->total_paye, 0, ',', ' ') }} FC</div>
        </div>

        <!-- Détails du paiement -->
        <div class="section">
            <div class="section-title">Détails</div>
            <div class="info-row">
                <span class="info-label">Montant demandé:</span>
                <span class="info-value">{{ number_format($payment->total_du, 0, ',', ' ') }} FC</span>
            </div>
            <div class="info-row">
                <span class="info-label">Mode paiement:</span>
                <span class="info-value">
                    @php
                        $modeClass = $payment->mode_paiement === 'cash' ? 'mode-cash' : 'mode-mobile';
                        $modeLabel = \App\Models\Payment::getModesPaiement()[$payment->mode_paiement] ?? $payment->mode_paiement;
                    @endphp
                    <span class="mode-badge {{ $modeClass }}">{{ $modeLabel }}</span>
                </span>
            </div>
            @if($payment->reference_paiement)
            <div class="info-row">
                <span class="info-label">Référence:</span>
                <span class="info-value">{{ $payment->reference_paiement }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Date demande:</span>
                <span class="info-value">{{ $payment->date_demande?->format('d/m/Y') ?? 'N/A' }}</span>
            </div>
        </div>

        @if($payment->notes)
        <div class="section">
            <div class="section-title">Notes</div>
            <p style="font-size: 9px;">{{ $payment->notes }}</p>
        </div>
        @endif

        <div class="divider"></div>

        <!-- Informations du collecteur -->
        <div class="info-row">
            <span class="info-label">Traité par:</span>
            <span class="info-value">{{ $payment->traitePar->name ?? 'N/A' }}</span>
        </div>

        <!-- Zone de signature pour paiement cash -->
        @if($payment->mode_paiement === 'cash')
        <div class="signature-area">
            <div class="info-row">
                <span class="info-label" style="width: 50%;">Signature Collecteur:</span>
                <span class="info-label" style="width: 50%; text-align: right;">Signature Bénéficiaire:</span>
            </div>
            <div style="display: table; width: 100%;">
                <div style="display: table-cell; width: 48%;">
                    <div class="signature-line"></div>
                </div>
                <div style="display: table-cell; width: 4%;"></div>
                <div style="display: table-cell; width: 48%;">
                    <div class="signature-line"></div>
                </div>
            </div>
        </div>
        @endif

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

