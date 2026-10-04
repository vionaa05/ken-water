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

            // Update loyalty level setelah pengurangan poin
            $this->updateLoyaltyLevel($user, $newBalance);
        });

        return true;
    }

    /**
     * Update level loyalitas berdasarkan total poin
     */
    public function updateLoyaltyLevel(User $user, int $currentPoints): void
    {
        $silverThreshold = (int) setting('silver_threshold', 50);
        $goldThreshold = (int) setting('gold_threshold', 150);

        $newLevel = 'bronze';
        if ($currentPoints >= $goldThreshold) {
            $newLevel = 'gold';
        } elseif ($currentPoints >= $silverThreshold) {
            $newLevel = 'silver';
        }

        if ($user->loyalty_level !== $newLevel) {
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
     * Hitung poin yang dibutuhkan untuk naik ke level berikutnya
     */
    public function getProgressToNextLevel(User $user): array
    {
        $silverThreshold = (int) setting('silver_threshold', 50);
        $goldThreshold = (int) setting('gold_threshold', 150);
        $currentPoints = $user->points;

        if ($user->loyalty_level === 'bronze') {
            return [
                'current' => $currentPoints,
                'target' => $silverThreshold,
                'next_threshold' => $silverThreshold,
                'needed' => max(0, $silverThreshold - $currentPoints),
                'points_needed' => max(0, $silverThreshold - $currentPoints),
                'next_level' => 'Silver',
                'is_max' => false,
                'percentage' => min(100, round(($currentPoints / $silverThreshold) * 100)),
            ];
        } elseif ($user->loyalty_level === 'silver') {
            return [
                'current' => $currentPoints,
                'target' => $goldThreshold,
                'next_threshold' => $goldThreshold,
                'needed' => max(0, $goldThreshold - $currentPoints),
                'points_needed' => max(0, $goldThreshold - $currentPoints),
                'next_level' => 'Gold',
                'is_max' => false,
                'percentage' => min(100, round((($currentPoints - $silverThreshold) / ($goldThreshold - $silverThreshold)) * 100)),
            ];
        }

        return [
            'current' => $currentPoints,
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
