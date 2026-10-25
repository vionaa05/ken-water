<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Member - {{ $user->customer_id }}</title>
    <style>
        @php
            $level = strtolower($user->loyalty_level ?? 'bronze');
            if ($level === 'gold') {
                $gradFrom    = '#78350f';
                $gradVia     = '#d97706';
                $gradTo      = '#f59e0b';
                $emoji       = '🥇';
            } elseif ($level === 'silver') {
                $gradFrom    = '#1e3a5f';
                $gradVia     = '#334155';
                $gradTo      = '#64748b';
                $emoji       = '🥈';
            } else {
                /* bronze / default */
                $gradFrom    = '#0284c7';
                $gradVia     = '#0369a1';
                $gradTo      = '#0c4a6e';
                $emoji       = '🥉';
            }
            $status = strtolower($user->status ?? 'aktif');
            $isActive = ($status === 'aktif');
        @endphp

        @page {
            margin: 0;
            size: 242.65pt 153.07pt;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            width: 242.65pt;
            height: 153.07pt;
            background: transparent;
            -webkit-print-color-adjust: exact;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }

        .card {
            position: absolute;
            top: 0; left: 0;
            width: 242.65pt;
            height: 153.07pt;
            border-radius: 14pt;
            overflow: hidden;
            color: #ffffff;
            background-color: {{ $gradFrom }};
            background: linear-gradient(135deg, {{ $gradFrom }} 0%, {{ $gradVia }} 50%, {{ $gradTo }} 100%);
        }

        /* Decorative circles */
        .deco-tr {
            position: absolute;
            top: -20pt;
            right: -20pt;
            width: 85pt;
            height: 85pt;
            border-radius: 43pt;
            background: rgba(255, 255, 255, 0.08);
        }
        .deco-br {
            position: absolute;
            bottom: -22pt;
            right: 20pt;
            width: 60pt;
            height: 60pt;
            border-radius: 30pt;
            background: rgba(255, 255, 255, 0.06);
        }

        .content {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            padding: 12pt 14pt;
        }

        /* Header */
        .hdr { width: 100%; border-collapse: collapse; }
        .hdr td { vertical-align: middle; padding: 0; }

        .icon-img {
            width: 12pt;
            height: 12pt;
            vertical-align: middle;
            margin-right: 4pt;
            opacity: 0.85;
        }
        .brand-name {
            font-size: 9pt;
            color: rgba(255, 255, 255, 0.85);
            letter-spacing: 0.8pt;
            vertical-align: middle;
        }
        .badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.20);
            border-radius: 12pt;
            padding: 2.5pt 8pt;
            font-size: 6.5pt;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 0.5pt;
        }

        /* Bottom Section */
        .bottom {
            position: absolute;
            bottom: 11pt;
            left: 14pt;
            right: 14pt;
        }
        .brow { width: 100%; border-collapse: collapse; }
        .brow td { vertical-align: bottom; padding: 0; }

        .mname {
            font-size: 11pt;
            font-weight: bold;
            color: #ffffff;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 140pt;
        }
        .mid {
            font-family: 'Courier New', Courier, monospace;
            font-size: 7.5pt;
            color: rgba(255, 255, 255, 0.75);
            letter-spacing: 1.2pt;
            margin-top: 2pt;
        }
        .points-row {
            margin-top: 5pt;
            font-size: 9pt;
            font-weight: bold;
            color: #ffffff;
        }
        .status-pill {
            display: inline-block;
            margin-top: 3pt;
            padding: 1.5pt 5pt;
            border-radius: 4pt;
            font-size: 6pt;
            font-weight: bold;
            background: {{ $isActive ? 'rgba(52, 211, 153, 0.28)' : 'rgba(248, 113, 113, 0.28)' }};
            color: {{ $isActive ? '#a7f3d0' : '#fecaca' }};
        }

        /* QR */
        .qr-td {
            text-align: right;
            vertical-align: bottom;
            width: 48pt;
        }
        .qr-box {
            display: inline-block;
            background: #ffffff;
            border-radius: 6pt;
            padding: 3pt;
            width: 45pt;
            height: 45pt;
        }
        .qr-img {
            width: 39pt;
            height: 39pt;
            display: block;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="deco-tr"></div>
    <div class="deco-br"></div>
    <div class="content">

        <table class="hdr" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <img class="icon-img" src="{{ $iconBase64 }}" alt="">
                    <span class="brand-name">Ken Water</span>
                </td>
                <td style="text-align:right;">
                    <span class="badge">{{ $emoji }} {{ strtoupper($user->loyalty_level ?? 'BRONZE') }}</span>
                </td>
            </tr>
        </table>

        <div class="bottom">
            <table class="brow" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <div class="mname">{{ $user->name }}</div>
                        <div class="mid">{{ $user->customer_id }}</div>
                        <div class="points-row">&#9733; {{ (int)($user->points ?? 0) }} Poin</div>
                        <div>
                            <span class="status-pill">{{ $isActive ? '● Aktif' : '● Tidak Aktif' }}</span>
                        </div>
                    </td>
                    <td class="qr-td">
                        <div class="qr-box">
                            <img class="qr-img" src="{{ $qrBase64 }}" alt="QR">
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</div>

</body>
</html>
