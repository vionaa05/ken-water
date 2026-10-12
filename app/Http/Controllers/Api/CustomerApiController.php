<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PointService;
use Illuminate\Http\Request;

class CustomerApiController extends Controller
{
    public function __construct(private PointService $pointService) {}

    /**
     * Dapatkan data Kartu Member & Loyalitas
     */
    public function memberCard(Request $request)
    {
        $user = $request->user();
        $progress = $this->pointService->getProgressToNextLevel($user);

        return response()->json([
            'success' => true,
            'card' => [
                'name' => $user->name,
                'customer_id' => $user->customer_id ?? 'KW-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                'loyalty_level' => $user->loyalty_level ?? 'bronze',
                'points' => (int) $user->points,
                'status' => $user->status,
                'phone' => $user->phone,
                'registered_at' => $user->registered_at ? $user->registered_at->format('d M Y') : $user->created_at->format('d M Y'),
                'qr_code_data' => $user->customer_id ?? (string) $user->id,
                'progress' => $progress,
            ],
        ]);
    }

    /**
     * Dapatkan daftar riwayat transaksi pelanggan
     */
    public function orders(Request $request)
    {
        $user = $request->user();

        $orders = Order::where('user_id', $user->id)
            ->latest('order_date')
            ->take(30)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_date' => $order->order_date ? $order->order_date->format('d M Y, H:i') : $order->created_at->format('d M Y, H:i'),
                    'gallon_qty' => $order->gallon_qty,
                    'unit_price' => (float) $order->unit_price,
                    'total_price' => (float) $order->total_price,
                    'delivery_method' => $order->delivery_method,
                    'payment_method' => $order->payment_method,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'status_color' => $order->status_color,
                    'notes' => $order->notes,
                ];
            });

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }

    /**
     * Buat Pesanan Isi Ulang Baru dari Pelanggan
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'gallon_qty' => 'required|integer|min:1|max:50',
            'delivery_method' => 'required|in:antar,ambil_sendiri',
            'delivery_address' => 'required_if:delivery_method,antar|nullable|string',
            'payment_method' => 'required|in:tunai,transfer',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $pricePerGallon = (float) (function_exists('setting') ? setting('price_per_gallon', 5000) : 5000);
        $qty = (int) $request->gallon_qty;
        $totalPrice = $qty * $pricePerGallon;

        $order = Order::create([
            'user_id' => $user->id,
            'order_date' => now(),
            'gallon_qty' => $qty,
            'unit_price' => $pricePerGallon,
            'total_price' => $totalPrice,
            'delivery_method' => $request->delivery_method,
            'delivery_address' => $request->delivery_method === 'antar' ? ($request->delivery_address ?: $user->address) : null,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
            'status' => 'dipesan',
            'source' => 'online',
            'period_start' => $user->current_period_start ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat!',
            'order' => $order,
        ], 201);
    }
}
