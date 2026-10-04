@extends('layouts.admin')
@section('title', 'Pesanan Masuk - Ken Water')
@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Pesanan Masuk (Online)</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola semua pesanan yang masuk dari portal pelanggan.</p>
    </div>
</div>

<!-- Status Summary -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    <a href="{{ route('admin.orders.index', ['status' => 'dipesan']) }}" class="card p-3 text-center hover:shadow-md transition-shadow border-l-4 border-l-yellow-400">
        <p class="text-2xl font-bold text-yellow-600">{{ $statusCounts['dipesan'] }}</p>
        <p class="text-xs text-gray-500 mt-1">Dipesan</p>
    </a>
    <a href="{{ route('admin.orders.index', ['status' => 'diproses']) }}" class="card p-3 text-center hover:shadow-md transition-shadow border-l-4 border-l-blue-400">
        <p class="text-2xl font-bold text-blue-600">{{ $statusCounts['diproses'] }}</p>
        <p class="text-xs text-gray-500 mt-1">Diproses</p>
    </a>
    <a href="{{ route('admin.orders.index', ['status' => 'diantar']) }}" class="card p-3 text-center hover:shadow-md transition-shadow border-l-4 border-l-purple-400">
        <p class="text-2xl font-bold text-purple-600">{{ $statusCounts['diantar'] }}</p>
        <p class="text-xs text-gray-500 mt-1">Diantar</p>
    </a>
    <a href="{{ route('admin.orders.index', ['status' => 'siap_diambil']) }}" class="card p-3 text-center hover:shadow-md transition-shadow border-l-4 border-l-orange-400">
        <p class="text-2xl font-bold text-orange-600">{{ $statusCounts['siap_diambil'] }}</p>
        <p class="text-xs text-gray-500 mt-1">Siap Diambil</p>
    </a>
</div>

<!-- Filter -->
<div class="card p-4 mb-6">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pelanggan atau HP..." class="input-field w-full">
        </div>
        <select name="status" class="input-field sm:w-44">
            <option value="">Semua Status</option>
            <option value="dipesan" {{ request('status') === 'dipesan' ? 'selected' : '' }}>Dipesan</option>
            <option value="diproses" {{ request('status') === 'diproses' ? 'selected' : '' }}>Diproses</option>
            <option value="diantar" {{ request('status') === 'diantar' ? 'selected' : '' }}>Diantar</option>
            <option value="siap_diambil" {{ request('status') === 'siap_diambil' ? 'selected' : '' }}>Siap Diambil</option>
            <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
            <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
        </select>
        <button type="submit" class="btn-primary">Filter</button>
        @if(request()->hasAny(['search','status']))
        <a href="{{ route('admin.orders.index') }}" class="btn-secondary">Reset</a>
        @endif
    </form>
</div>

<!-- Tabel -->
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu Pesan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Galon</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">Pengiriman</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Total</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($orders as $order)
                <tr class="hover:bg-blue-50 transition-colors {{ $order->status === 'dipesan' ? 'bg-yellow-50/50' : '' }}">
                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                        {{ $order->order_date->format('d M Y') }}<br>
                        <span class="text-xs text-gray-400">{{ $order->order_date->format('H:i') }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm font-semibold text-gray-900">{{ $order->user->name }}</div>
                        <div class="text-xs text-gray-400">{{ $order->user->phone }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm font-bold text-gray-900">{{ $order->gallon_qty }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 hidden sm:table-cell">
                        {{ $order->delivery_method === 'antar' ? '📦 Diantar' : '🏪 Ambil Sendiri' }}
                    </td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-800 hidden md:table-cell">{{ format_rupiah($order->total_price) }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                            @if($order->status === 'dipesan') bg-yellow-100 text-yellow-800
                            @elseif($order->status === 'diproses') bg-blue-100 text-blue-800
                            @elseif(in_array($order->status, ['diantar','siap_diambil'])) bg-purple-100 text-purple-800
                            @elseif($order->status === 'selesai') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-primary hover:text-primary-dark text-sm font-medium">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        Tidak ada pesanan ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
