@extends('layouts.admin')

@section('title', 'Buat Voucher Baru - Ken Water')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.loyalty.vouchers.index') }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Daftar Voucher
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Buat Voucher Diskon Baru</h1>
    <p class="text-sm text-gray-500 mt-1">Konfigurasi nilai diskon, syarat, dan tanggal berlaku</p>
</div>

<div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <form action="{{ route('admin.loyalty.vouchers.store') }}" method="POST">
        @csrf
        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Voucher <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" required placeholder="Contoh: HEMAT5K" uppercase
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm font-mono font-bold uppercase">
                    @error('code')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Voucher <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Diskon Merdeka 5 Ribu"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                    @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Voucher</label>
                <textarea name="description" rows="2" placeholder="Ketentuan & syarat voucher"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Diskon <span class="text-red-500">*</span></label>
                    <select name="discount_type" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                        <option value="nominal" {{ old('discount_type') === 'nominal' ? 'selected' : '' }}>Nominal (Rp)</option>
                        <option value="persen" {{ old('discount_type') === 'persen' ? 'selected' : '' }}>Persentase (%)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Diskon <span class="text-red-500">*</span></label>
                    <input type="number" name="discount_value" value="{{ old('discount_value') }}" min="1" required placeholder="Contoh: 5000 / 10"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                    @error('discount_value')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Minimal Pembelian (Rp)</label>
                <input type="number" name="min_purchase" value="{{ old('min_purchase') }}" min="0" placeholder="Contoh: 15000 (Kosongkan jika tanpa min belanja)"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Dari <span class="text-red-500">*</span></label>
                    <input type="date" name="valid_from" value="{{ old('valid_from', date('Y-m-d')) }}" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Sampai <span class="text-red-500">*</span></label>
                    <input type="date" name="valid_until" value="{{ old('valid_until', date('Y-m-d', strtotime('+30 days'))) }}" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ketentuan Pakai <span class="text-red-500">*</span></label>
                    <select name="usage_type" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                        <option value="sekali">Sekali Pakai per Pelanggan</option>
                        <option value="banyak">Bisa Digunakan Berulang</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Maksimal Total Penggunaan</label>
                    <input type="number" name="max_usage" value="{{ old('max_usage') }}" min="1" placeholder="Kosongkan jika unlimted"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
            </div>

            <div>
                <label class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="rounded text-primary focus:ring-primary">
                    <span class="ml-2 text-sm text-gray-700 font-medium">Aktifkan Voucher</span>
                </label>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-gray-100">
                <a href="{{ route('admin.loyalty.vouchers.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg border border-gray-300">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold text-sm rounded-lg shadow-sm">
                    Simpan Voucher
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
