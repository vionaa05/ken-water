@extends('layouts.admin')

@section('title', 'Bagikan Voucher - Ken Water')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.loyalty.vouchers.index') }}" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium mb-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Daftar Voucher
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Bagikan Voucher ke Pelanggan</h1>
    <p class="text-sm text-gray-500 mt-1">Pilih pelanggan yang akan menerima voucher <span class="font-bold text-primary">{{ $voucher->name }}</span> ({{ $voucher->code }})</p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <form action="{{ route('admin.loyalty.vouchers.do-assign', $voucher) }}" method="POST">
        @csrf
        
        <div class="mb-4 flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-200">
            <div class="text-sm font-semibold text-gray-700">
                Pilih Pelanggan Target (Total: {{ $customers->count() }} pelanggan aktif)
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="toggleSelectAll(true)" class="text-xs text-primary font-bold hover:underline">Pilih Semua</button>
                <span class="text-gray-300">|</span>
                <button type="button" onclick="toggleSelectAll(false)" class="text-xs text-gray-500 font-medium hover:underline">Batal Semua</button>
            </div>
        </div>

        <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100 mb-6">
            @foreach($customers as $customer)
                <label class="flex items-center justify-between p-3 hover:bg-blue-50/50 cursor-pointer">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="user_ids[]" value="{{ $customer->id }}" class="customer-checkbox rounded text-primary focus:ring-primary">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $customer->name }}</p>
                            <p class="text-xs text-gray-500">{{ $customer->customer_code }} • {{ $customer->phone }}</p>
                        </div>
                    </div>
                    <div>
                        <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                            {{ ucfirst($customer->loyalty_tier) }}
                        </span>
                    </div>
                </label>
            @endforeach
        </div>

        @error('user_ids')
            <p class="text-xs text-red-600 mb-4">{{ $message }}</p>
        @enderror

        <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
            <a href="{{ route('admin.loyalty.vouchers.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg border border-gray-300">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold text-sm rounded-lg shadow-sm">
                Bagikan ke Pelanggan Terpilih
            </button>
        </div>
    </form>
</div>

<script>
    function toggleSelectAll(select) {
        document.querySelectorAll('.customer-checkbox').forEach(cb => cb.checked = select);
    }
</script>
@endsection
