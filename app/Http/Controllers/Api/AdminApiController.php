<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Notification;
use App\Models\Voucher;
use App\Models\UserVoucher;
use App\Models\Setting;
use App\Models\Campaign;
use App\Models\PersonalPromo;
use App\Services\PointService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->take(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_date' => $order->order_date ? $order->order_date->format('d M Y, H:i') : '-',
                    'date' => $order->order_date ? $order->order_date->format('d M Y, H:i') : '-',
                    'customer_name' => $order->user ? $order->user->name : 'Anonim',
                    'customer_id' => $order->user ? $order->user->customer_id : '-',
                    'customer_phone' => $order->user ? $order->user->phone : '-',
                    'gallon_qty' => $order->gallon_qty,
                    'total_price' => (float) $order->total_price,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'status_color' => $order->status_color ?? '#10B981',
                    'source' => $order->source ?? 'kasir',
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
                  ->orWhere('customer_id', $request->customer_id)
                  ->orWhere('phone', $request->customer_id);
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
                "Pembelian {$qty} galon air di kasir depot",
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
            'dipesan' => Order::where('status', 'dipesan')->count(),
            'diproses' => Order::where('status', 'diproses')->count(),
            'diantar' => Order::where('status', 'diantar')->count(),
            'siap_diambil' => Order::where('status', 'siap_diambil')->count(),
            'selesai' => Order::where('status', 'selesai')->count(),
            'dibatalkan' => Order::where('status', 'dibatalkan')->count(),
            'kasir' => Order::where('source', 'kasir')->count(),
            'online' => Order::where('source', 'online')->count(),
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
        $status = $request->get('status', '');
        $level = $request->get('level', '');

        $customers = \App\Models\User::where('role', 'pelanggan')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%")
                       ->orWhere('customer_id', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($level, function ($q) use ($level) {
                $q->where('loyalty_level', $level);
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
                    'total_spent' => (int) ($c->total_spent ?? 0),
                    'email' => $c->email ?? '-',
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
            $note = $request->input('admin_notes') ?? $request->input('response');
            if ($note !== null && trim($note) !== '') {
                $complaint->response = trim($note);
                $complaint->action_taken = trim($note);
            }
            $complaint->handled_by = $request->user()->id;
            if ($status === 'selesai') {
                $complaint->resolved_at = now();
            }
            $complaint->save();

            // Kirim notifikasi ke pelanggan jika ada tanggapan
            if ($complaint->user_id && !empty($complaint->response)) {
                \App\Models\Notification::create([
                    'user_id' => $complaint->user_id,
                    'title' => $status === 'selesai' ? 'Keluhan Selesai Ditanggapi' : 'Keluhan Sedang Ditangani',
                    'body' => "Keluhan \"{$complaint->category}\": {$complaint->response}",
                    'type' => 'complaint_update',
                    'reference_id' => $complaint->id,
                    'reference_type' => 'complaint',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tindak lanjut dan status keluhan berhasil dicatat.',
                'complaint' => [
                    'id' => $complaint->id,
                    'status' => $complaint->status,
                    'admin_notes' => $complaint->response ?? $complaint->action_taken,
                    'response' => $complaint->response,
                ],
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

    /**
     * Segmentasi Pelanggan (Serupa Web Admin SegmentationController)
     */
    public function segmentation(Request $request)
    {
        try {
            $customers = User::customers()
                ->with('completedOrders')
                ->get()
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'customer_id' => $customer->customer_id ?? 'KW-' . str_pad($customer->id, 5, '0', STR_PAD_LEFT),
                        'name' => $customer->name,
                        'phone' => $customer->phone,
                        'status' => $customer->status,
                        'loyalty_level' => $customer->loyalty_level ?? 'bronze',
                        'points' => (int) $customer->points,
                        'segment' => $customer->segment,
                        'customer_value' => $customer->customer_value,
                        'order_count' => (int) $customer->order_count_current_period,
                        'total_spend' => (float) $customer->total_spend_current_period,
                        'days_since_last_order' => $customer->getDaysSinceLastOrder(),
                        'registered_at' => $customer->registered_at ? $customer->registered_at->format('d M Y') : '-',
                    ];
                });

            $segmentCounts = $customers->groupBy('segment')->map(fn($group) => $group->count());
            $levelCounts = $customers->groupBy('loyalty_level')->map(fn($group) => $group->count());
            $valueCounts = $customers->groupBy('customer_value')->map(fn($group) => $group->count());

            $chartSegmentData = [
                ['label' => 'Baru', 'count' => $segmentCounts->get('Baru', 0), 'color' => '#3b82f6'],
                ['label' => 'Reguler', 'count' => $segmentCounts->get('Reguler', 0), 'color' => '#10b981'],
                ['label' => 'Sering Beli', 'count' => $segmentCounts->get('Sering Beli', 0), 'color' => '#f59e0b'],
                ['label' => 'Hampir Hilang', 'count' => $segmentCounts->get('Hampir Hilang', 0), 'color' => '#ef4444'],
            ];

            $chartLevelData = [
                ['label' => 'Bronze', 'count' => $levelCounts->get('bronze', 0), 'color' => '#CD7F32'],
                ['label' => 'Silver', 'count' => $levelCounts->get('silver', 0), 'color' => '#94A3B8'],
                ['label' => 'Gold', 'count' => $levelCounts->get('gold', 0), 'color' => '#F59E0B'],
            ];

            return response()->json([
                'success' => true,
                'customers' => $customers,
                'chart_segment_data' => $chartSegmentData,
                'chart_level_data' => $chartLevelData,
                'summary' => [
                    'total_customers' => $customers->count(),
                    'segment_counts' => $segmentCounts,
                    'level_counts' => $levelCounts,
                    'value_counts' => $valueCounts,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat segmentasi: ' . $e->getMessage(),
                'customers' => [],
            ], 500);
        }
    }

    /**
     * Tambah Pelanggan Baru dari Admin Mobile
     */
    public function createCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:users,phone',
            'address' => 'required|string|max:255',
            'birth_date' => 'nullable|date|before:today',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $now = Carbon::now();
        $customer = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'birth_date' => $request->birth_date,
            'password' => Hash::make($request->password),
            'role' => 'pelanggan',
            'customer_id' => User::generateCustomerId(),
            'status' => 'aktif',
            'loyalty_level' => 'bronze',
            'points' => 0,
            'current_period_start' => $now,
            'registered_at' => $now,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil ditambahkan.',
            'customer' => [
                'id' => $customer->id,
                'customer_id' => $customer->customer_id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'loyalty_level' => $customer->loyalty_level,
                'points' => 0,
                'status' => $customer->status,
            ],
        ], 201);
    }

    /**
     * Update Data Pelanggan dari Admin Mobile
     */
    public function updateCustomer(Request $request, $id)
    {
        $customer = User::where('role', 'pelanggan')->find($id);
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Pelanggan tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:users,phone,' . $customer->id,
            'address' => 'required|string|max:255',
            'birth_date' => 'nullable|date|before:today',
            'status' => 'nullable|in:aktif,tidak_aktif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $customer->name = $request->name;
        $customer->phone = $request->phone;
        $customer->address = $request->address;
        if ($request->has('birth_date')) $customer->birth_date = $request->birth_date;
        if ($request->has('status')) $customer->status = $request->status;
        if ($request->filled('password')) $customer->password = Hash::make($request->password);
        $customer->save();

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil diperbarui.',
            'customer' => [
                'id' => $customer->id,
                'customer_id' => $customer->customer_id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'status' => $customer->status,
                'loyalty_level' => $customer->loyalty_level,
                'points' => (int) $customer->points,
            ],
        ]);
    }

    /**
     * Hapus Pelanggan dari Admin Mobile
     */
    public function deleteCustomer(Request $request, $id)
    {
        $customer = User::where('role', 'pelanggan')->find($id);
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Pelanggan tidak ditemukan.'], 404);
        }

        $customer->delete();
        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil dihapus.',
        ]);
    }

    /**
     * Dapatkan Pengaturan Sistem
     */
    public function settings(Request $request)
    {
        $settings = Setting::all()->pluck('value', 'key');
        return response()->json([
            'success' => true,
            'settings' => [
                'price_per_gallon' => (float) ($settings['price_per_gallon'] ?? 5000),
                'points_per_gallon' => (int) ($settings['points_per_gallon'] ?? 1),
                'inactive_days' => (int) ($settings['inactive_days'] ?? 30),
                'silver_threshold' => (int) ($settings['silver_threshold'] ?? 10),
                'gold_threshold' => (int) ($settings['gold_threshold'] ?? 30),
            ],
        ]);
    }

    /**
     * Simpan Pengaturan Sistem
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'price_per_gallon' => 'required|numeric|min:0',
            'points_per_gallon' => 'required|integer|min:0',
            'inactive_days' => 'required|integer|min:1',
            'silver_threshold' => 'required|integer|min:1',
            'gold_threshold' => 'required|integer|gt:silver_threshold',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $items = [
            'price_per_gallon' => $request->price_per_gallon,
            'points_per_gallon' => $request->points_per_gallon,
            'inactive_days' => $request->inactive_days,
            'silver_threshold' => $request->silver_threshold,
            'gold_threshold' => $request->gold_threshold,
        ];

        foreach ($items as $k => $v) {
            Setting::where('key', $k)->update(['value' => $v]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan sistem berhasil diperbarui.',
            'settings' => $items,
        ]);
    }

    /**
     * Daftar Voucher (Admin)
     */
    public function vouchers(Request $request)
    {
        $vouchers = Voucher::latest()->get()->map(function ($v) {
            return [
                'id' => $v->id,
                'code' => $v->code,
                'name' => $v->name,
                'description' => $v->description,
                'discount_type' => $v->discount_type,
                'discount_value' => (float) $v->discount_value,
                'min_purchase' => (float) ($v->min_purchase ?? 0),
                'valid_from' => $v->valid_from ? $v->valid_from->format('Y-m-d') : null,
                'valid_until' => $v->valid_until ? $v->valid_until->format('Y-m-d') : null,
                'usage_type' => $v->usage_type,
                'is_active' => (bool) $v->is_active,
            ];
        });

        return response()->json([
            'success' => true,
            'vouchers' => $vouchers,
        ]);
    }

    /**
     * Tambah Voucher Baru (Admin)
     */
    public function createVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:vouchers,code|max:30',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:nominal,persen',
            'discount_value' => 'required|numeric|min:1',
            'min_purchase' => 'nullable|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'usage_type' => 'required|in:sekali,banyak',
            'max_usage' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $voucher = Voucher::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil ditambahkan.',
            'voucher' => $voucher,
        ], 201);
    }

    /**
     * Assign / Bagikan Voucher ke Pelanggan (Admin)
     */
    public function assignVoucher(Request $request, $id)
    {
        $voucher = Voucher::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $users = User::whereIn('id', $request->user_ids)->get();
        $assigned = 0;

        foreach ($users as $user) {
            $exists = UserVoucher::where('user_id', $user->id)
                ->where('voucher_id', $voucher->id)
                ->where('status', 'aktif')
                ->exists();

            if (!$exists) {
                UserVoucher::create([
                    'user_id' => $user->id,
                    'voucher_id' => $voucher->id,
                    'period_start' => $user->current_period_start,
                ]);
                $assigned++;

                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Voucher Baru!',
                    'body' => "Anda mendapatkan voucher {$voucher->name}. Cek menu Voucher Saya.",
                    'type' => 'voucher',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Voucher berhasil dibagikan ke {$assigned} pelanggan.",
            'assigned_count' => $assigned,
        ]);
    }

    /**
     * Daftar Promo Personal (Admin)
     */
    public function promos(Request $request)
    {
        $promos = PersonalPromo::with('user:id,name,phone,customer_id')
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'customer_name' => $p->user ? $p->user->name : '-',
                    'customer_id' => $p->user ? $p->user->customer_id : '-',
                    'name' => $p->name,
                    'description' => $p->description,
                    'discount_type' => $p->discount_type,
                    'discount_value' => (float) $p->discount_value,
                    'valid_from' => $p->valid_from ? $p->valid_from->format('Y-m-d') : null,
                    'valid_until' => $p->valid_until ? $p->valid_until->format('Y-m-d') : null,
                    'is_used' => (bool) $p->is_used,
                    'is_expired' => (bool) $p->is_expired,
                    'is_active' => (bool) $p->isActive(),
                ];
            });

        return response()->json(['success' => true, 'promos' => $promos]);
    }

    /**
     * Toggle / Aktifkan Promo Personal (Admin)
     */
    public function togglePromo(Request $request, $id)
    {
        $promo = PersonalPromo::findOrFail($id);

        // Jika promo expired atau lewat masa berlaku, perpanjang dan aktifkan kembali
        if ($promo->is_expired || ($promo->valid_until && now()->gt($promo->valid_until->endOfDay()))) {
            $promo->is_expired = false;
            $promo->is_used = false;
            $promo->valid_from = now()->startOfDay();
            $promo->valid_until = now()->addDays(7)->endOfDay();
        } else {
            // Toggle status expired
            $promo->is_expired = !$promo->is_expired;
        }

        $promo->save();

        return response()->json([
            'success' => true,
            'message' => $promo->isActive() ? 'Promo personal berhasil diaktifkan!' : 'Promo personal dinonaktifkan.',
            'is_active' => $promo->isActive(),
            'promo' => [
                'id' => $promo->id,
                'is_active' => $promo->isActive(),
                'is_expired' => (bool) $promo->is_expired,
                'valid_until' => $promo->valid_until ? $promo->valid_until->format('Y-m-d') : null,
            ],
        ]);
    }

    /**
     * Tambah Promo Personal (Admin)
     */
    public function createPromo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:nominal,persen',
            'discount_value' => 'required|numeric|min:1',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::findOrFail($request->user_id);
        $promo = PersonalPromo::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'is_used' => false,
            'is_expired' => false,
            'period_start' => $user->current_period_start ?? now(),
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Promo Spesial Untuk Anda!',
            'body' => "Anda mendapat promo: {$promo->name}. Cek beranda Anda.",
            'type' => 'promo',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Promo personal berhasil dibuat dan dikirim ke pelanggan.',
            'promo' => $promo,
        ], 201);
    }

    /**
     * Daftar Kampanye WhatsApp CRM (Admin)
     */
    public function campaigns(Request $request)
    {
        $campaigns = Campaign::with(['creator:id,name', 'voucher:id,name,code'])
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'type' => $c->type,
                    'type_label' => match($c->type) {
                        'promo_baru' => 'Promo Pelanggan Baru',
                        'promo_loyal' => 'Promo Member Loyal',
                        'ulang_tahun' => 'Ucapan Ulang Tahun',
                        'ajakan_kembali' => 'Reaktivasi / Churn',
                        default => ucfirst($c->type),
                    },
                    'target_segment' => $c->target_segment,
                    'period_start' => $c->period_start ? $c->period_start->format('d M Y') : null,
                    'period_end' => $c->period_end ? $c->period_end->format('d M Y') : null,
                    'message_template' => $c->message_template,
                    'target_count' => (int) $c->target_count,
                    'sent_count' => (int) ($c->sent_count ?? 0),
                    'voucher_name' => $c->voucher ? $c->voucher->name : null,
                    'created_by' => $c->creator ? $c->creator->name : '-',
                    'created_at' => $c->created_at->format('d M Y, H:i'),
                ];
            });

        return response()->json(['success' => true, 'campaigns' => $campaigns]);
    }

    /**
     * Buat Kampanye WhatsApp Baru (Admin)
     */
    public function createCampaign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:promo_baru,promo_loyal,ulang_tahun,ajakan_kembali',
            'target_segment' => 'required|array',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'message_template' => 'required|string',
            'voucher_id' => 'nullable|exists:vouchers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $segments = $request->target_segment;
        $type = $request->type;

        // Hitung perkiraan target
        $query = User::customers();
        if (in_array('semua', $segments)) {
            // all
        } elseif (in_array('tidak_aktif', $segments) || $type === 'ajakan_kembali') {
            $query->inactive();
        } else {
            $query->active();
            if (in_array('loyal', $segments) || $type === 'promo_loyal') {
                $query->whereIn('loyalty_level', ['silver', 'gold']);
            }
            if ($type === 'ulang_tahun') {
                $query->whereMonth('birth_date', now()->month);
            }
        }
        $targetCount = $query->count();

        $campaign = Campaign::create([
            'name' => $request->name,
            'type' => $request->type,
            'target_segment' => $request->target_segment,
            'period_start' => $request->period_start,
            'period_end' => $request->period_end,
            'message_template' => $request->message_template,
            'voucher_id' => $request->voucher_id,
            'created_by' => $request->user()->id,
            'target_count' => $targetCount,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Kampanye berhasil dibuat dengan target {$targetCount} pelanggan.",
            'campaign' => $campaign,
        ], 201);
    }

    /**
     * Detail Kampanye WhatsApp beserta Target Pelanggan & Pesan Siap Kirim
     */
    public function campaignDetail(Request $request, $id)
    {
        try {
            $campaign = Campaign::with(['creator:id,name', 'voucher:id,name,code'])->findOrFail($id);

            // Ambil target pelanggan sesuai segmen
            $segments = $campaign->target_segment ?? ['semua'];
            $type = $campaign->type;

            $query = User::customers();
            if (in_array('semua', $segments)) {
                // all customers
            } elseif (in_array('tidak_aktif', $segments) || $type === 'ajakan_kembali') {
                $query->inactive();
            } else {
                $query->active();
                if (in_array('loyal', $segments) || $type === 'promo_loyal') {
                    $query->whereIn('loyalty_level', ['silver', 'gold']);
                }
                if ($type === 'ulang_tahun') {
                    $query->whereMonth('birth_date', now()->month);
                }
            }

            $customers = $query->orderBy('name')->get();
            $portalUrl = url('/portal');
            $voucherCode = optional($campaign->voucher)->code ?? '';

            $targetCustomers = $customers->map(function ($c) use ($campaign, $voucherCode, $portalUrl) {
                $personalizedMessage = str_replace(
                    ['{nama}', '{kode_voucher}', '{link_portal}'],
                    [$c->name, $voucherCode, $portalUrl],
                    $campaign->message_template
                );

                $cleanPhone = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $c->phone ?? ''));
                $waUrl = 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($personalizedMessage);

                return [
                    'id' => $c->id,
                    'customer_id' => $c->customer_id ?? 'KW-' . str_pad($c->id, 5, '0', STR_PAD_LEFT),
                    'name' => $c->name,
                    'phone' => $c->phone,
                    'clean_phone' => $cleanPhone,
                    'loyalty_level' => $c->loyalty_level ?? 'bronze',
                    'status' => $c->status,
                    'message' => $personalizedMessage,
                    'wa_url' => $waUrl,
                ];
            });

            return response()->json([
                'success' => true,
                'campaign' => [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'type' => $campaign->type,
                    'type_label' => match($campaign->type) {
                        'promo_baru' => 'Promo Pelanggan Baru',
                        'promo_loyal' => 'Promo Member Loyal',
                        'ulang_tahun' => 'Ucapan Ulang Tahun',
                        'ajakan_kembali' => 'Reaktivasi / Churn',
                        default => ucfirst($campaign->type),
                    },
                    'period_start' => $campaign->period_start ? $campaign->period_start->format('d M Y') : '-',
                    'period_end' => $campaign->period_end ? $campaign->period_end->format('d M Y') : '-',
                    'message_template' => $campaign->message_template,
                    'target_count' => $targetCustomers->count(),
                    'sent_count' => (int) ($campaign->sent_count ?? 0),
                    'voucher' => $campaign->voucher ? [
                        'id' => $campaign->voucher->id,
                        'name' => $campaign->voucher->name,
                        'code' => $campaign->voucher->code,
                    ] : null,
                    'created_by' => $campaign->creator ? $campaign->creator->name : '-',
                    'created_at' => $campaign->created_at->format('d M Y, H:i'),
                ],
                'target_customers' => $targetCustomers,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Kampanye tidak ditemukan: ' . $e->getMessage()], 404);
        }
    }

    /**
     * Tandai Pesan Kampanye Terkirim (+1 counter)
     */
    public function markCampaignSent(Request $request, $id)
    {
        try {
            $campaign = Campaign::findOrFail($id);
            $campaign->increment('sent_count');
            return response()->json([
                'success' => true,
                'sent_count' => $campaign->sent_count,
                'message' => 'Status pengiriman tercatat.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mencatat pengiriman.'], 404);
        }
    }

    /**
     * Hapus Kampanye WhatsApp (Admin)
     */
    public function deleteCampaign(Request $request, $id)
    {
        $campaign = Campaign::find($id);
        if (!$campaign) {
            return response()->json(['success' => false, 'message' => 'Kampanye tidak ditemukan.'], 404);
        }

        $campaign->delete();
        return response()->json(['success' => true, 'message' => 'Kampanye berhasil dihapus.']);
    }

    /**
     * Laporan Penjualan Admin (Sesuai Fitur Web: Filter Bulan & Tahun, Ringkasan, Daftar Transaksi)
     */
    public function reports(Request $request)
    {
        try {
            $month = (int) $request->get('month', now()->month);
            $year  = (int) $request->get('year',  now()->year);
            $month = max(1, min(12, $month));

            $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
            $periodEnd   = $periodStart->copy()->endOfMonth();
            $periodLabel = $periodStart->locale('id')->isoFormat('MMMM YYYY');

            // Periode bulan lalu untuk perbandingan
            $lastPeriodStart = $periodStart->copy()->subMonth()->startOfMonth();
            $lastPeriodEnd   = $lastPeriodStart->copy()->endOfMonth();

            // Query pesanan selesai pada periode yang dipilih
            $orders = Order::with('user')
                ->where('status', 'selesai')
                ->where(function ($q) use ($periodStart, $periodEnd) {
                    $q->whereBetween('completed_at', [$periodStart, $periodEnd])
                      ->orWhere(function ($sub) use ($periodStart, $periodEnd) {
                          $sub->whereNull('completed_at')
                              ->whereBetween('order_date', [$periodStart, $periodEnd]);
                      });
                })
                ->orderByDesc('completed_at')
                ->orderByDesc('order_date')
                ->get();

            $totalRevenue = (float) $orders->sum('total_price');
            $totalGallons = (int)   $orders->sum('gallon_qty');
            $totalOrders  = (int)   $orders->count();
            $onlineCount  = (int)   $orders->where('source', 'online')->count();
            $kasirCount   = (int)   $orders->filter(fn($o) => in_array($o->source, ['kasir', 'langsung']))->count();
            $avgOrder     = $totalOrders > 0 ? round($totalRevenue / $totalOrders) : 0;

            // Bulan sebelumnya untuk perbandingan
            $lastOrders = Order::where('status', 'selesai')
                ->where(function ($q) use ($lastPeriodStart, $lastPeriodEnd) {
                    $q->whereBetween('completed_at', [$lastPeriodStart, $lastPeriodEnd])
                      ->orWhere(function ($sub) use ($lastPeriodStart, $lastPeriodEnd) {
                          $sub->whereNull('completed_at')
                              ->whereBetween('order_date', [$lastPeriodStart, $lastPeriodEnd]);
                      });
                })->get();

            $lastRevenue = (float) $lastOrders->sum('total_price');
            $lastGallons = (int)   $lastOrders->sum('gallon_qty');
            $lastOrdersCount = (int) $lastOrders->count();

            // Daftar tahun yang ada transaksi
            $earliestYear = (int) (Order::whereNotNull('completed_at')->min(DB::raw('YEAR(completed_at)'))
                ?: Order::min(DB::raw('YEAR(order_date)'))
                ?: (now()->year - 2));
            $startYear = min($earliestYear, now()->year - 2);
            $years = [];
            for ($y = now()->year; $y >= $startYear; $y--) {
                $years[] = $y;
            }

            // Daftar pesanan diformat
            $orderList = $orders->map(function ($o) {
                $completedDate = $o->completed_at ?? $o->order_date;
                return [
                    'id' => $o->id,
                    'order_number' => 'ORD-' . str_pad($o->id, 5, '0', STR_PAD_LEFT),
                    'date' => $completedDate ? $completedDate->format('d M Y, H:i') : '-',
                    'customer_name' => $o->user ? $o->user->name : 'Pelanggan Langsung (Kasir)',
                    'customer_phone' => $o->user ? $o->user->phone : null,
                    'customer_id' => $o->user ? ($o->user->customer_id ?? 'KW-' . str_pad($o->user->id, 5, '0', STR_PAD_LEFT)) : '-',
                    'gallon_qty' => (int) $o->gallon_qty,
                    'unit_price' => (float) $o->unit_price,
                    'total_price' => (float) $o->total_price,
                    'source' => $o->source ?? 'online',
                    'source_label' => in_array($o->source, ['kasir', 'langsung']) ? 'Kasir Depot' : 'Aplikasi Online',
                    'payment_method' => $o->payment_method ?? 'tunai',
                    'delivery_method' => $o->delivery_method ?? 'ambil_sendiri',
                ];
            });

            // Top pelanggan di periode ini
            $topCustomers = $orders->whereNotNull('user_id')
                ->groupBy('user_id')
                ->map(function ($group) {
                    $first = $group->first();
                    return [
                        'name' => $first->user ? $first->user->name : 'Pelanggan',
                        'customer_id' => $first->user ? ($first->user->customer_id ?? '-') : '-',
                        'level' => $first->user ? ($first->user->loyalty_level ?? 'bronze') : 'bronze',
                        'total_spent' => (float) $group->sum('total_price'),
                        'total_gallons' => (int) $group->sum('gallon_qty'),
                        'order_count' => (int) $group->count(),
                    ];
                })
                ->sortByDesc('total_spent')
                ->take(5)
                ->values();

            return response()->json([
                'success' => true,
                'period_label' => $periodLabel,
                'month' => $month,
                'year' => $year,
                'years' => $years,
                'summary' => [
                    'total_revenue' => $totalRevenue,
                    'last_revenue' => $lastRevenue,
                    'total_gallons' => $totalGallons,
                    'last_gallons' => $lastGallons,
                    'total_orders' => $totalOrders,
                    'last_orders' => $lastOrdersCount,
                    'online_orders' => $onlineCount,
                    'kasir_orders' => $kasirCount,
                    'avg_order' => $avgOrder,
                ],
                'orders' => $orderList,
                'top_customers' => $topCustomers,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memuat laporan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hapus Voucher (Admin)
     */
    public function deleteVoucher(Request $request, $id)
    {
        $voucher = \App\Models\Voucher::find($id);
        if (!$voucher) {
            return response()->json(['success' => false, 'message' => 'Voucher tidak ditemukan.'], 404);
        }
        $voucher->delete();
        return response()->json(['success' => true, 'message' => 'Voucher berhasil dihapus.']);
    }

    /**
     * Hapus Promo Personal (Admin)
     */
    public function deletePromo(Request $request, $id)
    {
        $promo = PersonalPromo::find($id);
        if (!$promo) {
            return response()->json(['success' => false, 'message' => 'Promo tidak ditemukan.'], 404);
        }
        $promo->delete();
        return response()->json(['success' => true, 'message' => 'Promo berhasil dihapus.']);
    }
}

