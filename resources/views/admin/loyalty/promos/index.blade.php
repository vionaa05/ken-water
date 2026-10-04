@extends('layouts.admin')

@section('title', 'Promo Personal - Ken Water')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Promo Personal</h1>
        <p class="text-sm text-gray-500 mt-1">Promo diskon khusus yang ditujukan langsung ke individu pelanggan</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.loyalty.vouchers.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm">
            Voucher Massal
        </a>
        <a href="{{ route('admin.loyalty.promos.create') }}" class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium shadow-sm gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Promo Personal
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
                <th class="py-3 px-4">Pelanggan Penerima</th>
                <th class="py-3 px-4">Nama Promo</th>
                <th class="py-3 px-4">Diskon</th>
                <th class="py-3 px-4">Berlaku s/d</th>
                <th class="py-3 px-4">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
            @forelse($promos as $promo)
                <tr class="hover:bg-gray-50/50">
                    <td class="py-3 px-4 font-semibold text-gray-900">
                        <a href="{{ route('admin.customers.show', $promo->user) }}" class="hover:text-primary">
                            {{ $promo->user->name }}
                        </a>
                        <p class="text-xs text-gray-500 font-normal">{{ $promo->user->phone }}</p>
                    </td>
                    <td class="py-3 px-4">
                        <span class="font-semibold text-gray-900">{{ $promo->name }}</span>
                        @if($promo->description)
                            <p class="text-xs text-gray-500 line-clamp-1">{{ $promo->description }}</p>
                        @endif
                    </td>
                    <td class="py-3 px-4 font-bold text-indigo-600">
                        {{ $promo->discount_type === 'nominal' ? 'Rp ' . number_format($promo->discount_value, 0, ',', '.') : $promo->discount_value . '%' }}
                    </td>
                    <td class="py-3 px-4 text-xs text-gray-500 font-medium">
                        {{ \Carbon\Carbon::parse($promo->valid_until)->format('d M Y') }}
                    </td>
                    <td class="py-3 px-4">
                        @if($promo->is_used)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                Sudah Digunakan
                            </span>
                        @elseif($promo->is_expired)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                Kadaluarsa
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Aktif (Belum Digunakan)
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-gray-400">Belum ada promo personal diberikan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $promos->links() }}
</div>
@endsection
