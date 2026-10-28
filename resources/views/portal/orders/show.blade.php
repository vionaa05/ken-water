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

    <div class="bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-gray-100 space-y-6">
        <!-- Header & Status -->
        <div class="flex items-start justify-between gap-3 border-b border-gray-100 pb-4">
            <div>
                <span class="inline-block text-[11px] font-mono font-semibold text-gray-400 bg-gray-50 px-2 py-0.5 rounded-md">
                    #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                </span>
                <h1 class="text-lg font-bold text-gray-900 mt-1">Detail Pesanan</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->translatedFormat('d M Y, H:i') : '-' }}</p>
            </div>
            <div>
                @if($order->status === 'dipesan')
                    <span class="inline-flex items-center px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200/80 rounded-full text-xs font-semibold">Menunggu Konfirmasi</span>
                @elseif($order->status === 'diproses')
                    <span class="inline-flex items-center px-2.5 py-1 bg-blue-50 text-primary border border-blue-200/80 rounded-full text-xs font-semibold">Sedang Diproses</span>
                @elseif($order->status === 'diantar')
                    <span class="inline-flex items-center px-2.5 py-1 bg-indigo-50 text-indigo-700 border border-indigo-200/80 rounded-full text-xs font-semibold animate-pulse">Sedang Diantar</span>
                @elseif($order->status === 'siap_diambil')
                    <span class="inline-flex items-center px-2.5 py-1 bg-purple-50 text-purple-700 border border-purple-200/80 rounded-full text-xs font-semibold">Siap Diambil</span>
                @elseif($order->status === 'selesai')
                    <span class="inline-flex items-center px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200/80 rounded-full text-xs font-semibold">Selesai</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">Dibatalkan</span>
                @endif
            </div>
        </div>

        <!-- Tracking Visual Progress -->
        @if($order->status !== 'dibatalkan')
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Status Pengiriman</p>
                <div class="relative pl-6 space-y-5 before:absolute before:left-3 before:top-2.5 before:bottom-2.5 before:w-0.5 before:bg-gray-200">
                    <!-- Step 1: Dipesan -->
                    <div class="flex items-start gap-3 relative">
                        <div class="w-6 h-6 -ml-6 rounded-full flex items-center justify-center text-xs font-bold bg-primary text-white ring-4 ring-white shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-900">Pesanan Diterima</p>
                            <p class="text-[11px] text-gray-500 mt-0.5">Pesanan Anda telah masuk ke sistem kami.</p>
                        </div>
                    </div>

                    <!-- Step 2: Diproses -->
                    <div class="flex items-start gap-3 relative">
                        <div class="w-6 h-6 -ml-6 rounded-full flex items-center justify-center text-xs font-bold ring-4 ring-white shadow-xs {{ in_array($order->status, ['diproses', 'diantar', 'siap_diambil', 'selesai']) ? 'bg-primary text-white' : 'bg-gray-100 text-gray-400' }}">
                            @if(in_array($order->status, ['diantar', 'siap_diambil', 'selesai']))
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <span>2</span>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-900">Sedang Disiapkan / Isi Ulang</p>
                            <p class="text-[11px] text-gray-500 mt-0.5">Galon air sedang diproses di depot Ken Water.</p>
                        </div>
                    </div>

                    <!-- Step 3: Diantar / Siap -->
                    <div class="flex items-start gap-3 relative">
                        <div class="w-6 h-6 -ml-6 rounded-full flex items-center justify-center text-xs font-bold ring-4 ring-white shadow-xs {{ in_array($order->status, ['diantar', 'siap_diambil', 'selesai']) ? 'bg-primary text-white' : 'bg-gray-100 text-gray-400' }}">
                            @if($order->status === 'selesai')
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <span>3</span>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-900">
                                {{ $order->delivery_method === 'antar' ? 'Pengantaran Kurir' : 'Siap Diambil di Depot' }}
                            </p>
                            <p class="text-[11px] text-gray-500 mt-0.5">
                                {{ $order->delivery_method === 'antar' ? 'Kurir sedang dalam perjalanan menuju lokasi Anda.' : 'Silakan datang ke depot untuk mengambil galon Anda.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Step 4: Selesai -->
                    <div class="flex items-start gap-3 relative">
                        <div class="w-6 h-6 -ml-6 rounded-full flex items-center justify-center text-xs font-bold ring-4 ring-white shadow-xs {{ $order->status === 'selesai' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-400' }}">
                            @if($order->status === 'selesai')
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <span>4</span>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-900">Selesai</p>
                            <p class="text-[11px] text-gray-500 mt-0.5">Pesanan telah diterima & poin telah ditambahkan ke akun Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Rincian Item -->
        <div class="bg-gray-50/80 rounded-xl p-4 border border-gray-100 space-y-3">
            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Rincian Pembelian</h4>
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-700">Isi Ulang Galon Air ({{ $order->gallon_qty }}x @ Rp {{ number_format($order->unit_price, 0, ',', '.') }})</span>
                <span class="font-semibold text-gray-900">Rp {{ number_format($order->gallon_qty * $order->unit_price, 0, ',', '.') }}</span>
            </div>
            
            <div class="flex items-center justify-between pt-3 border-t border-gray-200/80 text-xs">
                <span class="font-bold text-gray-900">Total Pembayaran</span>
                <span class="text-base font-extrabold text-primary">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Info Pengiriman & Pembayaran -->
        <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="bg-sky-50/50 p-3.5 rounded-xl border border-sky-100">
                <div class="flex items-center gap-1.5 text-gray-500 mb-1">
                    <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="text-[11px] font-medium">Metode Pengiriman</span>
                </div>
                <p class="font-bold text-gray-900">{{ $order->delivery_method === 'antar' ? 'Diantar ke Rumah' : 'Ambil di Depot' }}</p>
                @if($order->delivery_address)
                    <p class="text-[11px] text-gray-600 mt-1 line-clamp-2 leading-relaxed">{{ $order->delivery_address }}</p>
                @endif
            </div>

            <div class="bg-sky-50/50 p-3.5 rounded-xl border border-sky-100">
                <div class="flex items-center gap-1.5 text-gray-500 mb-1">
                    <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <span class="text-[11px] font-medium">Metode Pembayaran</span>
                </div>
                <p class="font-bold text-gray-900">{{ $order->payment_method === 'tunai' ? 'Tunai di Tempat' : 'Transfer Bank' }}</p>
                <div class="mt-1 inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">
                    <span>+{{ $order->gallon_qty }} Poin Loyalitas</span>
                </div>
            </div>
        </div>

        <!-- Hubungi CS -->
        @php
            $depotWa = \App\Models\Setting::where('key', 'depot_whatsapp')->value('value') ?? '6281234567890';
        @endphp
        <div class="pt-1">
            <a href="https://wa.me/{{ $depotWa }}?text=Halo%20Ken%20Water,%20saya%20mau%20tanya%20mengenai%20pesanan%20%23ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}" target="_blank" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-semibold text-xs rounded-xl flex items-center justify-center gap-2 shadow-xs transition-all">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/></svg>
                <span>Tanya via WhatsApp Depot</span>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStatus = @json($order->status);
    const isTerminal = ['selesai', 'dibatalkan'].includes(currentStatus);

    console.log('[Realtime Order Detail] Polling status aktif. Status saat ini:', currentStatus);

    // Jangan poll jika sudah selesai atau dibatalkan
    if (isTerminal) return;

    const basePollUrl = "{{ route('portal.orders.status', $order) }}";
    
    // Cek setiap 2.5 detik secara real-time
    const timer = setInterval(async function() {
        try {
            // Gunakan timestamp cache buster agar browser tidak mengambil cache lama
            const res = await fetch(basePollUrl + '?_t=' + Date.now(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) return;

            const data = await res.json();
            if (data && data.status && data.status !== currentStatus) {
                console.log('[Realtime Order Detail] Status berubah dari', currentStatus, 'ke', data.status, '-> Reloading...');
                currentStatus = data.status;
                clearInterval(timer);
                // Langsung refresh halaman untuk memperbarui status dan step tracking
                window.location.reload();
            }
        } catch (e) {
            // Abaikan kesalahan koneksi sementara
        }
    }, 2500);
});
</script>
@endpush
@endsection
