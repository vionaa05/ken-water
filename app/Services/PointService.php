<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\PointMutation;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class PointService
{
    /**
     * Tambah poin ke pelanggan (dari pesanan selesai atau transaksi langsung)
     */
    public function addPoints(User $user, int $gallonQty, string $description, $referenceId = null, string $referenceType = 'order'): int
    {
        $pointsPerGalon = (int) setting('points_per_gallon', 1);
        $pointsToAdd = $gallonQty * $pointsPerGalon;

        return DB::transaction(function () use ($user, $pointsToAdd, $description, $referenceId, $referenceType) {
            $newBalance = $user->points + $pointsToAdd;

            $user->update(['points' => $newBalance]);

            PointMutation::create([
                'user_id' => $user->id,
                'type' => 'tambah',
                'amount' => $pointsToAdd,
                'balance_after' => $newBalance,
                'description' => $description,
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
            ]);

            // Update loyalty level setelah penambahan poin
            $this->updateLoyaltyLevel($user, $newBalance);

            // Kirim notifikasi
            Notification::create([
                'user_id' => $user->id,
                'title' => '🎉 Poin Bertambah!',
                'body' => "Anda mendapatkan {$pointsToAdd} poin. Total poin: {$newBalance}.",
                'type' => 'points_update',
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
            ]);

            return $pointsToAdd;
        });
    }

    /**
     * Kurangi poin (untuk penukaran reward)
     */
    public function deductPoints(User $user, int $pointsToDeduct, string $description, $referenceId = null, string $referenceType = 'reward_redemption'): bool
    {
        if ($user->points < $pointsToDeduct) {
            return false;
        }

        DB::transaction(function () use ($user, $pointsToDeduct, $description, $referenceId, $referenceType) {
            $newBalance = $user->points - $pointsToDeduct;

            $user->update(['points' => $newBalance]);

            PointMutation::create([
                'user_id' => $user->id,
                'type' => 'kurang',
                'amount' => $pointsToDeduct,
                'balance_after' => $newBalance,
                'description' => $description,
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
            ]);

            // Catatan: Loyalty level sengaja TIDAK diturunkan saat penukaran reward
            // agar pelanggan bebas menikmati reward tanpa takut kehilangan status level.
        });

        return true;
    }

    /**
     * Update level loyalitas berdasarkan akumulasi perolehan poin (hanya naik level, tidak pernah turun saat belanja)
     */
    public function updateLoyaltyLevel(User $user, ?int $currentPoints = null): void
    {
        $silverThreshold = (int) setting('silver_threshold', 50);
        $goldThreshold = (int) setting('gold_threshold', 150);

        // Gunakan nilai tertinggi antara saldo saat ini dan akumulasi poin yang pernah didapat di periode ini
        $qualificationPoints = max($user->points, $user->total_earned_points_current_period);

        $newLevel = 'bronze';
        if ($qualificationPoints >= $goldThreshold) {
            $newLevel = 'gold';
        } elseif ($qualificationPoints >= $silverThreshold) {
            $newLevel = 'silver';
        }

        // Hirarki level untuk memastikan level HANYA bisa naik
        $levelRank = [
            'bronze' => 1,
            'silver' => 2,
            'gold'   => 3,
        ];

        $currentRank = $levelRank[$user->loyalty_level] ?? 1;
        $newRank = $levelRank[$newLevel] ?? 1;

        // Hanya update jika level baru LEBIH TINGGI dari level saat ini
        if ($newRank > $currentRank) {
            $user->update(['loyalty_level' => $newLevel]);

            // Kirim notifikasi naik level
            $levelLabel = match($newLevel) {
                'silver' => '🥈 Silver',
                'gold' => '🥇 Gold',
                default => '🥉 Bronze',
            };

            Notification::create([
                'user_id' => $user->id,
                'title' => 'Level Loyalitas Naik!',
                'body' => "Selamat! Level Anda naik ke {$levelLabel}.",
                'type' => 'points_update',
            ]);
        }
    }

    /**
     * Hitung poin yang dibutuhkan untuk naik ke level berikutnya berdasarkan akumulasi poin kualifikasi
     */
    public function getProgressToNextLevel(User $user): array
    {
        $silverThreshold = (int) setting('silver_threshold', 50);
        $goldThreshold = (int) setting('gold_threshold', 150);
        
        // Poin kualifikasi adalah akumulasi perolehan poin (tidak berkurang saat tukar reward)
        $qualificationPoints = max($user->points, $user->total_earned_points_current_period);

        if ($user->loyalty_level === 'bronze') {
            $needed = max(0, $silverThreshold - $qualificationPoints);
            return [
                'current' => $qualificationPoints,
                'balance' => $user->points,
                'target' => $silverThreshold,
                'next_threshold' => $silverThreshold,
                'needed' => $needed,
                'points_needed' => $needed,
                'next_level' => 'Silver',
                'is_max' => false,
                'percentage' => min(100, round(($qualificationPoints / $silverThreshold) * 100)),
            ];
        } elseif ($user->loyalty_level === 'silver') {
            // Untuk Silver menuju Gold, rentang poin dari silverThreshold ke goldThreshold
            $needed = max(0, $goldThreshold - $qualificationPoints);
            $span = max(1, $goldThreshold - $silverThreshold);
            $progressWithinSpan = max(0, $qualificationPoints - $silverThreshold);
            $percentage = min(100, round(($progressWithinSpan / $span) * 100));

            return [
                'current' => $qualificationPoints,
                'balance' => $user->points,
                'target' => $goldThreshold,
                'next_threshold' => $goldThreshold,
                'needed' => $needed,
                'points_needed' => $needed,
                'next_level' => 'Gold',
                'is_max' => false,
                'percentage' => $percentage,
            ];
        }

        return [
            'current' => $qualificationPoints,
            'balance' => $user->points,
            'target' => $goldThreshold,
            'next_threshold' => $goldThreshold,
            'needed' => 0,
            'points_needed' => 0,
            'next_level' => null,
            'is_max' => true,
            'percentage' => 100,
        ];
    }
}
