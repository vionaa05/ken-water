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

            <div>
                <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Kontak & Bantuan Pelanggan</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp Depot</label>
                    <div class="flex items-center gap-3">
                        <input type="text" name="depot_whatsapp" value="{{ $settings->get('depot_whatsapp')?->value ?? '6281234567890' }}" required class="input-field flex-1" placeholder="Contoh: 6281234567890">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Nomor ini digunakan pelanggan untuk fitur "Tanya via WhatsApp Depot" di Web & Aplikasi Mobile (Gunakan kode negara, contoh: 6281234567890).</p>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200">
                <button type="submit" class="btn-primary">Simpan Pengaturan</button>
            </div>
        </form>
    </div>

    <!-- Form Ubah Password Admin -->
    <div class="card p-6 mt-6">
        <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-200">
            <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            <h3 class="font-semibold text-gray-800">Keamanan & Password Akun Admin</h3>
        </div>

        <form method="POST" action="{{ route('admin.settings.password') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini <span class="text-red-500">*</span></label>
                <input type="password" name="current_password" required class="input-field" placeholder="Masukkan password saat ini">
                @error('current_password')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required minlength="6" class="input-field" placeholder="Min. 6 karakter">
                    @error('password')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" required minlength="6" class="input-field" placeholder="Ulangi password baru">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="inline-flex justify-center items-center px-4 py-2 bg-gray-900 border border-transparent rounded-lg font-semibold text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 transition-all shadow-sm">
                    Perbarui Password Admin
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
