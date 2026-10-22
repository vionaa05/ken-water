@extends('layouts.portal')

@section('title', 'Keluhan Saya - Ken Water')

@section('content')
<div class="px-4 py-4 max-w-lg mx-auto space-y-4">
    <!-- Breadcrumb / Back Link -->
    <a href="{{ route('portal.home') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 hover:text-primary transition-colors gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Beranda
    </a>

    <!-- Top Header & Action in a neat row -->
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-gray-900 leading-tight">Pengajuan Keluhan</h1>
            <p class="text-xs text-gray-500 mt-0.5">Riwayat & pusat bantuan kendala</p>
        </div>
        <a href="{{ route('portal.complaints.create') }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-primary hover:bg-primary-dark text-white text-xs font-semibold rounded-xl shadow-sm hover:shadow transition-all active:scale-95 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Ajukan Keluhan</span>
        </a>
    </div>

    @if(session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Daftar Keluhan -->
    <div class="space-y-3">
        @forelse($complaints as $complaint)
            <a href="{{ route('portal.complaints.show', $complaint) }}" class="block bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:border-primary/40 hover:shadow-md transition-all group">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-primary uppercase tracking-wide">
                            {{ $complaint->category }}
                        </span>
                        <span class="text-[11px] text-gray-400">
                            {{ $complaint->created_at->translatedFormat('d M Y, H:i') }}
                        </span>
                    </div>
                    <div>
                        @if($complaint->status === 'baru')
                            <span class="inline-flex items-center px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200/80 rounded-full text-[10px] font-semibold">
                                Baru
                            </span>
                        @elseif($complaint->status === 'diproses')
                            <span class="inline-flex items-center px-2 py-0.5 bg-blue-50 text-primary border border-blue-200/80 rounded-full text-[10px] font-semibold">
                                Sedang Ditangani
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200/80 rounded-full text-[10px] font-semibold">
                                Selesai
                            </span>
                        @endif
                    </div>
                </div>

                <p class="text-xs text-gray-700 line-clamp-2 leading-relaxed">
                    {{ $complaint->description }}
                </p>

                @if($complaint->admin_response)
                    <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-start gap-2 bg-gray-50/80 p-2.5 rounded-xl text-gray-600">
                        <svg class="w-4 h-4 text-primary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Tanggapan Depot:</span>
                            <p class="text-xs text-gray-700 line-clamp-1 italic">"{{ $complaint->admin_response }}"</p>
                        </div>
                    </div>
                @endif
            </a>
        @empty
            <div class="bg-white rounded-2xl p-8 text-center border border-gray-100 shadow-sm space-y-2">
                <div class="w-12 h-12 bg-blue-50 text-primary rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-800">Belum Ada Riwayat Keluhan</p>
                <p class="text-xs text-gray-500 max-w-xs mx-auto">Semua pesanan Anda tercatat baik. Jika mengalami kendala, klik tombol "Ajukan Keluhan" di atas.</p>
            </div>
        @endforelse
    </div>

    @if($complaints->hasPages())
        <div class="pt-2">
            {{ $complaints->links() }}
        </div>
    @endif
</div>
@endsection
