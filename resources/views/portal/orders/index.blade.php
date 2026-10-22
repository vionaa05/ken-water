@extends('layouts.portal')

@section('title', 'Pesanan Saya - Ken Water')

@section('content')
<div class="px-4 py-4 max-w-lg mx-auto space-y-4">
    <!-- Back Button -->
    <a href="{{ route('portal.home') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 hover:text-primary transition-colors gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Beranda
    </a>

    <!-- Header Page (Clean without redundant button) -->
    <div>
        <h1 class="text-lg font-bold text-gray-900 leading-tight">Pesanan Saya</h1>
        <p class="text-xs text-gray-500 mt-0.5">Pantau status pengiriman & riwayat pesanan air galon Anda</p>
    </div>

    @if(session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- List Pesanan -->
    <div class="space-y-3">
        @forelse($orders as $order)
            <a href="{{ route('portal.orders.show', $order) }}" class="block bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:border-primary/40 hover:shadow-md transition-all group">
                <div class="flex items-center justify-between gap-2 mb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-mono font-semibold text-gray-400 bg-gray-50 px-2 py-0.5 rounded-md">
                            #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="text-[11px] text-gray-400">
                            {{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->translatedFormat('d M Y, H:i') : '-' }}
                        </span>
                    </div>
                    <div>
                        @if($order->status === 'dipesan')
                            <span class="inline-flex items-center px-2.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-200/80 rounded-full text-[10px] font-semibold">Menunggu Konfirmasi</span>
                        @elseif($order->status === 'diproses')
                            <span class="inline-flex items-center px-2.5 py-0.5 bg-blue-50 text-primary border border-blue-200/80 rounded-full text-[10px] font-semibold">Sedang Diproses</span>
                        @elseif($order->status === 'diantar')
                            <span class="inline-flex items-center px-2.5 py-0.5 bg-indigo-50 text-indigo-700 border border-indigo-200/80 rounded-full text-[10px] font-semibold animate-pulse">Sedang Diantar</span>
                        @elseif($order->status === 'siap_diambil')
                            <span class="inline-flex items-center px-2.5 py-0.5 bg-purple-50 text-purple-700 border border-purple-200/80 rounded-full text-[10px] font-semibold">Siap Diambil</span>
                        @elseif($order->status === 'selesai')
                            <span class="inline-flex items-center px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200/80 rounded-full text-[10px] font-semibold">Selesai</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 bg-gray-100 text-gray-600 rounded-full text-[10px] font-semibold">Dibatalkan</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2.5 border-t border-gray-50 mt-1">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 bg-blue-50 text-primary rounded-xl flex items-center justify-center font-bold text-xs flex-shrink-0">
                            {{ $order->gallon_qty }}x
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-800">{{ $order->gallon_qty }} Galon Air</p>
                            <p class="text-[11px] text-gray-500 capitalize flex items-center gap-1 mt-0.5">
                                @if($order->delivery_method === 'antar')
                                    <span>🚀 Diantar ke Rumah</span>
                                @else
                                    <span>🏃 Ambil di Depot</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-gray-400 block">Total</span>
                        <p class="text-xs font-bold text-primary">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="bg-white rounded-2xl p-8 text-center border border-gray-100 shadow-sm space-y-2">
                <div class="w-12 h-12 bg-blue-50 text-primary rounded-full flex items-center justify-center mx-auto text-xl">
                    💧
                </div>
                <p class="text-sm font-bold text-gray-800">Belum Ada Pesanan</p>
                <p class="text-xs text-gray-500 max-w-xs mx-auto">Anda belum memiliki riwayat pemesanan galon online. Untuk memesan, gunakan menu <strong>Pesan</strong> di navigasi bawah.</p>
            </div>
        @endforelse
    </div>

    @if($orders->hasPages())
        <div class="pt-2">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
