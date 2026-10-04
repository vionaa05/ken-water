@extends('layouts.admin')
@section('title', 'Buat Kampanye - Ken Water')
@section('content')
<div class="mb-6">
    <a href="{{ route('admin.campaigns.index') }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Kembali
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Buat Kampanye Baru</h1>
</div>

<div class="max-w-3xl">
    <div class="card p-6">
        <form method="POST" action="{{ route('admin.campaigns.store') }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kampanye <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="input-field" placeholder="Contoh: Promo Ulang Tahun Oktober 2026">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Kampanye <span class="text-red-500">*</span></label>
                    <select name="type" required class="input-field">
                        <option value="">Pilih Tipe...</option>
                        <option value="promo_baru" {{ old('type') === 'promo_baru' ? 'selected' : '' }}>Promo Pelanggan Baru</option>
                        <option value="promo_loyal" {{ old('type') === 'promo_loyal' ? 'selected' : '' }}>Promo Pelanggan Loyal</option>
                        <option value="ulang_tahun" {{ old('type') === 'ulang_tahun' ? 'selected' : '' }}>Promo Ulang Tahun</option>
                        <option value="ajakan_kembali" {{ old('type') === 'ajakan_kembali' ? 'selected' : '' }}>Ajakan Kembali</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Voucher (Opsional)</label>
                    <select name="voucher_id" class="input-field">
                        <option value="">Tanpa Voucher</option>
                        @foreach($vouchers as $voucher)
                        <option value="{{ $voucher->id }}" {{ old('voucher_id') == $voucher->id ? 'selected' : '' }}>{{ $voucher->name }} ({{ $voucher->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="period_start" value="{{ old('period_start', now()->format('Y-m-d')) }}" required class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Berakhir <span class="text-red-500">*</span></label>
                    <input type="date" name="period_end" value="{{ old('period_end', now()->addMonth()->format('Y-m-d')) }}" required class="input-field">
                </div>

                <!-- Target Segmen -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Target Segmen Pelanggan</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach(['semua' => 'Semua Pelanggan', 'loyal' => 'Pelanggan Loyal', 'tidak_aktif' => 'Tidak Aktif', 'baru' => 'Pelanggan Baru'] as $val => $label)
                        <label class="flex items-center gap-2 p-2 border border-gray-200 rounded-md hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="target_segment[]" value="{{ $val }}" {{ in_array($val, (array) old('target_segment', [])) ? 'checked' : '' }} class="w-4 h-4 text-primary rounded">
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Template Pesan -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Template Pesan WhatsApp <span class="text-red-500">*</span></label>
                    <textarea name="message_template" required rows="8" class="input-field font-mono text-sm" placeholder="Gunakan {nama} untuk nama pelanggan, {kode_voucher} untuk kode voucher...">{{ old('message_template', "Halo {nama}! 👋\n\nKami memiliki penawaran spesial untuk Anda dari Ken Water! 💧\n\n\nSalam hangat,\nTim Ken Water") }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Variabel tersedia: <code class="bg-gray-100 px-1 rounded">{nama}</code>, <code class="bg-gray-100 px-1 rounded">{kode_voucher}</code>, <code class="bg-gray-100 px-1 rounded">{link_portal}</code></p>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="btn-primary">Buat Kampanye</button>
                <a href="{{ route('admin.campaigns.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
