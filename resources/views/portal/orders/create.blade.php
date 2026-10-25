@extends('layouts.portal')

@section('title', 'Buat Pesanan - Ken Water')

@section('content')
<div class="mb-5 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="{{ route('portal.home') }}" class="w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-primary hover:border-primary flex items-center justify-center transition-colors shadow-xs">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <h1 class="text-xl font-bold text-gray-900">Buat Pesanan</h1>
    </div>
</div>

<form method="POST" action="{{ route('portal.order.store') }}" x-data="orderForm({{ $pricePerGallon }})">
    @csrf

    <div class="card p-5 mb-4 border border-gray-100 shadow-sm rounded-2xl bg-white">
        <!-- Qty Selector -->
        <div class="mb-5 bg-sky-50/60 p-4 rounded-xl border border-sky-100/70">
            <div class="flex justify-between items-center mb-2">
                <label class="block text-sm font-bold text-gray-800">Jumlah Galon</label>
                <span class="text-xs font-semibold text-primary">Rp {{ number_format($pricePerGallon, 0, ',', '.') }}/galon</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center bg-white rounded-xl shadow-xs border border-gray-200 p-1">
                    <button type="button" @click="decrementQty" class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center text-gray-600 hover:bg-gray-100 active:scale-95 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"></path></svg>
                    </button>
                    <input type="number" name="gallon_qty" x-model="qty" min="1" max="50" required
                        class="w-16 h-10 text-center border-0 focus:ring-0 font-extrabold text-xl text-gray-800 p-0">
                    <button type="button" @click="incrementQty" class="w-10 h-10 rounded-lg bg-primary text-white flex items-center justify-center hover:bg-primary-dark active:scale-95 transition-all shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                </div>
                <div class="flex-1 text-right">
                    <span class="text-base font-extrabold text-gray-800" x-text="'Rp ' + formatMoney(subtotal)"></span>
                </div>
            </div>
        </div>

        <!-- Delivery Method -->
        <div class="mb-5">
            <label class="block text-sm font-bold text-gray-700 mb-2">Metode Pengiriman</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="relative flex items-center justify-center p-3.5 border-2 rounded-xl cursor-pointer transition-all gap-2"
                    :class="deliveryMethod === 'antar' ? 'border-primary bg-sky-50/60 text-primary font-bold shadow-xs' : 'border-gray-200 hover:bg-gray-50 text-gray-600 font-medium'">
                    <input type="radio" name="delivery_method" value="antar" x-model="deliveryMethod" class="sr-only">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <span class="text-sm">Diantar Kurir</span>
                </label>
                
                <label class="relative flex items-center justify-center p-3.5 border-2 rounded-xl cursor-pointer transition-all gap-2"
                    :class="deliveryMethod === 'ambil_sendiri' ? 'border-primary bg-sky-50/60 text-primary font-bold shadow-xs' : 'border-gray-200 hover:bg-gray-50 text-gray-600 font-medium'">
                    <input type="radio" name="delivery_method" value="ambil_sendiri" x-model="deliveryMethod" class="sr-only">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span class="text-sm">Ambil Sendiri</span>
                </label>
            </div>
        </div>

        <div x-show="deliveryMethod === 'antar'" x-transition class="mb-5">
            <label for="delivery_address" class="block text-sm font-bold text-gray-700 mb-1.5">Alamat Pengiriman</label>
            <textarea name="delivery_address" id="delivery_address" rows="2" :required="deliveryMethod === 'antar'"
                class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary text-sm bg-white" placeholder="Alamat lengkap tujuan">{{ auth()->user()->address }}</textarea>
        </div>

        <div class="mb-5">
            <label class="block text-sm font-bold text-gray-700 mb-2">Metode Pembayaran</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="relative flex items-center justify-center p-3.5 border-2 rounded-xl cursor-pointer transition-all gap-2"
                    :class="paymentMethod === 'tunai' ? 'border-primary bg-sky-50/60 text-primary font-bold shadow-xs' : 'border-gray-200 hover:bg-gray-50 text-gray-600 font-medium'">
                    <input type="radio" name="payment_method" value="tunai" x-model="paymentMethod" class="sr-only">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="text-sm">Tunai / COD</span>
                </label>
                
                <label class="relative flex items-center justify-center p-3.5 border-2 rounded-xl cursor-pointer transition-all gap-2"
                    :class="paymentMethod === 'transfer' ? 'border-primary bg-sky-50/60 text-primary font-bold shadow-xs' : 'border-gray-200 hover:bg-gray-50 text-gray-600 font-medium'">
                    <input type="radio" name="payment_method" value="transfer" x-model="paymentMethod" class="sr-only">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    <span class="text-sm">Transfer / QRIS</span>
                </label>
            </div>
        </div>
        
        <div>
            <label for="notes" class="block text-sm font-bold text-gray-700 mb-1.5">Catatan (Opsional)</label>
            <input type="text" name="notes" id="notes" 
                class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary text-sm"
                placeholder="Catatan tambahan untuk kurir">
        </div>
    </div>

    <!-- Voucher & Promo -->
    @if($vouchers->count() > 0 || $promos->count() > 0)
    <div class="card p-5 mb-4 border border-gray-100 shadow-sm rounded-2xl bg-white">
        <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2 text-sm">
            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
            Diskon & Promo
        </h3>
        
        <input type="hidden" name="promo_id" :value="selectedPromoId">
        <input type="hidden" name="voucher_id" :value="selectedVoucherId">

        <div class="space-y-2.5">
            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors"
                :class="discountType === '' ? 'border-primary bg-sky-50/40 text-primary font-semibold' : 'text-gray-700'">
                <input type="radio" x-model="discountType" value="" @change="clearDiscount()" class="w-4 h-4 text-primary border-gray-300 focus:ring-primary">
                <span class="text-sm">Tanpa promo</span>
            </label>

            @foreach($promos as $promo)
            <label class="flex items-center gap-3 p-3 border border-sky-200 rounded-xl cursor-pointer hover:bg-sky-50/50 transition-colors bg-sky-50/20"
                :class="discountType === 'promo_{{ $promo->id }}' ? 'border-primary bg-sky-100/50 ring-1 ring-primary' : ''">
                <input type="radio" id="promo_radio_{{ $promo->id }}" value="promo_{{ $promo->id }}" x-model="discountType" 
                    @change="selectPromo('{{ $promo->id }}', {{ $promo->discount_type === 'nominal' ? $promo->discount_value : 0 }}, {{ $promo->discount_type === 'persen' ? $promo->discount_value : 0 }})"
                    class="w-4 h-4 text-primary border-gray-300 focus:ring-primary">
                <div class="flex-1">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-900">{{ $promo->name }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-sky-100 text-sky-800">
                            {{ $promo->discount_type === 'nominal' ? '- Rp'.number_format($promo->discount_value, 0, ',', '.') : '- '.$promo->discount_value.'%' }}
                        </span>
                    </div>
                    @if($promo->description)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $promo->description }}</p>
                    @endif
                </div>
            </label>
            @endforeach

            @foreach($vouchers as $uv)
            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors"
                :class="discountType === 'voucher_{{ $uv->id }}' ? 'border-primary bg-sky-50/50 ring-1 ring-primary' : ''">
                <input type="radio" id="voucher_radio_{{ $uv->id }}" value="voucher_{{ $uv->id }}" x-model="discountType" 
                    @change="selectVoucher('{{ $uv->id }}', {{ $uv->voucher->discount_type === 'nominal' ? $uv->voucher->discount_value : 0 }}, {{ $uv->voucher->discount_type === 'persen' ? $uv->voucher->discount_value : 0 }}, {{ $uv->voucher->min_purchase ?? 0 }})"
                    class="w-4 h-4 text-primary border-gray-300 focus:ring-primary">
                <div class="flex-1">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-sm font-bold text-gray-900">{{ $uv->voucher->name }}</span>
                            @if($uv->voucher->min_purchase)
                            <span class="text-[11px] text-gray-400 block">Min. Rp {{ number_format($uv->voucher->min_purchase, 0, ',', '.') }}</span>
                            @endif
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-green-100 text-green-800">
                            {{ $uv->voucher->discount_type === 'nominal' ? '- Rp'.number_format($uv->voucher->discount_value, 0, ',', '.') : '- '.$uv->voucher->discount_value.'%' }}
                        </span>
                    </div>
                </div>
            </label>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Total & Submit (Sticky Bottom) -->
    <div class="fixed bottom-16 sm:bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-gray-200/80 shadow-[0_-8px_20px_-4px_rgba(0,0,0,0.12)] p-4 sm:relative sm:shadow-none sm:rounded-2xl sm:border sm:mt-6 z-40 pb-3 sm:pb-4 transition-all">
        <div class="max-w-md mx-auto sm:max-w-none">
            <div class="flex justify-between items-center mb-1 text-xs sm:text-sm text-gray-500 font-medium">
                <span>Subtotal (<span x-text="qty" class="font-bold text-gray-700"></span> galon)</span>
                <span x-text="'Rp ' + formatMoney(subtotal)" class="text-gray-800"></span>
            </div>
            
            <div x-show="discountAmount > 0" class="flex justify-between items-center mb-1 text-xs sm:text-sm text-green-600 font-semibold">
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    Diskon
                </span>
                <span x-text="'- Rp ' + formatMoney(discountAmount)"></span>
            </div>
            
            <div x-show="discountError" class="text-xs text-red-500 mb-2 font-medium" x-text="discountError"></div>
            
            <div class="flex justify-between items-center mb-3 pt-2 border-t border-gray-100">
                <span class="font-bold text-gray-800 text-sm sm:text-base">Total Pembayaran</span>
                <span class="font-extrabold text-xl sm:text-2xl text-primary" x-text="'Rp ' + formatMoney(total)"></span>
            </div>
            
            <button type="submit" class="w-full btn-primary py-3.5 text-base font-bold rounded-xl shadow-lg shadow-sky-500/25 active:scale-[0.98] transition-all flex items-center justify-center gap-2" :disabled="qty < 1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>Pesan Sekarang</span>
            </button>
        </div>
    </div>
    
    <div class="h-48 sm:h-6"></div> <!-- Safe spacer for mobile sticky bar + bottom nav -->
</form>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('orderForm', (pricePerGallon) => ({
        pricePerGallon: pricePerGallon,
        qty: 1,
        deliveryMethod: 'antar',
        paymentMethod: 'tunai',
        
        discountType: '',
        selectedPromoId: '',
        selectedVoucherId: '',
        discountNominal: 0,
        discountPercent: 0,
        minPurchase: 0,
        discountError: '',

        init() {
            this.$nextTick(() => {
                const urlParams = new URLSearchParams(window.location.search);
                const pId = urlParams.get('promo_id');
                const vId = urlParams.get('voucher_id');
                if (pId) {
                    const el = document.getElementById('promo_radio_' + pId);
                    if (el) {
                        el.checked = true;
                        el.dispatchEvent(new Event('change'));
                    }
                } else if (vId) {
                    const el = document.getElementById('voucher_radio_' + vId);
                    if (el) {
                        el.checked = true;
                        el.dispatchEvent(new Event('change'));
                    }
                }
            });
        },
        
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
            this.discountType = '';
            this.selectedPromoId = '';
            this.selectedVoucherId = '';
            this.discountNominal = 0;
            this.discountPercent = 0;
            this.minPurchase = 0;
        },
        
        selectPromo(id, nominal, percent) {
            this.discountType = 'promo_' + id;
            this.selectedPromoId = id;
            this.selectedVoucherId = '';
            this.discountNominal = nominal;
            this.discountPercent = percent;
            this.minPurchase = 0;
        },

        selectVoucher(id, nominal, percent, min) {
            this.discountType = 'voucher_' + id;
            this.selectedVoucherId = id;
            this.selectedPromoId = '';
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
