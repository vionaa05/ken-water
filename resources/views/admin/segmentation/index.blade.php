@extends('layouts.admin')
@section('title', 'Segmentasi Pelanggan - Ken Water')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Segmentasi Pelanggan</h1>
    <p class="text-sm text-gray-500 mt-1">Analisis perilaku dan nilai pelanggan berdasarkan periode aktif saat ini.</p>
</div>

<!-- Chart Row -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="card p-5">
        <h3 class="font-bold text-gray-800 mb-4">Distribusi Segmen Perilaku</h3>
        <div class="w-full h-48"><canvas id="segmentChart"></canvas></div>
    </div>
    <div class="card p-5">
        <h3 class="font-bold text-gray-800 mb-4">Distribusi Level Loyalty</h3>
        <div class="w-full h-48"><canvas id="levelChart"></canvas></div>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    @foreach($chartSegmentData as $seg)
    <div class="card p-4 text-center">
        <p class="text-2xl font-bold" style="color: {{ $seg['color'] }}">{{ $seg['count'] }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $seg['label'] }}</p>
    </div>
    @endforeach
</div>

<!-- Tabel Pelanggan -->
<div class="card overflow-hidden">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center">
        <h3 class="font-bold text-gray-800">Data Seluruh Pelanggan</h3>
        <span class="text-xs text-gray-500">{{ count($customers) }} pelanggan</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">Segmen</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Level</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Poin</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Nilai RFM</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden xl:table-cell">Terakhir Beli</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($customers as $customer)
                <tr class="hover:bg-blue-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-sm mr-3
                                @if($customer['loyalty_level'] === 'gold') bg-yellow-500
                                @elseif($customer['loyalty_level'] === 'silver') bg-gray-400
                                @else bg-orange-400 @endif">
                                {{ strtoupper(substr($customer['name'], 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $customer['name'] }}</p>
                                <p class="text-xs text-gray-400">{{ $customer['phone'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 hidden sm:table-cell">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            @if($customer['segment'] === 'Sering Beli') bg-green-100 text-green-800
                            @elseif($customer['segment'] === 'Reguler') bg-blue-100 text-blue-800
                            @elseif($customer['segment'] === 'Hampir Hilang') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ $customer['segment'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <span class="text-xs font-bold uppercase
                            @if($customer['loyalty_level'] === 'gold') text-yellow-600
                            @elseif($customer['loyalty_level'] === 'silver') text-gray-500
                            @else text-orange-600 @endif">
                            {{ $customer['loyalty_level'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-700 hidden lg:table-cell">{{ $customer['points'] }}</td>
                    <td class="px-4 py-3 hidden lg:table-cell">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            @if($customer['customer_value'] === 'Tinggi') bg-green-100 text-green-800
                            @elseif($customer['customer_value'] === 'Sedang') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-600 @endif">
                            {{ $customer['customer_value'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500 hidden xl:table-cell">
                        {{ $customer['days_since_last_order'] }} hari lalu
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.customers.show', $customer['id']) }}" class="text-primary hover:text-primary-dark text-sm font-medium">Detail</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const segmentData = {!! json_encode($chartSegmentData) !!};
    const levelData = {!! json_encode($chartLevelData) !!};

    new Chart(document.getElementById('segmentChart'), {
        type: 'doughnut',
        data: {
            labels: segmentData.map(d => d.label),
            datasets: [{
                data: segmentData.map(d => d.count),
                backgroundColor: segmentData.map(d => d.color),
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
    });

    new Chart(document.getElementById('levelChart'), {
        type: 'doughnut',
        data: {
            labels: levelData.map(d => d.label),
            datasets: [{
                data: levelData.map(d => d.count),
                backgroundColor: levelData.map(d => d.color),
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
    });
</script>
@endsection
