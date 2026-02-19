<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Rapport' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #1a237e 0%, #3949ab 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 11px;
            opacity: 0.9;
        }
        .company-info {
            float: right;
            text-align: right;
            font-size: 10px;
        }
        .company-info strong {
            font-size: 12px;
        }
        .content {
            padding: 0 20px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #e0e0e0;
            background: #f5f5f5;
        }
        .stat-box .value {
            font-size: 18px;
            font-weight: bold;
            color: #1a237e;
        }
        .stat-box .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }
        .stat-success { background: #e8f5e9; }
        .stat-success .value { color: #2e7d32; }
        .stat-warning { background: #fff3e0; }
        .stat-warning .value { color: #ef6c00; }
        .stat-danger { background: #ffebee; }
        .stat-danger .value { color: #c62828; }
        .stat-info { background: #e3f2fd; }
        .stat-info .value { color: #1565c0; }

        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1a237e;
            border-bottom: 2px solid #1a237e;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th {
            background: #1a237e;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        table td {
            padding: 6px 5px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 10px;
        }
        table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success { background: #c8e6c9; color: #2e7d32; }
        .badge-warning { background: #ffe0b2; color: #ef6c00; }
        .badge-danger { background: #ffcdd2; color: #c62828; }
        .badge-info { background: #bbdefb; color: #1565c0; }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 20px;
            background: #f5f5f5;
            border-top: 1px solid #e0e0e0;
            font-size: 9px;
            color: #666;
        }
        .footer .page-number {
            float: right;
        }
        .amount {
            font-family: 'DejaVu Sans Mono', monospace;
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background: #e8eaf6 !important;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
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
        <h1>{{ $title ?? 'Rapport' }}</h1>
        <p>{{ $subtitle ?? '' }}</p>
        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="content">
        @yield('content')
    </div>

    <div class="footer">
        <span>© {{ date('Y') }} New Technology Hub Sarl - Système de Gestion des Motos-Tricycles</span>
        <span class="page-number">Page <script type="text/php">
            if (isset($pdf)) {
                $pdf->page_text(520, 820, "Page {PAGE_NUM} / {PAGE_COUNT}", null, 9, array(0,0,0));
            }
        </script></span>
    </div>
</body>
</html>

