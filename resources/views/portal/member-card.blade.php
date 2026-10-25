@extends('layouts.portal')

@section('title', 'Kartu Member Digital - Ken Water')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <a href="{{ route('portal.home') }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Kartu Member</h1>
    </div>
    
    <a href="{{ route('portal.member-card.download') }}" class="btn-secondary py-2 px-3 text-sm">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
        Unduh PDF
    </a>
</div>

<!-- Digital Card -->
<div class="flex justify-center mb-8">
    <div class="relative w-full max-w-sm aspect-[1.586/1] rounded-2xl shadow-xl overflow-hidden p-6 flex flex-col justify-between
        @if($user->loyalty_level === 'gold') bg-gradient-to-br from-[#78350f] via-[#d97706] to-[#f59e0b] text-white
        @elseif($user->loyalty_level === 'silver') bg-gradient-to-br from-[#1e3a5f] via-[#334155] to-[#64748b] text-white
        @else bg-gradient-to-br from-[#0284c7] via-[#0369a1] to-[#0c4a6e] text-white
        @endif">
        
        <!-- Decoration -->
        <div class="absolute -top-5 -right-5 w-28 h-28 rounded-full bg-white opacity-10 pointer-events-none"></div>
        <div class="absolute -bottom-6 -right-2 w-20 h-20 rounded-full bg-white opacity-5 pointer-events-none"></div>
        
        <!-- Top row: Logo & Level -->
        <div class="flex justify-between items-start relative z-10">
            <div class="flex items-center gap-2">
                <svg class="w-6 h-6 text-white/80" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                <span class="font-bold text-base tracking-wider text-white/90">Ken Water</span>
            </div>
            <div class="px-3 py-1 bg-white/20 rounded-full text-xs font-bold uppercase tracking-wider backdrop-blur-sm border border-white/30 text-white">
                {{ $user->loyalty_level === 'gold' ? '🥇' : ($user->loyalty_level === 'silver' ? '🥈' : '🥉') }} {{ strtoupper($user->loyalty_level) }}
            </div>
        </div>
        
        <!-- Bottom row: Name & ID -->
        <div class="relative z-10 flex justify-between items-end">
            <div>
                <p class="font-bold text-lg text-white leading-tight">{{ $user->name }}</p>
                <p class="font-mono mt-0.5 text-xs text-white/70 tracking-widest">{{ $user->customer_id }}</p>
                <p class="mt-2 text-sm font-bold text-white flex items-center gap-1">
                    <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    {{ $user->points }} Poin
                </p>
                <div class="mt-1">
                    <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold {{ ($user->status ?? 'aktif') === 'aktif' ? 'bg-emerald-400/25 text-emerald-200' : 'bg-red-400/25 text-red-200' }}">
                        {{ ($user->status ?? 'aktif') === 'aktif' ? '● Aktif' : '● Tidak Aktif' }}
                    </span>
                </div>
            </div>
            
            <!-- QR Code -->
            <div class="w-16 h-16 bg-white rounded-lg p-1.5 shadow-sm shrink-0 flex items-center justify-center" id="qrcode-container">
                @if(isset($qrBase64) && $qrBase64)
                <img src="{{ $qrBase64 }}" alt="QR" class="w-full h-full object-contain">
                @else
                <!-- Fallback if JS fails -->
                <div class="w-full h-full border border-dashed border-gray-300 flex items-center justify-center text-[8px] text-gray-400 text-center leading-none">
                    QR CODE
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Info & Progress -->
<div class="card p-6 border border-gray-100 shadow-sm">
    <div class="text-center mb-6">
        <h3 class="text-sm font-medium text-gray-500 mb-1">Total Poin Saat Ini</h3>
        <p class="text-4xl font-bold text-primary">{{ $user->points }}</p>
    </div>

    @if($progress['next_level'])
        <p class="text-sm text-center text-gray-600 mb-4">
            Tingkatkan transaksi, kumpulkan <span class="font-bold text-gray-800">{{ $progress['points_needed'] }} poin</span> lagi untuk naik ke level <span class="font-bold capitalize {{ $progress['next_level'] === 'silver' ? 'text-gray-500' : 'text-yellow-600' }}">{{ $progress['next_level'] }}</span>!
        </p>
        
        <div class="w-full bg-gray-200 rounded-full h-4 mb-2 overflow-hidden shadow-inner">
            <div class="bg-primary h-4 rounded-full transition-all duration-1000 ease-out relative" style="width: {{ $progress['percentage'] }}%">
                <div class="absolute right-0 top-0 bottom-0 w-8 bg-white/20"></div>
            </div>
        </div>
        <div class="flex justify-between text-xs text-gray-500 font-medium">
            <span class="uppercase font-bold">{{ $user->loyalty_level }}</span>
            <span class="uppercase font-bold">{{ $progress['next_level'] }}</span>
        </div>
    @else
        <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-center">
            <svg class="w-8 h-8 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
            <p class="font-bold text-green-800">Level Tertinggi Tercapai!</p>
            <p class="text-sm text-green-700 mt-1">Anda menikmati keuntungan maksimal sebagai pelanggan Gold Ken Water.</p>
        </div>
    @endif
</div>

<!-- QR Code Library -->
<script src="https://cdn.jsdelivr.net/npm/qrcode-svg@1.1.0/lib/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const qrcode = new QRCode({
            content: "{{ $user->customer_id }}",
            padding: 0,
            width: 64,
            height: 64,
            color: "#000000",
            background: "#ffffff",
            ecl: "L"
        });
        document.getElementById('qrcode-container').innerHTML = qrcode.svg();
    });
</script>
@endsection
