@extends('layouts.portal')

@section('title', 'Profil Saya - Ken Water')

@section('content')
<div class="px-4 py-4 space-y-4 max-w-lg mx-auto">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Profil Saya</h1>
        <p class="text-xs text-gray-500">Informasi akun & pengaturan keamanan</p>
    </div>

    @if(session('success'))
        <div class="p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Card Header Pelanggan -->
    <div class="bg-gradient-to-r from-blue-600 to-primary rounded-2xl p-5 text-white shadow-md flex items-center gap-4">
        <div class="w-14 h-14 bg-white/20 text-white rounded-full flex items-center justify-center font-extrabold text-2xl border border-white/30">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
            <h2 class="font-extrabold text-lg">{{ $user->name }}</h2>
            <p class="text-xs text-blue-100 font-mono">Kode: {{ $user->customer_code }}</p>
            <div class="mt-1.5 flex items-center gap-2">
                <span class="text-[10px] font-bold bg-white text-primary px-2.5 py-0.5 rounded-full uppercase">
                    Level {{ ucfirst($user->loyalty_tier) }}
                </span>
                <span class="text-[10px] font-bold bg-amber-400 text-gray-900 px-2.5 py-0.5 rounded-full">
                    {{ number_format($user->points_balance) }} Poin
                </span>
            </div>
        </div>
    </div>

    <!-- Form Edit Profil -->
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-gray-100 space-y-4">
        <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Perbarui Kontak & Alamat</h3>

        <form action="{{ route('portal.profile.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Lengkap</label>
                    <input type="text" value="{{ $user->name }}" disabled class="w-full px-3 py-2 bg-gray-100 rounded-xl border border-gray-200 text-xs font-semibold text-gray-500 cursor-not-allowed">
                    <p class="text-[10px] text-gray-400 mt-0.5">Nama hanya bisa diubah oleh admin depot.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nomor WhatsApp <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required class="w-full px-3.5 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary text-xs font-semibold">
                    @error('phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Alamat Pengantaran Galon <span class="text-red-500">*</span></label>
                    <textarea name="address" rows="3" required class="w-full px-3.5 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary text-xs font-semibold">{{ old('address', $user->address) }}</textarea>
                    @error('address')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 bg-primary text-white text-xs font-bold rounded-xl shadow-sm hover:bg-primary-dark">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Form Ubah Password -->
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-gray-100 space-y-4">
        <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Ubah Password</h3>

        <form action="{{ route('portal.profile.password') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Password Saat Ini</label>
                    <input type="password" name="current_password" required class="w-full px-3.5 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary text-xs font-semibold">
                    @error('current_password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Password Baru</label>
                    <input type="password" name="password" required class="w-full px-3.5 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary text-xs font-semibold">
                    @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" required class="w-full px-3.5 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary text-xs font-semibold">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 bg-gray-800 text-white text-xs font-bold rounded-xl shadow-sm hover:bg-gray-900">
                        Ubah Password
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
