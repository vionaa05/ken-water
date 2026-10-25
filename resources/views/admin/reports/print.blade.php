<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - {{ $periodLabel }} - Ken Water</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #000;
            background: #fff;
            padding: 24px 32px;
        }
        .no-print-bar {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            font-size: 9pt;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-print {
            background: #0f172a;
            color: #fff;
            border: 1px solid #0f172a;
        }
        .btn-close {
            background: #fff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header p {
            font-size: 9pt;
            color: #333;
            margin-top: 2px;
        }
        .doc-title {
            text-align: center;
            margin-bottom: 16px;
        }
        .doc-title h2 {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .doc-title p {
            font-size: 9.5pt;
            font-weight: bold;
            color: #222;
            margin-top: 3px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
        }
        .summary-table th {
            background-color: #f4f4f5;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .summary-table td {
            font-size: 9.5pt;
            font-weight: bold;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px 7px;
            font-size: 9pt;
        }
        .data-table th {
            background-color: #f4f4f5;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5pt;
        }
        .data-table tfoot td {
            background-color: #f4f4f5;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .signature-wrap {
            display: flex;
            justify-content: flex-end;
            margin-top: 24px;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 200px;
            text-align: center;
            font-size: 9pt;
        }
        .signature-space {
            height: 60px;
        }
        .signature-line {
            border-top: 1px solid #000;
            font-weight: bold;
            padding-top: 4px;
        }
        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                padding: 0;
            }
            @page {
                size: A4 portrait;
                margin: 1.2cm;
            }
            .data-table thead {
                display: table-header-group;
            }
            .data-table tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body onload="window.print()">

    {{-- Bar aksi layar (otomatis hilang saat cetak) --}}
    <div class="no-print-bar">
        <div>
            <strong style="font-size: 11pt; color: #0f172a;">Pratinjau Format Cetak Laporan Penjualan</strong>
            <p style="font-size: 8.5pt; color: #64748b; margin-top: 2px;">Tampilan ini khusus dokumen tabel resmi untuk dicetak atau disimpan sebagai PDF.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn-action btn-print">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Sekarang
            </button>
            <button onclick="window.close()" class="btn-action btn-close">
                Tutup
            </button>
        </div>
    </div>

    {{-- Kop Laporan Resmi --}}
    <div class="header">
        <h1>DEPOT AIR MINUM KEN WATER</h1>
        <p>Layanan Isi Ulang & Pengantaran Air Galon Higienis Berkualitas</p>
        <p style="font-size: 8pt; color: #666; margin-top: 3px;">
            Dicetak: {{ now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }} WIB &nbsp;|&nbsp; Oleh: {{ auth()->user()->name }}
        </p>
    </div>

    {{-- Judul Dokumen --}}
    <div class="doc-title">
        <h2>LAPORAN REKAP PENJUALAN</h2>
        <p>Periode: {{ $periodLabel }}</p>
    </div>

    {{-- Tabel Ringkasan --}}
    <table class="summary-table">
        <thead>
            <tr>
                <th>Total Transaksi</th>
                <th>Pesanan Online</th>
                <th>Pesanan Langsung</th>
                <th>Total Galon Terjual</th>
                <th>Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $totalOrders }} Transaksi</td>
                <td>{{ $onlineCount }} Transaksi</td>
                <td>{{ $directCount }} Transaksi</td>
                <td>{{ number_format($totalGallons) }} Galon</td>
                <td style="color: #000;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Tabel Data Transaksi --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">No</th>
                <th style="width: 85px;" class="text-left">Tanggal</th>
                <th class="text-left">Nama Pelanggan</th>
                <th style="width: 100px;" class="text-left">No. Telepon</th>
                <th style="width: 70px;" class="text-center">Sumber</th>
                <th style="width: 55px;" class="text-center">Galon</th>
                <th style="width: 85px;" class="text-right">Harga</th>
                <th style="width: 95px;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $i => $order)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-left whitespace-nowrap">
                        {{ $order->completed_at ? $order->completed_at->locale('id')->isoFormat('D MMM YYYY') : '-' }}
                    </td>
                    <td class="text-left">
                        <strong>{{ $order->user?->name ?? 'Pelanggan Langsung' }}</strong>
                    </td>
                    <td class="text-left" style="color: #444;">
                        {{ $order->user?->phone ?? '-' }}
                    </td>
                    <td class="text-center">
                        {{ ucfirst($order->source) }}
                    </td>
                    <td class="text-center font-bold">
                        {{ $order->gallon_qty }}
                    </td>
                    <td class="text-right">
                        Rp {{ number_format($order->unit_price, 0, ',', '.') }}
                    </td>
                    <td class="text-right font-bold">
                        Rp {{ number_format($order->total_price, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 24px; color: #666; font-style: italic;">
                        Tidak ada transaksi selesai pada periode {{ $periodLabel }}
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($orders->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right" style="text-transform: uppercase; font-size: 8.5pt;">
                        Total Keseluruhan ({{ $periodLabel }}):
                    </td>
                    <td class="text-center font-bold">
                        {{ $totalGallons }}
                    </td>
                    <td class="text-right" style="font-size: 8pt; color: #555; font-weight: normal;">
                        Avg: Rp {{ number_format($avgOrder, 0, ',', '.') }}
                    </td>
                    <td class="text-right font-bold" style="font-size: 9.5pt;">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- Kolom Tanda Tangan --}}
    <div class="signature-wrap">
        <div class="signature-box">
            <p>Tanggal: {{ now()->locale('id')->isoFormat('D MMMM YYYY') }}</p>
            <p style="font-weight: bold; margin-top: 2px;">Penanggung Jawab Depot</p>
            <div class="signature-space"></div>
            <p class="signature-line">{{ auth()->user()->name }}</p>
            <p style="font-size: 8pt; color: #666; text-transform: uppercase; margin-top: 1px;">{{ auth()->user()->role ?? 'Admin' }}</p>
        </div>
    </div>

</body>
</html>
