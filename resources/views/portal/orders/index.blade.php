@extends('layouts.portal')

@section('title', 'Pesanan Saya - Ken Water')

@section('content')
<div class="px-4 py-4 space-y-4 max-w-lg mx-auto">
    <!-- Header Page -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Pesanan Saya</h1>
            <p class="text-xs text-gray-500">Pantau status pengiriman & riwayat pesanan online</p>
        </div>
        <a href="{{ route('portal.order.create') }}" class="px-3.5 py-2 bg-primary text-white text-xs font-bold rounded-xl shadow-md flex items-center gap-1.5 active:scale-95 transition-transform">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Pesan Galon
        </a>
    </div>

    @if(session('success'))
        <div class="p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- List Pesanan -->
    <div class="space-y-3">
        @forelse($orders as $order)
            <a href="{{ route('portal.orders.show', $order) }}" class="block bg-white rounded-2xl p-4 shadow-xs border border-gray-100 hover:border-blue-200 transition-all">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <span class="text-[10px] font-mono text-gray-400">#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                        <p class="text-xs text-gray-500">{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->translatedFormat('d M Y, H:i') : '-' }}</p>
                    </div>
                    <div>
                        @if($order->status === 'dipesan')
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-600 border border-amber-200 rounded-full text-[11px] font-bold">Menunggu Konfirmasi</span>
                        @elseif($order->status === 'diproses')
                            <span class="px-2.5 py-1 bg-blue-50 text-blue-600 border border-blue-200 rounded-full text-[11px] font-bold">Sedang Diproses</span>
                        @elseif($order->status === 'diantar')
                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 border border-indigo-200 rounded-full text-[11px] font-bold animate-pulse">Sedang Diantar</span>
                        @elseif($order->status === 'siap_diambil')
                            <span class="px-2.5 py-1 bg-purple-50 text-purple-600 border border-purple-200 rounded-full text-[11px] font-bold">Siap Diambil</span>
                        @elseif($order->status === 'selesai')
                            <span class="px-2.5 py-1 bg-green-50 text-green-600 border border-green-200 rounded-full text-[11px] font-bold">Selesai</span>
                        @else
                            <span class="px-2.5 py-1 bg-gray-100 text-gray-500 rounded-full text-[11px] font-bold">Dibatalkan</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-gray-50 mt-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-blue-50 text-primary rounded-lg flex items-center justify-center font-bold text-xs">
                            {{ $order->gallon_qty }}x
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-800">{{ $order->gallon_qty }} Galon Air</p>
                            <p class="text-[11px] text-gray-500 capitalize">{{ $order->delivery_method === 'antar' ? '🚀 Diantar ke Rumah' : '🏃 Ambil di Depot' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-extrabold text-primary">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="bg-white rounded-2xl p-8 text-center border border-gray-100 space-y-3">
                <div class="w-16 h-16 bg-blue-50 text-primary rounded-full flex items-center justify-center mx-auto text-2xl">
                    💧
                </div>
                <p class="text-sm font-bold text-gray-800">Belum Ada Pesanan</p>
                <p class="text-xs text-gray-500">Anda belum pernah melakukan pemesanan galon online periode ini.</p>
                <a href="{{ route('portal.order.create') }}" class="inline-block px-5 py-2.5 bg-primary text-white text-xs font-bold rounded-xl shadow-md">
                    Pesan Sekarang
                </a>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
@endsection
