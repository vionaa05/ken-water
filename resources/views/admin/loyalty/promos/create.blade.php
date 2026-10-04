@extends('layouts.admin')

@section('title', 'Buat Promo Personal - Ken Water')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.loyalty.promos.index') }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Promo Personal
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Buat Promo Personal Baru</h1>
    <p class="text-sm text-gray-500 mt-1">Berikan promo diskon eksklusif untuk 1 pelanggan tertentu</p>
</div>

<div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <form action="{{ route('admin.loyalty.promos.store') }}" method="POST">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pelanggan Penerima <span class="text-red-500">*</span></label>
                <select name="user_id" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                    <option value="">-- Pilih Pelanggan --</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('user_id', request('user_id')) == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }} ({{ $customer->customer_code }} - {{ $customer->phone }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Promo <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Promo Spesial Ulang Tahun / Diskon Kangen Galon"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi / Pesan Promo</label>
                <textarea name="description" rows="2" placeholder="Contoh: Diskon khusus Rp 3.000 untuk pengisian galon berikutnya."
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
                    <input type="number" name="discount_value" value="{{ old('discount_value') }}" min="1" required placeholder="Contoh: 3000 atau 15"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                    @error('discount_value')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Dari <span class="text-red-500">*</span></label>
                    <input type="date" name="valid_from" value="{{ old('valid_from', date('Y-m-d')) }}" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Sampai <span class="text-red-500">*</span></label>
                    <input type="date" name="valid_until" value="{{ old('valid_until', date('Y-m-d', strtotime('+14 days'))) }}" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-gray-100">
                <a href="{{ route('admin.loyalty.promos.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg border border-gray-300">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold text-sm rounded-lg shadow-sm">
                    Kirim Promo ke Pelanggan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
