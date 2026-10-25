@extends('layouts.admin')
@section('title', 'Detail Pelanggan - Ken Water')
@section('content')
<div class="mb-6">
    <a href="{{ route('admin.customers.index') }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Kembali ke Daftar Pelanggan
    </a>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h1>
            <p class="text-sm font-mono text-gray-400">{{ $customer->customer_id }}</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn-secondary py-2 px-3 text-sm">Edit Data</a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Profile Card with Loyalty Card -->
    <div class="card p-5">
        <!-- Physical-style Loyalty Card (Sama Persis dengan Mobile) -->
        <div class="mb-5">
            @php
                $cardLevel = $customer->loyalty_level;
                if ($cardLevel === 'gold') {
                    $gradFrom = '#78350f'; $gradVia = '#d97706'; $gradTo = '#f59e0b';
                    $levelEmoji = '🥇';
                } elseif ($cardLevel === 'silver') {
                    $gradFrom = '#1e3a5f'; $gradVia = '#334155'; $gradTo = '#64748b';
                    $levelEmoji = '🥈';
                } else {
                    $gradFrom = '#0284c7'; $gradVia = '#0369a1'; $gradTo = '#0c4a6e';
                    $levelEmoji = '🥉';
                }
                $statusBg = $customer->status === 'aktif' ? 'rgba(52,211,153,0.25)' : 'rgba(248,113,113,0.25)';
                $statusColor = $customer->status === 'aktif' ? '#6ee7b7' : '#fca5a5';
                $statusText = $customer->status === 'aktif' ? '● Aktif' : '● Tidak Aktif';
            @endphp

            <!-- Card preview (tampilan di halaman) -->
            <div id="loyalty-card-wrapper"
                style="
                    position: relative;
                    width: 100%;
                    aspect-ratio: 1.586 / 1;
                    border-radius: 1rem;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
                    overflow: hidden;
                    padding: 1.25rem;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                    background: linear-gradient(135deg, {{ $gradFrom }} 0%, {{ $gradVia }} 50%, {{ $gradTo }} 100%);
                    color: white;
                    font-family: 'Inter', sans-serif;
                    box-sizing: border-box;
                ">

                <!-- Decorative Circles -->
                <div style="position:absolute; top:-1.25rem; right:-1.25rem; width:7rem; height:7rem; border-radius:50%; background:rgba(255,255,255,0.10); pointer-events:none;"></div>
                <div style="position:absolute; bottom:-1.5rem; right:-0.5rem; width:5rem; height:5rem; border-radius:50%; background:rgba(255,255,255,0.05); pointer-events:none;"></div>

                <!-- Top Row: Logo & Level -->
                <div style="display:flex; justify-content:space-between; align-items:flex-start; position:relative; z-index:10;">
                    <div style="display:flex; align-items:center; gap:0.375rem;">
                        <svg width="20" height="20" fill="rgba(255,255,255,0.85)" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                        <span style="font-weight:700; font-size:0.875rem; letter-spacing:0.05em; color:rgba(255,255,255,0.92);">Ken Water</span>
                    </div>
                    <div style="padding:0.25rem 0.625rem; background:rgba(255,255,255,0.20); border-radius:9999px; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; border:1px solid rgba(255,255,255,0.30); color:white;">
                        {{ $levelEmoji }} {{ strtoupper($cardLevel) }}
                    </div>
                </div>

                <!-- Bottom Row: Name, ID, Poin, Status, QR -->
                <div style="position:relative; z-index:10; display:flex; justify-content:space-between; align-items:flex-end;">
                    <div style="min-width:0; padding-right:0.5rem; flex:1;">
                        <p style="font-weight:700; font-size:1rem; color:white; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $customer->name }}</p>
                        <p style="font-family:monospace; margin-top:0.125rem; font-size:0.7rem; color:rgba(255,255,255,0.70); letter-spacing:0.15em;">{{ $customer->customer_id }}</p>
                        <p style="margin-top:0.5rem; font-size:0.875rem; font-weight:700; color:white; display:flex; align-items:center; gap:0.25rem;">
                            <svg width="16" height="16" fill="white" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            {{ (int)$customer->points }} Poin
                        </p>
                        <div style="margin-top:0.25rem;">
                            <span style="display:inline-block; padding:0.125rem 0.5rem; border-radius:0.25rem; font-size:0.6875rem; font-weight:600; background:{{ $statusBg }}; color:{{ $statusColor }};">
                                {{ $statusText }}
                            </span>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div style="width:3.5rem; height:3.5rem; background:white; border-radius:0.5rem; padding:0.25rem; box-shadow:0 1px 4px rgba(0,0,0,0.15); flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                        @if(isset($qrBase64) && $qrBase64)
                        <img src="{{ $qrBase64 }}" alt="QR" style="width:100%; height:100%; object-fit:contain;" crossorigin="anonymous">
                        @else
                        <span style="font-size:0.5rem; color:#9ca3af; font-family:monospace; text-align:center;">QR</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Hidden data for canvas drawing -->
            <span id="card-data"
                data-level="{{ $cardLevel }}"
                data-grad-from="{{ $gradFrom }}"
                data-grad-via="{{ $gradVia }}"
                data-grad-to="{{ $gradTo }}"
                data-name="{{ $customer->name }}"
                data-id="{{ $customer->customer_id }}"
                data-points="{{ (int)$customer->points }}"
                data-status="{{ $customer->status }}"
                data-emoji="{{ $levelEmoji }}"
                data-qr="{{ isset($qrBase64) && $qrBase64 ? $qrBase64 : '' }}"
                style="display:none;"
            ></span>

            <!-- Tombol Unduh Gambar Kartu (PNG) -->
            <button type="button" id="btn-download-card" onclick="downloadLoyaltyCardPng()" style="width:100%; margin-top:0.75rem; display:flex; align-items:center; justify-content:center; gap:0.5rem; padding:0.625rem 1rem; border-radius:0.75rem; border:1px solid #bfdbfe; background:rgba(239,246,255,0.7); color:#1d4ed8; font-size:0.875rem; font-weight:600; cursor:pointer; transition:background 0.2s; box-shadow:0 1px 3px rgba(0,0,0,0.08);" onmouseover="this.style.background='rgba(219,234,254,0.9)'" onmouseout="this.style.background='rgba(239,246,255,0.7)'">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Unduh Gambar Kartu (PNG)</span>
            </button>
        </div>

        <div class="space-y-3 text-sm">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                <div><p class="text-xs text-gray-500">No. HP/WhatsApp</p><p class="font-medium text-gray-900">{{ $customer->phone }}</p></div>
            </div>
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <div><p class="text-xs text-gray-500">Alamat</p><p class="font-medium text-gray-900">{{ $customer->address }}</p></div>
            </div>
            @if($customer->birth_date)
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <div><p class="text-xs text-gray-500">Tanggal Lahir</p><p class="font-medium text-gray-900">{{ $customer->birth_date->format('d M Y') }}</p></div>
            </div>
            @endif
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <div><p class="text-xs text-gray-500">Terdaftar</p><p class="font-medium text-gray-900">{{ $customer->registered_at->format('d M Y') }}</p></div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-4 content-start">
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-primary">{{ $customer->points }}</p>
            <p class="text-xs text-gray-500 mt-1">Poin Aktif</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-gray-800">{{ $customer->order_count_current_period }}</p>
            <p class="text-xs text-gray-500 mt-1">Pesanan (Periode)</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xl font-bold text-gray-800">{{ format_rupiah($customer->total_spend_current_period) }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Belanja</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold {{ $progress['next_level'] ? 'text-amber-500' : 'text-green-600' }}">{{ $progress['percentage'] }}%</p>
            <p class="text-xs text-gray-500 mt-1">Ke Level Berikutnya</p>
        </div>

        <!-- Input Transaksi Langsung -->
        <div class="col-span-2 sm:col-span-4 card p-5 bg-blue-50 border border-blue-200">
            <h4 class="font-bold text-blue-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Input Transaksi Langsung (Tunai di Depot)
            </h4>
            @if($customer->status === 'tidak_aktif')
            <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded text-sm">
                ⚠️ Pelanggan tidak aktif. Tidak bisa melakukan transaksi baru.
            </div>
            @else
            <form method="POST" action="{{ route('admin.customers.store-direct', $customer) }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="text-xs font-medium text-blue-800 mb-1 block">Jumlah Galon *</label>
                        <input type="number" name="gallon_qty" required min="1" max="100" class="input-field" placeholder="1">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-blue-800 mb-1 block">Pembayaran *</label>
                        <select name="payment_method" required class="input-field">
                            <option value="tunai">Tunai</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-blue-800 mb-1 block">Pengiriman *</label>
                        <select name="delivery_method" required class="input-field">
                            <option value="ambil_sendiri">Ambil Sendiri</option>
                            <option value="antar">Diantar</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full btn-primary py-2 text-sm" onclick="return confirm('Catat transaksi ini?')">
                            Catat Transaksi
                        </button>
                    </div>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>

<!-- Tabs: Riwayat, Poin, Voucher, Keluhan -->
<div x-data="{ tab: 'orders' }">
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-6 overflow-x-auto">
            <button @click="tab = 'orders'" :class="tab === 'orders' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors">Riwayat Pesanan</button>
            <button @click="tab = 'points'" :class="tab === 'points' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors">Mutasi Poin</button>
            <button @click="tab = 'vouchers'" :class="tab === 'vouchers' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors">Voucher & Promo</button>
            <button @click="tab = 'complaints'" :class="tab === 'complaints' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors">Keluhan</button>
        </nav>
    </div>

    <!-- Riwayat Pesanan Tab -->
    <div x-show="tab === 'orders'" x-cloak>
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Galon</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sumber</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $order->order_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $order->gallon_qty }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ format_rupiah($order->total_price) }}</td>
                            <td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $order->source === 'online' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700' }}">{{ $order->source === 'online' ? 'Online' : 'Langsung' }}</span></td>
                            <td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $order->status === 'selesai' ? 'bg-green-100 text-green-800' : ($order->status === 'dibatalkan' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($order->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada riwayat pesanan di periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>

    <!-- Mutasi Poin Tab -->
    <div x-show="tab === 'points'" x-cloak>
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Poin</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($pointMutations as $mutation)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $mutation->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $mutation->description }}</td>
                            <td class="px-4 py-3 text-right font-bold {{ $mutation->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $mutation->amount > 0 ? '+' : '' }}{{ $mutation->amount }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada mutasi poin.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Voucher & Promo Tab -->
    <div x-show="tab === 'vouchers'" x-cloak>
        <div class="space-y-3">
            @forelse($activeVouchers as $uv)
            <div class="card p-4 border-l-4 border-l-green-500">
                <div class="flex justify-between items-center">
                    <div><h4 class="font-semibold text-gray-900">{{ $uv->voucher->name }}</h4><p class="text-xs text-gray-500 mt-1">Kode: {{ $uv->voucher->code }} · Berlaku s/d {{ $uv->voucher->valid_until }}</p></div>
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800">Aktif</span>
                </div>
            </div>
            @empty
            <div class="card p-8 text-center text-sm text-gray-500">Tidak ada voucher aktif.</div>
            @endforelse
        </div>
    </div>

    <!-- Keluhan Tab -->
    <div x-show="tab === 'complaints'" x-cloak>
        <div class="space-y-3">
            @forelse($recentComplaints as $complaint)
            <div class="card p-4">
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-semibold text-gray-900">{{ $complaint->category }}</h4>
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $complaint->status === 'selesai' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ ucfirst($complaint->status) }}</span>
                </div>
                <p class="text-sm text-gray-600 line-clamp-2">{{ $complaint->description }}</p>
                <p class="text-xs text-gray-400 mt-2">{{ $complaint->created_at->format('d M Y') }}</p>
            </div>
            @empty
            <div class="card p-8 text-center text-sm text-gray-500">Belum ada keluhan dari pelanggan ini.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function downloadLoyaltyCardPng() {
    const btn = document.getElementById('btn-download-card');
    const dataEl = document.getElementById('card-data');
    if (!dataEl) return;

    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<svg style="animation:spin 1s linear infinite;margin-right:6px;" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8v8H4z" fill="currentColor"/></svg> Memproses...';
    btn.disabled = true;

    try {
        // Card dimensions (credit card ratio 1.586:1)
        const W = 1024, H = Math.round(1024 / 1.586);
        const canvas = document.createElement('canvas');
        canvas.width = W;
        canvas.height = H;
        const ctx = canvas.getContext('2d');

        const data = dataEl.dataset;
        const level = data.level;
        const gradFrom = data.gradFrom;
        const gradVia  = data.gradVia;
        const gradTo   = data.gradTo;
        const name     = data.name;
        const custId   = data.id;
        const points   = parseInt(data.points);
        const status   = data.status;
        const emoji    = data.emoji;
        const qrSrc    = data.qr;

        const pad = W * 0.05; // 5% padding

        // --- Background gradient ---
        const grad = ctx.createLinearGradient(0, 0, W, H);
        grad.addColorStop(0,    gradFrom);
        grad.addColorStop(0.5,  gradVia);
        grad.addColorStop(1,    gradTo);
        ctx.fillStyle = grad;
        roundRect(ctx, 0, 0, W, H, W * 0.04);
        ctx.fill();

        // --- Decorative circles ---
        ctx.save();
        ctx.globalAlpha = 0.10;
        ctx.fillStyle = '#ffffff';
        ctx.beginPath();
        ctx.arc(W - pad, pad, W * 0.13, 0, Math.PI * 2);
        ctx.fill();
        ctx.globalAlpha = 0.05;
        ctx.beginPath();
        ctx.arc(W - pad * 0.4, H - pad * 0.5, W * 0.09, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();

        // --- Top row: Water drop icon + "Ken Water" ---
        const iconX = pad;
        const iconY = pad;
        const iconSize = H * 0.085;

        // Draw water drop SVG path scaled
        ctx.save();
        ctx.fillStyle = 'rgba(255,255,255,0.85)';
        const dropScale = iconSize / 24;
        ctx.translate(iconX, iconY);
        ctx.scale(dropScale, dropScale);
        const dropPath = new Path2D('M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z');
        ctx.fill(dropPath);
        ctx.restore();

        ctx.fillStyle = 'rgba(255,255,255,0.92)';
        ctx.font = `700 ${Math.round(H * 0.065)}px Inter, system-ui, sans-serif`;
        ctx.textBaseline = 'middle';
        ctx.fillText('Ken Water', iconX + iconSize + H * 0.02, iconY + iconSize / 2);

        // --- Level badge (top right) ---
        const badgeText = emoji + ' ' + level.toUpperCase();
        ctx.font = `700 ${Math.round(H * 0.048)}px Inter, system-ui, sans-serif`;
        const badgeTextW = ctx.measureText(badgeText).width;
        const badgePadX = H * 0.04;
        const badgeW = badgeTextW + (badgePadX * 2);
        const badgeH = H * 0.085;
        const badgeX = W - pad - badgeW;
        const badgeY = pad;

        ctx.save();
        ctx.globalAlpha = 0.20;
        ctx.fillStyle = '#ffffff';
        roundRect(ctx, badgeX, badgeY, badgeW, badgeH, badgeH / 2);
        ctx.fill();
        ctx.globalAlpha = 1;
        ctx.strokeStyle = 'rgba(255,255,255,0.30)';
        ctx.lineWidth = 2;
        roundRect(ctx, badgeX, badgeY, badgeW, badgeH, badgeH / 2);
        ctx.stroke();
        ctx.restore();

        ctx.fillStyle = '#ffffff';
        ctx.font = `700 ${Math.round(H * 0.048)}px Inter, system-ui, sans-serif`;
        ctx.textBaseline = 'middle';
        ctx.fillText(badgeText, badgeX + badgePadX, badgeY + badgeH / 2);

        // --- Bottom Row Layout ---
        // QR Code positioned at bottom-right
        const qrSize = Math.round(H * 0.38);
        const qrPadding = Math.round(H * 0.025);
        const qrBoxSize = qrSize + (qrPadding * 2);
        const qrBoxX = W - pad - qrBoxSize;
        const qrBoxY = H - pad - qrBoxSize;

        // White QR background card
        ctx.save();
        ctx.fillStyle = '#ffffff';
        ctx.shadowColor = 'rgba(0,0,0,0.18)';
        ctx.shadowBlur = 12;
        ctx.shadowOffsetY = 4;
        roundRect(ctx, qrBoxX, qrBoxY, qrBoxSize, qrBoxSize, 12);
        ctx.fill();
        ctx.restore();

        const qrX = qrBoxX + qrPadding;
        const qrY = qrBoxY + qrPadding;

        if (qrSrc) {
            await new Promise((resolve) => {
                const qrImg = new Image();
                qrImg.crossOrigin = 'anonymous';
                qrImg.onload = () => {
                    ctx.drawImage(qrImg, qrX, qrY, qrSize, qrSize);
                    resolve();
                };
                qrImg.onerror = resolve;
                qrImg.src = qrSrc;
            });
        } else {
            ctx.fillStyle = '#9ca3af';
            ctx.font = `${Math.round(H * 0.04)}px monospace`;
            ctx.textBaseline = 'middle';
            ctx.textAlign = 'center';
            ctx.fillText('QR CODE', qrX + qrSize / 2, qrY + qrSize / 2);
            ctx.textAlign = 'start';
        }

        // --- Bottom left texts (Customer Info) ---
        // Align nicely relative to card height so nothing gets truncated
        const textX = pad;
        const maxTextW = qrBoxX - pad - (W * 0.03);

        // 1. Name
        const nameFontSize = Math.round(H * 0.075);
        ctx.font = `700 ${nameFontSize}px Inter, system-ui, sans-serif`;
        ctx.fillStyle = '#ffffff';
        ctx.textBaseline = 'alphabetic';

        let nameText = name;
        while (ctx.measureText(nameText).width > maxTextW && nameText.length > 2) {
            nameText = nameText.slice(0, -1);
        }
        if (nameText !== name) nameText += '…';
        ctx.fillText(nameText, textX, Math.round(H * 0.56));

        // 2. Customer ID
        const idFontSize = Math.round(H * 0.050);
        ctx.font = `400 ${idFontSize}px 'Courier New', monospace`;
        ctx.fillStyle = 'rgba(255,255,255,0.75)';
        ctx.textBaseline = 'alphabetic';
        ctx.fillText(custId, textX, Math.round(H * 0.66));

        // 3. Points with star
        const pointFontSize = Math.round(H * 0.060);
        ctx.font = `700 ${pointFontSize}px Inter, system-ui, sans-serif`;
        ctx.fillStyle = '#ffffff';
        ctx.textBaseline = 'middle';

        const starSize = Math.round(H * 0.055);
        const starScale = starSize / 24;
        const starY = Math.round(H * 0.77);

        ctx.save();
        ctx.translate(textX, starY - starSize / 2);
        ctx.scale(starScale, starScale);
        const starPath = new Path2D('M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z');
        ctx.fill(starPath);
        ctx.restore();

        ctx.fillText(points + ' Poin', textX + starSize + Math.round(H * 0.02), starY);

        // 4. Status badge
        const isActive = (status || '').toLowerCase() === 'aktif';
        const sbgColor = isActive ? 'rgba(52,211,153,0.25)' : 'rgba(248,113,113,0.25)';
        const stextColor = isActive ? '#6ee7b7' : '#fca5a5';
        const sText = isActive ? '● Aktif' : '● Tidak Aktif';

        const statusFontSize = Math.round(H * 0.045);
        ctx.font = `600 ${statusFontSize}px Inter, system-ui, sans-serif`;
        const sTextW = ctx.measureText(sText).width;
        const sbPadX = Math.round(H * 0.035);
        const sbW = sTextW + (sbPadX * 2);
        const sbH = Math.round(H * 0.075);
        const sbY = Math.round(H * 0.85);

        ctx.fillStyle = sbgColor;
        roundRect(ctx, textX, sbY, sbW, sbH, 6);
        ctx.fill();

        ctx.fillStyle = stextColor;
        ctx.textBaseline = 'middle';
        ctx.fillText(sText, textX + sbPadX, sbY + sbH / 2);

        // --- Download ---
        const link = document.createElement('a');
        link.download = 'kartu_member_{{ strtolower($customer->customer_id) }}.png';
        link.href = canvas.toDataURL('image/png');
        link.click();

    } catch (err) {
        console.error('Gagal membuat kartu:', err);
        alert('Gagal membuat gambar kartu: ' + err.message);
    } finally {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    }
}

function roundRect(ctx, x, y, w, h, r) {
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.lineTo(x + w - r, y);
    ctx.quadraticCurveTo(x + w, y, x + w, y + r);
    ctx.lineTo(x + w, y + h - r);
    ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
    ctx.lineTo(x + r, y + h);
    ctx.quadraticCurveTo(x, y + h, x, y + h - r);
    ctx.lineTo(x, y + r);
    ctx.quadraticCurveTo(x, y, x + r, y);
    ctx.closePath();
}

// CSS spin animation
const style = document.createElement('style');
style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
document.head.appendChild(style);
</script>
@endpush
