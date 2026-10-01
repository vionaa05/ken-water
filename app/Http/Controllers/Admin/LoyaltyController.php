<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\Voucher;
use App\Models\UserVoucher;
use App\Models\PersonalPromo;
use App\Models\User;
use App\Models\Notification;
use App\Services\PointService;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function __construct(private PointService $pointService) {}

    // =====================
    // REWARDS
    // =====================
    public function index()
    {
        $rewards = Reward::latest()->paginate(15);
        return view('admin.loyalty.rewards.index', compact('rewards'));
    }

    public function create()
    {
        return view('admin.loyalty.rewards.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points_cost' => 'required|integer|min:1',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        Reward::create($request->all());

        return redirect()->route('admin.loyalty.rewards.index')
            ->with('success', 'Reward berhasil ditambahkan.');
    }

    public function edit(Reward $reward)
    {
        return view('admin.loyalty.rewards.edit', compact('reward'));
    }

    public function update(Request $request, Reward $reward)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points_cost' => 'required|integer|min:1',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $reward->update($request->all());

        return redirect()->route('admin.loyalty.rewards.index')
            ->with('success', 'Reward berhasil diperbarui.');
    }

    public function destroy(Reward $reward)
    {
        $reward->delete();
        return redirect()->route('admin.loyalty.rewards.index')
            ->with('success', 'Reward berhasil dihapus.');
    }

    // =====================
    // REDEMPTIONS
    // =====================
    public function redemptionsIndex(Request $request)
    {
        $query = RewardRedemption::with(['user', 'reward', 'validator'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $redemptions = $query->paginate(20)->withQueryString();

        return view('admin.loyalty.redemptions.index', compact('redemptions'));
    }

    public function validateRedemption(Request $request, RewardRedemption $redemption)
    {
        if ($redemption->status !== 'pending') {
            return back()->with('error', 'Hanya penukaran pending yang bisa divalidasi.');
        }

        $redemption->update([
            'status' => 'validated',
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        // Kurangi stok reward jika ada
        if ($redemption->reward->stock !== null) {
            $redemption->reward->decrement('stock');
        }

        Notification::create([
            'user_id' => $redemption->user_id,
            'title' => 'Penukaran Reward Berhasil',
            'body' => "Penukaran {$redemption->reward->name} Anda telah divalidasi.",
            'type' => 'points_update',
            'reference_id' => $redemption->id,
            'reference_type' => 'reward_redemption',
        ]);

        return back()->with('success', 'Penukaran reward berhasil divalidasi.');
    }

    public function cancelRedemption(Request $request, RewardRedemption $redemption)
    {
        if ($redemption->status !== 'pending') {
            return back()->with('error', 'Hanya penukaran pending yang bisa dibatalkan.');
        }

        // Kembalikan poin
        $this->pointService->addPoints(
            $redemption->user,
            $redemption->points_used,
            "Pengembalian poin dari pembatalan penukaran reward: {$redemption->reward->name}",
            $redemption->id,
            'reward_redemption'
        );

        $redemption->update([
            'status' => 'cancelled',
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        Notification::create([
            'user_id' => $redemption->user_id,
            'title' => 'Penukaran Reward Dibatalkan',
            'body' => "Penukaran {$redemption->reward->name} dibatalkan. Poin Anda telah dikembalikan.",
            'type' => 'points_update',
            'reference_id' => $redemption->id,
            'reference_type' => 'reward_redemption',
        ]);

        return back()->with('success', 'Penukaran dibatalkan dan poin dikembalikan.');
    }

    // =====================
    // VOUCHERS
    // =====================
    public function vouchersIndex()
    {
        $vouchers = Voucher::latest()->paginate(15);
        return view('admin.loyalty.vouchers.index', compact('vouchers'));
    }

    public function vouchersCreate()
    {
        return view('admin.loyalty.vouchers.create');
    }

    public function vouchersStore(Request $request)
    {
        $request->validate([
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

        Voucher::create($request->all());

        return redirect()->route('admin.loyalty.vouchers.index')
            ->with('success', 'Voucher berhasil ditambahkan.');
    }

    public function assignVoucher(Voucher $voucher)
    {
        $customers = User::customers()->active()->get();
        return view('admin.loyalty.vouchers.assign', compact('voucher', 'customers'));
    }

    public function doAssignVoucher(Request $request, Voucher $voucher)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();
        $assigned = 0;

        foreach ($users as $user) {
            // Cek apakah user sudah punya voucher aktif ini
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

        return redirect()->route('admin.loyalty.vouchers.index')
            ->with('success', "Voucher berhasil dibagikan ke {$assigned} pelanggan.");
    }

    // =====================
    // PROMO PERSONAL
    // =====================
    public function promosIndex()
    {
        $promos = PersonalPromo::with('user')->latest()->paginate(15);
        return view('admin.loyalty.promos.index', compact('promos'));
    }

    public function promosCreate()
    {
        $customers = User::customers()->active()->get();
        return view('admin.loyalty.promos.create', compact('customers'));
    }

    public function promosStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:nominal,persen',
            'discount_value' => 'required|numeric|min:1',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
        ]);

        $user = User::find($request->user_id);

        $promo = PersonalPromo::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'period_start' => $user->current_period_start,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Promo Spesial Untuk Anda!',
            'body' => "Anda mendapat promo: {$promo->name}. Cek beranda Anda.",
            'type' => 'promo',
        ]);

        return redirect()->route('admin.loyalty.promos.index')
            ->with('success', 'Promo personal berhasil diberikan.');
    }
}
