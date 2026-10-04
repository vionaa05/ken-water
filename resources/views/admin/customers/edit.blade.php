@extends('layouts.admin')
@section('title', 'Edit Pelanggan - Ken Water')
@section('content')
<div class="mb-6">
    <a href="{{ route('admin.customers.show', $customer) }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Kembali ke Detail Pelanggan
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Edit Data: {{ $customer->name }}</h1>
</div>

<div class="max-w-2xl">
    <div class="card p-6">
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="space-y-5">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP/WhatsApp <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $customer->birth_date?->format('Y-m-d')) }}" class="input-field">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap <span class="text-red-500">*</span></label>
                    <textarea name="address" required rows="3" class="input-field">{{ old('address', $customer->address) }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100">
                <h4 class="font-medium text-gray-700 mb-3">Informasi Member</h4>
                <div class="grid grid-cols-2 gap-4 text-sm text-gray-600 bg-gray-50 p-4 rounded-lg">
                    <div><p class="text-xs text-gray-400">ID Pelanggan</p><p class="font-mono font-bold">{{ $customer->customer_id }}</p></div>
                    <div><p class="text-xs text-gray-400">Level Loyalty</p><p class="font-bold uppercase">{{ $customer->loyalty_level }}</p></div>
                    <div><p class="text-xs text-gray-400">Status</p><p class="font-bold">{{ $customer->status_label }}</p></div>
                    <div><p class="text-xs text-gray-400">Poin Aktif</p><p class="font-bold">{{ $customer->points }}</p></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Level, status, dan poin hanya dapat berubah melalui mekanisme sistem (transaksi, reaktivasi).</p>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <a href="{{ route('admin.customers.show', $customer) }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
