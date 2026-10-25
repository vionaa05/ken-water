@extends('layouts.portal')

@section('title', 'Kartu Member Digital - Ken Water')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <a href="{{ route('portal.home') }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Kartu Member</h1>
    </div>
    
    <button type="button" id="btn-download-card" onclick="downloadLoyaltyCardPng()" class="btn-primary py-2 px-3 text-sm flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
        <span>Unduh Kartu (PNG)</span>
    </button>
</div>

<!-- Digital Card -->
<div class="flex flex-col items-center mb-8">
    @php
        $cardLevel = $user->loyalty_level ?? 'bronze';
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
        $statusBg = ($user->status ?? 'aktif') === 'aktif' ? 'rgba(52,211,153,0.25)' : 'rgba(248,113,113,0.25)';
        $statusColor = ($user->status ?? 'aktif') === 'aktif' ? '#6ee7b7' : '#fca5a5';
        $statusText = ($user->status ?? 'aktif') === 'aktif' ? '● Aktif' : '● Tidak Aktif';
    @endphp

    <div id="loyalty-card-wrapper"
        style="
            position: relative;
            width: 100%;
            max-width: 24rem;
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
                <svg width="22" height="22" fill="rgba(255,255,255,0.85)" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                <span style="font-weight:700; font-size:0.95rem; letter-spacing:0.05em; color:rgba(255,255,255,0.92);">Ken Water</span>
            </div>
            <div style="padding:0.25rem 0.625rem; background:rgba(255,255,255,0.20); border-radius:9999px; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; border:1px solid rgba(255,255,255,0.30); color:white;">
                {{ $levelEmoji }} {{ strtoupper($cardLevel) }}
            </div>
        </div>
        
        <!-- Bottom Row: Name, ID, Poin, Status, and QR -->
        <div style="position:relative; z-index:10; display:flex; justify-content:space-between; align-items:flex-end;">
            <div style="min-width:0; padding-right:0.5rem; flex:1;">
                <p style="font-weight:700; font-size:1rem; color:white; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $user->name }}</p>
                <p style="font-family:monospace; margin-top:0.125rem; font-size:0.7rem; color:rgba(255,255,255,0.70); letter-spacing:0.15em;">{{ $user->customer_id }}</p>
                <p style="margin-top:0.5rem; font-size:0.875rem; font-weight:700; color:white; display:flex; align-items:center; gap:0.25rem;">
                    <svg width="16" height="16" fill="white" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    {{ (int)$user->points }} Poin
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

    <!-- Hidden data for canvas download -->
    <span id="card-data"
        data-level="{{ $cardLevel }}"
        data-grad-from="{{ $gradFrom }}"
        data-grad-via="{{ $gradVia }}"
        data-grad-to="{{ $gradTo }}"
        data-name="{{ $user->name }}"
        data-id="{{ $user->customer_id }}"
        data-points="{{ (int)$user->points }}"
        data-status="{{ $user->status ?? 'aktif' }}"
        data-emoji="{{ $levelEmoji }}"
        data-qr="{{ isset($qrBase64) && $qrBase64 ? $qrBase64 : '' }}"
        style="display:none;"
    ></span>
</div>

<!-- Info & Progress -->
<div class="card p-6 border border-gray-100 shadow-sm">
    <div class="text-center mb-6">
        <h3 class="text-sm font-medium text-gray-500 mb-1">Total Poin Saat Ini</h3>
        <p class="text-4xl font-bold text-primary">{{ $user->points }}</p>
    </div>

    @if($progress['next_level'])
        <p class="text-sm text-center text-gray-600 mb-4">
            Tingkatkan transaksi, kumpulkan <span class="font-bold text-gray-800">{{ $progress['points_needed'] }} poin</span> lagi untuk naik ke level <span class="font-bold capitalize {{ $progress['next_level'] === 'silver' ? 'text-gray-500' : 'text-yellow-600' }}">{{ $progress['next_level'] }}</span>!
        </p>
        
        <div class="w-full bg-gray-200 rounded-full h-4 mb-2 overflow-hidden shadow-inner">
            <div class="bg-primary h-4 rounded-full transition-all duration-1000 ease-out relative" style="width: {{ $progress['percentage'] }}%">
                <div class="absolute right-0 top-0 bottom-0 w-8 bg-white/20"></div>
            </div>
        </div>
        <div class="flex justify-between text-xs text-gray-500 font-medium">
            <span class="uppercase font-bold">{{ $user->loyalty_level }}</span>
            <span class="uppercase font-bold">{{ $progress['next_level'] }}</span>
        </div>
    @else
        <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-center">
            <svg class="w-8 h-8 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
            <p class="font-bold text-green-800">Level Tertinggi Tercapai!</p>
            <p class="text-sm text-green-700 mt-1">Anda menikmati keuntungan maksimal sebagai pelanggan Gold Ken Water.</p>
        </div>
    @endif
</div>

<script>
async function downloadLoyaltyCardPng() {
    const btn = document.getElementById('btn-download-card');
    const dataEl = document.getElementById('card-data');
    if (!dataEl) return;

    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<svg style="animation:spin 1s linear infinite;margin-right:6px;" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8v8H4z" fill="currentColor"/></svg> Memproses...';
    btn.disabled = true;

    try {
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

        const pad = W * 0.05;

        // Background gradient
        const grad = ctx.createLinearGradient(0, 0, W, H);
        grad.addColorStop(0,   gradFrom);
        grad.addColorStop(0.5, gradVia);
        grad.addColorStop(1,   gradTo);
        ctx.fillStyle = grad;
        roundRect(ctx, 0, 0, W, H, W * 0.04);
        ctx.fill();

        // Decorative circles
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

        // Top row: Water drop icon + Ken Water
        const iconX = pad;
        const iconY = pad;
        const iconSize = H * 0.085;

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

        // Level badge
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

        // QR Code
        const qrSize = Math.round(H * 0.38);
        const qrPadding = Math.round(H * 0.025);
        const qrBoxSize = qrSize + (qrPadding * 2);
        const qrBoxX = W - pad - qrBoxSize;
        const qrBoxY = H - pad - qrBoxSize;

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

        // Bottom left text
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

        // 3. Points
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

        // Download PNG
        const link = document.createElement('a');
        link.download = 'kartu_member_{{ strtolower($user->customer_id) }}.png';
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

const spinStyle = document.createElement('style');
spinStyle.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
document.head.appendChild(spinStyle);
</script>
@endsection
