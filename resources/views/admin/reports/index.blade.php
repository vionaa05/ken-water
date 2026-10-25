@extends('layouts.admin')
@section('title', 'Laporan Penjualan - ' . $periodLabel . ' - Ken Water')

@section('content')

{{-- ============================================================== --}}
{{-- 1. TAMPILAN LAYAR WEB (Disembunyikan saat dicetak)            --}}
{{-- ============================================================== --}}
<div class="print:hidden">
    {{-- Header & Aksi --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Penjualan</h1>
            <p class="text-sm text-gray-500 mt-1">Rekapitulasi transaksi selesai pada periode yang dipilih.</p>
        </div>

        <div class="flex items-center gap-2 flex-nowrap shrink-0">
            {{-- Filter Terpisah: Dropdown Bulan & Dropdown Tahun --}}
            <form method="GET" action="{{ route('admin.reports.index') }}" id="filterForm" class="flex items-center gap-2 shrink-0">
                {{-- Dropdown Bulan --}}
                <select name="month" id="filterMonth" onchange="document.getElementById('filterForm').submit()"
                    class="text-sm font-medium border border-gray-300 rounded-lg px-3 py-2 bg-white text-gray-800 focus:ring-2 focus:ring-primary focus:border-primary outline-none shadow-sm cursor-pointer">
                    @foreach ($months as $num => $name)
                        <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>

                {{-- Dropdown Tahun --}}
                <select name="year" id="filterYear" onchange="document.getElementById('filterForm').submit()"
                    class="text-sm font-medium border border-gray-300 rounded-lg px-3 py-2 bg-white text-gray-800 focus:ring-2 focus:ring-primary focus:border-primary outline-none shadow-sm cursor-pointer">
                    @foreach ($years as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
            </form>

            {{-- Tombol Cetak (Buka Format Tabel Dokumen di Tab Baru & Otomatis Print) --}}
            <a href="{{ route('admin.reports.index', ['month' => $month, 'year' => $year, 'print' => 1]) }}" target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 active:scale-95 transition-all shadow-sm cursor-pointer whitespace-nowrap shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak Laporan
            </a>
        </div>
    </div>

    {{-- Ringkasan Periode Web --}}
    <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-5 mb-6">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">
            Ringkasan · {{ $periodLabel }}
        </p>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-x-6 gap-y-4 text-sm">
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Total Transaksi</p>
                <p class="font-bold text-gray-900 text-lg">{{ $totalOrders }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Pesanan Online</p>
                <p class="font-semibold text-sky-700 text-lg">{{ $onlineCount }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Pesanan Langsung</p>
                <p class="font-semibold text-violet-700 text-lg">{{ $directCount }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Galon Terjual</p>
                <p class="font-bold text-gray-900 text-lg">{{ number_format($totalGallons) }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Total Pendapatan</p>
                <p class="font-bold text-gray-900 text-lg">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Tabel Transaksi Web --}}
    <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
            <p class="text-sm font-semibold text-gray-700">
                Daftar Transaksi Selesai
                @if ($totalOrders > 0)
                    <span class="ml-2 text-xs font-normal text-gray-400">({{ $totalOrders }} transaksi)</span>
                @endif
            </p>
            @if ($totalOrders > 0)
                <p class="text-xs text-gray-400">Rata-rata/pesanan: <span class="font-semibold text-gray-600">Rp {{ number_format($avgOrder, 0, ',', '.') }}</span></p>
            @endif
        </div>

        @if ($orders->isEmpty())
            <div class="py-16 text-center">
                <svg class="mx-auto w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm font-medium text-gray-400">Tidak ada transaksi selesai</p>
                <p class="text-xs text-gray-300 mt-1">pada periode {{ $periodLabel }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide border-b border-gray-100">
                            <th class="px-5 py-3 text-left font-semibold w-12">No</th>
                            <th class="px-5 py-3 text-left font-semibold">Tanggal Selesai</th>
                            <th class="px-5 py-3 text-left font-semibold">Pelanggan</th>
                            <th class="px-4 py-3 text-center font-semibold">Sumber</th>
                            <th class="px-5 py-3 text-right font-semibold">Galon</th>
                            <th class="px-5 py-3 text-right font-semibold">Harga Satuan</th>
                            <th class="px-5 py-3 text-right font-semibold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($orders as $i => $order)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                                <td class="px-5 py-3 text-gray-600 whitespace-nowrap">
                                    {{ $order->completed_at ? $order->completed_at->locale('id')->isoFormat('D MMM YYYY') : '—' }}
                                </td>
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-800">{{ $order->user?->name ?? 'Pelanggan Langsung' }}</p>
                                    <p class="text-xs text-gray-400">{{ $order->user?->phone ?? '' }}</p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($order->source === 'online')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-sky-50 text-sky-700 border border-sky-100">
                                            Online
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-violet-50 text-violet-700 border border-violet-100">
                                            Langsung
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right text-gray-700 font-medium">{{ $order->gallon_qty }}</td>
                                <td class="px-5 py-3 text-right text-gray-500">Rp {{ number_format($order->unit_price, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-gray-900">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-200 bg-gray-50 font-semibold text-gray-800">
                            <td colspan="4" class="px-5 py-3.5 text-xs uppercase tracking-wide text-gray-700">
                                Total ({{ $periodLabel }})
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold text-gray-900">{{ $totalGallons }}</td>
                            <td class="px-5 py-3.5 text-right text-gray-400 text-xs font-normal">
                                Rata-rata: Rp {{ number_format($avgOrder, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold text-gray-900">
                                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>


{{-- ============================================================== --}}
{{-- 2. DOKUMEN CETAK KHUSUS (Hanya muncul saat print)              --}}
{{-- ============================================================== --}}
<div class="hidden print:block print-document text-black">
    {{-- Kop Laporan --}}
    <div class="print-header text-center pb-3 mb-4 border-b-2 border-black">
        <h1 class="text-xl font-bold tracking-wider uppercase text-black">DEPOT AIR MINUM KEN WATER</h1>
        <p class="text-xs text-gray-700 mt-0.5">Sistem Pengelolaan Penjualan & Distribusi Air Galon</p>
        <p class="text-[11px] text-gray-600 mt-0.5">
            Dicetak: {{ now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }} WIB &nbsp;|&nbsp; Oleh: {{ auth()->user()->name }}
        </p>
    </div>

    {{-- Judul Dokumen --}}
    <div class="text-center mb-4">
        <h2 class="text-base font-bold uppercase tracking-wide text-black underline">LAPORAN REKAP PENJUALAN</h2>
        <p class="text-xs font-semibold text-gray-800 mt-1">Periode: {{ $periodLabel }}</p>
    </div>

    {{-- Tabel Ringkasan Resmi --}}
    <table class="w-full text-xs mb-4 border-collapse border border-black">
        <thead>
            <tr class="bg-gray-100 text-center font-bold text-[11px]">
                <th class="border border-black py-1.5 px-2">Total Transaksi</th>
                <th class="border border-black py-1.5 px-2">Pesanan Online</th>
                <th class="border border-black py-1.5 px-2">Pesanan Langsung</th>
                <th class="border border-black py-1.5 px-2">Total Galon Terjual</th>
                <th class="border border-black py-1.5 px-2">Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            <tr class="text-center font-semibold text-xs">
                <td class="border border-black py-1.5 px-2">{{ $totalOrders }} Transaksi</td>
                <td class="border border-black py-1.5 px-2">{{ $onlineCount }} Transaksi</td>
                <td class="border border-black py-1.5 px-2">{{ $directCount }} Transaksi</td>
                <td class="border border-black py-1.5 px-2">{{ number_format($totalGallons) }} Galon</td>
                <td class="border border-black py-1.5 px-2 font-bold">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Tabel Data Transaksi Resmi Cetak --}}
    <table class="w-full text-xs border-collapse border border-black table-print">
        <thead>
            <tr class="bg-gray-100 font-bold text-center text-[11px] uppercase">
                <th class="border border-black px-2 py-1.5 w-8">No</th>
                <th class="border border-black px-2 py-1.5 text-left w-24">Tanggal</th>
                <th class="border border-black px-2 py-1.5 text-left">Nama Pelanggan</th>
                <th class="border border-black px-2 py-1.5 text-left w-28">No. Telepon</th>
                <th class="border border-black px-2 py-1.5 text-center w-20">Sumber</th>
                <th class="border border-black px-2 py-1.5 text-center w-16">Galon</th>
                <th class="border border-black px-2 py-1.5 text-right w-24">Harga</th>
                <th class="border border-black px-2 py-1.5 text-right w-28">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $i => $order)
                <tr class="text-black">
                    <td class="border border-black px-2 py-1.5 text-center">{{ $i + 1 }}</td>
                    <td class="border border-black px-2 py-1.5 whitespace-nowrap">
                        {{ $order->completed_at ? $order->completed_at->locale('id')->isoFormat('D MMM YYYY') : '-' }}
                    </td>
                    <td class="border border-black px-2 py-1.5 font-medium">
                        {{ $order->user?->name ?? 'Pelanggan Langsung' }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-gray-700">
                        {{ $order->user?->phone ?? '-' }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-center">
                        {{ ucfirst($order->source) }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-center font-semibold">
                        {{ $order->gallon_qty }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-right">
                        Rp {{ number_format($order->unit_price, 0, ',', '.') }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-right font-bold">
                        Rp {{ number_format($order->total_price, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="border border-black px-3 py-6 text-center text-gray-600 italic">
                        Tidak ada transaksi yang selesai pada periode {{ $periodLabel }}
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($orders->isNotEmpty())
            <tfoot>
                <tr class="bg-gray-100 font-bold border-t-2 border-black">
                    <td colspan="5" class="border border-black px-3 py-2 text-right uppercase text-[11px]">
                        Total Keseluruhan ({{ $periodLabel }}):
                    </td>
                    <td class="border border-black px-2 py-2 text-center text-sm">
                        {{ $totalGallons }}
                    </td>
                    <td class="border border-black px-2 py-2 text-right text-[10px] text-gray-700 font-normal">
                        Rata2: Rp {{ number_format($avgOrder, 0, ',', '.') }}
                    </td>
                    <td class="border border-black px-2 py-2 text-right font-bold text-sm">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- Bagian Tanda Tangan Resmi --}}
    <div class="mt-8 flex justify-end print-signature">
        <div class="text-center w-56 text-xs">
            <p class="text-gray-800">Tanggal: {{ now()->locale('id')->isoFormat('D MMMM YYYY') }}</p>
            <p class="font-bold text-black mt-1">Penanggung Jawab Depot</p>
            <div class="h-16"></div>
            <p class="border-t border-black font-bold text-black pt-1">{{ auth()->user()->name }}</p>
            <p class="text-[10px] text-gray-600 uppercase">{{ auth()->user()->role ?? 'Admin' }}</p>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 1.2cm;
    }
    body {
        font-family: Arial, Helvetica, sans-serif !important;
        font-size: 11px !important;
        background: #ffffff !important;
        color: #000000 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .print-document {
        display: block !important;
        width: 100% !important;
    }
    .table-print {
        border-collapse: collapse !important;
        width: 100% !important;
    }
    .table-print th, .table-print td {
        border: 1px solid #000000 !important;
        padding: 5px 7px !important;
    }
    .table-print thead {
        display: table-header-group !important;
    }
    .table-print tr {
        page-break-inside: avoid !important;
    }
    .print-signature {
        page-break-inside: avoid !important;
    }
}
</style>
@endpush