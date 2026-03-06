<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu Lavage {{ $lavage->numero_lavage }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
            padding: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            width: 80mm;
            margin: 0 auto;
            padding: 0;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            padding: 5mm;
        }
        .container {
            width: 100%;
            text-align: center;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #333;
        }
        .header h1 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .header p {
            font-size: 9px;
            color: #666;
            margin: 1px 0;
        }
        .numero-lavage {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            padding: 6px;
            background: #f5f5f5;
            margin-bottom: 8px;
        }
        .section {
            margin-bottom: 8px;
            text-align: center;
        }
        .section-title {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 4px;
            color: #333;
            text-transform: uppercase;
            text-align: center;
        }
        .info {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
        }
        .info td {
            padding: 3px 2px;
            vertical-align: top;
            font-size: 9px;
        }
        .info .label {
            color: #666;
            width: 40%;
            text-align: left;
        }
        .info .value {
            font-weight: 500;
            text-align: right;
            width: 60%;
        }
        .montant-box {
            background: #e8f4fd;
            padding: 8px;
            text-align: center;
            margin: 8px 0;
            border-radius: 4px;
        }
        .montant-box .montant-label {
            font-size: 9px;
            color: #666;
            display: block;
        }
        .montant-box .montant {
            font-size: 16px;
            font-weight: bold;
            color: #0d6efd;
        }
        .repartition {
            width: 100%;
            margin: 8px 0;
            border-collapse: collapse;
        }
        .repartition td {
            width: 50%;
            text-align: center;
            padding: 6px 2px;
            background: #f8f9fa;
            vertical-align: top;
            font-size: 9px;
        }
        .repartition td:first-child {
            border-right: 1px solid #ddd;
        }
        .repartition .rep-label {
            font-size: 8px;
            color: #666;
            display: block;
        }
        .repartition .rep-value {
            font-size: 11px;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-info {
            background: #0dcaf0;
            color: white;
        }
        .badge-secondary {
            background: #6c757d;
            color: white;
        }
        .footer {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #333;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        .footer p {
            margin-bottom: 2px;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <h1>SERVICE DE LAVAGE</h1>
        <p>New Technology Hub Sarl</p>
        <p>Kinshasa, RDC</p>
    </div>

    <!-- Numéro de lavage -->
    <div class="numero-lavage">
        {{ $lavage->numero_lavage }}
    </div>

    <!-- Informations moto -->
    <div class="section">
        <div class="section-title">Moto</div>
        <table class="info">
            <tr>
                <td class="label">Type</td>
                <td class="value">
                    @if($lavage->is_externe)
                    <span class="badge badge-secondary">Externe</span>
                    @else
                    <span class="badge badge-info">Système</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Plaque</td>
                <td class="value">{{ $lavage->plaque }}</td>
            </tr>
            @if(!$lavage->is_externe && $lavage->moto)
            <tr>
                <td class="label">Propriétaire</td>
                <td class="value">{{ $lavage->moto->proprietaire?->user?->name ?? 'N/A' }}</td>
            </tr>
            @elseif($lavage->is_externe && $lavage->proprietaire_externe)
            <tr>
                <td class="label">Propriétaire</td>
                <td class="value">{{ $lavage->proprietaire_externe }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Détails lavage -->
    <div class="section">
        <div class="section-title">Lavage</div>
        <table class="info">
            <tr>
                <td class="label">Type</td>
                <td class="value">{{ $lavage->type_lavage_label }}</td>
            </tr>
            <tr>
                <td class="label">Date</td>
                <td class="value">{{ $lavage->date_lavage->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Paiement</td>
                <td class="value">{{ $lavage->mode_paiement === 'cash' ? 'Cash' : 'Mobile Money' }}</td>
            </tr>
            @if($lavage->remise > 0)
            <tr>
                <td class="label">Remise</td>
                <td class="value">-{{ number_format($lavage->remise, 0, ',', ' ') }} FC</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Montant -->
    <div class="montant-box">
        <span class="montant-label">MONTANT PAYÉ</span>
        <div class="montant">{{ number_format($lavage->prix_final, 0, ',', ' ') }} FC</div>
    </div>

    <!-- Répartition (si moto du système) -->
    @if(!$lavage->is_externe)
    <table class="repartition">
        <tr>
            <td>
                <span class="rep-label">Service Lavage (80%)</span>
                <span class="rep-value" style="color: #198754;">{{ number_format($lavage->part_cleaner, 0, ',', ' ') }} FC</span>
            </td>
            <td>
                <span class="rep-label">OKAMI (20%)</span>
                <span class="rep-value" style="color: #ffc107;">{{ number_format($lavage->part_okami, 0, ',', ' ') }} FC</span>
            </td>
        </tr>
    </table>
    @endif

    <!-- Laveur -->
    <div class="section">
        <table class="info">
            <tr>
                <td class="label">Laveur</td>
                <td class="value">{{ $lavage->cleaner?->user?->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">ID Laveur</td>
                <td class="value">{{ $lavage->cleaner?->identifiant ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Merci pour votre confiance!</p>
        <p>{{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</div>
</body>
</html>

