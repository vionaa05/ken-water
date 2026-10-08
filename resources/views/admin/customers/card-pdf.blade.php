<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Member - {{ ($customer ?? $user)->customer_id }}</title>
    <style>
        @page {
            margin: 0;
            size: 241.89pt 153.07pt; /* Ukuran standar kartu CR-80: 85.6mm x 54mm */
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #ffffff;
            -webkit-print-color-adjust: exact;
        }
        @php
            $c = $customer ?? $user;
            $level = strtolower($c->loyalty_level ?? 'bronze');
            if ($level === 'gold') {
                $bgColor = '#1e293b';
                $accentColor = '#f59e0b';
                $badgeBg = '#d97706';
                $badgeText = '#ffffff';
                $borderColor = '#f59e0b';
            } elseif ($level === 'silver') {
                $bgColor = '#334155';
                $accentColor = '#cbd5e1';
                $badgeBg = '#64748b';
                $badgeText = '#ffffff';
                $borderColor = '#94a3b8';
            } else {
                $bgColor = '#3b2514';
                $accentColor = '#f59e0b';
                $badgeBg = '#92400e';
                $badgeText = '#ffffff';
                $borderColor = '#b45309';
            }
        @endphp
        .card-container {
            width: 241.89pt;
            height: 153.07pt;
            background-color: {{ $bgColor }};
            color: #ffffff;
            padding: 10pt 12pt;
            position: relative;
            border: 2pt solid {{ $borderColor }};
            overflow: hidden;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8pt;
        }
        .brand-title {
            font-size: 11pt;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 0.5pt;
        }
        .brand-sub {
            font-size: 5.5pt;
            color: {{ $accentColor }};
            text-transform: uppercase;
            letter-spacing: 0.8pt;
            margin-top: 1pt;
        }
        .badge {
            background-color: {{ $badgeBg }};
            color: {{ $badgeText }};
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1pt;
            padding: 2.5pt 7pt;
            border-radius: 8pt;
            border: 1pt solid {{ $accentColor }};
            display: inline-block;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4pt;
        }
        .label-text {
            font-size: 5pt;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.8pt;
            margin-bottom: 1.5pt;
        }
        .name-text {
            font-size: 9.5pt;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 0.5pt;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 140pt;
        }
        .id-text {
            font-family: 'Courier New', Courier, monospace;
            font-size: 8.5pt;
            font-weight: bold;
            color: {{ $accentColor }};
            letter-spacing: 1.2pt;
            margin-top: 2pt;
        }
        .info-sub {
            font-size: 5.5pt;
            color: #cbd5e1;
            margin-top: 3pt;
        }
        .right-box {
            text-align: right;
            vertical-align: bottom;
        }
        .points-box {
            background-color: rgba(255, 255, 255, 0.08);
            border: 0.75pt solid {{ $accentColor }};
            border-radius: 4pt;
            padding: 3pt 5pt;
            text-align: center;
            display: inline-block;
            min-width: 48pt;
        }
        .points-val {
            font-size: 11pt;
            font-weight: bold;
            color: #ffffff;
            line-height: 1;
        }
        .points-lbl {
            font-size: 4.5pt;
            color: {{ $accentColor }};
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            margin-top: 1pt;
        }
        .footer-bar {
            position: absolute;
            bottom: 4pt;
            left: 12pt;
            right: 12pt;
            font-size: 4.5pt;
            color: #94a3b8;
            text-align: center;
            border-top: 0.5pt solid rgba(255, 255, 255, 0.15);
            padding-top: 2pt;
        }
    </style>
</head>
<body>
    <div class="card-container">
        <table class="header-table">
            <tr>
                <td style="vertical-align: middle;">
                    <div class="brand-title">💧 KEN WATER</div>
                    <div class="brand-sub">Depot Air Minum Isi Ulang</div>
                </td>
                <td style="text-align: right; vertical-align: middle;">
                    <div class="badge">{{ $c->loyalty_level ?? 'BRONZE' }}</div>
                </td>
            </tr>
        </table>

        <table class="content-table">
            <tr>
                <td style="vertical-align: top;">
                    <div class="label-text">Nama Pelanggan</div>
                    <div class="name-text">{{ strtoupper($c->name) }}</div>
                    <div class="id-text">{{ $c->customer_id ?? 'KW-XXXXXX' }}</div>
                    <div class="info-sub">
                        {{ $c->phone ? 'WA: ' . $c->phone : '' }} 
                        @if($c->registered_at)
                            • Sejak {{ \Carbon\Carbon::parse($c->registered_at)->format('m/Y') }}
                        @endif
                    </div>
                </td>
                <td class="right-box">
                    <div class="points-box">
                        <div class="points-val">{{ number_format($c->points ?? 0) }}</div>
                        <div class="points-lbl">Poin Reward</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer-bar">
            Tunjukkan kartu ini saat transaksi di depot Ken Water • Sistem CRM Ken Water
        </div>
    </div>
</body>
</html>
