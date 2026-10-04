@extends('layouts.admin')
@section('title', 'Detail Kampanye - Ken Water')
@section('content')
<div class="mb-6">
    <a href="{{ route('admin.campaigns.index') }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Kembali ke Kampanye
    </a>
    <h1 class="text-2xl font-bold text-gray-900">{{ $campaign->name }}</h1>
    <p class="text-sm text-gray-500 mt-1">{{ $campaign->type_label }} · {{ $campaign->period_start->format('d M Y') }} - {{ $campaign->period_end->format('d M Y') }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Template Pesan -->
        <div class="card p-6">
            <h3 class="font-bold text-gray-900 mb-4">Template Pesan WhatsApp</h3>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <pre class="text-sm text-gray-700 whitespace-pre-wrap font-sans">{{ $campaign->message_template }}</pre>
            </div>
            @if($campaign->voucher)
            <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                <div>
                    <p class="text-xs text-green-700 font-medium">Voucher Terlampir</p>
                    <p class="text-sm font-bold text-green-900">{{ $campaign->voucher->name }} ({{ $campaign->voucher->code }})</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Daftar Target Pelanggan -->
        <div class="card p-0">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-900">Daftar Target Pelanggan ({{ count($targetCustomers) }})</h3>
            </div>
            <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                @foreach($targetCustomers as $customer)
                <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $customer->name }}</p>
                            <p class="text-xs text-gray-400">{{ $customer->phone }}</p>
                        </div>
                    </div>
                    <!-- Link WA langsung -->
                    @php
                        $msg = str_replace(
                            ['{nama}', '{kode_voucher}', '{link_portal}'],
                            [$customer->name, optional($campaign->voucher)->code ?? '', url('/portal')],
                            $campaign->message_template
                        );
                        $waLink = 'https://wa.me/' . preg_replace('/^0/', '62', $customer->phone) . '?text=' . urlencode($msg);
                    @endphp
                    <a href="{{ $waLink }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-100 hover:bg-green-200 px-3 py-1.5 rounded-full transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Kirim WA
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="card p-6 h-fit">
        <h3 class="font-bold text-gray-900 mb-4">Info Kampanye</h3>
        <div class="space-y-3 text-sm">
            <div><p class="text-xs text-gray-500">Tipe</p><p class="font-medium">{{ $campaign->type_label }}</p></div>
            <div><p class="text-xs text-gray-500">Periode</p><p class="font-medium">{{ $campaign->period_start->format('d M Y') }} - {{ $campaign->period_end->format('d M Y') }}</p></div>
            <div><p class="text-xs text-gray-500">Target Pelanggan</p><p class="font-bold text-primary text-xl">{{ count($targetCustomers) }}</p></div>
            <div><p class="text-xs text-gray-500">Dibuat oleh</p><p class="font-medium">{{ $campaign->creator->name }}</p></div>
        </div>
        <div class="mt-6 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-500 mb-3">Klik tombol "Kirim WA" di samping nama pelanggan untuk membuka WhatsApp dengan pesan yang sudah terisi otomatis.</p>
        </div>
    </div>
</div>
@endsection
