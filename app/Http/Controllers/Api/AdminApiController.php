<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Notification;
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
     * Riwayat Transaksi Depo & Pesanan Online
     */
    public function orders(Request $request)
    {
        $statusCounts = [
            'dipesan' => Order::where('source', 'online')->where('status', 'dipesan')->count(),
            'diproses' => Order::where('source', 'online')->where('status', 'diproses')->count(),
            'diantar' => Order::where('source', 'online')->where('status', 'diantar')->count(),
            'siap_diambil' => Order::where('source', 'online')->where('status', 'siap_diambil')->count(),
            'selesai' => Order::where('source', 'online')->where('status', 'selesai')->count(),
            'dibatalkan' => Order::where('source', 'online')->where('status', 'dibatalkan')->count(),
        ];

        $orders = Order::with('user:id,name,phone,customer_id,loyalty_level', 'processor:id,name')
            ->latest('order_date')
            ->take(60)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->user ? $order->user->name : 'Anonim',
                    'customer_id' => $order->user ? $order->user->customer_id : '-',
                    'customer_phone' => $order->user ? $order->user->phone : '-',
                    'customer_level' => $order->user ? $order->user->loyalty_level : 'bronze',
                    'gallon_qty' => $order->gallon_qty,
                    'total_price' => (float) $order->total_price,
                    'delivery_method' => $order->delivery_method,
                    'delivery_address' => $order->delivery_address,
                    'payment_method' => $order->payment_method,
                    'notes' => $order->notes,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'date' => $order->order_date ? $order->order_date->format('d M Y, H:i') : '-',
                    'source' => $order->source ?? 'kasir',
                    'processor_name' => $order->processor ? $order->processor->name : null,
                ];
            });

        return response()->json([
            'success' => true,
            'orders' => $orders,
            'status_counts' => $statusCounts,
        ]);
    }

    /**
     * Update Status Pesanan (Serupa Web Admin)
     */
    public function updateOrderStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|in:dipesan,diproses,diantar,siap_diambil,selesai,dibatalkan,pending,confirmed,delivered,cancelled',
        ]);

        $order = Order::with('user')->findOrFail($orderId);

        // Normalisasi status dari request
        $newStatus = match($request->status) {
            'pending' => 'dipesan',
            'confirmed' => 'diproses',
            'delivered', 'completed' => 'selesai',
            'cancelled' => 'dibatalkan',
            default => $request->status,
        };

        $updateData = [
            'status' => $newStatus,
            'processed_by' => $request->user()->id,
        ];

        if ($newStatus === 'selesai') {
            $updateData['completed_at'] = now();
        }

        $order->update($updateData);

        // Tambah poin saat pesanan selesai (hanya untuk pesanan online)
        if ($newStatus === 'selesai' && $order->source === 'online' && $order->user) {
            $this->pointService->addPoints(
                $order->user,
                $order->gallon_qty,
                "Pesanan #{$order->id} selesai: {$order->gallon_qty} galon",
                $order->id,
                'order'
            );
        }

        // Kirim notifikasi ke pelanggan
        $statusMessages = [
            'diproses' => ['Pesanan Sedang Diproses', 'Pesanan Anda sedang kami siapkan. Mohon tunggu sebentar.'],
            'diantar' => ['Pesanan Sedang Diantar', 'Pesanan Anda sedang dalam perjalanan menuju lokasi Anda.'],
            'siap_diambil' => ['Pesanan Siap Diambil', 'Pesanan Anda sudah siap diambil di depot Ken Water.'],
            'selesai' => ['Pesanan Selesai! 🎉', 'Pesanan Anda telah selesai. Terima kasih sudah berbelanja di Ken Water!'],
            'dibatalkan' => ['Pesanan Dibatalkan', 'Maaf, pesanan Anda telah dibatalkan. Hubungi kami untuk informasi lebih lanjut.'],
        ];

        if (isset($statusMessages[$newStatus]) && $order->user_id) {
            [$title, $body] = $statusMessages[$newStatus];
            Notification::create([
                'user_id' => $order->user_id,
                'title' => $title,
                'body' => $body,
                'type' => 'order_update',
                'reference_id' => $order->id,
                'reference_type' => 'order',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Status pesanan berhasil diperbarui menjadi {$order->fresh()->status_label}.",
            'order' => [
                'id' => $order->id,
                'status' => $order->status,
                'status_label' => $order->status_label,
            ],
        ]);
    }

    /**
     * Daftar Semua Pelanggan
     */
    public function customers(Request $request)
    {
        $search = $request->get('search', '');
        $customers = \App\Models\User::where('role', 'pelanggan')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%")
                       ->orWhere('customer_id', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'customer_id' => $c->customer_id,
                    'name' => $c->name,
                    'phone' => $c->phone,
                    'address' => $c->address,
                    'loyalty_level' => $c->loyalty_level,
                    'points' => (int) $c->points,
                    'status' => $c->status,
                    'registered_at' => $c->registered_at ? $c->registered_at->format('d M Y') : $c->created_at->format('d M Y'),
                ];
            });

        return response()->json(['success' => true, 'customers' => $customers]);
    }

    /**
     * Daftar Keluhan untuk Admin
     */
    public function complaints(Request $request)
    {
        try {
            $complaints = \App\Models\Complaint::with('user:id,name,phone,customer_id')
                ->latest()
                ->take(50)
                ->get()
                ->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'customer_name' => $c->user ? $c->user->name : '-',
                        'customer_id' => $c->user ? $c->user->customer_id : '-',
                        'subject' => $c->category ?? 'Keluhan',
                        'category' => $c->category ?? 'Umum',
                        'description' => $c->description,
                        'status' => $c->status,
                        'status_label' => match($c->status) {
                            'baru' => 'Menunggu',
                            'diproses' => 'Diproses',
                            'selesai' => 'Selesai',
                            default => ucfirst($c->status),
                        },
                        'admin_notes' => $c->response ?? $c->action_taken,
                        'response' => $c->response,
                        'created_at' => $c->created_at->format('d M Y, H:i'),
                    ];
                });

            return response()->json(['success' => true, 'complaints' => $complaints]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'complaints' => []]);
        }
    }

    /**
     * Update Status Keluhan
     */
    public function updateComplaintStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:baru,menunggu,diproses,in_progress,selesai,resolved,ditolak,rejected',
            'admin_notes' => 'nullable|string|max:1000',
            'response' => 'nullable|string|max:1000',
        ]);

        try {
            $complaint = \App\Models\Complaint::findOrFail($id);

            $status = match($request->status) {
                'baru', 'menunggu' => 'baru',
                'diproses', 'in_progress' => 'diproses',
                'selesai', 'resolved' => 'selesai',
                default => 'diproses',
            };

            $complaint->status = $status;
            $note = $request->admin_notes ?: $request->response;
            if ($note !== null) {
                $complaint->response = $note;
                $complaint->action_taken = $note;
            }
            if ($status === 'selesai') {
                $complaint->resolved_at = now();
                $complaint->handled_by = $request->user()->id;
            }
            $complaint->save();

            return response()->json([
                'success' => true,
                'message' => 'Status keluhan berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Keluhan tidak ditemukan.'], 404);
        }
    }

    /**
     * ==========================================
     * KATALOG REWARD (ADMIN)
     * ==========================================
     */
    public function rewards(Request $request)
    {
        try {
            $rewards = \App\Models\Reward::latest()->get()->map(function ($r) {
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'description' => $r->description,
                    'points_cost' => (int) $r->points_cost,
                    'stock' => $r->stock,
                    'is_active' => (bool) $r->is_active,
                ];
            });

            return response()->json(['success' => true, 'rewards' => $rewards]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'rewards' => []]);
        }
    }

    public function createReward(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points_cost' => 'required|integer|min:1',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $reward = \App\Models\Reward::create([
            'name' => $request->name,
            'description' => $request->description,
            'points_cost' => $request->points_cost,
            'stock' => $request->stock,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reward berhasil ditambahkan.',
            'reward' => $reward,
        ], 201);
    }

    public function updateReward(Request $request, $id)
    {
        $reward = \App\Models\Reward::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'points_cost' => 'sometimes|integer|min:1',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $reward->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Reward berhasil diperbarui.',
            'reward' => $reward,
        ]);
    }

    public function deleteReward($id)
    {
        $reward = \App\Models\Reward::findOrFail($id);
        $reward->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reward berhasil dihapus.',
        ]);
    }

    /**
     * ==========================================
     * VALIDASI PENUKARAN REWARD (ADMIN)
     * ==========================================
     */
    public function redemptions(Request $request)
    {
        try {
            $redemptions = \App\Models\RewardRedemption::with(['user:id,name,phone,customer_id', 'reward:id,name,points_cost'])
                ->latest()
                ->take(50)
                ->get()
                ->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'customer_name' => $r->user ? $r->user->name : '-',
                        'customer_id' => $r->user ? $r->user->customer_id : '-',
                        'customer_phone' => $r->user ? $r->user->phone : '-',
                        'reward_name' => $r->reward ? $r->reward->name : 'Reward',
                        'points_used' => (int) $r->points_used,
                        'redemption_code' => $r->redemption_code ?? '-',
                        'status' => $r->status,
                        'status_label' => $r->status_label ?? ucfirst($r->status),
                        'created_at' => $r->created_at ? $r->created_at->format('d M Y, H:i') : '-',
                    ];
                });

            return response()->json(['success' => true, 'redemptions' => $redemptions]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'redemptions' => []]);
        }
    }

    public function validateRedemption(Request $request, $id)
    {
        try {
            $redemption = \App\Models\RewardRedemption::with(['user', 'reward'])->findOrFail($id);

            if ($redemption->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya penukaran dengan status pending yang bisa divalidasi.',
                ], 422);
            }

            $redemption->update([
                'status' => 'validated',
                'validated_by' => $request->user()->id,
                'validated_at' => now(),
            ]);

            // Kurangi stok reward jika stok terbatas
            if ($redemption->reward && $redemption->reward->stock !== null) {
                $redemption->reward->decrement('stock');
            }

            if ($redemption->user_id) {
                Notification::create([
                    'user_id' => $redemption->user_id,
                    'title' => 'Penukaran Reward Berhasil',
                    'body' => "Penukaran {$redemption->reward->name} Anda telah divalidasi oleh petugas depot.",
                    'type' => 'points_update',
                    'reference_id' => $redemption->id,
                    'reference_type' => 'reward_redemption',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Penukaran reward '{$redemption->reward->name}' berhasil divalidasi.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Penukaran tidak ditemukan.'], 404);
        }
    }

    public function cancelRedemption(Request $request, $id)
    {
        try {
            $redemption = \App\Models\RewardRedemption::with(['user', 'reward'])->findOrFail($id);

            if ($redemption->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya penukaran dengan status pending yang bisa dibatalkan.',
                ], 422);
            }

            // Kembalikan poin menggunakan PointService
            if ($redemption->user) {
                $this->pointService->addPoints(
                    $redemption->user,
                    $redemption->points_used,
                    "Pengembalian poin dari pembatalan penukaran reward: {$redemption->reward->name}",
                    $redemption->id,
                    'reward_redemption'
                );
            }

            $redemption->update([
                'status' => 'cancelled',
                'validated_by' => $request->user()->id,
                'validated_at' => now(),
            ]);

            if ($redemption->user_id) {
                Notification::create([
                    'user_id' => $redemption->user_id,
                    'title' => 'Penukaran Reward Dibatalkan',
                    'body' => "Penukaran {$redemption->reward->name} dibatalkan dan {$redemption->points_used} poin telah dikembalikan ke akun Anda.",
                    'type' => 'points_update',
                    'reference_id' => $redemption->id,
                    'reference_type' => 'reward_redemption',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Penukaran berhasil dibatalkan dan {$redemption->points_used} poin telah dikembalikan ke pelanggan.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Penukaran tidak ditemukan.'], 404);
        }
    }
}
