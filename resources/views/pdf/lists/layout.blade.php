<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Liste' }}</title>
    <style>
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
            padding: 15px 20px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 16px;
            margin-bottom: 3px;
        }
        .header p {
            font-size: 10px;
            opacity: 0.9;
        }
        .company-info {
            float: right;
            text-align: right;
            font-size: 9px;
        }
        .company-info strong {
            font-size: 11px;
        }
        .content {
            padding: 0 15px;
        }
        .stats-row {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stat-item {
            display: table-cell;
            padding: 8px;
            text-align: center;
            border: 1px solid #e0e0e0;
            background: #f8f9fa;
        }
        .stat-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #1a237e;
        }
        .stat-item .label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
        }
        .filters-info {
            background: #f0f0f0;
            padding: 8px 12px;
            margin-bottom: 12px;
            font-size: 9px;
            border-radius: 3px;
        }
        .filters-info strong {
            color: #1a237e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }
        table th {
            background: #1a237e;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            font-weight: bold;
        }
        table td {
            padding: 5px 4px;
            border-bottom: 1px solid #e0e0e0;
        }
        table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 2px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }
        .amount {
            font-family: 'DejaVu Sans Mono', monospace;
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background: #e8eaf6 !important;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 8px 15px;
            background: #f5f5f5;
            border-top: 1px solid #ddd;
            font-size: 8px;
            color: #666;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .no-break {
            page-break-inside: avoid;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header clearfix">
        <div class="company-info">
            <strong>New Technology Hub Sarl</strong><br>
            Gestion Motos-Tricycles<br>
            Kinshasa, RDC
        </div>
        <h1>{{ $title ?? 'Liste' }}</h1>
        @isset($subtitle)
            <p>{{ $subtitle }}</p>
        @endisset
        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="content">
        @yield('content')
    </div>

    <div class="footer">
        <span>© {{ date('Y') }} New Technology Hub Sarl - Système de Gestion des Motos-Tricycles</span>
    </div>
</body>
</html>

