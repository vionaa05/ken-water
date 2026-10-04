@extends('layouts.admin')
@section('title', 'Pengaturan Sistem - Ken Water')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Pengaturan Sistem</h1>
    <p class="text-sm text-gray-500 mt-1">Konfigurasi parameter bisnis Ken Water.</p>
</div>

<div class="max-w-2xl">
    <div class="card p-6">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Harga & Poin</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Per Galon (Rp)</label>
                        <input type="number" name="price_per_gallon" value="{{ $settings->get('price_per_gallon')?->value ?? 5000 }}" min="0" required class="input-field">
                        <p class="text-xs text-gray-500 mt-1">Harga dasar per galon air minum isi ulang.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Poin Per Galon</label>
                        <input type="number" name="points_per_gallon" value="{{ $settings->get('points_per_gallon')?->value ?? 1 }}" min="0" required class="input-field">
                        <p class="text-xs text-gray-500 mt-1">Jumlah poin yang diberikan untuk setiap 1 galon yang dibeli.</p>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Level Loyalty</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Poin Silver</label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="silver_threshold" value="{{ $settings->get('silver_threshold')?->value ?? 50 }}" min="1" required class="input-field flex-1">
                            <span class="shrink-0 text-sm font-bold text-gray-500 bg-gray-100 px-3 py-2 rounded-md">poin</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Pelanggan naik ke level Silver setelah mencapai jumlah poin ini.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Poin Gold</label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="gold_threshold" value="{{ $settings->get('gold_threshold')?->value ?? 150 }}" min="1" required class="input-field flex-1">
                            <span class="shrink-0 text-sm font-bold text-gray-500 bg-gray-100 px-3 py-2 rounded-md">poin</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Pelanggan naik ke level Gold setelah mencapai jumlah poin ini.</p>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Aktivitas Pelanggan</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Batas Hari Tidak Aktif</label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="inactive_days" value="{{ $settings->get('inactive_days')?->value ?? 90 }}" min="1" required class="input-field flex-1">
                        <span class="shrink-0 text-sm font-bold text-gray-500 bg-gray-100 px-3 py-2 rounded-md">hari</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Pelanggan dianggap tidak aktif jika tidak ada transaksi selama jumlah hari ini.</p>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200">
                <button type="submit" class="btn-primary">Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>
@endsection
