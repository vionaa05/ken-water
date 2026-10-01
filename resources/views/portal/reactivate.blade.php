@extends('layouts.portal')

@section('title', 'Konfirmasi Reaktivasi - Ken Water')

@section('content')
<div class="mb-6">
    <a href="{{ route('portal.home') }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Kembali
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Reaktivasi Akun</h1>
    <p class="text-sm text-gray-500 mt-1">Konfirmasi pengaktifan kembali akun Anda.</p>
</div>

<div class="card p-6 mb-6 border-t-4 border-t-primary">
    <h3 class="font-bold text-lg text-gray-900 mb-4">Syarat & Ketentuan Reaktivasi</h3>
    
    <div class="space-y-3 text-sm text-gray-600 mb-6 bg-gray-50 p-4 rounded-lg">
        <p>Dengan mereaktivasi akun, Anda memahami dan menyetujui bahwa:</p>
        <ul class="list-decimal pl-5 space-y-2">
            <li><strong>Level Loyalty</strong> Anda akan diatur ulang menjadi <span class="font-bold text-[#cd7f32]">Bronze</span>.</li>
            <li><strong>Poin Terkumpul</strong> akan di-reset menjadi <span class="font-bold">0 (Nol)</span>.</li>
            <li><strong>Voucher & Promo</strong> aktif Anda di periode sebelumnya akan dihanguskan dan tidak dapat digunakan lagi.</li>
            <li>Riwayat transaksi Anda sebelumnya <strong>tetap tersimpan</strong> dalam sistem kami.</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('portal.reactivate.confirm') }}">
        @csrf
        
        <label class="flex items-start gap-3 mb-6 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
            <div class="flex-shrink-0 mt-0.5">
                <input type="checkbox" name="agreement" required class="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary">
            </div>
            <span class="text-sm font-medium text-gray-800">
                Saya telah membaca dan menyetujui syarat ketentuan reaktivasi akun di atas.
            </span>
        </label>

        @error('agreement')
            <p class="text-red-500 text-xs italic mb-4">{{ $message }}</p>
        @enderror

        <button type="submit" class="btn-primary w-full py-3">
            Konfirmasi Reaktivasi
        </button>
    </form>
</div>
@endsection
