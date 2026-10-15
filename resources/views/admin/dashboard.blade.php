@extends('layouts.admin')

@section('title', 'Dashboard - Ken Water BO')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-0.5">Ringkasan performa bisnis Ken Water hari ini.</p>
    </div>
    <a href="{{ route('admin.orders.index') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-primary-dark active:scale-95 transition-all duration-150 self-start sm:self-auto">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        Lihat Pesanan Masuk
        @if($pendingOrdersCount > 0)
            <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold bg-red-500 text-white rounded-full ring-2 ring-primary">
                {{ $pendingOrdersCount }}
            </span>
        @endif
    </a>
</div>

{{-- STAT CARDS (1 Baris / 4 Kolom) --}}
<div class="grid grid-cols-4 gap-4 mb-8">

    {{-- Total Pelanggan --}}
    <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-sky-400 to-blue-500 rounded-t-2xl"></div>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 mb-2">Total Pelanggan</p>
                <p class="text-3xl font-bold text-gray-900">{{ $totalCustomers }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-sky-50 flex items-center justify-center text-sky-500 group-hover:bg-sky-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-3">Semua pelanggan terdaftar</p>
    </div>

    {{-- Pelanggan Aktif --}}
    <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-400 to-green-500 rounded-t-2xl"></div>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 mb-2">Pelanggan Aktif</p>
                <p class="text-3xl font-bold text-gray-900">{{ $activeCustomers }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500 group-hover:bg-emerald-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-3">Aktif 30 hari terakhir</p>
    </div>

    {{-- Pelanggan Loyal --}}
    <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-400 to-yellow-500 rounded-t-2xl"></div>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 mb-2">Pelanggan Loyal</p>
                <p class="text-3xl font-bold text-gray-900">{{ $loyalCustomers }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500 group-hover:bg-amber-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-3">Level Gold & Platinum</p>
    </div>

    {{-- Total Pendapatan --}}
    <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-violet-400 to-purple-600 rounded-t-2xl"></div>
        <div class="flex items-center justify-between">
            <div class="min-w-0 flex-1 mr-3">
                <p class="text-xs font-medium text-gray-500 mb-2">Total Pendapatan</p>
                <p class="text-xl font-bold text-gray-900 leading-snug">{{ format_rupiah($totalRevenue) }}</p>
            </div>
            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center text-violet-500 group-hover:bg-violet-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-3">Akumulasi seluruh periode</p>
    </div>

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
