@extends('layouts.admin')

@section('title', 'Dashboard - Ken Water BO')

@section('content')

{{-- PAGE HEADER & ACTION --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-100">
                <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                Ken Water Back Office
            </span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight mt-1">Dashboard</h1>
        <p class="text-xs text-gray-500 mt-0.5">Monitoring performa depot & ringkasan operasional harian.</p>
    </div>
    <div class="flex items-center gap-2.5">
        <a href="{{ route('admin.orders.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white text-xs font-semibold rounded-xl shadow-sm hover:bg-primary-dark active:scale-95 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Pesanan Masuk
            @if($pendingOrdersCount > 0)
                <span class="inline-flex items-center justify-center min-w-[18px] h-4 px-1 text-[10px] font-bold bg-red-500 text-white rounded-full">
                    {{ $pendingOrdersCount }}
                </span>
            @endif
        </a>
    </div>
</div>

{{-- BANNER HERO GRADIENT (Identik Mobile) --}}
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-sky-600 via-sky-500 to-cyan-600 p-6 text-white shadow-lg shadow-sky-500/15 mb-6">
    <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
    <div class="absolute right-24 -top-12 w-32 h-32 bg-cyan-300/20 rounded-full blur-xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-white/20 backdrop-blur-sm text-white border border-white/30">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Hari Ini, {{ now()->translatedFormat('d F Y') }}
                </span>
            </div>
            <p class="text-xs text-sky-100 font-medium">Pendapatan Hari Ini</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight mt-0.5">{{ format_rupiah($todayRevenue) }}</h2>
            <div class="flex flex-wrap items-center gap-2.5 mt-3.5">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-white/15 backdrop-blur-sm text-white">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                    {{ $todayGallons }} Galon Terjual
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-white/15 backdrop-blur-sm text-white">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    {{ $todayOrdersCount }} Transaksi
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-white/15 backdrop-blur-sm text-white">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $pendingOrdersCount }} Menunggu
                </span>
            </div>
        </div>

        {{-- Pintasan Cepat --}}
        <div class="flex items-center gap-2 self-start md:self-auto">
            <a href="{{ route('admin.campaigns.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-sky-700 hover:bg-sky-50 text-xs font-bold transition-all shadow-sm">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Kampanye WhatsApp
            </a>
            <a href="{{ route('admin.reports.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs font-bold backdrop-blur-sm transition-all border border-white/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Laporan
            </a>
        </div>
    </div>
</div>

{{-- METRIC CARDS GRID (Sesuai Gaya Mobile) --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3.5 mb-8">

    {{-- Total Pelanggan --}}
    <a href="{{ route('admin.customers.index') }}" 
       class="group bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md hover:border-sky-300 hover:-translate-y-0.5 transition-all flex items-center justify-between cursor-pointer">
        <div>
            <p class="text-[11px] font-medium text-gray-400 group-hover:text-sky-600 transition-colors">Total Pelanggan</p>
            <p class="text-2xl font-extrabold text-gray-900 mt-0.5">{{ $totalCustomers }}</p>
            <span class="inline-block mt-1 text-[10px] font-semibold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-full group-hover:bg-sky-100 transition-colors">Semua Member &rarr;</span>
        </div>
        <div class="w-11 h-11 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center group-hover:scale-110 group-hover:bg-sky-500 group-hover:text-white transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
    </a>

    {{-- Pelanggan Aktif --}}
    <a href="{{ route('admin.customers.index', ['status' => 'aktif']) }}" 
       class="group bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-300 hover:-translate-y-0.5 transition-all flex items-center justify-between cursor-pointer">
        <div>
            <p class="text-[11px] font-medium text-gray-400 group-hover:text-emerald-600 transition-colors">Pelanggan Aktif</p>
            <p class="text-2xl font-extrabold text-gray-900 mt-0.5">{{ $activeCustomers }}</p>
            <span class="inline-block mt-1 text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full group-hover:bg-emerald-100 transition-colors">Status Aktif &rarr;</span>
        </div>
        <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 group-hover:bg-emerald-500 group-hover:text-white transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </a>

    {{-- Tidak Aktif --}}
    <a href="{{ route('admin.customers.index', ['status' => 'tidak_aktif']) }}" 
       class="group bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md hover:border-rose-300 hover:-translate-y-0.5 transition-all flex items-center justify-between cursor-pointer">
        <div>
            <p class="text-[11px] font-medium text-gray-400 group-hover:text-rose-600 transition-colors">Tidak Aktif</p>
            <p class="text-2xl font-extrabold text-rose-600 mt-0.5">{{ $inactiveCustomers }}</p>
            <span class="inline-block mt-1 text-[10px] font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full group-hover:bg-rose-100 transition-colors">Perlu Reaktivasi &rarr;</span>
        </div>
        <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:scale-110 group-hover:bg-rose-500 group-hover:text-white transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        </div>
    </a>

    {{-- Pelanggan Loyal --}}
    <a href="{{ route('admin.customers.index', ['level' => 'loyal']) }}" 
       class="group bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md hover:border-amber-300 hover:-translate-y-0.5 transition-all flex items-center justify-between cursor-pointer">
        <div>
            <p class="text-[11px] font-medium text-gray-400 group-hover:text-amber-600 transition-colors">Pelanggan Loyal</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-0.5">{{ $loyalCustomers }}</p>
            <span class="inline-block mt-1 text-[10px] font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full group-hover:bg-amber-100 transition-colors">Silver & Gold &rarr;</span>
        </div>
        <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 group-hover:bg-amber-500 group-hover:text-white transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
        </div>
    </a>

    {{-- Total Pendapatan Akumulasi --}}
    <a href="{{ route('admin.reports.index') }}" 
       class="group bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md hover:border-violet-300 hover:-translate-y-0.5 transition-all flex items-center justify-between col-span-2 md:col-span-1 cursor-pointer">
        <div>
            <p class="text-[11px] font-medium text-gray-400 group-hover:text-violet-600 transition-colors">Total Akumulasi</p>
            <p class="text-base font-extrabold text-violet-700 mt-0.5">{{ format_rupiah($totalRevenue) }}</p>
            <span class="inline-block mt-1 text-[10px] font-semibold text-violet-600 bg-violet-50 px-2 py-0.5 rounded-full group-hover:bg-violet-100 transition-colors">Semua Transaksi &rarr;</span>
        </div>
        <div class="w-11 h-11 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center group-hover:scale-110 group-hover:bg-violet-600 group-hover:text-white transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </a>

</div>

{{-- CHART + TOP 5 CUSTOMERS --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- Grafik Pendapatan --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base font-bold text-gray-800">Grafik Transaksi</h3>
                <p class="text-xs text-gray-400 mt-0.5">12 bulan terakhir</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm bg-sky-400 inline-block"></span> Pendapatan
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-4 h-0.5 rounded-full bg-amber-400 inline-block"></span> Pesanan
                </span>
            </div>
        </div>
        <div class="w-full" style="height: 280px; position: relative;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- Top 5 Pelanggan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
            <div>
                <h3 class="text-base font-bold text-gray-800">Top 5 Pelanggan</h3>
                <p class="text-xs text-gray-400 mt-0.5">Berdasarkan total belanja</p>
            </div>
            <span class="text-xs bg-sky-50 text-sky-600 font-semibold px-2.5 py-1 rounded-full">Periode Ini</span>
        </div>
        <div class="flex-1 divide-y divide-gray-50">
            @forelse($topCustomers as $index => $customer)
            @php
                $rankColors = ['bg-amber-400 text-white', 'bg-gray-300 text-gray-700', 'bg-orange-300 text-white', 'bg-gray-100 text-gray-600', 'bg-gray-100 text-gray-600'];
                $levelBadge = [
                    'platinum' => 'bg-violet-100 text-violet-700',
                    'gold'     => 'bg-amber-100 text-amber-700',
                    'silver'   => 'bg-gray-200 text-gray-600',
                    'bronze'   => 'bg-orange-100 text-orange-700',
                    'regular'  => 'bg-blue-50 text-blue-500',
                ];
                $level = strtolower($customer->loyalty_level ?? 'regular');
                $badgeClass = $levelBadge[$level] ?? 'bg-gray-100 text-gray-500';
            @endphp
            <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 transition-colors">
                <span class="flex-shrink-0 w-7 h-7 rounded-full text-xs font-bold flex items-center justify-center {{ $rankColors[$index] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ $index + 1 }}
                </span>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('admin.customers.show', $customer) }}"
                       class="block text-sm font-semibold text-gray-900 truncate hover:text-primary transition-colors">
                        {{ $customer->name }}
                    </a>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $badgeClass }}">
                            {{ loyalty_level_label($customer->loyalty_level) }}
                        </span>
                        <span class="text-[11px] text-gray-400">{{ $customer->order_count }} pesanan</span>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="text-xs font-bold text-gray-800">{{ format_rupiah($customer->total_spend) }}</span>
                </div>
            </div>
            @empty
            <div class="py-10 text-center text-gray-400 text-sm">
                <svg class="w-8 h-8 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Belum ada data transaksi pelanggan.
            </div>
            @endforelse
        </div>
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
            <a href="{{ route('admin.segmentation.index') }}"
               class="text-xs font-semibold text-primary hover:text-primary-dark flex items-center justify-center gap-1 transition-colors">
                Lihat Semua Segmentasi
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
</div>

{{-- PESANAN MASUK + KELUHAN --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Pesanan Masuk --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-800">Pesanan Masuk (Online)</h3>
            </div>
            @if($pendingOrdersCount > 0)
            <span class="inline-flex items-center gap-1 bg-red-50 text-red-600 text-xs font-bold px-2.5 py-1 rounded-full border border-red-100">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                {{ $pendingOrdersCount }} Baru
            </span>
            @endif
        </div>
        <div class="overflow-x-auto flex-1">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Tgl / Jam</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Pelanggan</th>
                        <th class="px-5 py-3 text-center text-[11px] font-bold text-gray-400 uppercase tracking-wider">Jml Galon</th>
                        <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pendingOrders as $order)
                    <tr class="hover:bg-blue-50/40 transition-colors">
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <p class="text-sm font-semibold text-gray-800">{{ $order->order_date->format('d M') }}</p>
                            <p class="text-xs text-gray-400">{{ $order->order_date->format('H:i') }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="text-sm font-semibold text-gray-900">{{ $order->user->name }}</p>
                            <span class="text-[11px] text-gray-400">{{ $order->delivery_method_label }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-700 text-sm font-bold">
                                {{ $order->gallon_qty }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary text-white text-xs font-semibold rounded-lg hover:bg-primary-dark active:scale-95 transition-all shadow-sm">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Proses
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center">
                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm text-gray-400">Tidak ada pesanan masuk baru.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pendingOrdersCount > 10)
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
            <a href="{{ route('admin.orders.index', ['status' => 'dipesan']) }}"
               class="text-xs font-semibold text-primary hover:text-primary-dark flex items-center justify-center gap-1 transition-colors">
                Lihat Semua Pesanan Masuk
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endif
    </div>

    {{-- Keluhan Belum Selesai --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-800">Keluhan Belum Selesai</h3>
            </div>
            @if($openComplaintsCount > 0)
            <span class="inline-flex items-center gap-1 bg-orange-50 text-orange-600 text-xs font-bold px-2.5 py-1 rounded-full border border-orange-100">
                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                {{ $openComplaintsCount }} Terbuka
            </span>
            @endif
        </div>
        <div class="flex-1 divide-y divide-gray-50 overflow-y-auto">
            @forelse($openComplaints as $complaint)
            @php
                $statusStyles = [
                    'baru'            => 'bg-red-50 text-red-700 border border-red-100',
                    'diproses'        => 'bg-blue-50 text-blue-700 border border-blue-100',
                    'menunggu'        => 'bg-amber-50 text-amber-700 border border-amber-100',
                    'sedang_diproses' => 'bg-blue-50 text-blue-700 border border-blue-100',
                ];
                $statusKey = strtolower(str_replace(' ', '_', $complaint->status ?? 'baru'));
                $statusClass = $statusStyles[$statusKey] ?? 'bg-gray-100 text-gray-600 border border-gray-200';
            @endphp
            <div class="px-5 py-4 hover:bg-orange-50/30 transition-colors">
                <div class="flex items-start justify-between mb-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $statusClass }}">
                        {{ $complaint->status_label }}
                    </span>
                    <span class="text-[11px] text-gray-400 shrink-0 ml-2">{{ $complaint->created_at->diffForHumans() }}</span>
                </div>
                <h4 class="text-sm font-bold text-gray-900">{{ $complaint->category }}</h4>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed line-clamp-2">{{ $complaint->description }}</p>
                <div class="mt-3 flex items-center justify-between">
                    <span class="text-xs text-gray-400">
                        <span class="font-medium text-gray-600">Dari:</span> {{ $complaint->user->name }}
                    </span>
                    <a href="{{ route('admin.complaints.show', $complaint) }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-orange-500 text-white text-[11px] font-bold rounded-lg hover:bg-orange-600 active:scale-95 transition-all shadow-sm">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Tindak Lanjuti
                    </a>
                </div>
            </div>
            @empty
            <div class="py-12 text-center">
                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-green-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-700">Semua bersih!</p>
                <p class="text-xs text-gray-400 mt-1">Tidak ada keluhan yang terbuka. 🎉</p>
            </div>
            @endforelse
        </div>
        @if($openComplaintsCount > 5)
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
            <a href="{{ route('admin.complaints.index') }}"
               class="text-xs font-semibold text-primary hover:text-primary-dark flex items-center justify-center gap-1 transition-colors">
                Lihat Semua Keluhan
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endif
    </div>
</div>

{{-- CHART SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('revenueChart').getContext('2d');

    const labels      = {!! json_encode($chartLabels) !!};
    const revenueData = {!! json_encode($chartRevenue) !!};
    const orderData   = {!! json_encode($chartOrders) !!};

    // Gradient fill for bar
    const gradient = ctx.createLinearGradient(0, 0, 0, 280);
    gradient.addColorStop(0, 'rgba(14, 165, 233, 0.75)');
    gradient.addColorStop(1, 'rgba(14, 165, 233, 0.08)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pendapatan (Rp)',
                    data: revenueData,
                    backgroundColor: gradient,
                    borderColor: 'rgba(14, 165, 233, 0.9)',
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                    yAxisID: 'y',
                    order: 2,
                },
                {
                    label: 'Jumlah Pesanan',
                    data: orderData,
                    type: 'line',
                    fill: false,
                    borderColor: 'rgba(245, 158, 11, 1)',
                    backgroundColor: 'rgba(245, 158, 11, 1)',
                    tension: 0.4,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: 'rgba(245, 158, 11, 1)',
                    pointBorderWidth: 2,
                    yAxisID: 'y1',
                    order: 1,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#94a3b8',
                    bodyColor: '#f1f5f9',
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label + ': ';
                            if (context.datasetIndex === 0) {
                                label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                            } else {
                                label += context.raw + ' pesanan';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 11, family: 'Inter' } },
                    border: { display: false }
                },
                y: {
                    type: 'linear',
                    position: 'left',
                    grid: { color: 'rgba(0,0,0,0.05)', drawTicks: false },
                    border: { display: false, dash: [4, 4] },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11, family: 'Inter' },
                        padding: 8,
                        maxTicksLimit: 6,
                        callback: function (value) {
                            if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            if (value >= 1000)    return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                            return value;
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    border: { display: false },
                    ticks: {
                        color: '#f59e0b',
                        font: { size: 11, family: 'Inter' },
                        padding: 8,
                        maxTicksLimit: 6,
                        stepSize: 1,
                    }
                }
            }
        }
    });
});
</script>
@endsection
