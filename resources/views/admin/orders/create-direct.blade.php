@extends('layouts.admin')

@section('title', 'Input Transaksi Langsung - Ken Water')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.customers.show', $customer) }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Detail Pelanggan
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Input Transaksi Langsung</h1>
    <p class="text-sm text-gray-500 mt-1">Catat transaksi pembelian galon langsung di depot (Kasir / Offline)</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form Transaksi -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.transactions.store', $customer) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <!-- Info Pelanggan -->
                <div class="bg-blue-50/60 p-4 rounded-lg border border-blue-100 flex items-center gap-3">
                    <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center font-bold text-lg">
                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-bold text-gray-900">{{ $customer->name }}</p>
                        <p class="text-xs text-gray-600">Kode: {{ $customer->customer_code }} • HP: {{ $customer->phone }}</p>
                        <span class="inline-block mt-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst($customer->loyalty_tier) }} • {{ number_format($customer->points_balance) }} Poin
                        </span>
                    </div>
                </div>

                <!-- Jumlah Galon -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Galon <span class="text-red-500">*</span></label>
                    <input type="number" name="gallon_qty" id="gallon_qty" value="{{ old('gallon_qty', 1) }}" min="1" max="100" required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary font-semibold text-lg"
                        oninput="calculateTotal()">
                    @error('gallon_qty')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Harga Satuan & Subtotal -->
                <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Harga per Galon</p>
                        <p class="text-base font-bold text-gray-900">Rp {{ number_format($pricePerGallon, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 font-medium">Total Tagihan</p>
                        <p class="text-xl font-extrabold text-primary" id="total_display">Rp {{ number_format($pricePerGallon, 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Metode Pembayaran <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="tunai" checked class="text-primary focus:ring-primary">
                            <span class="ml-2 text-sm font-medium text-gray-800">Tunai</span>
                        </label>
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="transfer" class="text-primary focus:ring-primary">
                            <span class="ml-2 text-sm font-medium text-gray-800">Transfer QRIS / Bank</span>
                        </label>
                    </div>
                </div>

                <!-- Catatan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm" placeholder="Contoh: Titip di garasi, dll.">{{ old('notes') }}</textarea>
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <a href="{{ route('admin.customers.show', $customer) }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg border border-gray-300">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold text-sm rounded-lg shadow-sm">
                        Simpan & Selesaikan Transaksi
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Info Poin & Perolehan -->
    <div class="space-y-6">
        <div class="bg-gradient-to-br from-blue-600 to-cyan-700 text-white rounded-xl p-6 shadow-md">
            <h3 class="text-sm uppercase tracking-wider text-blue-200 font-semibold mb-2">Simulasi Poin Earned</h3>
            <div class="text-3xl font-extrabold" id="poin_simulasi">+1 Poin</div>
            <p class="text-xs text-blue-100 mt-2">Pelanggan akan secara otomatis mendapatkan 1 poin untuk setiap galon yang dibeli.</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h4 class="font-bold text-gray-900 text-sm mb-3">Ketentuan Transaksi Langsung</h4>
            <ul class="text-xs text-gray-600 space-y-2 list-disc list-inside">
                <li>Status transaksi langsung otomatis dianggap <strong>Selesai</strong>.</li>
                <li>Poin loyalitas langsung ditambahkan ke saldo pelanggan.</li>
                <li>Jumlah pesanan per periode akan bertambah secara otomatis.</li>
            </ul>
        </div>
    </div>
</div>

<script>
    const unitPrice = {{ $pricePerGallon }};
    function calculateTotal() {
        const qtyInput = document.getElementById('gallon_qty');
        let qty = parseInt(qtyInput.value) || 1;
        if (qty < 1) qty = 1;
        const total = qty * unitPrice;
        document.getElementById('total_display').innerText = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('poin_simulasi').innerText = '+' + qty + ' Poin';
    }
</script>
@endsection
