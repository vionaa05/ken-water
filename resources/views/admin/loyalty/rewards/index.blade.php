@extends('layouts.admin')

@section('title', 'Katalog Reward - Ken Water')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Katalog Reward</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola hadiah / item yang dapat ditukarkan pelanggan menggunakan poin loyalitas</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.loyalty.redemptions.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm">
            Validasi Penukaran
        </a>
        <a href="{{ route('admin.loyalty.rewards.create') }}" class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium shadow-sm gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Reward
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase tracking-wider border-b border-gray-100">
                <th class="py-3 px-4">Nama Reward</th>
                <th class="py-3 px-4">Biaya Poin</th>
                <th class="py-3 px-4">Stok</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
            @forelse($rewards as $reward)
                <tr class="hover:bg-gray-50/50">
                    <td class="py-3 px-4 font-semibold text-gray-900">
                        {{ $reward->name }}
                        @if($reward->description)
                            <p class="text-xs font-normal text-gray-500 line-clamp-1">{{ $reward->description }}</p>
                        @endif
                    </td>
                    <td class="py-3 px-4 font-bold text-amber-600">
                        {{ number_format($reward->points_cost) }} Poin
                    </td>
                    <td class="py-3 px-4 font-medium">
                        {{ $reward->stock !== null ? number_format($reward->stock) . ' unit' : 'Tak Terbatas' }}
                    </td>
                    <td class="py-3 px-4">
                        @if($reward->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                Nonaktif
                            </span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right space-x-2">
                        <a href="{{ route('admin.loyalty.rewards.edit', $reward) }}" class="text-primary hover:text-primary-dark font-medium text-xs">Edit</a>
                        <form action="{{ route('admin.loyalty.rewards.destroy', $reward) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus reward ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-xs">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-gray-400">Belum ada reward ditambahkan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $rewards->links() }}
</div>
@endsection
