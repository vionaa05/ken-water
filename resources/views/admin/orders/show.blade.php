@extends('layouts.admin')
@section('title', 'Detail Pesanan - Ken Water')
@section('content')
<div class="mb-6">
    <a href="{{ route('admin.orders.index') }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Kembali ke Daftar Pesanan
    </a>
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pesanan #{{ $order->id }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $order->order_date->format('d M Y, H:i') }}</p>
        </div>
        <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold
            @if($order->status === 'dipesan') bg-yellow-100 text-yellow-800
            @elseif($order->status === 'diproses') bg-blue-100 text-blue-800
            @elseif(in_array($order->status, ['diantar','siap_diambil'])) bg-purple-100 text-purple-800
            @elseif($order->status === 'selesai') bg-green-100 text-green-800
            @else bg-red-100 text-red-800 @endif">
            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Detail Pesanan -->
        <div class="card p-6">
            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Detail Pesanan
            </h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><p class="text-gray-500 text-xs">Jumlah Galon</p><p class="font-bold text-2xl text-gray-900">{{ $order->gallon_qty }}<span class="text-sm font-normal text-gray-500 ml-1">galon</span></p></div>
                <div><p class="text-gray-500 text-xs">Total Pembayaran</p><p class="font-bold text-xl text-primary">{{ format_rupiah($order->total_price) }}</p></div>
                <div><p class="text-gray-500 text-xs">Pengiriman</p><p class="font-semibold text-gray-800">{{ $order->delivery_method === 'antar' ? '📦 Diantar' : '🏪 Ambil Sendiri' }}</p></div>
                <div><p class="text-gray-500 text-xs">Pembayaran</p><p class="font-semibold text-gray-800 capitalize">{{ $order->payment_method }}</p></div>
                @if($order->delivery_address)
                <div class="col-span-2"><p class="text-gray-500 text-xs">Alamat Pengiriman</p><p class="font-semibold text-gray-800">{{ $order->delivery_address }}</p></div>
                @endif
                @if($order->notes)
                <div class="col-span-2"><p class="text-gray-500 text-xs">Catatan</p><p class="text-gray-700 italic">{{ $order->notes }}</p></div>
                @endif
            </div>
        </div>

        <!-- Update Status -->
        @if(!in_array($order->status, ['selesai', 'dibatalkan']))
        <div class="card p-6">
            <h3 class="font-bold text-gray-900 mb-4">Perbarui Status Pesanan</h3>
            <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @if($order->status === 'dipesan')
                    <button name="status" value="diproses" type="submit" class="btn-primary py-3">✅ Proses Sekarang</button>
                    <button name="status" value="dibatalkan" type="submit" class="btn-danger py-3" onclick="return confirm('Batalkan pesanan ini?')">❌ Batalkan</button>
                    @elseif($order->status === 'diproses')
                    @if($order->delivery_method === 'antar')
                    <button name="status" value="diantar" type="submit" class="btn-primary py-3">🚚 Tandai Diantar</button>
                    @else
                    <button name="status" value="siap_diambil" type="submit" class="btn-primary py-3">✔ Siap Diambil</button>
                    @endif
                    <button name="status" value="dibatalkan" type="submit" class="btn-danger py-3" onclick="return confirm('Batalkan pesanan ini?')">❌ Batalkan</button>
                    @elseif(in_array($order->status, ['diantar', 'siap_diambil']))
                    <button name="status" value="selesai" type="submit" class="btn-primary py-3">🎉 Tandai Selesai</button>
                    <button name="status" value="dibatalkan" type="submit" class="btn-danger py-3" onclick="return confirm('Batalkan pesanan ini?')">❌ Batalkan</button>
                    @endif
                </div>
            </form>
        </div>
        @endif
    </div>

    <!-- Info Pelanggan -->
    <div class="space-y-6">
        <div class="card p-6">
            <h3 class="font-bold text-gray-900 mb-4">Pelanggan</h3>
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-lg mr-3">
                    {{ strtoupper(substr($order->user->name, 0, 1)) }}
                </div>
                <div>
                    <a href="{{ route('admin.customers.show', $order->user) }}" class="font-bold text-gray-900 hover:text-primary">{{ $order->user->name }}</a>
                    <p class="text-xs text-gray-400 font-mono">{{ $order->user->customer_id }}</p>
                </div>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">No. HP</span><span class="font-medium">{{ $order->user->phone }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Level</span><span class="font-bold uppercase {{ $order->user->loyalty_level === 'gold' ? 'text-yellow-600' : ($order->user->loyalty_level === 'silver' ? 'text-gray-500' : 'text-orange-600') }}">{{ $order->user->loyalty_level }}</span></div>
            </div>
        </div>

        @if($order->processor)
        <div class="card p-4">
            <p class="text-xs text-gray-500 mb-1">Diproses oleh</p>
            <p class="font-semibold text-gray-900">{{ $order->processor->name }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
