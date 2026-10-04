@extends('layouts.portal')

@section('title', 'Voucher & Promo Saya - Ken Water')

@section('content')
<div class="px-4 py-4 space-y-4 max-w-lg mx-auto">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Voucher & Promo Saya</h1>
        <p class="text-xs text-gray-500">Gunakan diskon saat pemesanan galon online</p>
    </div>

    <!-- Section 1: Promo Personal Spesial -->
    @if($promos->count() > 0)
        <div class="space-y-2">
            <h2 class="text-xs font-bold text-gray-700 uppercase tracking-wider">🔥 Promo Eksklusif Kamu</h2>
            @foreach($promos as $promo)
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-4 text-white shadow-md relative overflow-hidden">
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full text-white">Promo Khusus</span>
                            <h3 class="font-extrabold text-base mt-1">{{ $promo->name }}</h3>
                            <p class="text-xs text-indigo-100 mt-0.5">{{ $promo->description }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-black text-amber-300">
                                {{ $promo->discount_type === 'nominal' ? 'Rp ' . number_format($promo->discount_value, 0, ',', '.') : $promo->discount_value . '%' }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-white/20 flex justify-between items-center text-[11px] text-indigo-100 relative z-10">
                        <span>s/d {{ \Carbon\Carbon::parse($promo->valid_until)->translatedFormat('d M Y') }}</span>
                        @if($promo->is_used)
                            <span class="font-bold text-white bg-black/30 px-2 py-0.5 rounded">Terpakai</span>
                        @else
                            <a href="{{ route('portal.order.create') }}" class="font-bold text-indigo-900 bg-white px-3 py-1 rounded-lg shadow-xs hover:bg-indigo-50">Pakai Promo</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Section 2: Voucher Saya -->
    <div class="space-y-2">
        <h2 class="text-xs font-bold text-gray-700 uppercase tracking-wider">🎟️ Voucher Saya</h2>
        <div class="space-y-3">
            @forelse($vouchers as $uv)
                @php $v = $uv->voucher; @endphp
                <div class="bg-white rounded-2xl p-4 border border-blue-100 shadow-xs flex items-center justify-between">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 bg-blue-100 text-primary font-mono font-bold text-xs rounded border border-blue-200">
                                {{ $v->code }}
                            </span>
                            @if($uv->status === 'terpakai')
                                <span class="text-[10px] bg-gray-100 text-gray-500 font-bold px-2 py-0.5 rounded-full">Terpakai</span>
                            @elseif($uv->status === 'kadaluarsa')
                                <span class="text-[10px] bg-red-100 text-red-600 font-bold px-2 py-0.5 rounded-full">Kadaluarsa</span>
                            @else
                                <span class="text-[10px] bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">Aktif</span>
                            @endif
                        </div>
                        <h3 class="font-bold text-xs text-gray-900">{{ $v->name }}</h3>
                        <p class="text-[11px] text-gray-500">
                            Diskon: <strong class="text-emerald-600">{{ $v->discount_type === 'nominal' ? 'Rp ' . number_format($v->discount_value, 0, ',', '.') : $v->discount_value . '%' }}</strong>
                            @if($v->min_purchase) (Min. Rp {{ number_format($v->min_purchase, 0, ',', '.') }}) @endif
                        </p>
                    </div>

                    <div>
                        @if($uv->status === 'aktif')
                            <a href="{{ route('portal.order.create') }}" class="px-3 py-1.5 bg-primary text-white text-xs font-bold rounded-xl shadow-xs">
                                Pakai
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl p-6 text-center border border-gray-100 text-xs text-gray-500">
                    Belum ada voucher aktif saat ini.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
