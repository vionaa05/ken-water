<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Services\PointService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminApiController extends Controller
{
    public function __construct(private PointService $pointService) {}

    /**
     * Dashboard Ringkasan Admin
     */
    public function dashboard(Request $request)
    {
        $today = Carbon::today();

        // Transaksi hari ini
        $todayOrders = Order::whereDate('order_date', $today)->where('status', 'selesai');
        $todayRevenue = (float) $todayOrders->sum('total_price');
        $todayGallons = (int) $todayOrders->sum('gallon_qty');
        $todayCount = (int) $todayOrders->count();

        // Pelanggan
        $totalCustomers = User::where('role', 'pelanggan')->count();
        $pendingOrdersCount = Order::where('status', 'dipesan')->count();

        // Transaksi terbaru
        $recentOrders = Order::with('user:id,name,phone,customer_id')
            ->latest('order_date')
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->user ? $order->user->name : 'Anonim',
                    'customer_id' => $order->user ? $order->user->customer_id : '-',
                    'gallon_qty' => $order->gallon_qty,
                    'total_price' => (float) $order->total_price,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'time' => $order->order_date ? $order->order_date->format('H:i') : '-',
                ];
            });

        return response()->json([
            'success' => true,
            'summary' => [
                'today_revenue' => $todayRevenue,
                'today_gallons' => $todayGallons,
                'today_orders_count' => $todayCount,
                'total_customers' => $totalCustomers,
                'pending_orders_count' => $pendingOrdersCount,
            ],
            'recent_orders' => $recentOrders,
        ]);
    }

    /**
     * Cari Pelanggan via QR Code / Customer ID / No HP / Nama
     */
    public function searchCustomer(Request $request)
    {
        $query = trim($request->get('query', ''));

        if (!$query) {
            return response()->json([
                'success' => false,
                'message' => 'Kata kunci pencarian tidak boleh kosong.',
            ], 422);
        }

        // Cari exact match customer_id dulu (untuk Scan QR)
        $customer = User::where('role', 'pelanggan')
            ->where(function ($q) use ($query) {
                $q->where('customer_id', $query)
                  ->orWhere('phone', $query)
                  ->orWhere('id', $query);
            })->first();

        // Jika tidak ketemu exact, coba LIKE name/phone
        if (!$customer) {
            $customer = User::where('role', 'pelanggan')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('phone', 'like', "%{$query}%");
                })->first();
        }

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak ditemukan.',
            ], 404);
        }

        $progress = $this->pointService->getProgressToNextLevel($customer);

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'customer_id' => $customer->customer_id ?? 'KW-' . str_pad($customer->id, 5, '0', STR_PAD_LEFT),
                'name' => $customer->name,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'loyalty_level' => $customer->loyalty_level ?? 'bronze',
                'points' => (int) $customer->points,
                'status' => $customer->status,
                'progress' => $progress,
            ],
        ]);
    }

    /**
     * Kasir: Buat & Selesaikan Transaksi Langsung di Depo
     */
    public function createTransaction(Request $request)
    {
        $request->validate([
            'customer_id' => 'required', // Bisa ID user atau string customer_id
            'gallon_qty' => 'required|integer|min:1|max:100',
            'payment_method' => 'required|in:tunai,transfer,qris',
            'notes' => 'nullable|string|max:500',
        ]);

        $customer = User::where('role', 'pelanggan')
            ->where(function ($q) use ($request) {
                $q->where('id', $request->customer_id)
                  ->orWhere('customer_id', $request->customer_id);
            })->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak ditemukan.',
            ], 404);
        }

        $pricePerGallon = (float) (function_exists('setting') ? setting('price_per_gallon', 5000) : 5000);
        $qty = (int) $request->gallon_qty;
        $totalPrice = $qty * $pricePerGallon;

        return DB::transaction(function () use ($customer, $qty, $pricePerGallon, $totalPrice, $request) {
            $order = Order::create([
                'user_id' => $customer->id,
                'order_date' => now(),
                'gallon_qty' => $qty,
                'unit_price' => $pricePerGallon,
                'total_price' => $totalPrice,
                'delivery_method' => 'ambil_sendiri',
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'status' => 'selesai',
                'source' => 'kasir',
                'processed_by' => $request->user()->id,
                'completed_at' => now(),
                'period_start' => $customer->current_period_start ?? now(),
            ]);

            // Tambahkan poin ke pelanggan
            $addedPoints = $this->pointService->addPoints(
                $customer,
                $qty,
                "Pembelian {$qty} galon air di kasir depo",
                $order->id,
                'order'
            );

            $customer->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dicatat dan poin berhasil ditambahkan!',
                'order' => [
                    'id' => $order->id,
                    'customer_name' => $customer->name,
                    'gallon_qty' => $qty,
                    'total_price' => $totalPrice,
                    'payment_method' => $request->payment_method,
                    'added_points' => $addedPoints,
                    'customer_points_now' => (int) $customer->points,
                    'customer_level' => $customer->loyalty_level,
                    'time' => $order->order_date->format('d/m/Y H:i'),
                ],
            ]);
        });
    }

    /**
     * Riwayat Transaksi Depo
     */
    public function orders(Request $request)
    {
        $orders = Order::with('user:id,name,phone,customer_id')
            ->latest('order_date')
            ->take(30)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->user ? $order->user->name : 'Anonim',
                    'customer_id' => $order->user ? $order->user->customer_id : '-',
                    'gallon_qty' => $order->gallon_qty,
                    'total_price' => (float) $order->total_price,
                    'delivery_method' => $order->delivery_method,
                    'payment_method' => $order->payment_method,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'date' => $order->order_date ? $order->order_date->format('d M Y, H:i') : '-',
                ];
            });

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }
}
