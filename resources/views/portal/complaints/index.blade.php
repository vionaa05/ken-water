@extends('layouts.portal')

@section('title', 'Keluhan Saya - Ken Water')

@section('content')
<div class="px-4 py-4 space-y-4 max-w-lg mx-auto">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Keluhan Pelanggan</h1>
            <p class="text-xs text-gray-500">Laporkan kendala kualitas air, pengiriman, atau layanan</p>
        </div>
        <a href="{{ route('portal.complaints.create') }}" class="px-3.5 py-2 bg-red-600 text-white text-xs font-bold rounded-xl shadow-md flex items-center gap-1.5 active:scale-95 transition-transform">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Keluhan
        </a>
    </div>

    @if(session('success'))
        <div class="p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-3">
        @forelse($complaints as $complaint)
            <a href="{{ route('portal.complaints.show', $complaint) }}" class="block bg-white rounded-2xl p-4 shadow-xs border border-gray-100 hover:border-blue-200 transition-all">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 uppercase">
                            {{ $complaint->category }}
                        </span>
                        <p class="text-xs text-gray-400 mt-1">{{ $complaint->created_at->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                    <div>
                        @if($complaint->status === 'baru')
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-600 border border-amber-200 rounded-full text-[11px] font-bold">Baru</span>
                        @elseif($complaint->status === 'diproses')
                            <span class="px-2.5 py-1 bg-blue-50 text-blue-600 border border-blue-200 rounded-full text-[11px] font-bold">Sedang Ditangani</span>
                        @else
                            <span class="px-2.5 py-1 bg-green-50 text-green-600 border border-green-200 rounded-full text-[11px] font-bold">Selesai</span>
                        @endif
                    </div>
                </div>

                <p class="text-xs font-medium text-gray-800 line-clamp-2 mt-2">
                    {{ $complaint->description }}
                </p>

                @if($complaint->admin_response)
                    <div class="mt-3 pt-2 border-t border-gray-100 bg-gray-50 p-2.5 rounded-xl">
                        <p class="text-[11px] font-bold text-gray-700">Tanggapan Depot:</p>
                        <p class="text-[11px] text-gray-600 line-clamp-1 italic">"{{ $complaint->admin_response }}"</p>
                    </div>
                @endif
            </a>
        @empty
            <div class="bg-white rounded-2xl p-8 text-center border border-gray-100 space-y-2">
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-full flex items-center justify-center mx-auto text-xl font-bold">
                    👍
                </div>
                <p class="text-sm font-bold text-gray-800">Tidak Ada Keluhan</p>
                <p class="text-xs text-gray-500">Kualitas dan kepuasan Anda adalah prioritas kami.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $complaints->links() }}
    </div>
</div>
@endsection
