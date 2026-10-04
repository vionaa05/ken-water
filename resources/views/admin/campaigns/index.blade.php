@extends('layouts.admin')
@section('title', 'Kampanye WhatsApp - Ken Water')
@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Kampanye WhatsApp</h1>
        <p class="text-sm text-gray-500 mt-1">Buat dan kelola kampanye pemasaran via WhatsApp.</p>
    </div>
    <a href="{{ route('admin.campaigns.create') }}" class="btn-primary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Buat Kampanye Baru
    </a>
</div>

<div class="space-y-4">
    @forelse($campaigns as $campaign)
    <div class="card p-5 hover:shadow-md transition-shadow">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                        {{ $campaign->type_label }}
                    </span>
                    @if($campaign->isActive())
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                    @endif
                </div>
                <h3 class="font-bold text-gray-900 text-lg">{{ $campaign->name }}</h3>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $campaign->period_start->format('d M Y') }} - {{ $campaign->period_end->format('d M Y') }}
                    · Target: <span class="font-semibold text-gray-700">{{ $campaign->target_count }} pelanggan</span>
                </p>
                <p class="text-sm text-gray-600 mt-2 line-clamp-2 bg-gray-50 p-3 rounded italic">
                    "{{ Str::limit($campaign->message_template, 120) }}"
                </p>
            </div>
            <div class="flex gap-2 shrink-0">
                <a href="{{ route('admin.campaigns.show', $campaign) }}" class="btn-primary py-2 px-3 text-sm">Lihat & Kirim</a>
                <form method="POST" action="{{ route('admin.campaigns.destroy', $campaign) }}" onsubmit="return confirm('Hapus kampanye ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-secondary py-2 px-3 text-sm text-red-600 border-red-200 hover:bg-red-50">Hapus</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="card p-12 text-center text-gray-500">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
        <p class="font-medium mb-2">Belum ada kampanye.</p>
        <p class="text-sm mb-4">Buat kampanye pertama untuk menjangkau pelanggan Anda.</p>
        <a href="{{ route('admin.campaigns.create') }}" class="btn-primary">Buat Kampanye Sekarang</a>
    </div>
    @endforelse
</div>
@if($campaigns->hasPages())
<div class="mt-4">{{ $campaigns->links() }}</div>
@endif
@endsection
