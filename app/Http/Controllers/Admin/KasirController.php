<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\PointService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    public function __construct(private PointService $pointService) {}

    public function index()
    {
        $customers = User::customers()->active()
            ->select('id', 'name', 'phone', 'customer_id', 'loyalty_level', 'points', 'address')
            ->orderBy('name')
            ->get()
            ->toArray();

        $pricePerGallon = (float) (function_exists('setting') ? setting('price_per_gallon', 5000) : 5000);

        $todayOrders = Order::with('user:id,name')
            ->where('source', 'kasir')
            ->whereDate('order_date', Carbon::today())
            ->latest('order_date')
            ->get();

        return view('admin.kasir.index', compact('customers', 'pricePerGallon', 'todayOrders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'      => 'required|exists:users,id',
            'gallon_qty'       => 'required|integer|min:1|max:100',
            'payment_method'   => 'required|in:tunai,transfer,qris',
            'delivery_method'  => 'required|in:antar,ambil_sendiri',
            'delivery_address' => 'nullable|required_if:delivery_method,antar|string|max:500',
            'notes'            => 'nullable|string|max:500',
        ]);

        $customer = User::findOrFail($request->customer_id);

        if ($customer->status === 'tidak_aktif') {
            return back()->with('error', 'Pelanggan tidak aktif. Tidak bisa melakukan transaksi.');
        }

        $pricePerGallon = (float) (function_exists('setting') ? setting('price_per_gallon', 5000) : 5000);
        $qty = (int) $request->gallon_qty;
        $totalPrice = $qty * $pricePerGallon;

        DB::transaction(function () use ($customer, $qty, $pricePerGallon, $totalPrice, $request) {
            $order = Order::create([
                'user_id'          => $customer->id,
                'order_date'       => now(),
                'gallon_qty'       => $qty,
                'unit_price'       => $pricePerGallon,
                'total_price'      => $totalPrice,
                'delivery_method'  => $request->delivery_method,
                'delivery_address' => ($request->delivery_method === 'antar') ? $request->delivery_address : null,
                'payment_method'   => $request->payment_method,
                'notes'            => $request->notes,
                'status'           => 'selesai',
                'source'           => 'kasir',
                'processed_by'     => auth()->id(),
                'completed_at'     => now(),
                'period_start'     => $customer->current_period_start ?? now(),
            ]);

            $this->pointService->addPoints(
                $customer,
                $qty,
                "Pembelian {$qty} galon air di kasir depot",
                $order->id,
                'order'
            );
        });

        return back()->with('success', "✅ Transaksi {$qty} galon untuk {$customer->name} berhasil dicatat! Total: Rp " . number_format($totalPrice, 0, ',', '.'));
    }
}

