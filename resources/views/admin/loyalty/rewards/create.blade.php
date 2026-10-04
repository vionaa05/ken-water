@extends('layouts.admin')

@section('title', 'Tambah Reward - Ken Water')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.loyalty.rewards.index') }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Katalog Reward
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Tambah Reward Baru</h1>
    <p class="text-sm text-gray-500 mt-1">Buat item penukaran poin loyalitas pelanggan</p>
</div>

<div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <form action="{{ route('admin.loyalty.rewards.store') }}" method="POST">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Reward <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Gratis 1 Galon Air, Kaos Ken Water"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3" placeholder="Penjelasan singkat mengenai reward"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">{{ old('description') }}</textarea>
                @error('description')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Biaya Poin <span class="text-red-500">*</span></label>
                    <input type="number" name="points_cost" value="{{ old('points_cost', 10) }}" min="1" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                    @error('points_cost')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok Unit (Kosongkan jika unlimted)</label>
                    <input type="number" name="stock" value="{{ old('stock') }}" min="0" placeholder="Contoh: 50"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                    @error('stock')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="rounded text-primary focus:ring-primary">
                    <span class="ml-2 text-sm text-gray-700 font-medium">Aktifkan Reward ini di Portal Pelanggan</span>
                </label>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-gray-100">
                <a href="{{ route('admin.loyalty.rewards.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg border border-gray-300">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold text-sm rounded-lg shadow-sm">
                    Simpan Reward
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
