<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu KWADO {{ $service->numero_service }}</title>
    <style>
        @page {
            size: 72mm auto;
            margin: 0;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            margin: 0;
            padding: 2mm;
            width: 72mm;
        }
        .center { text-align: center; }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 4px;
            margin-bottom: 4px;
        }
        .header h1 {
            font-size: 12px;
            margin: 0 0 2px 0;
        }
        .header p {
            font-size: 8px;
            margin: 1px 0;
            color: #444;
        }
        .numero {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            background: #eee;
            padding: 4px;
            margin-bottom: 4px;
        }
        .section-title {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            border-bottom: 1px solid #ccc;
            margin: 4px 0 2px 0;
            padding-bottom: 2px;
            color: #666;
        }
        table.info {
            width: 100%;
            border-collapse: collapse;
        }
        table.info td {
            padding: 2px 0;
            font-size: 8px;
            vertical-align: top;
        }
        table.info td.label {
            color: #666;
            width: 40%;
        }
        table.info td.value {
            text-align: right;
            font-weight: 500;
        }
        .amount-box {
            text-align: center;
            background: #222;
            color: #fff;
            padding: 6px;
            margin: 6px 0;
        }
        .amount-box .label { font-size: 7px; opacity: 0.8; }
        .amount-box .value { font-size: 14px; font-weight: bold; }
        .footer {
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 4px;
            margin-top: 6px;
        }
        .footer p {
            font-size: 7px;
            color: #666;
            margin: 1px 0;
        }
        .footer .merci {
            font-size: 9px;
            font-weight: bold;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>KWADO SERVICE</h1>
        <p>Réparation de Pneus</p>
        <p>New Technology Hub Sarl - Kinshasa</p>
    </div>

    <div class="numero">REÇU N° {{ $service->numero_service }}</div>
    <div class="center" style="font-size: 8px; color: #666; margin-bottom: 4px;">
        {{ $service->date_service?->format('d/m/Y à H:i') }}
    </div>

    <!-- Véhicule -->
    <div class="section-title">Véhicule</div>
    <table class="info">
        <tr>
            <td class="label">Plaque</td>
            <td class="value">{{ $service->plaque }}</td>
        </tr>
        @if($service->proprietaire_nom)
        <tr>
            <td class="label">Propriétaire</td>
            <td class="value">{{ $service->proprietaire_nom }}</td>
        </tr>
        @endif
        @if($service->is_externe && $service->telephone_externe)
        <tr>
            <td class="label">Téléphone</td>
            <td class="value">{{ $service->telephone_externe }}</td>
        </tr>
        @endif
    </table>

    <!-- Service -->
    <div class="section-title">Service effectué</div>
    <table class="info">
        <tr>
            <td class="label">Type</td>
            <td class="value">{{ $service->type_service_label }}</td>
        </tr>
        @if($service->position_pneu_label)
        <tr>
            <td class="label">Position</td>
            <td class="value">{{ $service->position_pneu_label }}</td>
        </tr>
        @endif
        @if($service->description_service)
        <tr>
            <td class="label">Détail</td>
            <td class="value">{{ $service->description_service }}</td>
        </tr>
        @endif
    </table>

    <!-- Montant -->
    <div class="amount-box">
        <div class="label">MONTANT ENCAISSÉ</div>
        <div class="value">{{ number_format($service->montant_encaisse, 0, ',', ' ') }} FC</div>
    </div>

    <!-- Détails financiers -->
    <div class="section-title">Détails</div>
    <table class="info">
        <tr>
            <td class="label">Prix service</td>
            <td class="value">{{ number_format($service->prix, 0, ',', ' ') }} FC</td>
        </tr>
        @if(($service->cout_pieces ?? 0) > 0)
        <tr>
            <td class="label">Coût pièces</td>
            <td class="value">{{ number_format($service->cout_pieces, 0, ',', ' ') }} FC</td>
        </tr>
        @endif
        <tr>
            <td class="label">Mode</td>
            <td class="value">{{ $service->mode_paiement === 'cash' ? 'Cash' : 'Mobile Money' }}</td>
        </tr>
        <tr>
            <td class="label">Statut</td>
            <td class="value" style="font-weight: bold; color: {{ $service->statut_paiement === 'payé' ? '#198754' : '#dc3545' }};">
                {{ strtoupper($service->statut_paiement) }}
            </td>
        </tr>
    </table>

    <!-- Opérateur -->
    <div class="section-title">Opérateur</div>
    <table class="info">
        <tr>
            <td class="label">Technicien</td>
            <td class="value">{{ $service->cleaner?->user?->name ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>Ce reçu fait foi de paiement</p>
        <p>Conservez-le précieusement</p>
        <p class="merci">Merci pour votre confiance!</p>
        <p style="margin-top: 4px;">{{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>

