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
        @if($user->loyalty_level === 'bronze') bg-gradient-to-br from-[#e6aa68] to-[#9b5b14] text-white
        @elseif($user->loyalty_level === 'silver') bg-gradient-to-br from-gray-300 to-gray-500 text-gray-900
        @else bg-gradient-to-br from-yellow-300 to-yellow-600 text-yellow-950
        @endif">
        
        <!-- Decoration -->
        <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-white opacity-10"></div>
        <div class="absolute bottom-0 left-0 -ml-8 -mb-8 w-24 h-24 rounded-full bg-black opacity-10"></div>
        
        <!-- Top row: Logo & Level -->
        <div class="flex justify-between items-start relative z-10">
            <div class="flex items-center gap-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                <span class="font-bold text-lg tracking-wider">Ken Water</span>
            </div>
            <div class="px-3 py-1 bg-white/20 rounded-full text-xs font-bold uppercase tracking-wider backdrop-blur-sm border border-white/30">
                {{ $user->loyalty_level }}
            </div>
        </div>
        
        <!-- Bottom row: Name & ID -->
        <div class="relative z-10 flex justify-between items-end">
            <div>
                <p class="text-xs opacity-80 mb-1">MEMBER NAME</p>
                <p class="font-bold text-xl uppercase tracking-widest">{{ $user->name }}</p>
                <p class="font-mono mt-1 opacity-90 text-sm tracking-[0.2em]">{{ chunk_split($user->customer_id, 4, ' ') }}</p>
            </div>
            
            <!-- QR Code (Placeholder visually, use JS library if needed) -->
            <div class="w-16 h-16 bg-white rounded p-1" id="qrcode-container">
                <!-- Fallback if JS fails -->
                <div class="w-full h-full border-2 border-dashed border-gray-300 flex items-center justify-center text-[8px] text-gray-400 text-center leading-none">
                    QR CODE
                </div>
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
