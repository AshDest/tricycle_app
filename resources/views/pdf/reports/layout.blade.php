<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Rapport' }}</title>
    <style>
        @page {
            margin: 15mm 10mm 20mm 10mm;
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
        }
        .header {
            background: #1a237e;
            color: white;
            padding: 12px 15px;
            margin: -15mm -10mm 15px -10mm;
            width: calc(100% + 20mm);
        }
        .header h1 {
            font-size: 16px;
            margin-bottom: 3px;
        }
        .header p {
            font-size: 9px;
            opacity: 0.9;
            margin: 0;
        }
        .company-info {
            float: right;
            text-align: right;
            font-size: 8px;
            margin-top: -30px;
        }
        .company-info strong {
            font-size: 10px;
        }
        .content {
            padding: 0;
        }
        .stats-grid {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .stats-grid td {
            width: 25%;
            padding: 8px;
            text-align: center;
            border: 1px solid #e0e0e0;
            background: #f5f5f5;
        }
        .stat-box .value {
            font-size: 14px;
            font-weight: bold;
            color: #1a237e;
        }
        .stat-box .label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .stat-success { background: #e8f5e9 !important; }
        .stat-success .value { color: #2e7d32; }
        .stat-warning { background: #fff3e0 !important; }
        .stat-warning .value { color: #ef6c00; }
        .stat-danger { background: #ffebee !important; }
        .stat-danger .value { color: #c62828; }
        .stat-info { background: #e3f2fd !important; }
        .stat-info .value { color: #1565c0; }

        .section {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #1a237e;
            border-bottom: 2px solid #1a237e;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table th {
            background: #1a237e;
            color: white;
            padding: 5px 4px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
        }
        table td {
            padding: 4px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 9px;
        }
        table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success { background: #c8e6c9; color: #2e7d32; }
        .badge-warning { background: #ffe0b2; color: #ef6c00; }
        .badge-danger { background: #ffcdd2; color: #c62828; }
        .badge-info { background: #bbdefb; color: #1565c0; }

        .amount {
            font-family: 'DejaVu Sans Mono', monospace;
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background: #e8eaf6 !important;
        }
        .footer-note {
            margin-top: 15px;
            padding: 8px;
            background: #f5f5f5;
            font-size: 8px;
            border-radius: 3px;
        }
        .page-footer {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            height: 12mm;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #e0e0e0;
            padding-top: 3mm;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title ?? 'Rapport' }}</h1>
        <p>{{ $subtitle ?? '' }} | Généré le {{ now()->format('d/m/Y à H:i') }}</p>
        <div class="company-info">
            <strong>New Technology Hub Sarl</strong><br>
            Kinshasa, RDC
        </div>
    </div>

    <div class="content">
        @yield('content')
    </div>

    <div class="page-footer">
        © {{ date('Y') }} New Technology Hub Sarl - Système de Gestion des Motos-Tricycles
    </div>
</body>
</html>

