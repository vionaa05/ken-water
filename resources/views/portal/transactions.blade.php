@extends('layouts.portal')

@section('title', 'Riwayat Transaksi - Ken Water')

@section('content')
<div class="px-4 py-4 space-y-4 max-w-lg mx-auto">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Riwayat Transaksi</h1>
        <p class="text-xs text-gray-500">Daftar transaksi galon yang telah selesai (Online & Kasir)</p>
    </div>

    <div class="space-y-3">
        @forelse($orders as $order)
            <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-50 text-green-600 rounded-full flex items-center justify-center font-bold text-sm">
                        ✓
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-900">{{ $order->gallon_qty }} Galon Air Isi Ulang</p>
                        <p class="text-[11px] text-gray-400">
                            {{ $order->completed_at ? \Carbon\Carbon::parse($order->completed_at)->translatedFormat('d M Y, H:i') : ($order->order_date ? \Carbon\Carbon::parse($order->order_date)->translatedFormat('d M Y') : '-') }}
                            • <span class="capitalize">{{ $order->source === 'online' ? 'Online' : 'Kasir / Langsung' }}</span>
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs font-extrabold text-gray-900">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                    <span class="inline-block text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full mt-0.5">
                        +{{ $order->gallon_qty }} Poin
                    </span>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-8 text-center border border-gray-100 space-y-2">
                <p class="text-sm font-bold text-gray-800">Belum Ada Transaksi Selesai</p>
                <p class="text-xs text-gray-500">Transaksi yang telah kamu selesaikan akan muncul di sini.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
@endsection
