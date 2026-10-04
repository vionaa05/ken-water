@extends('layouts.portal')

@section('title', 'Notifikasi - Ken Water')

@section('content')
<div class="px-4 py-4 space-y-4 max-w-lg mx-auto">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Notifikasi</h1>
            <p class="text-xs text-gray-500">Pemberitahuan status pesanan, poin & promo</p>
        </div>
        <form action="{{ route('portal.notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="text-xs font-bold text-primary hover:underline">
                Tandai Semua Dibaca
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-2">
        @forelse($notifications as $notification)
            <div class="p-4 rounded-2xl border transition-all {{ $notification->is_read ? 'bg-white border-gray-100' : 'bg-blue-50/60 border-blue-200' }}">
                <div class="flex justify-between items-start mb-1">
                    <h3 class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                        @if(!$notification->is_read)
                            <span class="w-2 h-2 rounded-full bg-primary inline-block"></span>
                        @endif
                        {{ $notification->title }}
                    </h3>
                    <span class="text-[10px] text-gray-400 font-medium">
                        {{ $notification->created_at->diffForHumans() }}
                    </span>
                </div>
                <p class="text-xs text-gray-600 font-medium leading-relaxed">
                    {{ $notification->body }}
                </p>

                @if(!$notification->is_read)
                    <div class="mt-2 text-right">
                        <form action="{{ route('portal.notifications.read', $notification) }}" method="POST" class="inline-block">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-[11px] font-bold text-primary hover:underline">
                                Lihat Rincian & Dibaca →
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl p-8 text-center border border-gray-100 space-y-2">
                <div class="w-12 h-12 bg-blue-50 text-primary rounded-full flex items-center justify-center mx-auto text-xl font-bold">
                    🔔
                </div>
                <p class="text-sm font-bold text-gray-800">Tidak Ada Notifikasi</p>
                <p class="text-xs text-gray-500">Anda sudah melihat semua pemberitahuan.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
