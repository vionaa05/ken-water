<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\UserVoucher;
use App\Models\Complaint;
use App\Models\Notification;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
                'email' => $user->email,
                'address' => $user->address,
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
            ->take(50)
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
                    'status_color' => $order->status_color ?? '#10B981',
                    'notes' => $order->notes,
                    'source' => $order->source,
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
        $validator = Validator::make($request->all(), [
            'gallon_qty' => 'required|integer|min:1|max:50',
            'delivery_method' => 'required|in:antar,ambil_sendiri',
            'delivery_address' => 'required_if:delivery_method,antar|nullable|string',
            'payment_method' => 'required|in:tunai,transfer',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

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
            'message' => 'Pesanan berhasil dibuat! Kami akan segera memproses pesanan Anda.',
            'order' => [
                'id' => $order->id,
                'gallon_qty' => $qty,
                'total_price' => $totalPrice,
                'status' => 'dipesan',
                'status_label' => 'Dipesan',
            ],
        ], 201);
    }

    /**
     * Update Profil Pelanggan
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500',
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('phone')) $user->phone = $request->phone;
        if ($request->has('address')) $user->address = $request->address;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'customer_id' => $user->customer_id,
                'loyalty_level' => $user->loyalty_level,
                'points' => (int) $user->points,
                'status' => $user->status,
            ],
        ]);
    }

    /**
     * Katalog Reward yang Tersedia & Riwayat Penukaran Saya (Serupa Web Portal)
     */
    public function rewards(Request $request)
    {
        try {
            $user = $request->user();
            $rewards = Reward::where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('stock')->orWhere('stock', '>', 0);
                })
                ->orderBy('points_cost')
                ->get()
                ->map(function ($reward) use ($user) {
                    return [
                        'id' => $reward->id,
                        'name' => $reward->name,
                        'description' => $reward->description,
                        'points_cost' => (int) $reward->points_cost,
                        'points_required' => (int) $reward->points_cost,
                        'stock' => $reward->stock,
                        'can_redeem' => (int) $user->points >= $reward->points_cost && ($reward->stock === null || $reward->stock > 0),
                    ];
                });

            $redemptions = RewardRedemption::with('reward')
                ->where('user_id', $user->id)
                ->latest()
                ->take(30)
                ->get()
                ->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'reward_name' => $r->reward ? $r->reward->name : 'Reward',
                        'points_used' => (int) $r->points_used,
                        'redemption_code' => $r->redemption_code,
                        'status' => $r->status,
                        'status_label' => $r->status_label,
                        'created_at' => $r->created_at ? $r->created_at->format('d M Y, H:i') : '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'rewards' => $rewards,
                'redemptions' => $redemptions,
                'user_points' => (int) $user->points,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat reward: ' . $e->getMessage(),
                'rewards' => [],
                'redemptions' => [],
                'user_points' => (int) $request->user()->points,
            ]);
        }
    }

    /**
     * Tukar Poin dengan Reward (Sama seperti Web Portal)
     */
    public function redeemReward(Request $request, $rewardId)
    {
        try {
            $user = $request->user();
            $reward = Reward::findOrFail($rewardId);

            if (!$reward->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maaf, reward ini sedang tidak tersedia atau stok habis.',
                ], 422);
            }

            if ((int) $user->points < $reward->points_cost) {
                return response()->json([
                    'success' => false,
                    'message' => 'Poin Anda tidak mencukupi untuk menukar reward ini.',
                ], 422);
            }

            // Generate kode unik 8 digit
            $code = strtoupper(Str::random(8));

            // Kurangi poin menggunakan PointService
            $success = $this->pointService->deductPoints(
                $user,
                $reward->points_cost,
                "Penukaran reward: {$reward->name}",
                null,
                'reward_redemption'
            );

            if ($success) {
                $redemption = RewardRedemption::create([
                    'user_id' => $user->id,
                    'reward_id' => $reward->id,
                    'points_used' => $reward->points_cost,
                    'redemption_code' => $code,
                    'status' => 'pending',
                    'period_start' => $user->current_period_start,
                ]);

                // Update reference_id pada mutasi poin
                $latestMutation = $user->pointMutations()->latest()->first();
                if ($latestMutation) {
                    $latestMutation->update(['reference_id' => $redemption->id]);
                }

                $user->refresh();

                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menukar poin! Tunjukkan kode penukaran {$code} ke admin depot kami.",
                    'redemption_code' => $code,
                    'remaining_points' => (int) $user->points,
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Gagal memproses penukaran poin.'], 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menukar reward: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Daftar Voucher Milik Pelanggan
     */
    public function vouchers(Request $request)
    {
        try {
            $user = $request->user();
            $vouchers = UserVoucher::with('voucher')
                ->where('user_id', $user->id)
                ->latest()
                ->get()
                ->map(function ($uv) {
                    $v = $uv->voucher;
                    return [
                        'id' => $uv->id,
                        'voucher_id' => $v ? $v->id : null,
                        'code' => $v ? $v->code : '-',
                        'name' => $v ? $v->name : 'Voucher',
                        'description' => $v ? $v->description : '',
                        'discount_type' => $v ? $v->discount_type : 'nominal',
                        'discount_value' => $v ? (float) $v->discount_value : 0,
                        'min_purchase' => $v ? (float) ($v->min_purchase ?? 0) : 0,
                        'expired_at' => $v && $v->expired_at ? $v->expired_at->format('d M Y') : null,
                        'is_used' => (bool) $uv->is_used,
                        'used_at' => $uv->used_at ? $uv->used_at->format('d M Y') : null,
                        'assigned_at' => $uv->created_at->format('d M Y'),
                    ];
                });

            return response()->json(['success' => true, 'vouchers' => $vouchers]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'vouchers' => []]);
        }
    }

    /**
     * Daftar Keluhan Pelanggan
     */
    public function complaints(Request $request)
    {
        try {
            $user = $request->user();
            $complaints = Complaint::where('user_id', $user->id)
                ->latest()
                ->get()
                ->map(function ($c) {
                    return [
                        'id' => $c->id,
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
                        'updated_at' => $c->updated_at->format('d M Y, H:i'),
                    ];
                });

            return response()->json(['success' => true, 'complaints' => $complaints]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'complaints' => []]);
        }
    }

    /**
     * Buat Keluhan Baru
     */
    public function createComplaint(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $category = $request->category ?: ($request->subject ?: 'Keluhan Umum');

            $complaint = Complaint::create([
                'user_id' => $request->user()->id,
                'category' => $category,
                'description' => $request->description,
                'status' => 'baru',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Keluhan berhasil dikirim. Kami akan segera menindaklanjuti.',
                'complaint' => [
                    'id' => $complaint->id,
                    'subject' => $complaint->category,
                    'category' => $complaint->category,
                    'status' => $complaint->status,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengirim keluhan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Detail Keluhan
     */
    public function showComplaint(Request $request, $id)
    {
        try {
            $complaint = Complaint::where('user_id', $request->user()->id)->findOrFail($id);
            return response()->json([
                'success' => true,
                'complaint' => [
                    'id' => $complaint->id,
                    'subject' => $complaint->category,
                    'category' => $complaint->category,
                    'description' => $complaint->description,
                    'status' => $complaint->status,
                    'status_label' => match($complaint->status) {
                        'baru' => 'Menunggu',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                        default => ucfirst($complaint->status),
                    },
                    'admin_notes' => $complaint->response ?? $complaint->action_taken,
                    'response' => $complaint->response,
                    'created_at' => $complaint->created_at->format('d M Y, H:i'),
                    'updated_at' => $complaint->updated_at->format('d M Y, H:i'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Keluhan tidak ditemukan.'], 404);
        }
    }

    /**
     * Notifikasi Pelanggan
     */
    public function notifications(Request $request)
    {
        try {
            $user = $request->user();
            $notifs = Notification::where('user_id', $user->id)
                ->latest()
                ->take(30)
                ->get()
                ->map(function ($n) {
                    return [
                        'id' => $n->id,
                        'title' => $n->title,
                        'body' => $n->body,
                        'message' => $n->body,
                        'type' => $n->type ?? 'info',
                        'is_read' => (bool) $n->is_read,
                        'created_at' => $n->created_at ? $n->created_at->format('d M Y, H:i') : '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'notifications' => $notifs,
                'unread_count' => Notification::where('user_id', $user->id)->where('is_read', false)->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'notifications' => [], 'unread_count' => 0]);
        }
    }

    /**
     * Tandai Notifikasi sebagai Dibaca
     */
    public function markNotificationRead(Request $request, $id)
    {
        try {
            Notification::where('user_id', $request->user()->id)
                ->where('id', $id)
                ->update(['is_read' => true]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Tandai Semua Notifikasi sebagai Dibaca
     */
    public function markAllNotificationsRead(Request $request)
    {
        try {
            Notification::where('user_id', $request->user()->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }
}
