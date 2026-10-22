@extends('layouts.portal')

@section('title', 'Buat Keluhan - Ken Water')

@section('content')
<div class="px-4 py-5 max-w-xl mx-auto space-y-4">
    <div class="flex items-center">
        <a href="{{ route('portal.complaints.index') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 hover:text-primary transition-colors gap-1.5 py-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Daftar Keluhan
        </a>
    </div>

    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 shadow-sm space-y-5">
        <div class="border-b border-gray-100 pb-4">
            <h1 class="text-xl font-bold text-gray-900 tracking-tight">Ajukan Keluhan Baru</h1>
            <p class="text-xs text-gray-500 mt-1">Sampaikan keluhan atau kendala Anda. Tim kami akan segera meninjau dan menindaklanjutinya.</p>
        </div>

        <form action="{{ route('portal.complaints.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Kategori Kendala <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select name="category" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 text-xs font-medium text-gray-800 bg-gray-50/50 appearance-none transition-all">
                        <option value="">-- Pilih Kategori Kendala --</option>
                        <option value="Kualitas Air" {{ old('category') === 'Kualitas Air' ? 'selected' : '' }}>Kualitas Air (Rasa, Bau, Kejernihan)</option>
                        <option value="Kondisi Galon" {{ old('category') === 'Kondisi Galon' ? 'selected' : '' }}>Kondisi Galon (Bocor, Kotor, Tutup Rusak)</option>
                        <option value="Pengiriman" {{ old('category') === 'Pengiriman' ? 'selected' : '' }}>Pengiriman (Terlambat, Salah Alamat/Jadwal)</option>
                        <option value="Layanan Kasir / Kurir" {{ old('category') === 'Layanan Kasir / Kurir' ? 'selected' : '' }}>Layanan Staff / Kurir</option>
                        <option value="Lainnya" {{ old('category') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                @error('category')<p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Detail Keluhan <span class="text-red-500">*</span>
                </label>
                <textarea name="description" rows="4" required placeholder="Jelaskan secara singkat kendala yang Anda alami (misal: nomor pesanan jika ada, waktu kejadian, atau detail lainnya)..."
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 text-xs font-medium text-gray-800 bg-gray-50/50 transition-all placeholder:text-gray-400 leading-relaxed">{{ old('description') }}</textarea>
                @error('description')<p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3 bg-primary hover:bg-primary-dark text-white font-semibold text-xs rounded-xl shadow-sm hover:shadow transition-all active:scale-98 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Kirim Keluhan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
