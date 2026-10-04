@extends('layouts.admin')
@section('title', 'Data Pelanggan - Ken Water')
@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Data Pelanggan</h1>
        <p class="text-sm text-gray-500 mt-1">Total {{ $customers->total() }} pelanggan terdaftar.</p>
    </div>
    <a href="{{ route('admin.customers.create') }}" class="btn-primary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Tambah Pelanggan
    </a>
</div>

<!-- Filter -->
<div class="card p-4 mb-6">
    <form method="GET" action="{{ route('admin.customers.index') }}" class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, HP, atau ID..." 
                class="input-field w-full">
        </div>
        <select name="status" class="input-field sm:w-40">
            <option value="">Semua Status</option>
            <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="tidak_aktif" {{ request('status') === 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
        </select>
        <select name="level" class="input-field sm:w-40">
            <option value="">Semua Level</option>
            <option value="bronze" {{ request('level') === 'bronze' ? 'selected' : '' }}>Bronze</option>
            <option value="silver" {{ request('level') === 'silver' ? 'selected' : '' }}>Silver</option>
            <option value="gold" {{ request('level') === 'gold' ? 'selected' : '' }}>Gold</option>
        </select>
        <button type="submit" class="btn-primary">Filter</button>
        @if(request()->hasAny(['search','status','level']))
        <a href="{{ route('admin.customers.index') }}" class="btn-secondary">Reset</a>
        @endif
    </form>
</div>

<!-- Tabel -->
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">No. HP</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Level</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Poin</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Total Belanja</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($customers as $customer)
                <tr class="hover:bg-blue-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-sm mr-3 shrink-0
                                @if($customer->loyalty_level === 'gold') bg-yellow-500
                                @elseif($customer->loyalty_level === 'silver') bg-gray-400
                                @else bg-orange-400 @endif">
                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                            </div>
                            <div>
                                <a href="{{ route('admin.customers.show', $customer) }}" class="text-sm font-semibold text-gray-900 hover:text-primary">{{ $customer->name }}</a>
                                <p class="text-xs text-gray-400 font-mono">{{ $customer->customer_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 hidden sm:table-cell">{{ $customer->phone }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $customer->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $customer->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase
                            @if($customer->loyalty_level === 'gold') bg-yellow-100 text-yellow-800
                            @elseif($customer->loyalty_level === 'silver') bg-gray-100 text-gray-700
                            @else bg-orange-100 text-orange-800 @endif">
                            {{ $customer->loyalty_level }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-700 hidden lg:table-cell">{{ $customer->points }}</td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-700 hidden lg:table-cell">{{ format_rupiah($customer->total_spend) }}</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('admin.customers.show', $customer) }}" class="text-primary hover:text-primary-dark text-sm font-medium mr-3">Detail</a>
                        <a href="{{ route('admin.customers.edit', $customer) }}" class="text-gray-500 hover:text-gray-700 text-sm font-medium">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Tidak ada pelanggan ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
        {{ $customers->links() }}
    </div>
    @endif
</div>
@endsection
