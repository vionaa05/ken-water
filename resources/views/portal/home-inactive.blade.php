@extends('layouts.portal')

@section('title', 'Akun Tidak Aktif - Ken Water')

@section('content')
<div class="flex flex-col items-center justify-center text-center py-12 px-4">
    <div class="w-24 h-24 bg-red-100 text-red-500 rounded-full flex items-center justify-center mb-6">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
    </div>
    
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Akun Anda Sedang Tidak Aktif</h1>
    <p class="text-gray-600 mb-8 max-w-sm">
        Sistem mendeteksi tidak ada aktivitas pemesanan selama periode tertentu. Untuk mulai memesan lagi, silakan lakukan reaktivasi akun.
    </p>

    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800 mb-8 text-left max-w-sm w-full">
        <h4 class="font-bold flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Informasi Reaktivasi
        </h4>
        <ul class="list-disc pl-5 space-y-1">
            <li>Level member akan diatur ulang.</li>
            <li>Poin loyalitas sebelumnya akan di-reset menjadi 0.</li>
            <li>Voucher & promo lama akan dihanguskan.</li>
            <li>Riwayat pesanan lama tetap tersimpan.</li>
        </ul>
    </div>

    <a href="{{ route('portal.reactivate.show') }}" class="btn-primary w-full max-w-sm py-3 text-lg shadow-lg">
        Reaktivasi Akun Sekarang
    </a>
</div>
@endsection
