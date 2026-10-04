@extends('layouts.admin')
@section('title', 'Tambah Pelanggan - Ken Water')
@section('content')
<div class="mb-6">
    <a href="{{ route('admin.customers.index') }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Kembali ke Daftar Pelanggan
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Tambah Pelanggan Baru</h1>
</div>

<div class="max-w-2xl">
    <div class="card p-6">
        <form method="POST" action="{{ route('admin.customers.store') }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="input-field" placeholder="Nama lengkap pelanggan">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP/WhatsApp <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required class="input-field" placeholder="08123456789">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="input-field">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap <span class="text-red-500">*</span></label>
                    <textarea name="address" required rows="3" class="input-field" placeholder="Alamat lengkap pelanggan">{{ old('address') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required minlength="6" class="input-field" placeholder="Min. 6 karakter">
                </div>
            </div>
            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="btn-primary">Simpan Pelanggan</button>
                <a href="{{ route('admin.customers.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
