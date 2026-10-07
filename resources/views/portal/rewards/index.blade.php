@extends('layouts.portal')

@section('title', 'Tukar Poin & Reward - Ken Water')

@section('content')
<div class="mb-6">
    <a href="{{ route('portal.home') }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Kembali
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Tukar Poin</h1>
    <p class="text-sm text-gray-500 mt-1">Kumpulkan poin dan tukarkan dengan hadiah menarik.</p>
</div>

<!-- Point Balance Card -->
<div class="card p-6 mb-8 bg-gradient-to-br from-primary to-blue-600 text-white relative overflow-hidden">
    <!-- Decoration -->
    <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-white opacity-10"></div>
    <div class="absolute bottom-0 left-0 -ml-8 -mb-8 w-24 h-24 rounded-full bg-black opacity-10"></div>
    
    <div class="relative z-10 flex flex-col items-center justify-center text-center">
        <p class="text-blue-100 font-medium mb-2">Poin Anda Saat Ini</p>
        <div class="flex items-center gap-2">
            <svg class="w-10 h-10 text-yellow-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
            <span class="text-5xl font-bold tracking-tight">{{ $user->points }}</span>
        </div>
        <p class="text-xs text-blue-100 mt-4 opacity-80">1 Galon = {{ setting('points_per_gallon', 1) }} Poin</p>
    </div>
</div>

<div x-data="{ tab: 'katalog' }">
    <!-- Tabs -->
    <div class="flex border-b border-gray-200 mb-6">
        <button @click="tab = 'katalog'" :class="tab === 'katalog' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="flex-1 py-3 text-sm font-medium border-b-2 text-center transition-colors">
            Katalog Reward
        </button>
        <button @click="tab = 'riwayat'" :class="tab === 'riwayat' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="flex-1 py-3 text-sm font-medium border-b-2 text-center transition-colors relative">
            Riwayat Penukaran
            @if($redemptions->where('status', 'pending')->count() > 0)
            <span class="absolute top-2 right-4 w-2 h-2 rounded-full bg-red-500"></span>
            @endif
        </button>
    </div>

    <!-- Katalog Tab -->
    <div x-show="tab === 'katalog'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @forelse($rewards as $reward)
            <div class="card p-0 flex flex-col h-full hover:shadow-md transition-shadow">
                <div class="p-5 flex-1">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-gray-900 text-lg leading-tight">{{ $reward->name }}</h3>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 shrink-0 ml-2">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            {{ $reward->points_cost }} Poin
                        </span>
                    </div>
                    @if($reward->description)
                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $reward->description }}</p>
                    @endif
                    
                    @if($reward->stock !== null)
                    <p class="text-xs font-medium {{ $reward->stock < 5 ? 'text-red-500' : 'text-green-600' }} mb-2">
                        Sisa stok: {{ $reward->stock }}
                    </p>
                    @endif
                </div>
                <div class="p-4 bg-gray-50 border-t border-gray-100 mt-auto">
                    @if($user->points >= $reward->points_cost)
                    <form method="POST" action="{{ route('portal.rewards.redeem', $reward) }}" onsubmit="return confirm('Tukar {{ $reward->points_cost }} poin dengan {{ $reward->name }}?');">
                        @csrf
                        <button type="submit" class="w-full btn-primary py-2 text-sm">
                            Tukar Sekarang
                        </button>
                    </form>
                    @else
                    <button type="button" disabled class="w-full btn-secondary py-2 text-sm opacity-50 cursor-not-allowed">
                        Poin Tidak Cukup
                    </button>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full py-10 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Belum ada reward yang tersedia saat ini.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Riwayat Tab -->
    <div x-show="tab === 'riwayat'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
        
        <h3 class="font-bold text-gray-800 mb-4 px-1">Kode Penukaran Aktif</h3>
        <div class="space-y-4 mb-8">
            @forelse($redemptions->where('status', 'pending') as $pending)
            <div class="card p-4 border-l-4 border-l-yellow-400 bg-yellow-50/50">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h4 class="font-bold text-gray-900">{{ $pending->reward->name }}</h4>
                        <p class="text-xs text-gray-500">{{ $pending->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Menunggu Validasi</span>
                </div>
                <div class="mt-4 p-3 bg-white rounded-lg border border-yellow-200 text-center">
                    <p class="text-xs text-gray-500 mb-1 uppercase tracking-wider">Kode Penukaran</p>
                    <p class="font-mono text-xl font-bold tracking-[0.2em] text-gray-900">{{ $pending->redemption_code }}</p>
                </div>
                <p class="text-xs text-gray-600 mt-3 text-center">
                    Tunjukkan kode ini ke admin depot untuk mengambil hadiah Anda.
                </p>
            </div>
            @empty
            <div class="p-4 text-center text-gray-500 text-sm bg-gray-50 rounded-lg border border-gray-100">
                Tidak ada penukaran yang menunggu validasi.
            </div>
            @endforelse
        </div>

        <h3 class="font-bold text-gray-800 mb-4 px-1">Riwayat Selesai</h3>
        <div class="space-y-3">
            @forelse($redemptions->whereIn('status', ['validated', 'cancelled']) as $history)
            <div class="card p-4">
                <div class="flex justify-between items-center mb-1">
                    <h4 class="font-medium text-gray-900">{{ $history->reward->name }}</h4>
                    <span class="text-xs font-bold {{ $history->status === 'validated' ? 'text-green-600' : 'text-red-500' }}">
                        {{ $history->status === 'validated' ? 'Selesai' : 'Dibatalkan' }}
                    </span>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-xs text-gray-500">{{ $history->created_at->format('d M Y') }}</span>
                    <span class="text-sm font-semibold text-gray-700">-{{ $history->points_used }} Poin</span>
                </div>
            </div>
            @empty
            <div class="p-4 text-center text-gray-500 text-sm">
                Belum ada riwayat penukaran selesai.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
