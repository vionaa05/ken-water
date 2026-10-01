@extends('layouts.portal')

@section('title', 'Buat Pesanan - Ken Water')

@section('content')
<div class="mb-6">
    <a href="{{ route('portal.home') }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Kembali
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Buat Pesanan</h1>
    <p class="text-sm text-gray-500 mt-1">Pesan galon air minum Anda sekarang.</p>
</div>

<form method="POST" action="{{ route('portal.order.store') }}" x-data="orderForm({{ $pricePerGallon }})">
    @csrf

    <div class="card p-5 mb-4">
        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            Detail Pesanan
        </h3>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Galon</label>
            <div class="flex items-center">
                <button type="button" @click="decrementQty" class="w-10 h-10 rounded-l-lg bg-gray-100 border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                </button>
                <input type="number" name="gallon_qty" x-model="qty" min="1" max="50" required
                    class="w-20 h-10 text-center border-y border-gray-300 focus:ring-0 focus:border-gray-300 font-bold text-lg p-0">
                <button type="button" @click="incrementQty" class="w-10 h-10 rounded-r-lg bg-gray-100 border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-2">Harga per galon: Rp {{ number_format($pricePerGallon, 0, ',', '.') }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pengiriman</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="relative flex flex-col items-center justify-center p-3 border rounded-lg cursor-pointer transition-all"
                    :class="deliveryMethod === 'antar' ? 'border-primary bg-blue-50 text-primary' : 'border-gray-200 hover:bg-gray-50 text-gray-600'">
                    <input type="radio" name="delivery_method" value="antar" x-model="deliveryMethod" class="sr-only">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    <span class="text-sm font-medium">Diantar</span>
                </label>
                
                <label class="relative flex flex-col items-center justify-center p-3 border rounded-lg cursor-pointer transition-all"
                    :class="deliveryMethod === 'ambil_sendiri' ? 'border-primary bg-blue-50 text-primary' : 'border-gray-200 hover:bg-gray-50 text-gray-600'">
                    <input type="radio" name="delivery_method" value="ambil_sendiri" x-model="deliveryMethod" class="sr-only">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span class="text-sm font-medium">Ambil Sendiri</span>
                </label>
            </div>
        </div>

        <div x-show="deliveryMethod === 'antar'" x-transition class="mb-4">
            <label for="delivery_address" class="block text-sm font-medium text-gray-700 mb-2">Alamat Pengiriman</label>
            <textarea name="delivery_address" id="delivery_address" rows="3" :required="deliveryMethod === 'antar'"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary text-sm">{{ auth()->user()->address }}</textarea>
            <p class="text-xs text-gray-500 mt-1">Ubah jika ingin dikirim ke alamat berbeda.</p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="relative flex items-center p-3 border rounded-lg cursor-pointer transition-all"
                    :class="paymentMethod === 'tunai' ? 'border-primary bg-blue-50 text-primary' : 'border-gray-200 hover:bg-gray-50 text-gray-600'">
                    <input type="radio" name="payment_method" value="tunai" x-model="paymentMethod" class="sr-only">
                    <span class="text-sm font-medium w-full text-center">Tunai / COD</span>
                </label>
                
                <label class="relative flex items-center p-3 border rounded-lg cursor-pointer transition-all"
                    :class="paymentMethod === 'transfer' ? 'border-primary bg-blue-50 text-primary' : 'border-gray-200 hover:bg-gray-50 text-gray-600'">
                    <input type="radio" name="payment_method" value="transfer" x-model="paymentMethod" class="sr-only">
                    <span class="text-sm font-medium w-full text-center">Transfer / QRIS</span>
                </label>
            </div>
        </div>
        
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
            <input type="text" name="notes" id="notes" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary text-sm"
                placeholder="Misal: galon ditaruh di teras">
        </div>
    </div>

    <!-- Voucher & Promo -->
    @if($vouchers->count() > 0 || $promos->count() > 0)
    <div class="card p-5 mb-4">
        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
            Diskon & Promo
        </h3>
        
        <p class="text-xs text-gray-500 mb-3">Hanya bisa menggunakan salah satu diskon.</p>
        
        <div class="space-y-3">
            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                :class="discountType === '' ? 'border-gray-400 bg-gray-50' : ''">
                <input type="radio" x-model="discountType" value="" @change="clearDiscount" class="w-4 h-4 text-primary border-gray-300 focus:ring-primary">
                <span class="text-sm font-medium text-gray-700">Tidak gunakan diskon</span>
            </label>

            @foreach($promos as $promo)
            <label class="flex items-center gap-3 p-3 border border-blue-200 rounded-lg cursor-pointer hover:bg-blue-50 transition-colors bg-blue-50/50"
                :class="discountType === 'promo_{{ $promo->id }}' ? 'border-primary bg-blue-100 ring-1 ring-primary' : ''">
                <input type="radio" name="promo_id" value="{{ $promo->id }}" x-model="discountType" 
                    @change="setDiscount({{ $promo->discount_type === 'nominal' ? $promo->discount_value : 0 }}, {{ $promo->discount_type === 'persen' ? $promo->discount_value : 0 }}, 0)"
                    class="w-4 h-4 text-primary border-gray-300 focus:ring-primary">
                <div class="flex-1">
                    <div class="flex justify-between items-start">
                        <span class="text-sm font-bold text-blue-900">{{ $promo->name }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $promo->discount_type === 'nominal' ? '- Rp'.number_format($promo->discount_value, 0, ',', '.') : '- '.$promo->discount_value.'%' }}
                        </span>
                    </div>
                    @if($promo->description)
                    <p class="text-xs text-blue-700 mt-1">{{ $promo->description }}</p>
                    @endif
                </div>
            </label>
            @endforeach

            @foreach($vouchers as $uv)
            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                :class="discountType === 'voucher_{{ $uv->id }}' ? 'border-primary bg-blue-50 ring-1 ring-primary' : ''">
                <input type="radio" name="voucher_id" value="{{ $uv->id }}" x-model="discountType" 
                    @change="setDiscount({{ $uv->voucher->discount_type === 'nominal' ? $uv->voucher->discount_value : 0 }}, {{ $uv->voucher->discount_type === 'persen' ? $uv->voucher->discount_value : 0 }}, {{ $uv->voucher->min_purchase ?? 0 }})"
                    class="w-4 h-4 text-primary border-gray-300 focus:ring-primary">
                <div class="flex-1">
                    <div class="flex justify-between items-start">
                        <span class="text-sm font-bold text-gray-900">{{ $uv->voucher->name }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                            {{ $uv->voucher->discount_type === 'nominal' ? '- Rp'.number_format($uv->voucher->discount_value, 0, ',', '.') : '- '.$uv->voucher->discount_value.'%' }}
                        </span>
                    </div>
                    @if($uv->voucher->min_purchase)
                    <p class="text-xs text-gray-500 mt-1">Min. belanja: Rp {{ number_format($uv->voucher->min_purchase, 0, ',', '.') }}</p>
                    @endif
                </div>
            </label>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Total & Submit (Sticky Bottom) -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] p-4 sm:relative sm:shadow-none sm:rounded-xl sm:border sm:mt-6 z-40 pb-safe">
        <div class="max-w-md mx-auto sm:max-w-none">
            <div class="flex justify-between items-center mb-1 text-sm text-gray-500">
                <span>Subtotal (<span x-text="qty"></span> galon)</span>
                <span x-text="'Rp ' + formatMoney(subtotal)"></span>
            </div>
            
            <div x-show="discountAmount > 0" class="flex justify-between items-center mb-1 text-sm text-green-600 font-medium">
                <span>Diskon</span>
                <span x-text="'- Rp ' + formatMoney(discountAmount)"></span>
            </div>
            
            <div x-show="discountError" class="text-xs text-red-500 mb-2" x-text="discountError"></div>
            
            <div class="flex justify-between items-center mb-4 pt-2 border-t border-gray-100">
                <span class="font-bold text-gray-900">Total Pembayaran</span>
                <span class="font-bold text-xl text-primary" x-text="'Rp ' + formatMoney(total)"></span>
            </div>
            
            <button type="submit" class="w-full btn-primary py-3 text-lg" :disabled="qty < 1">
                Pesan Sekarang
            </button>
        </div>
    </div>
    
    <div class="h-40 sm:h-0"></div> <!-- Spacer for fixed bottom on mobile -->
</form>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('orderForm', (pricePerGallon) => ({
        pricePerGallon: pricePerGallon,
        qty: 1,
        deliveryMethod: 'antar',
        paymentMethod: 'tunai',
        
        discountType: '',
        discountNominal: 0,
        discountPercent: 0,
        minPurchase: 0,
        discountError: '',
        
        get subtotal() {
            return this.qty * this.pricePerGallon;
        },
        
        get discountAmount() {
            this.discountError = '';
            
            if (this.discountType === '') return 0;
            
            if (this.minPurchase > 0 && this.subtotal < this.minPurchase) {
                this.discountError = `Belum memenuhi minimal belanja Rp ${this.formatMoney(this.minPurchase)}`;
                return 0;
            }
            
            if (this.discountNominal > 0) {
                return Math.min(this.discountNominal, this.subtotal);
            }
            
            if (this.discountPercent > 0) {
                return Math.round(this.subtotal * (this.discountPercent / 100));
            }
            
            return 0;
        },
        
        get total() {
            return Math.max(0, this.subtotal - this.discountAmount);
        },
        
        incrementQty() {
            if (this.qty < 50) this.qty++;
        },
        
        decrementQty() {
            if (this.qty > 1) this.qty--;
        },
        
        clearDiscount() {
            this.discountNominal = 0;
            this.discountPercent = 0;
            this.minPurchase = 0;
            
            // clear hidden inputs
            document.querySelectorAll('input[name="promo_id"], input[name="voucher_id"]').forEach(el => {
                if(el.type === 'radio' && !el.checked) el.checked = false; // Just to be safe, v-model handles it mostly
            });
        },
        
        setDiscount(nominal, percent, min) {
            this.discountNominal = nominal;
            this.discountPercent = percent;
            this.minPurchase = min;
        },
        
        formatMoney(amount) {
            return new Intl.NumberFormat('id-ID').format(amount);
        }
    }))
})
</script>
@endsection
