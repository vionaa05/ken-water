<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RewardPortalController extends Controller
{
    public function __construct(private PointService $pointService) {}

    public function index()
    {
        $user = auth()->user();
        
        $rewards = Reward::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('stock')->orWhere('stock', '>', 0);
            })
            ->get();
            
        $redemptions = $user->rewardRedemptions()
            ->where('period_start', '>=', $user->current_period_start)
            ->with('reward')
            ->latest()
            ->get();
            
        $pointMutations = $user->pointMutations()
            ->latest()
            ->take(20)
            ->get();
            
        return view('portal.rewards.index', compact('user', 'rewards', 'redemptions', 'pointMutations'));
    }

    public function redeem(Request $request, Reward $reward)
    {
        $user = auth()->user();
        
        if (!$reward->isAvailable()) {
            return back()->with('error', 'Maaf, reward ini sedang tidak tersedia atau stok habis.');
        }
        
        if ($user->points < $reward->points_cost) {
            return back()->with('error', 'Poin Anda tidak cukup untuk menukarkan reward ini.');
        }
        
        // Generate kode unik
        $code = strtoupper(Str::random(8));
        
        // Kurangi poin
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
            
            // Update reference_id di PointMutation
            $user->pointMutations()->latest()->first()->update([
                'reference_id' => $redemption->id
            ]);
            
            return back()->with('success', "Berhasil menukar poin! Tunjukkan kode penukaran {$code} ke staf depot kami.");
        }
        
        return back()->with('error', 'Terjadi kesalahan sistem, silakan coba lagi.');
    }
}
