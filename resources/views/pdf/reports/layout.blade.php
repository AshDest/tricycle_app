<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Rapport' }}</title>
    <style>
        @page {
            margin: 10mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #333;
        }
        .header {
            background: #1a237e;
            color: white;
            padding: 10px 12px;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 14px;
            margin-bottom: 2px;
        }
        .header p {
            font-size: 8px;
            opacity: 0.9;
            margin: 0;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #1a237e;
            border-bottom: 1px solid #1a237e;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th {
            background: #1a237e;
            color: white;
            padding: 4px 5px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
        }
        table td {
            padding: 4px 5px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 8px;
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
            font-size: 7px;
            font-weight: bold;
        }
        .badge-success { background: #c8e6c9; color: #2e7d32; }
        .badge-warning { background: #ffe0b2; color: #ef6c00; }
        .badge-danger { background: #ffcdd2; color: #c62828; }
        .amount {
            font-family: 'DejaVu Sans Mono', monospace;
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background: #e8eaf6 !important;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title ?? 'Rapport' }}</h1>
        <p>{{ $subtitle ?? '' }} | Généré le {{ now()->format('d/m/Y H:i') }} | New Technology Hub Sarl</p>
    </div>

    @yield('content')
</body>
</html>

