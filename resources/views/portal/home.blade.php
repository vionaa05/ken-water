@extends('layouts.portal')

@section('title', 'Beranda - Ken Water')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Halo, {{ explode(' ', $user->name)[0] }}!</h1>
        <p class="text-sm text-gray-500 mt-1">Siap pesan air minum hari ini?</p>
    </div>
    
    <!-- Status Aktif / Loyalty Badge -->
    <a href="{{ route('portal.member-card') }}" class="flex flex-col items-center p-2 bg-gradient-to-r from-amber-100 to-yellow-100 rounded-lg shadow-sm border border-yellow-200">
        <span class="text-xs text-gray-600 font-medium mb-1">Level Anda</span>
        <span class="px-2 py-1 rounded-full text-xs font-bold uppercase
            @if($user->loyalty_level === 'bronze') bg-[#cd7f32] text-white
            @elseif($user->loyalty_level === 'silver') bg-gray-300 text-gray-800
            @else bg-yellow-400 text-yellow-900
            @endif">
            {{ $user->loyalty_level }}
        </span>
    </a>
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
    
    @if($progress['next_level'])
        <p class="text-xs text-gray-500 mb-4">Butuh <span class="font-bold text-gray-700">{{ $progress['points_needed'] }} poin</span> lagi untuk naik ke level <span class="font-bold capitalize {{ $progress['next_level'] === 'silver' ? 'text-gray-500' : 'text-yellow-600' }}">{{ $progress['next_level'] }}</span>.</p>
        
        <div class="w-full bg-gray-200 rounded-full h-3 mb-1 overflow-hidden">
            <div class="bg-primary h-3 rounded-full transition-all duration-1000 ease-out relative" style="width: {{ $progress['percentage'] }}%">
                <div class="absolute right-0 top-0 bottom-0 w-8 bg-white/20"></div>
            </div>
        </div>
        <div class="flex justify-between text-[10px] text-gray-400 font-medium">
            <span>{{ $user->loyalty_level }}</span>
            <span>{{ $progress['next_level'] }} ({{ $progress['next_threshold'] }})</span>
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
    <a href="{{ route('portal.complaints.create') }}" class="flex-1 card p-4 flex items-center justify-center gap-2 hover:bg-gray-50 text-gray-600 text-sm font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
        Pusat Bantuan
    </a>
</div>

@endsection
