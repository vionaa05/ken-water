@extends('layouts.portal')

@section('title', 'Beranda - Ken Water')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Halo, {{ explode(' ', $user->name)[0] }}!</h1>
        <p class="text-sm text-gray-500 mt-1">Siap pesan air minum hari ini?</p>
    </div>
</div>

<!-- Digital Loyalty Member Card (Identik dengan Mobile) -->
<div class="mb-6">
    <div class="relative w-full aspect-[1.75/1] sm:aspect-[1.9/1] max-w-md mx-auto rounded-2xl shadow-lg overflow-hidden p-5 flex flex-col justify-between
        @if($user->loyalty_level === 'gold') bg-gradient-to-br from-[#78350f] via-[#d97706] to-[#f59e0b] text-white
        @elseif($user->loyalty_level === 'silver') bg-gradient-to-br from-[#1e3a5f] via-[#334155] to-[#64748b] text-white
        @else bg-gradient-to-br from-[#0284c7] via-[#0369a1] to-[#0c4a6e] text-white
        @endif">
        
        <!-- Decoration Circles -->
        <div class="absolute -top-6 -right-6 w-28 h-28 rounded-full bg-white opacity-10 pointer-events-none"></div>
        <div class="absolute -bottom-8 -right-3 w-20 h-20 rounded-full bg-white opacity-5 pointer-events-none"></div>

        <!-- Top Row: Logo & Level Badge -->
        <div class="flex justify-between items-start relative z-10">
            <div class="flex items-center gap-1.5">
                <svg class="w-5 h-5 text-white/80" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                <span class="font-bold text-sm tracking-wider text-white/90">Ken Water</span>
            </div>
            <div class="px-2.5 py-1 bg-white/20 rounded-full text-xs font-bold uppercase tracking-wider backdrop-blur-sm border border-white/30 text-white">
                {{ $user->loyalty_level === 'gold' ? '🥇' : ($user->loyalty_level === 'silver' ? '🥈' : '🥉') }} {{ strtoupper($user->loyalty_level) }}
            </div>
        </div>

        <!-- Bottom Row: Name, ID, Poin, Status, and QR -->
        <div class="relative z-10 flex justify-between items-end">
            <div class="min-w-0 pr-2">
                <p class="font-bold text-base text-white leading-tight truncate">{{ $user->name }}</p>
                <p class="font-mono mt-0.5 text-xs text-white/70 tracking-widest">{{ $user->customer_id }}</p>
                <p class="mt-2 text-sm font-bold text-white flex items-center gap-1">
                    <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    {{ (int)$user->points }} Poin
                </p>
                <div class="mt-1">
                    <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold {{ ($user->status ?? 'aktif') === 'aktif' ? 'bg-emerald-400/25 text-emerald-200' : 'bg-red-400/25 text-red-200' }}">
                        {{ ($user->status ?? 'aktif') === 'aktif' ? '● Aktif' : '● Tidak Aktif' }}
                    </span>
                </div>
            </div>

            <!-- QR Code -->
            <div class="w-14 h-14 bg-white rounded-lg p-1 shadow-sm shrink-0 flex items-center justify-center">
                @if(isset($qrBase64) && $qrBase64)
                <img src="{{ $qrBase64 }}" alt="QR" class="w-full h-full object-contain">
                @else
                <div class="text-[8px] text-gray-400 font-mono text-center">QR CODE</div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Action Unduh / Lihat Kartu -->
    <div class="flex justify-center gap-3 mt-2.5">
        <a href="{{ route('portal.member-card') }}" class="text-xs text-primary font-medium hover:underline flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            Detail Kartu Member
        </a>
        <span class="text-gray-300">•</span>
        <a href="{{ route('portal.member-card.download') }}" class="text-xs text-gray-600 font-medium hover:text-primary flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Unduh PDF
        </a>
    </div>
</div>

<!-- Promo & Voucher Banner -->
@if($personalPromosCount > 0 || $activeVouchersCount > 0)
<div class="mb-8">
    <a href="{{ route('portal.vouchers.index') }}" class="block w-full rounded-2xl bg-gradient-to-r from-primary to-blue-500 p-5 text-white shadow-md relative overflow-hidden transform hover:scale-[1.02] transition-transform">
        <!-- Decoration -->
        <div class="absolute -right-6 -top-6 opacity-20">
            <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
        </div>
        
        <div class="relative z-10 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-lg mb-1">Yey! Anda punya diskon 🎉</h3>
                <p class="text-blue-100 text-sm">
                    {{ $personalPromosCount }} Promo Personal & {{ $activeVouchersCount }} Voucher Aktif
                </p>
            </div>
            <div class="bg-white/20 p-2 rounded-full backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </div>
    </a>
</div>
@endif

<!-- Quick Actions Grid -->
<div class="grid grid-cols-2 gap-4 mb-8">
    <a href="{{ route('portal.order.create') }}" class="card flex flex-col items-center justify-center p-6 text-center hover:bg-blue-50 hover:border-primary transition-colors group">
        <div class="w-14 h-14 rounded-full bg-blue-100 text-primary flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        </div>
        <span class="font-semibold text-gray-800">Pesan Sekarang</span>
        <span class="text-xs text-gray-500 mt-1">Antar / Ambil</span>
    </a>
    
    <a href="{{ route('portal.rewards.index') }}" class="card flex flex-col items-center justify-center p-6 text-center hover:bg-orange-50 hover:border-orange-500 transition-colors group">
        <div class="w-14 h-14 rounded-full bg-orange-100 text-orange-500 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
        </div>
        <span class="font-semibold text-gray-800">Tukar Poin</span>
        <span class="text-xs text-gray-500 mt-1">{{ $user->points }} Poin</span>
    </a>

    <a href="{{ route('portal.orders.index') }}" class="card flex flex-col items-center justify-center p-6 text-center hover:bg-gray-50 transition-colors group">
        <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
        </div>
        <span class="font-medium text-sm text-gray-700">Pesanan Aktif</span>
    </a>

    <a href="{{ route('portal.transactions') }}" class="card flex flex-col items-center justify-center p-6 text-center hover:bg-gray-50 transition-colors group">
        <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <span class="font-medium text-sm text-gray-700">Riwayat Belanja</span>
    </a>
</div>

<!-- Progress Level Section -->
<div class="card p-5 mb-8 bg-white border border-gray-100 shadow-sm rounded-xl">
    <div class="flex justify-between items-end mb-2">
        <h3 class="font-bold text-gray-800">Progress Level</h3>
        <span class="text-xs font-semibold text-primary">{{ $user->points }} Poin Terkumpul</span>
    </div>
    
    @if(!$progress['is_max'] && !empty($progress['next_level']))
        <p class="text-xs text-gray-500 mb-4">Butuh <span class="font-bold text-gray-700">{{ $progress['points_needed'] }} poin</span> lagi untuk naik ke level <span class="font-bold capitalize {{ strtolower($progress['next_level']) === 'silver' ? 'text-gray-500' : 'text-yellow-600' }}">{{ $progress['next_level'] }}</span>.</p>
        
        <div class="w-full bg-gray-200 rounded-full h-3 mb-1 overflow-hidden">
            <div class="bg-primary h-3 rounded-full transition-all duration-1000 ease-out relative" style="width: {{ $progress['percentage'] }}%">
                <div class="absolute right-0 top-0 bottom-0 w-8 bg-white/20"></div>
            </div>
        </div>
        <div class="flex justify-between text-[10px] text-gray-400 font-medium">
            <span>{{ ucfirst($user->loyalty_level) }}</span>
            <span>{{ $progress['next_level'] }} ({{ $progress['next_threshold'] }} Poin)</span>
        </div>
    @else
        <p class="text-xs text-green-600 mb-4 font-medium">Selamat! Anda sudah mencapai level tertinggi (Gold).</p>
        <div class="w-full bg-gray-200 rounded-full h-3 mb-1 overflow-hidden">
            <div class="bg-yellow-400 h-3 rounded-full relative" style="width: 100%"></div>
        </div>
    @endif
</div>

<!-- Bottom Help Section -->
<div class="flex gap-4 mb-4">
    <a href="{{ route('portal.complaints.index') }}" class="flex-1 card p-4 flex items-center justify-center gap-2 hover:bg-gray-50 text-gray-600 text-sm font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
        Pusat Bantuan & Keluhan
    </a>
</div>

@endsection
