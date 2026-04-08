<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu Lavage {{ $lavage->numero_lavage }}</title>
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
        }
        .line {
            overflow: hidden;
            margin: 2px 0;
        }
        .line .lbl {
            float: left;
            color: #555;
            font-size: 8px;
        }
        .line .val {
            float: right;
            font-weight: bold;
            font-size: 8px;
            max-width: 55%;
            text-align: right;
            word-wrap: break-word;
        }
        .clear { clear: both; }
        .total-box {
            background: #d0e8ff;
            text-align: center;
            padding: 5px;
            margin: 5px 0;
        }
        .total-box small {
            font-size: 7px;
            color: #555;
            display: block;
        }
        .total-box strong {
            font-size: 13px;
            color: #0056b3;
        }
        .split {
            overflow: hidden;
            background: #f5f5f5;
            margin: 4px 0;
            padding: 3px 0;
        }
        .split .col {
            float: left;
            width: 50%;
            text-align: center;
        }
        .split .col small {
            font-size: 7px;
            color: #666;
            display: block;
        }
        .split .col span {
            font-size: 9px;
            font-weight: bold;
        }
        .green { color: #228B22; }
        .orange { color: #FF8C00; }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 7px;
            background: #666;
            color: #fff;
        }
        .badge-info { background: #17a2b8; }
        .footer {
            border-top: 1px dashed #000;
            text-align: center;
            padding-top: 4px;
            margin-top: 5px;
            font-size: 7px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SERVICE DE LAVAGE</h1>
        <p>LATEM Sarl</p>
        <p>Kinshasa, RDC</p>
    </div>

    <div class="numero">{{ $lavage->numero_lavage }}</div>

    <div class="section-title">Moto</div>
    <div class="line">
        <span class="lbl">Type:</span>
        <span class="val">
            @if($lavage->is_externe)
            <span class="badge">EXT</span>
            @else
            <span class="badge badge-info">SYS</span>
            @endif
        </span>
        <div class="clear"></div>
    </div>
    <div class="line">
        <span class="lbl">Plaque:</span>
        <span class="val">{{ $lavage->plaque }}</span>
        <div class="clear"></div>
    </div>
    @if(!$lavage->is_externe && $lavage->moto && $lavage->moto->proprietaire)
    <div class="line">
        <span class="lbl">Proprio:</span>
        <span class="val">{{ Str::limit($lavage->moto->proprietaire->user->name ?? 'N/A', 15) }}</span>
        <div class="clear"></div>
    </div>
    @elseif($lavage->is_externe && $lavage->proprietaire_externe)
    <div class="line">
        <span class="lbl">Proprio:</span>
        <span class="val">{{ Str::limit($lavage->proprietaire_externe, 15) }}</span>
        <div class="clear"></div>
    </div>
    @endif

    <div class="section-title">Détails</div>
    <div class="line">
        <span class="lbl">Type lavage:</span>
        <span class="val">{{ $lavage->type_lavage_label ?? 'Standard' }}</span>
        <div class="clear"></div>
    </div>
    <div class="line">
        <span class="lbl">Date:</span>
        <span class="val">{{ $lavage->date_lavage->format('d/m/Y H:i') }}</span>
        <div class="clear"></div>
    </div>
    <div class="line">
        <span class="lbl">Paiement:</span>
        <span class="val">{{ $lavage->mode_paiement === 'cash' ? 'Cash' : 'Mobile' }}</span>
        <div class="clear"></div>
    </div>
    @if($lavage->remise > 0)
    <div class="line">
        <span class="lbl">Remise:</span>
        <span class="val">-{{ number_format($lavage->remise, 0, ',', ' ') }} FC</span>
        <div class="clear"></div>
    </div>
    @endif

    <div class="total-box">
        <small>MONTANT PAYÉ</small>
        <strong>{{ number_format($lavage->prix_final, 0, ',', ' ') }} FC</strong>
    </div>

    @if(!$lavage->is_externe)
    <div class="split">
        <div class="col">
            <small>Lavage (80%)</small>
            <span class="green">{{ number_format($lavage->part_cleaner, 0, ',', ' ') }} FC</span>
        </div>
        <div class="col">
            <small>OKAMI (20%)</small>
            <span class="orange">{{ number_format($lavage->part_okami, 0, ',', ' ') }} FC</span>
        </div>
        <div class="clear"></div>
    </div>
    @endif

    <div class="line">
        <span class="lbl">Laveur:</span>
        <span class="val">{{ Str::limit($lavage->cleaner?->user?->name ?? 'N/A', 15) }}</span>
        <div class="clear"></div>
    </div>

    <div class="footer">
        <p>Merci pour votre confiance!</p>
        <p>{{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>

