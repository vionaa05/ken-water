@extends('layouts.admin')
@section('title', 'Detail Keluhan - Ken Water')
@section('content')
<div class="mb-6">
    <a href="{{ route('admin.complaints.index') }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Kembali ke Daftar Keluhan
    </a>
    <div class="flex justify-between items-start">
        <h1 class="text-2xl font-bold text-gray-900">Detail Keluhan</h1>
        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium
            {{ $complaint->status === 'baru' ? 'bg-red-100 text-red-800' : ($complaint->status === 'diproses' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
            {{ ucfirst($complaint->status) }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Keluhan -->
        <div class="card p-6">
            <h3 class="font-bold text-gray-900 text-lg mb-2">{{ $complaint->category }}</h3>
            <p class="text-sm text-gray-400 mb-4">Dikirim: {{ $complaint->created_at->format('d M Y, H:i') }}</p>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <p class="text-gray-700">{{ $complaint->description }}</p>
            </div>
        </div>

        @if($complaint->action_taken || $complaint->response)
        <div class="card p-6 bg-blue-50 border border-blue-200">
            <h3 class="font-bold text-blue-900 mb-4">Tindakan & Tanggapan</h3>
            @if($complaint->action_taken)
            <div class="mb-3">
                <p class="text-xs font-medium text-blue-700 mb-1 uppercase tracking-wider">Tindakan yang Diambil</p>
                <p class="text-blue-900">{{ $complaint->action_taken }}</p>
            </div>
            @endif
            @if($complaint->response)
            <div>
                <p class="text-xs font-medium text-blue-700 mb-1 uppercase tracking-wider">Tanggapan ke Pelanggan</p>
                <p class="text-blue-900">{{ $complaint->response }}</p>
            </div>
            @endif
        </div>
        @endif

        <!-- Form Update Status -->
        @if($complaint->status !== 'selesai')
        <div class="card p-6">
            <h3 class="font-bold text-gray-900 mb-4">Perbarui Status Keluhan</h3>
            <form method="POST" action="{{ route('admin.complaints.update', $complaint) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Baru</label>
                    <select name="status" required class="input-field">
                        @if($complaint->status === 'baru')
                        <option value="diproses">Proses Keluhan</option>
                        @endif
                        <option value="selesai">Tandai Selesai</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tindakan yang Diambil (Internal)</label>
                    <textarea name="action_taken" rows="2" class="input-field" placeholder="Catatan internal untuk staf...">{{ $complaint->action_taken }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggapan ke Pelanggan</label>
                    <textarea name="response" rows="3" class="input-field" placeholder="Pesan ini akan dikirim sebagai notifikasi ke pelanggan...">{{ $complaint->response }}</textarea>
                </div>
                <div class="pt-2">
                    <button type="submit" class="btn-primary">Simpan & Kirim Notifikasi</button>
                </div>
            </form>
        </div>
        @endif
    </div>

    <!-- Sidebar Pelanggan -->
    <div>
        <div class="card p-6">
            <h3 class="font-bold text-gray-900 mb-4">Pelanggan</h3>
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-lg mr-3">
                    {{ strtoupper(substr($complaint->user->name, 0, 1)) }}
                </div>
                <div>
                    <a href="{{ route('admin.customers.show', $complaint->user) }}" class="font-bold text-gray-900 hover:text-primary">{{ $complaint->user->name }}</a>
                    <p class="text-xs text-gray-400">{{ $complaint->user->phone }}</p>
                </div>
            </div>
            @if($complaint->handler)
            <div class="border-t border-gray-100 pt-4">
                <p class="text-xs text-gray-500 mb-1">Ditangani oleh</p>
                <p class="font-semibold text-gray-800">{{ $complaint->handler->name }}</p>
                @if($complaint->resolved_at)
                <p class="text-xs text-gray-400 mt-1">Selesai: {{ $complaint->resolved_at->format('d M Y') }}</p>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
