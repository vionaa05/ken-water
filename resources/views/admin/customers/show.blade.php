@extends('layouts.admin')
@section('title', 'Detail Pelanggan - Ken Water')
@section('content')
<div class="mb-6">
    <a href="{{ route('admin.customers.index') }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Kembali ke Daftar Pelanggan
    </a>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h1>
            <p class="text-sm font-mono text-gray-400">{{ $customer->customer_id }}</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('admin.customers.print-card', $customer) }}" class="btn-secondary py-2 px-3 text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Cetak Kartu
            </a>
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn-secondary py-2 px-3 text-sm">Edit Data</a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Profile Card -->
    <div class="card p-6">
        <div class="flex flex-col items-center text-center mb-6">
            <div class="w-20 h-20 rounded-full flex items-center justify-center text-white font-bold text-3xl mb-4
                @if($customer->loyalty_level === 'gold') bg-yellow-500
                @elseif($customer->loyalty_level === 'silver') bg-gray-400
                @else bg-orange-400 @endif">
                {{ strtoupper(substr($customer->name, 0, 1)) }}
            </div>
            <h3 class="font-bold text-lg text-gray-900">{{ $customer->name }}</h3>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase mt-2
                @if($customer->loyalty_level === 'gold') bg-yellow-100 text-yellow-800
                @elseif($customer->loyalty_level === 'silver') bg-gray-100 text-gray-700
                @else bg-orange-100 text-orange-800 @endif">
                {{ $customer->loyalty_level }}
            </span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2
                {{ $customer->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $customer->status_label }}
            </span>
        </div>

        <div class="space-y-3 text-sm">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                <div><p class="text-xs text-gray-500">No. HP/WhatsApp</p><p class="font-medium text-gray-900">{{ $customer->phone }}</p></div>
            </div>
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <div><p class="text-xs text-gray-500">Alamat</p><p class="font-medium text-gray-900">{{ $customer->address }}</p></div>
            </div>
            @if($customer->birth_date)
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <div><p class="text-xs text-gray-500">Tanggal Lahir</p><p class="font-medium text-gray-900">{{ $customer->birth_date->format('d M Y') }}</p></div>
            </div>
            @endif
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <div><p class="text-xs text-gray-500">Terdaftar</p><p class="font-medium text-gray-900">{{ $customer->registered_at->format('d M Y') }}</p></div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-4 content-start">
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-primary">{{ $customer->points }}</p>
            <p class="text-xs text-gray-500 mt-1">Poin Aktif</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-gray-800">{{ $customer->order_count_current_period }}</p>
            <p class="text-xs text-gray-500 mt-1">Pesanan (Periode)</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xl font-bold text-gray-800">{{ format_rupiah($customer->total_spend_current_period) }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Belanja</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold {{ $progress['next_level'] ? 'text-amber-500' : 'text-green-600' }}">{{ $progress['percentage'] }}%</p>
            <p class="text-xs text-gray-500 mt-1">Ke Level Berikutnya</p>
        </div>

        <!-- Input Transaksi Langsung -->
        <div class="col-span-2 sm:col-span-4 card p-5 bg-blue-50 border border-blue-200">
            <h4 class="font-bold text-blue-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Input Transaksi Langsung (Tunai di Depot)
            </h4>
            @if($customer->status === 'tidak_aktif')
            <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded text-sm">
                ⚠️ Pelanggan tidak aktif. Tidak bisa melakukan transaksi baru.
            </div>
            @else
            <form method="POST" action="{{ route('admin.customers.store-direct', $customer) }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="text-xs font-medium text-blue-800 mb-1 block">Jumlah Galon *</label>
                        <input type="number" name="gallon_qty" required min="1" max="100" class="input-field" placeholder="1">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-blue-800 mb-1 block">Pembayaran *</label>
                        <select name="payment_method" required class="input-field">
                            <option value="tunai">Tunai</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-blue-800 mb-1 block">Pengiriman *</label>
                        <select name="delivery_method" required class="input-field">
                            <option value="ambil_sendiri">Ambil Sendiri</option>
                            <option value="antar">Diantar</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full btn-primary py-2 text-sm" onclick="return confirm('Catat transaksi ini?')">
                            Catat Transaksi
                        </button>
                    </div>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>

<!-- Tabs: Riwayat, Poin, Voucher, Keluhan -->
<div x-data="{ tab: 'orders' }">
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-6 overflow-x-auto">
            <button @click="tab = 'orders'" :class="tab === 'orders' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors">Riwayat Pesanan</button>
            <button @click="tab = 'points'" :class="tab === 'points' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors">Mutasi Poin</button>
            <button @click="tab = 'vouchers'" :class="tab === 'vouchers' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors">Voucher & Promo</button>
            <button @click="tab = 'complaints'" :class="tab === 'complaints' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors">Keluhan</button>
        </nav>
    </div>

    <!-- Riwayat Pesanan Tab -->
    <div x-show="tab === 'orders'" x-cloak>
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Galon</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sumber</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $order->order_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $order->gallon_qty }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ format_rupiah($order->total_price) }}</td>
                            <td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $order->source === 'online' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700' }}">{{ $order->source === 'online' ? 'Online' : 'Langsung' }}</span></td>
                            <td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $order->status === 'selesai' ? 'bg-green-100 text-green-800' : ($order->status === 'dibatalkan' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($order->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada riwayat pesanan di periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>

    <!-- Mutasi Poin Tab -->
    <div x-show="tab === 'points'" x-cloak>
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Poin</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($pointMutations as $mutation)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $mutation->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $mutation->description }}</td>
                            <td class="px-4 py-3 text-right font-bold {{ $mutation->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $mutation->amount > 0 ? '+' : '' }}{{ $mutation->amount }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada mutasi poin.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Voucher & Promo Tab -->
    <div x-show="tab === 'vouchers'" x-cloak>
        <div class="space-y-3">
            @forelse($activeVouchers as $uv)
            <div class="card p-4 border-l-4 border-l-green-500">
                <div class="flex justify-between items-center">
                    <div><h4 class="font-semibold text-gray-900">{{ $uv->voucher->name }}</h4><p class="text-xs text-gray-500 mt-1">Kode: {{ $uv->voucher->code }} · Berlaku s/d {{ $uv->voucher->valid_until }}</p></div>
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800">Aktif</span>
                </div>
            </div>
            @empty
            <div class="card p-8 text-center text-sm text-gray-500">Tidak ada voucher aktif.</div>
            @endforelse
        </div>
    </div>

    <!-- Keluhan Tab -->
    <div x-show="tab === 'complaints'" x-cloak>
        <div class="space-y-3">
            @forelse($recentComplaints as $complaint)
            <div class="card p-4">
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-semibold text-gray-900">{{ $complaint->category }}</h4>
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $complaint->status === 'selesai' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ ucfirst($complaint->status) }}</span>
                </div>
                <p class="text-sm text-gray-600 line-clamp-2">{{ $complaint->description }}</p>
                <p class="text-xs text-gray-400 mt-2">{{ $complaint->created_at->format('d M Y') }}</p>
            </div>
            @empty
            <div class="card p-8 text-center text-sm text-gray-500">Belum ada keluhan dari pelanggan ini.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
