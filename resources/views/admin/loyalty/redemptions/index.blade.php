@extends('layouts.admin')

@section('title', 'Validasi Penukaran Reward - Ken Water')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Validasi Penukaran Reward</h1>
        <p class="text-sm text-gray-500 mt-1">Proses dan serahkan hadiah penukaran poin milik pelanggan</p>
    </div>
    <div>
        <a href="{{ route('admin.loyalty.rewards.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm">
            Katalog Reward
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
        {{ session('error') }}
    </div>
@endif

<!-- Filter Status -->
<div class="mb-4 flex gap-2">
    <a href="{{ route('admin.loyalty.redemptions.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ !request('status') ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        Semua
    </a>
    <a href="{{ route('admin.loyalty.redemptions.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('status') === 'pending' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        Pending (Perlu Validasi)
    </a>
    <a href="{{ route('admin.loyalty.redemptions.index', ['status' => 'validated']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('status') === 'validated' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        Divalidasi
    </a>
    <a href="{{ route('admin.loyalty.redemptions.index', ['status' => 'cancelled']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('status') === 'cancelled' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        Dibatalkan
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase tracking-wider border-b border-gray-100">
                <th class="py-3 px-4">Tanggal & Waktu</th>
                <th class="py-3 px-4">Pelanggan</th>
                <th class="py-3 px-4">Reward</th>
                <th class="py-3 px-4">Biaya Poin</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
            @forelse($redemptions as $redemption)
                <tr class="hover:bg-gray-50/50">
                    <td class="py-3 px-4 text-xs text-gray-500 font-medium">
                        {{ $redemption->created_at->translatedFormat('d M Y H:i') }}
                    </td>
                    <td class="py-3 px-4">
                        <a href="{{ route('admin.customers.show', $redemption->user) }}" class="font-bold text-gray-900 hover:text-primary">
                            {{ $redemption->user->name }}
                        </a>
                        <p class="text-xs text-gray-500">{{ $redemption->user->phone }}</p>
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-800">
                        {{ $redemption->reward->name }}
                    </td>
                    <td class="py-3 px-4 font-bold text-amber-600">
                        {{ number_format($redemption->points_used) }} Poin
                    </td>
                    <td class="py-3 px-4">
                        @if($redemption->status === 'pending')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                Pending (Menunggu)
                            </span>
                        @elseif($redemption->status === 'validated')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Divalidasi oleh {{ $redemption->validator?->name ?? 'Admin' }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Dibatalkan
                            </span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right">
                        @if($redemption->status === 'pending')
                            <div class="flex justify-end gap-2">
                                <form action="{{ route('admin.loyalty.redemptions.validate', $redemption) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-semibold shadow-sm">
                                        Validasi & Serahkan
                                    </button>
                                </form>
                                <form action="{{ route('admin.loyalty.redemptions.cancel', $redemption) }}" method="POST" onsubmit="return confirm('Kembalikan poin ke pelanggan dan batalkan?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1 bg-gray-200 hover:bg-red-100 text-red-600 rounded text-xs font-semibold">
                                        BataIkan
                                    </button>
                                </form>
                            </div>
                        @else
                            <span class="text-xs text-gray-400 font-medium">Selesai</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-400">Belum ada penukaran reward.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $redemptions->links() }}
</div>
@endsection
