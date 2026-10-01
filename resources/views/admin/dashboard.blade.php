@extends('layouts.admin')

@section('title', 'Dashboard - Ken Water BO')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Ringkasan performa bisnis Ken Water.</p>
    </div>
    
    <div class="mt-4 sm:mt-0 flex gap-2">
        <a href="{{ route('admin.orders.index') }}" class="btn-primary">
            Lihat Pesanan Masuk
            @if($pendingOrdersCount > 0)
            <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">{{ $pendingOrdersCount }}</span>
            @endif
        </a>
    </div>
</div>

<!-- Kartu Ringkasan -->
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
    <div class="card p-5 border-l-4 border-l-blue-500 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Pelanggan</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalCustomers }}</p>
            </div>
        </div>
    </div>
    
    <div class="card p-5 border-l-4 border-l-green-500 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Pelanggan Aktif</p>
                <p class="text-2xl font-bold text-gray-900">{{ $activeCustomers }}</p>
            </div>
        </div>
    </div>
    
    <div class="card p-5 border-l-4 border-l-yellow-500 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Pelanggan Loyal</p>
                <p class="text-2xl font-bold text-gray-900">{{ $loyalCustomers }}</p>
            </div>
        </div>
    </div>
    
    <div class="card p-5 border-l-4 border-l-purple-500 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Pendapatan</p>
                <p class="text-xl font-bold text-gray-900">{{ format_rupiah($totalRevenue) }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Grafik Pendapatan -->
    <div class="lg:col-span-2 card p-5">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Grafik Transaksi (12 Bulan)</h3>
        <div class="w-full h-72">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    
    <!-- Top 5 Pelanggan -->
    <div class="card p-0">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Top 5 Pelanggan</h3>
            <span class="text-xs text-gray-500">Periode Saat Ini</span>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($topCustomers as $index => $customer)
            <div class="p-4 hover:bg-gray-50 transition-colors flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm mr-3">
                        {{ $index + 1 }}
                    </div>
                    <div>
                        <a href="{{ route('admin.customers.show', $customer) }}" class="text-sm font-semibold text-gray-900 hover:text-primary">{{ $customer->name }}</a>
                        <p class="text-xs text-gray-500">{{ loyalty_level_label($customer->loyalty_level) }} • {{ $customer->order_count }} pesanan</p>
                    </div>
                </div>
                <div class="text-sm font-semibold text-gray-700">
                    {{ format_rupiah($customer->total_spend) }}
                </div>
            </div>
            @empty
            <div class="p-6 text-center text-gray-500 text-sm">Belum ada data transaksi pelanggan.</div>
            @endforelse
        </div>
        <div class="p-3 border-t border-gray-100 bg-gray-50 text-center">
            <a href="{{ route('admin.segmentation.index') }}" class="text-sm text-primary hover:text-primary-dark font-medium">Lihat Semua Segmentasi →</a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Pesanan Masuk (Pending) -->
    <div class="card p-0">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <h3 class="text-lg font-semibold text-gray-800">Pesanan Masuk (Online)</h3>
                @if($pendingOrdersCount > 0)
                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">{{ $pendingOrdersCount }} Baru</span>
                @endif
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl / Jam</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jml</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pendingOrders as $order)
                    <tr class="hover:bg-blue-50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->order_date->format('d M') }} <br>
                            <span class="text-xs">{{ $order->order_date->format('H:i') }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium text-gray-900">{{ $order->user->name }}</div>
                            <div class="text-gray-500 text-xs">{{ $order->delivery_method_label }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-700">
                            {{ $order->gallon_qty }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-primary hover:text-primary-dark">Proses</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500 text-sm">Tidak ada pesanan masuk baru.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pendingOrdersCount > 10)
        <div class="p-3 border-t border-gray-100 bg-gray-50 text-center">
            <a href="{{ route('admin.orders.index', ['status' => 'dipesan']) }}" class="text-sm text-primary hover:text-primary-dark font-medium">Lihat Semua Pesanan Masuk →</a>
        </div>
        @endif
    </div>

    <!-- Keluhan Terbuka -->
    <div class="card p-0">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <h3 class="text-lg font-semibold text-gray-800">Keluhan Belum Selesai</h3>
                @if($openComplaintsCount > 0)
                <span class="bg-orange-100 text-orange-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">{{ $openComplaintsCount }} Terbuka</span>
                @endif
            </div>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($openComplaints as $complaint)
            <div class="p-4 hover:bg-gray-50 transition-colors">
                <div class="flex justify-between items-start mb-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $complaint->status_color }}-100 text-{{ $complaint->status_color }}-800">
                        {{ $complaint->status_label }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $complaint->created_at->diffForHumans() }}</span>
                </div>
                <h4 class="text-sm font-semibold text-gray-900 mt-2">{{ $complaint->category }}</h4>
                <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $complaint->description }}</p>
                <div class="mt-2 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Dari: {{ $complaint->user->name }}</span>
                    <a href="{{ route('admin.complaints.show', $complaint) }}" class="text-xs font-medium text-primary hover:text-primary-dark">Tindak Lanjuti →</a>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-500 text-sm">Tidak ada keluhan yang terbuka. Luar biasa! 🎉</div>
            @endforelse
        </div>
        @if($openComplaintsCount > 5)
        <div class="p-3 border-t border-gray-100 bg-gray-50 text-center">
            <a href="{{ route('admin.complaints.index') }}" class="text-sm text-primary hover:text-primary-dark font-medium">Lihat Semua Keluhan →</a>
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        const labels = {!! json_encode($chartLabels) !!};
        const revenueData = {!! json_encode($chartRevenue) !!};
        const orderData = {!! json_encode($chartOrders) !!};
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Pendapatan (Rp)',
                        data: revenueData,
                        backgroundColor: 'rgba(14, 165, 233, 0.7)',
                        borderColor: 'rgb(14, 165, 233)',
                        borderWidth: 1,
                        borderRadius: 4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Jumlah Pesanan',
                        data: orderData,
                        type: 'line',
                        fill: false,
                        borderColor: 'rgb(245, 158, 11)',
                        backgroundColor: 'rgb(245, 158, 11)',
                        tension: 0.3,
                        borderWidth: 2,
                        pointBackgroundColor: 'rgb(245, 158, 11)',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.datasetIndex === 0) {
                                    label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                } else {
                                    label += context.raw;
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Pendapatan (Rp)'
                        },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                                } else if (value >= 1000) {
                                    return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                                }
                                return value;
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Jml Pesanan'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                }
            }
        });
    });
</script>
@endsection
