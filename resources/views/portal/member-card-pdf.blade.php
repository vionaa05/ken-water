<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Member - {{ $user->customer_id }}</title>
    <style>
        @php
            $level = strtolower($user->loyalty_level ?? 'bronze');
            if ($level === 'gold') {
                $gradFrom    = '#fde68a';
                $gradTo      = '#d97706';
                $textColor   = '#1c1917';
                $badgeBg     = 'rgba(0,0,0,0.22)';
                $badgeBorder = 'rgba(0,0,0,0.30)';
                $deco1Color  = 'rgba(255,255,255,0.12)';
                $deco2Color  = 'rgba(0,0,0,0.10)';
            } elseif ($level === 'silver') {
                $gradFrom    = '#d1d5db';
                $gradTo      = '#6b7280';
                $textColor   = '#1f2937';
                $badgeBg     = 'rgba(0,0,0,0.15)';
                $badgeBorder = 'rgba(0,0,0,0.25)';
                $deco1Color  = 'rgba(255,255,255,0.12)';
                $deco2Color  = 'rgba(0,0,0,0.10)';
            } else {
                /* bronze */
                $gradFrom    = '#e6aa68';
                $gradTo      = '#9b5b14';
                $textColor   = '#ffffff';
                $badgeBg     = 'rgba(255,255,255,0.20)';
                $badgeBorder = 'rgba(255,255,255,0.30)';
                $deco1Color  = 'rgba(255,255,255,0.10)';
                $deco2Color  = 'rgba(0,0,0,0.10)';
            }
        @endphp

        @page {
            margin: 0;
            size: 242.65pt 153.07pt;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            width: 242.65pt;
            height: 153.07pt;
            /* Transparan — tidak ada halaman putih di belakang kartu */
            background: transparent;
            -webkit-print-color-adjust: exact;
        }

        /* ── Card mengisi seluruh halaman ── */
        /* border-radius terpotong terhadap background putih body */
        .card {
            position: absolute;
            top: 0; left: 0;
            width: 242.65pt;
            height: 153.07pt;
            border-radius: 12pt;
            overflow: hidden;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: {{ $textColor }};
        }

        /* Gradient mengisi kartu */
        .card-bg {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, {{ $gradFrom }} 0%, {{ $gradTo }} 100%);
            background-color: {{ $gradFrom }};
        }

        /* Decorative circle kanan atas */
        .deco-tr {
            position: absolute;
            top: -28pt;
            right: -28pt;
            width: 90pt;
            height: 90pt;
            border-radius: 45pt;
            background: {{ $deco1Color }};
        }
        /* Decorative circle kiri bawah */
        .deco-bl {
            position: absolute;
            bottom: -24pt;
            left: -24pt;
            width: 72pt;
            height: 72pt;
            border-radius: 36pt;
            background: {{ $deco2Color }};
        }

        /* Content di atas semua layer */
        .content {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            padding: 11pt 13pt;
        }

        /* Header */
        .hdr { width: 100%; border-collapse: collapse; }
        .hdr td { vertical-align: middle; padding: 0; }

        .icon-img {
            width: 14pt;
            height: 14pt;
            vertical-align: middle;
            margin-right: 4pt;
        }
        .brand-name {
            font-size: 10pt;
            font-weight: bold;
            color: {{ $textColor }};
            letter-spacing: 0.3pt;
            vertical-align: middle;
        }
        .badge {
            display: inline-block;
            background: {{ $badgeBg }};
            border: 0.75pt solid {{ $badgeBorder }};
            border-radius: 12pt;
            padding: 2.5pt 7pt;
            font-size: 5.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1pt;
            color: {{ $textColor }};
        }

        /* Bagian bawah: nama, id, QR */
        .bottom {
            position: absolute;
            bottom: 11pt;
            left: 13pt;
            right: 13pt;
        }
        .brow { width: 100%; border-collapse: collapse; }
        .brow td { vertical-align: bottom; padding: 0; }

        .lbl {
            font-size: 4pt;
            color: {{ $textColor }};
            opacity: 0.75;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            margin-bottom: 2pt;
        }
        .mname {
            font-size: 12pt;
            font-weight: bold;
            color: {{ $textColor }};
            text-transform: uppercase;
            letter-spacing: 1.5pt;
            line-height: 1.1;
        }
        .mid {
            font-family: 'Courier New', Courier, monospace;
            font-size: 6.5pt;
            color: {{ $textColor }};
            opacity: 0.88;
            letter-spacing: 2pt;
            margin-top: 3pt;
        }

        /* QR */
        .qr-td {
            text-align: right;
            vertical-align: bottom;
            width: 46pt;
        }
        .qr-box {
            display: inline-block;
            background: #ffffff;
            border-radius: 3pt;
            padding: 2.5pt;
            width: 43pt;
            height: 43pt;
        }
        .qr-img {
            width: 38pt;
            height: 38pt;
            display: block;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="card-bg"></div>
    <div class="deco-tr"></div>
    <div class="deco-bl"></div>
    <div class="content">

        <table class="hdr" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <img class="icon-img" src="{{ $iconBase64 }}" alt="">
                    <span class="brand-name">Ken Water</span>
                </td>
                <td style="text-align:right;">
                    <span class="badge">{{ strtoupper($user->loyalty_level ?? 'BRONZE') }}</span>
                </td>
            </tr>
        </table>

        <div class="bottom">
            <table class="brow" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <div class="lbl">MEMBER NAME</div>
                        <div class="mname">{{ strtoupper($user->name) }}</div>
                        <div class="mid">{{ chunk_split($user->customer_id, 4, ' ') }}</div>
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
