@extends('layouts.admin')
@section('title', 'Keluhan Pelanggan - Ken Water')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Keluhan Pelanggan</h1>
    <p class="text-sm text-gray-500 mt-1">Pantau dan tanggapi semua keluhan dari pelanggan.</p>
</div>

<!-- Status Counter -->
<div class="grid grid-cols-3 gap-3 mb-6">
    <a href="{{ route('admin.complaints.index', ['status' => 'baru']) }}" class="card p-3 text-center hover:shadow-md transition-shadow border-l-4 border-l-red-400">
        <p class="text-2xl font-bold text-red-600">{{ $counts['baru'] }}</p>
        <p class="text-xs text-gray-500 mt-1">Baru</p>
    </a>
    <a href="{{ route('admin.complaints.index', ['status' => 'diproses']) }}" class="card p-3 text-center hover:shadow-md transition-shadow border-l-4 border-l-yellow-400">
        <p class="text-2xl font-bold text-yellow-600">{{ $counts['diproses'] }}</p>
        <p class="text-xs text-gray-500 mt-1">Diproses</p>
    </a>
    <a href="{{ route('admin.complaints.index', ['status' => 'selesai']) }}" class="card p-3 text-center hover:shadow-md transition-shadow border-l-4 border-l-green-400">
        <p class="text-2xl font-bold text-green-600">{{ $counts['selesai'] }}</p>
        <p class="text-xs text-gray-500 mt-1">Selesai</p>
    </a>
</div>

<!-- Filter -->
<div class="card p-4 mb-6">
    <form method="GET" action="{{ route('admin.complaints.index') }}" class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pelanggan..." class="input-field w-full">
        </div>
        <select name="status" class="input-field sm:w-40">
            <option value="">Semua Status</option>
            <option value="baru" {{ request('status') === 'baru' ? 'selected' : '' }}>Baru</option>
            <option value="diproses" {{ request('status') === 'diproses' ? 'selected' : '' }}>Diproses</option>
            <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
        </select>
        <button type="submit" class="btn-primary">Filter</button>
        @if(request()->hasAny(['search','status']))
        <a href="{{ route('admin.complaints.index') }}" class="btn-secondary">Reset</a>
        @endif
    </form>
</div>

<!-- Daftar Keluhan -->
<div class="space-y-3">
    @forelse($complaints as $complaint)
    <div class="card p-5 hover:shadow-md transition-shadow {{ $complaint->status === 'baru' ? 'border-l-4 border-l-red-400' : ($complaint->status === 'diproses' ? 'border-l-4 border-l-yellow-400' : 'border-l-4 border-l-green-400') }}">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $complaint->status === 'baru' ? 'bg-red-100 text-red-800' : ($complaint->status === 'diproses' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                        {{ ucfirst($complaint->status) }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $complaint->created_at->diffForHumans() }}</span>
                </div>
                <h4 class="font-bold text-gray-900">{{ $complaint->category }}</h4>
                <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $complaint->description }}</p>
                <div class="flex items-center gap-4 mt-3 text-xs text-gray-500">
                    <span>Dari: <span class="font-medium text-gray-700">{{ $complaint->user->name }}</span></span>
                    @if($complaint->handler)
                    <span>Ditangani: <span class="font-medium text-gray-700">{{ $complaint->handler->name }}</span></span>
                    @endif
                </div>
            </div>
            <div class="sm:ml-4 shrink-0">
                <a href="{{ route('admin.complaints.show', $complaint) }}" class="btn-primary py-2 px-4 text-sm">
                    {{ $complaint->status === 'selesai' ? 'Lihat Detail' : 'Tindak Lanjuti' }}
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="card p-12 text-center text-gray-500">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        Tidak ada keluhan. Luar biasa!
    </div>
    @endforelse
</div>

@if($complaints->hasPages())
<div class="mt-4">{{ $complaints->links() }}</div>
@endif
@endsection
