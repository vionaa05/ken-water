@extends('layouts.portal')

@section('title', 'Buat Keluhan - Ken Water')

@section('content')
<div class="px-4 py-4 space-y-4 max-w-lg mx-auto">
    <a href="{{ route('portal.complaints.index') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 hover:text-primary gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Daftar Keluhan
    </a>

    <div class="bg-white rounded-2xl p-5 shadow-xs border border-gray-100 space-y-4">
        <div>
            <h1 class="text-lg font-extrabold text-gray-900">Kirim Keluhan Baru</h1>
            <p class="text-xs text-gray-500">Kami siap membantu memberikan solusi terbaik untuk Anda</p>
        </div>

        <form action="{{ route('portal.complaints.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Kategori Keluhan <span class="text-red-500">*</span></label>
                    <select name="category" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary text-xs font-semibold">
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Kualitas Air" {{ old('category') === 'Kualitas Air' ? 'selected' : '' }}>Kualitas Air (Rasa, Bau, Kejernihan)</option>
                        <option value="Kondisi Galon" {{ old('category') === 'Kondisi Galon' ? 'selected' : '' }}>Kondisi Galon (Bocor, Kotor, Tutup Rusak)</option>
                        <option value="Pengiriman" {{ old('category') === 'Pengiriman' ? 'selected' : '' }}>Pengiriman (Keterlambatan, Salah Alamat)</option>
                        <option value="Layanan Kasir / Kurir" {{ old('category') === 'Layanan Kasir / Kurir' ? 'selected' : '' }}>Layanan Staff / Kurir</option>
                        <option value="Lainnya" {{ old('category') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('category')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Detail Keluhan <span class="text-red-500">*</span></label>
                    <textarea name="description" rows="4" required placeholder="Jelaskan kendala yang Anda alami secara detail..."
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary text-xs font-medium">{{ old('description') }}</textarea>
                    @error('description')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-md active:scale-95 transition-transform">
                        Kirim Keluhan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
