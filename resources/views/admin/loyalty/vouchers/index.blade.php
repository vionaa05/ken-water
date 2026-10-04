@extends('layouts.admin')

@section('title', 'Manajemen Voucher - Ken Water')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Manajemen Voucher</h1>
        <p class="text-sm text-gray-500 mt-1">Buat kode voucher diskon & distribusikan ke pelanggan</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.loyalty.promos.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm">
            Promo Personal
        </a>
        <a href="{{ route('admin.loyalty.vouchers.create') }}" class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium shadow-sm gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Voucher Baru
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
                <th class="py-3 px-4">Kode & Nama</th>
                <th class="py-3 px-4">Nilai Diskon</th>
                <th class="py-3 px-4">Min. Belanja</th>
                <th class="py-3 px-4">Masa Berlaku</th>
                <th class="py-3 px-4">Penggunaan</th>
                <th class="py-3 px-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
            @forelse($vouchers as $voucher)
                <tr class="hover:bg-gray-50/50">
                    <td class="py-3 px-4">
                        <span class="inline-block px-2 py-0.5 bg-blue-100 text-blue-800 font-mono font-bold text-xs rounded border border-blue-200 mb-1">
                            {{ $voucher->code }}
                        </span>
                        <p class="font-semibold text-gray-900">{{ $voucher->name }}</p>
                    </td>
                    <td class="py-3 px-4 font-bold text-emerald-600">
                        {{ $voucher->discount_type === 'nominal' ? 'Rp ' . number_format($voucher->discount_value, 0, ',', '.') : $voucher->discount_value . '%' }}
                    </td>
                    <td class="py-3 px-4 font-medium text-gray-600">
                        {{ $voucher->min_purchase ? 'Rp ' . number_format($voucher->min_purchase, 0, ',', '.') : 'Tidak ada' }}
                    </td>
                    <td class="py-3 px-4 text-xs text-gray-500">
                        {{ \Carbon\Carbon::parse($voucher->valid_from)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($voucher->valid_until)->format('d M Y') }}
                    </td>
                    <td class="py-3 px-4 text-xs font-medium">
                        {{ $voucher->used_count }}x terpakai
                        @if($voucher->max_usage)
                            <span class="text-gray-400">/ {{ $voucher->max_usage }} max</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right">
                        <a href="{{ route('admin.loyalty.vouchers.assign', $voucher) }}" class="inline-flex items-center px-3 py-1 bg-primary text-white text-xs font-semibold rounded hover:bg-primary-dark shadow-xs">
                            Bagikan Voucher
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-400">Belum ada voucher dibuat.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $vouchers->links() }}
</div>
@endsection
