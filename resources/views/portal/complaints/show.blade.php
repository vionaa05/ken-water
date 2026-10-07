@extends('layouts.portal')

@section('title', 'Detail Keluhan - Ken Water')

@section('content')
<div class="px-4 py-4 space-y-4 max-w-lg mx-auto">
    <a href="{{ route('portal.complaints.index') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 hover:text-primary gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Daftar Keluhan
    </a>

    <div class="bg-white rounded-2xl p-5 shadow-xs border border-gray-100 space-y-4">
        <div class="flex justify-between items-start border-b border-gray-100 pb-3">
            <div>
                <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 uppercase">
                    {{ $complaint->category }}
                </span>
                <p class="text-xs text-gray-400 mt-1">{{ $complaint->created_at->translatedFormat('d M Y, H:i') }}</p>
            </div>
            <div>
                @if($complaint->status === 'baru')
                    <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold">Baru</span>
                @elseif($complaint->status === 'diproses')
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">Sedang Ditangani</span>
                @else
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">Selesai</span>
                @endif
            </div>
        </div>

        <div>
            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Rincian Keluhan Anda:</h3>
            <p class="text-xs text-gray-800 bg-gray-50 p-3 rounded-xl border border-gray-100 font-medium">
                {{ $complaint->description }}
            </p>
        </div>

        @if($complaint->admin_response)
            <div class="bg-blue-50/70 p-4 rounded-xl border border-blue-100 space-y-1">
                <p class="text-xs font-bold text-blue-900 flex items-center gap-1">
                    💬 Tanggapan dari Tim Ken Water:
                </p>
                <p class="text-xs text-blue-800 font-medium">
                    "{{ $complaint->admin_response }}"
                </p>
                @if($complaint->resolved_at)
                    <p class="text-[10px] text-blue-600 pt-1">
                        Ditindaklanjuti pada: {{ \Carbon\Carbon::parse($complaint->resolved_at)->translatedFormat('d M Y, H:i') }}
                    </p>
                @endif
            </div>
        @else
            <div class="bg-amber-50 p-3 rounded-xl border border-amber-200 text-xs text-amber-800 flex items-center gap-2">
                ⏳ Keluhan Anda telah diterima dan sedang dalam antrean tindak lanjut oleh tim kami.
            </div>
        @endif
    </div>
</div>
@endsection
