@extends('layouts.portal')

@section('title', 'Detail Pesanan - Ken Water')

@section('content')
<div class="px-4 py-4 space-y-4 max-w-lg mx-auto">
    <!-- Header Back -->
    <a href="{{ route('portal.orders.index') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 hover:text-primary gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Pesanan Saya
    </a>

    @if(session('success'))
        <div class="p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl p-5 shadow-xs border border-gray-100 space-y-5">
        <!-- Header & Status -->
        <div class="flex justify-between items-start border-b border-gray-100 pb-4">
            <div>
                <span class="text-xs font-mono text-gray-400">#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                <h1 class="text-lg font-extrabold text-gray-900 mt-0.5">Detail Pesanan</h1>
                <p class="text-xs text-gray-500">{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->translatedFormat('d M Y, H:i') : '-' }}</p>
            </div>
            <div>
                @if($order->status === 'dipesan')
                    <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold">Dipesan</span>
                @elseif($order->status === 'diproses')
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">Diproses</span>
                @elseif($order->status === 'diantar')
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-bold animate-pulse">Sedang Diantar</span>
                @elseif($order->status === 'siap_diambil')
                    <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-bold">Siap Diambil</span>
                @elseif($order->status === 'selesai')
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">Selesai</span>
                @else
                    <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold">Dibatalkan</span>
                @endif
            </div>
        </div>

        <!-- Tracking Visual Progress -->
        @if($order->status !== 'dibatalkan')
            <div>
                <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">Status Pengiriman</h3>
                <div class="space-y-4 relative before:absolute before:left-3 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-200">
                    <!-- Step 1: Dipesan -->
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold bg-primary text-white">✓</div>
                        <div>
                            <p class="text-xs font-bold text-gray-900">Pesanan Diterima</p>
                            <p class="text-[11px] text-gray-500">Pesanan Anda telah masuk ke sistem kami.</p>
                        </div>
                    </div>

                    <!-- Step 2: Diproses -->
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ in_array($order->status, ['diproses', 'diantar', 'siap_diambil', 'selesai']) ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500' }}">
                            {{ in_array($order->status, ['diantar', 'siap_diambil', 'selesai']) ? '✓' : '2' }}
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-900">Sedang Siapkan / Isu Ulang</p>
                            <p class="text-[11px] text-gray-500">Galon sedang diproses di depot Ken Water.</p>
                        </div>
                    </div>

                    <!-- Step 3: Diantar / Siap -->
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ in_array($order->status, ['diantar', 'siap_diambil', 'selesai']) ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500' }}">
                            {{ $order->status === 'selesai' ? '✓' : '3' }}
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-900">
                                {{ $order->delivery_method === 'antar' ? 'Pengantaran Kurir' : 'Siap Diambil di Depot' }}
                            </p>
                            <p class="text-[11px] text-gray-500">
                                {{ $order->delivery_method === 'antar' ? 'Kurir sedang dalam perjalanan ke lokasi Anda.' : 'Silakan datang ke depot untuk mengambil galon.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Step 4: Selesai -->
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $order->status === 'selesai' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                            {{ $order->status === 'selesai' ? '✓' : '4' }}
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-900">Selesai</p>
                            <p class="text-[11px] text-gray-500">Pesanan telah diterima & poin telah ditambahkan.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Rincian Item -->
        <div class="bg-gray-50 rounded-xl p-4 space-y-2 border border-gray-100">
            <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Rincian Pembelian</h4>
            <div class="flex justify-between text-xs text-gray-600">
                <span>Isi Ulang Galon Air ({{ $order->gallon_qty }}x @ Rp {{ number_format($order->unit_price, 0, ',', '.') }})</span>
                <span class="font-semibold text-gray-900">Rp {{ number_format($order->gallon_qty * $order->unit_price, 0, ',', '.') }}</span>
            </div>
            
            <div class="flex justify-between text-xs text-gray-600 pt-2 border-t border-gray-200 font-extrabold">
                <span class="text-gray-900">Total Pembayaran</span>
                <span class="text-primary text-base">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Info Pengiriman & Pembayaran -->
        <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="bg-blue-50/50 p-3 rounded-xl border border-blue-100">
                <p class="text-gray-400 font-medium">Metode Kirim</p>
                <p class="font-bold text-gray-800 mt-0.5 capitalize">{{ $order->delivery_method === 'antar' ? '🚀 Diantar ke Rumah' : '🏃 Ambil Sendiri' }}</p>
                @if($order->delivery_address)
                    <p class="text-[11px] text-gray-500 mt-1">{{ $order->delivery_address }}</p>
                @endif
            </div>

            <div class="bg-blue-50/50 p-3 rounded-xl border border-blue-100">
                <p class="text-gray-400 font-medium">Pembayaran</p>
                <p class="font-bold text-gray-800 mt-0.5 capitalize">{{ $order->payment_method === 'tunai' ? '💵 Tunai' : '💳 Transfer' }}</p>
                <p class="text-[11px] font-semibold text-blue-700 mt-1">+{{ $order->gallon_qty }} Poin Loyalitas</p>
            </div>
        </div>

        <!-- Hubungi CS -->
        <div class="pt-2">
            <a href="https://wa.me/6281234567890?text=Halo%20Ken%20Water,%20saya%20mau%20tanya%20mengenai%20pesanan%20%23ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}" target="_blank" class="w-full py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl flex items-center justify-center gap-2 shadow-xs">
                💬 Tanya via WhatsApp Depot
            </a>
        </div>
    </div>
</div>
@endsection
