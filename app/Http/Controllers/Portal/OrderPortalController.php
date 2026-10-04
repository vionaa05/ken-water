<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\UserVoucher;
use App\Models\PersonalPromo;
use Illuminate\Http\Request;

class OrderPortalController extends Controller
{
    public function create()
    {
        $user = auth()->user();
        
        // Ambil voucher aktif
        $vouchers = $user->userVouchers()
            ->where('status', 'aktif')
            ->with('voucher')
            ->get();
            
        // Ambil promo aktif
        $promos = $user->personalPromos()
            ->where('is_used', false)
            ->where('is_expired', false)
            ->get();
            
        $pricePerGallon = (float) setting('price_per_gallon', 5000);
        
        return view('portal.orders.create', compact('user', 'vouchers', 'promos', 'pricePerGallon'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'gallon_qty' => 'required|integer|min:1|max:50',
            'delivery_method' => 'required|in:antar,ambil_sendiri',
            'delivery_address' => 'required_if:delivery_method,antar|nullable|string',
            'payment_method' => 'required|in:tunai,transfer',
            'notes' => 'nullable|string|max:500',
            'voucher_id' => 'nullable|exists:user_vouchers,id',
            'promo_id' => 'nullable|exists:personal_promos,id',
        ]);

        $user = auth()->user();
        $pricePerGallon = (float) setting('price_per_gallon', 5000);
        $gallons = (int) $request->gallon_qty;
        $subtotal = $gallons * $pricePerGallon;
        $discount = 0;
        
        // Tidak bisa pakai voucher dan promo bersamaan
        if ($request->voucher_id && $request->promo_id) {
            return back()->withInput()->with('error', 'Hanya bisa menggunakan 1 jenis diskon (Voucher ATAU Promo).');
        }

        $userVoucher = null;
        if ($request->voucher_id) {
            $userVoucher = UserVoucher::where('id', $request->voucher_id)
                ->where('user_id', $user->id)
                ->where('status', 'aktif')
                ->with('voucher')
                ->first();
                
            if (!$userVoucher || !$userVoucher->voucher->isValid() || 
               ($userVoucher->voucher->min_purchase && $subtotal < $userVoucher->voucher->min_purchase)) {
                return back()->withInput()->with('error', 'Voucher tidak valid atau syarat minimal belanja tidak terpenuhi.');
            }
            
            $discount = $userVoucher->voucher->calculateDiscount($subtotal);
        }

        $promo = null;
        if ($request->promo_id) {
            $promo = PersonalPromo::where('id', $request->promo_id)
                ->where('user_id', $user->id)
                ->where('is_used', false)
                ->first();
                
            if (!$promo || !$promo->isActive()) {
                return back()->withInput()->with('error', 'Promo tidak valid atau sudah kadaluarsa.');
            }
            
            if ($promo->discount_type === 'nominal') {
                $discount = min($promo->discount_value, $subtotal);
            } else {
                $discount = round($subtotal * ($promo->discount_value / 100));
            }
        }

        $totalPrice = max(0, $subtotal - $discount);

        $order = Order::create([
            'user_id' => $user->id,
            'order_date' => now(),
            'gallon_qty' => $gallons,
            'unit_price' => $pricePerGallon,
            'total_price' => $totalPrice,
            'delivery_method' => $request->delivery_method,
            'delivery_address' => $request->delivery_method === 'antar' ? $request->delivery_address : null,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
            'status' => 'dipesan',
            'source' => 'online',
            'period_start' => $user->current_period_start,
        ]);

        // Tandai voucher dipakai
        if ($userVoucher) {
            $userVoucher->update([
                'status' => 'terpakai',
                'used_at' => now(),
                'order_id' => $order->id,
            ]);
            
            // Update jumlah pemakaian master voucher
            $userVoucher->voucher->increment('used_count');
        }

        // Tandai promo dipakai
        if ($promo) {
            $promo->update(['is_used' => true]);
        }

        return redirect()->route('portal.orders.show', $order)
            ->with('success', 'Pesanan berhasil dibuat! Tim kami akan segera memprosesnya.');
    }

    public function index()
    {
        $user = auth()->user();
        
        $orders = $user->orders()
            ->when($user->current_period_start, fn($q, $start) => $q->where('period_start', '>=', $start))
            ->latest('order_date')
            ->paginate(10);
            
        return view('portal.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }
        
        return view('portal.orders.show', compact('order'));
    }

    public function transactions()
    {
        $user = auth()->user();
        
        $orders = $user->orders()
            ->when($user->current_period_start, fn($q, $start) => $q->where('period_start', '>=', $start))
            ->where('status', 'selesai')
            ->latest('completed_at')
            ->paginate(15);
            
        return view('portal.transactions', compact('orders'));
    }
}
