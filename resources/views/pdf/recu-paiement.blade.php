<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reçu de Paiement</title>
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
            background: #28a745;
            color: #fff;
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
            background: #28a745;
            color: #fff;
            padding: 8px;
            margin: 8px 0;
            text-align: center;
        }
        .amount-label {
            font-size: 8px;
            opacity: 0.9;
        }
        .amount-value {
            font-size: 16px;
            font-weight: bold;
        }
        .mode-badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
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
        <div class="receipt-title">REÇU DE PAIEMENT</div>

        <!-- Numéro de reçu -->
        <div class="receipt-number">N° {{ $payment->numero_envoi ?? sprintf('PAY-%s-%06d', date('Ymd'), $payment->id) }}</div>

        <!-- Date et heure -->
        <div class="datetime">{{ $payment->date_paiement?->format('d/m/Y') ?? now()->format('d/m/Y') }} à {{ $payment->updated_at->format('H:i') }}</div>

        <!-- Informations du propriétaire -->
        <div class="section">
            <div class="section-title">Bénéficiaire</div>
            <table class="info">
                <tr>
                    <td class="label">Nom</td>
                    <td class="value">{{ $payment->proprietaire->user->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Téléphone</td>
                    <td class="value">{{ $payment->proprietaire->telephone ?? $payment->proprietaire->user->phone ?? 'N/A' }}</td>
                </tr>
                @if($payment->numero_compte)
                <tr>
                    <td class="label">N° Compte</td>
                    <td class="value">{{ $payment->numero_compte }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Montant payé -->
        <div class="amount-box">
            <div class="amount-label">MONTANT PAYÉ</div>
            <div class="amount-value">{{ number_format($payment->total_paye ?? 0, 0, ',', ' ') }} FC</div>
        </div>

        <!-- Détails du paiement -->
        <div class="section">
            <div class="section-title">Détails</div>
            <table class="info">
                @if($payment->montant_usd)
                <tr>
                    <td class="label">Montant (USD)</td>
                    <td class="value">${{ number_format($payment->montant_usd, 2, ',', ' ') }} USD</td>
                </tr>
                <tr>
                    <td class="label">Taux de conversion</td>
                    <td class="value">1 USD = {{ number_format($payment->taux_conversion, 2, ',', ' ') }} FC</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Montant demandé</td>
                    <td class="value">{{ number_format($payment->total_du ?? 0, 0, ',', ' ') }} FC</td>
                </tr>
                <tr>
                    <td class="label">Mode</td>
                    <td class="value">
                        @php
                            $modeClass = $payment->mode_paiement === 'cash' ? 'mode-cash' : 'mode-mobile';
                            $modes = ['cash' => 'Cash', 'mobile_money' => 'Mobile Money', 'mpesa' => 'M-Pesa', 'airtel_money' => 'Airtel Money', 'orange_money' => 'Orange Money', 'virement' => 'Virement'];
                            $modeLabel = $modes[$payment->mode_paiement] ?? ucfirst($payment->mode_paiement ?? 'N/A');
                        @endphp
                        <span class="mode-badge {{ $modeClass }}">{{ $modeLabel }}</span>
                    </td>
                </tr>
                @if($payment->reference_paiement)
                <tr>
                    <td class="label">Référence</td>
                    <td class="value">{{ $payment->reference_paiement }}</td>
                </tr>
                @endif
                @if($payment->numero_envoi)
                <tr>
                    <td class="label">N° Envoi</td>
                    <td class="value">{{ $payment->numero_envoi }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Période couverte -->
        @if($payment->periode_debut || $payment->periode_fin)
        <div class="section">
            <div class="section-title">Période</div>
            <table class="info">
                <tr>
                    <td class="label">Du</td>
                    <td class="value">{{ $payment->periode_debut?->format('d/m/Y') ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Au</td>
                    <td class="value">{{ $payment->periode_fin?->format('d/m/Y') ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
        @endif

        <!-- Traité par -->
        <div class="section">
            <table class="info">
                <tr>
                    <td class="label">Traité par</td>
                    <td class="value">{{ $payment->traitePar->name ?? 'Système' }}</td>
                </tr>
                <tr>
                    <td class="label">Date paiement</td>
                    <td class="value">{{ $payment->date_paiement?->format('d/m/Y') ?? 'N/A' }}</td>
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

