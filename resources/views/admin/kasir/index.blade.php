@extends('layouts.admin')
@section('title', 'Kasir Depot - Ken Water')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-xs border border-gray-100">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 text-white flex items-center justify-center shadow-md shadow-emerald-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-gray-900 tracking-tight">Kasir Depot Ken Water</h1>
                <p class="text-xs text-gray-500 mt-0.5">Catat transaksi langsung di depot. Transaksi otomatis tercatat ke sistem, berstatus Selesai, dan masuk ke riwayat pesanan.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.orders.index', ['source' => 'kasir']) }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                Lihat Riwayat Pesanan Kasir
            </a>
            <a href="{{ route('admin.customers.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition-all border border-emerald-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Pelanggan Baru
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-start gap-3">
        <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="flex-1">
            <p class="text-sm font-bold text-emerald-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3">
        <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="flex-1">
            <p class="text-sm font-bold text-red-800">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form Transaksi Kasir (POS) -->
        <div class="lg:col-span-7 bg-white rounded-2xl p-6 shadow-xs border border-gray-100">
            <div class="border-b border-gray-100 pb-4 mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-900">Form Transaksi Kasir</h2>
                    <p class="text-xs text-gray-500">Pilih pelanggan dan tentukan jumlah galon yang dibeli.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Kasir Aktif
                </span>
            </div>

            <form action="{{ route('admin.kasir.store') }}" method="POST" id="kasirForm" class="space-y-5">
                @csrf

                <!-- Pilih / Cari Pelanggan -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Pilih Pelanggan <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" id="customerSearch" placeholder="Ketik nama, nomor HP, atau ID pelanggan..." 
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all pl-10"
                            autocomplete="off">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>

                    <!-- Hidden input for actual user ID -->
                    <input type="hidden" name="customer_id" id="selectedCustomerId" value="{{ old('customer_id') }}" required>

                    <!-- Dropdown List Hasil Pencarian -->
                    <div id="customerResults" class="hidden mt-2 max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg divide-y divide-gray-100 z-30 relative">
                        <!-- Populated by JS -->
                    </div>

                    <!-- Kartu Informasi Pelanggan Terpilih -->
                    <div id="selectedCustomerCard" class="hidden mt-3 p-3.5 bg-emerald-50/60 border border-emerald-200 rounded-xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-sm shadow-xs">
                                    <span id="custAvatar">U</span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-gray-900" id="custName">-</h4>
                                        <span id="custLevelBadge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 uppercase">Bronze</span>
                                    </div>
                                    <p class="text-xs text-gray-500 font-mono" id="custMeta">-</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-emerald-700 font-bold block" id="custPoints">0 Poin</span>
                                <button type="button" onclick="clearSelectedCustomer()" class="text-[11px] text-red-500 hover:text-red-700 hover:underline">Ganti</button>
                            </div>
                        </div>
                    </div>
                    @error('customer_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jumlah Galon -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Jumlah Galon Air <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="adjustQty(-1)" class="w-11 h-11 rounded-xl bg-gray-100 hover:bg-gray-200 active:scale-95 flex items-center justify-center text-lg font-bold text-gray-700 transition-all select-none">
                            -
                        </button>
                        <input type="number" name="gallon_qty" id="gallonQty" min="1" max="100" value="{{ old('gallon_qty', 1) }}"
                            class="w-24 text-center font-bold text-lg py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white"
                            oninput="calculateTotal()">
                        <button type="button" onclick="adjustQty(1)" class="w-11 h-11 rounded-xl bg-gray-100 hover:bg-gray-200 active:scale-95 flex items-center justify-center text-lg font-bold text-gray-700 transition-all select-none">
                            +
                        </button>

                        <!-- Quick buttons -->
                        <div class="flex items-center gap-1.5 ml-auto">
                            <button type="button" onclick="setQty(1)" class="px-2.5 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 border border-transparent rounded-lg transition-all">1</button>
                            <button type="button" onclick="setQty(2)" class="px-2.5 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 border border-transparent rounded-lg transition-all">2</button>
                            <button type="button" onclick="setQty(3)" class="px-2.5 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 border border-transparent rounded-lg transition-all">3</button>
                            <button type="button" onclick="setQty(5)" class="px-2.5 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 border border-transparent rounded-lg transition-all">5</button>
                            <button type="button" onclick="setQty(10)" class="px-2.5 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 border border-transparent rounded-lg transition-all">10</button>
                        </div>
                    </div>
                </div>

                <!-- Metode Pengambilan & Metode Pembayaran -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Metode Pengambilan
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center justify-center p-2.5 border rounded-xl cursor-pointer text-xs font-semibold text-center transition-all has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-800">
                                <input type="radio" name="delivery_method" value="ambil_sendiri" class="sr-only" checked onchange="toggleDeliveryAddress()">
                                🏪 Ambil di Depot
                            </label>
                            <label class="flex items-center justify-center p-2.5 border rounded-xl cursor-pointer text-xs font-semibold text-center transition-all has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-800">
                                <input type="radio" name="delivery_method" value="antar" class="sr-only" onchange="toggleDeliveryAddress()">
                                🚚 Diantar
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Metode Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="flex items-center justify-center p-2.5 border rounded-xl cursor-pointer text-xs font-semibold text-center transition-all has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-800">
                                <input type="radio" name="payment_method" value="tunai" class="sr-only" checked>
                                💵 Tunai
                            </label>
                            <label class="flex items-center justify-center p-2.5 border rounded-xl cursor-pointer text-xs font-semibold text-center transition-all has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-800">
                                <input type="radio" name="payment_method" value="transfer" class="sr-only">
                                🏦 Transfer
                            </label>
                            <label class="flex items-center justify-center p-2.5 border rounded-xl cursor-pointer text-xs font-semibold text-center transition-all has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-800">
                                <input type="radio" name="payment_method" value="qris" class="sr-only">
                                📱 QRIS
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Alamat Pengantaran (Jika Diantar) -->
                <div id="deliveryAddressField" class="hidden">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Alamat Pengantaran
                    </label>
                    <textarea name="delivery_address" id="deliveryAddressInput" rows="2" placeholder="Masukkan alamat tujuan pengantaran..." 
                        class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <!-- Catatan Transaksi -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Catatan Transaksi (Opsional)
                    </label>
                    <input type="text" name="notes" placeholder="Contoh: Galon titipan / bawa galon sendiri" 
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Ringkasan Perhitungan Pembayaran -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-2">
                    <div class="flex justify-between text-xs text-gray-600">
                        <span>Harga Satuan Galon:</span>
                        <span class="font-semibold">Rp {{ number_format($pricePerGallon, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600">
                        <span>Jumlah Pembelian:</span>
                        <span class="font-semibold" id="summaryQty">1 Galon</span>
                    </div>
                    <div class="flex justify-between text-xs text-emerald-700 font-semibold pt-1 border-t border-gray-200">
                        <span>Poin Loyalitas Didapat:</span>
                        <span id="summaryPoints">+1 Poin</span>
                    </div>
                    <div class="flex justify-between items-center text-sm font-extrabold text-gray-900 pt-2 border-t border-gray-300">
                        <span>Total Pembayaran:</span>
                        <span class="text-xl text-emerald-600 font-black" id="summaryTotal">Rp {{ number_format($pricePerGallon, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Action Button -->
                <button type="submit" id="btnSubmitKasir" class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-sm rounded-xl shadow-md shadow-emerald-500/25 transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Transaksi Kasir (Selesai)
                </button>
            </form>
        </div>

        <!-- Panel Kanan: Riwayat Transaksi Kasir Hari Ini -->
        <div class="lg:col-span-5 space-y-5">
            <div class="bg-white rounded-2xl p-5 shadow-xs border border-gray-100">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Transaksi Kasir Hari Ini</h3>
                        <p class="text-[11px] text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <span class="text-xs font-extrabold px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full">
                        {{ $todayOrders->count() }} Transaksi
                    </span>
                </div>

                <div class="space-y-3 max-h-[560px] overflow-y-auto pr-1">
                    @forelse($todayOrders as $order)
                    <div class="p-3 bg-gray-50/80 hover:bg-emerald-50/30 rounded-xl border border-gray-100 transition-all flex items-center justify-between">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-gray-900">{{ $order->user->name ?? 'Pelanggan' }}</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-sm bg-emerald-100 text-emerald-800 font-bold">Kasir</span>
                            </div>
                            <p class="text-[11px] text-gray-500">
                                {{ $order->gallon_qty }} Galon • Rp {{ number_format($order->total_price, 0, ',', '.') }}
                            </p>
                            <p class="text-[10px] text-gray-400">
                                {{ $order->order_date ? $order->order_date->format('H:i') : '-' }} • 
                                {{ ucfirst($order->payment_method) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Selesai
                            </span>
                            <span class="block text-[10px] text-emerald-600 font-semibold mt-1">
                                +{{ $order->gallon_qty }} Poin
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <p class="text-xs font-semibold text-gray-600">Belum ada transaksi kasir hari ini</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Transaksi yang Anda simpan akan muncul langsung di sini.</p>
                    </div>
                    @endforelse
                </div>

                @if($todayOrders->count() > 0)
                <div class="pt-3 border-t border-gray-100 mt-3 text-center">
                    <a href="{{ route('admin.orders.index', ['source' => 'kasir']) }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                        Lihat Seluruh Riwayat Pesanan &rarr;
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Data pelanggan dari server
    const customersData = @json($customers);
    const pricePerGallon = {{ $pricePerGallon }};

    const customerSearch = document.getElementById('customerSearch');
    const customerResults = document.getElementById('customerResults');
    const selectedCustomerId = document.getElementById('selectedCustomerId');
    const selectedCustomerCard = document.getElementById('selectedCustomerCard');
    const custAvatar = document.getElementById('custAvatar');
    const custName = document.getElementById('custName');
    const custMeta = document.getElementById('custMeta');
    const custLevelBadge = document.getElementById('custLevelBadge');
    const custPoints = document.getElementById('custPoints');
    const gallonQty = document.getElementById('gallonQty');
    const summaryQty = document.getElementById('summaryQty');
    const summaryPoints = document.getElementById('summaryPoints');
    const summaryTotal = document.getElementById('summaryTotal');
    const deliveryAddressField = document.getElementById('deliveryAddressField');
    const deliveryAddressInput = document.getElementById('deliveryAddressInput');

    let currentCustomer = null;

    // Filter pelanggan saat mengetik
    customerSearch.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        if (query.length === 0) {
            customerResults.classList.add('hidden');
            customerResults.innerHTML = '';
            return;
        }

        const filtered = customersData.filter(c => {
            const name = (c.name || '').toLowerCase();
            const phone = (c.phone || '').toLowerCase();
            const code = (c.customer_id || '').toLowerCase();
            return name.includes(query) || phone.includes(query) || code.includes(query);
        }).slice(0, 8);

        if (filtered.length === 0) {
            customerResults.innerHTML = `
                <div class="p-3 text-xs text-gray-500 text-center">
                    Tidak ditemukan pelanggan dengan kata kunci "<strong>${escapeHtml(query)}</strong>".
                    <div class="mt-2">
                        <a href="{{ route('admin.customers.create') }}" target="_blank" class="text-emerald-600 font-bold hover:underline">+ Daftarkan Pelanggan Baru</a>
                    </div>
                </div>
            `;
            customerResults.classList.remove('hidden');
            return;
        }

        let html = '';
        filtered.forEach(c => {
            html += `
                <div onclick="selectCustomer(${c.id})" class="p-3 hover:bg-emerald-50/50 cursor-pointer flex items-center justify-between transition-colors">
                    <div>
                        <p class="text-xs font-bold text-gray-900">${escapeHtml(c.name)}</p>
                        <p class="text-[11px] text-gray-500">${escapeHtml(c.phone || '-')} • ID: ${escapeHtml(c.customer_id || '-')}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 uppercase">${escapeHtml(c.loyalty_level || 'bronze')}</span>
                        <span class="text-[11px] text-emerald-600 font-bold block mt-0.5">${c.points || 0} Poin</span>
                    </div>
                </div>
            `;
        });
        customerResults.innerHTML = html;
        customerResults.classList.remove('hidden');
    });

    // Pilih pelanggan
    function selectCustomer(id) {
        const c = customersData.find(item => item.id === id);
        if (!c) return;

        currentCustomer = c;
        selectedCustomerId.value = c.id;
        custAvatar.textContent = (c.name || 'U').charAt(0).toUpperCase();
        custName.textContent = c.name;
        custMeta.textContent = `${c.phone || '-'} • ID: ${c.customer_id || '-'}`;
        custLevelBadge.textContent = (c.loyalty_level || 'bronze').toUpperCase();
        custPoints.textContent = `${c.points || 0} Poin`;

        if (c.address && deliveryAddressInput) {
            deliveryAddressInput.value = c.address;
        }

        customerResults.classList.add('hidden');
        customerSearch.value = '';
        customerSearch.classList.add('hidden');
        selectedCustomerCard.classList.remove('hidden');
    }

    function clearSelectedCustomer() {
        currentCustomer = null;
        selectedCustomerId.value = '';
        selectedCustomerCard.classList.add('hidden');
        customerSearch.classList.remove('hidden');
        customerSearch.value = '';
        customerSearch.focus();
    }

    function setQty(val) {
        gallonQty.value = val;
        calculateTotal();
    }

    function adjustQty(delta) {
        let current = parseInt(gallonQty.value) || 1;
        current += delta;
        if (current < 1) current = 1;
        if (current > 100) current = 100;
        gallonQty.value = current;
        calculateTotal();
    }

    function calculateTotal() {
        let qty = parseInt(gallonQty.value) || 1;
        if (qty < 1) qty = 1;
        const total = qty * pricePerGallon;

        summaryQty.textContent = `${qty} Galon`;
        summaryPoints.textContent = `+${qty} Poin`;
        summaryTotal.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(total)}`;
    }

    function toggleDeliveryAddress() {
        const method = document.querySelector('input[name="delivery_method"]:checked').value;
        if (method === 'antar') {
            deliveryAddressField.classList.remove('hidden');
            if (currentCustomer && currentCustomer.address && !deliveryAddressInput.value) {
                deliveryAddressInput.value = currentCustomer.address;
            }
        } else {
            deliveryAddressField.classList.add('hidden');
        }
    }

    // Validasi form sebelum kirim
    document.getElementById('kasirForm').addEventListener('submit', function(e) {
        if (!selectedCustomerId.value) {
            e.preventDefault();
            alert('Silakan cari dan pilih pelanggan terlebih dahulu!');
            customerSearch.focus();
        }
    });

    // Tutup autocomplete saat klik di luar
    document.addEventListener('click', function(e) {
        if (!customerSearch.contains(e.target) && !customerResults.contains(e.target)) {
            customerResults.classList.add('hidden');
        }
    });

    function escapeHtml(text) {
        if (!text) return '';
        return text.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Init jika ada old value
    @if(old('customer_id'))
        selectCustomer({{ old('customer_id') }});
    @endif
</script>
@endpush
@endsection
