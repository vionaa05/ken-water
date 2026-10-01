<?php

namespace App\Services;

use App\Models\User;
use App\Models\ReactivationLog;
use App\Models\PointMutation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReactivationService
{
    /**
     * Proses reaktivasi secara atomik (semua berhasil atau tidak sama sekali)
     */
    public function reactivate(User $user): array
    {
        if ($user->status !== 'tidak_aktif') {
            return ['success' => false, 'message' => 'Akun sudah aktif.'];
        }

        DB::beginTransaction();

        try {
            // Snapshot data sebelum reset untuk log audit
            $pointsBefore = $user->points;
            $levelBefore = $user->loyalty_level;
            $transactionsCount = $user->completedOrders()->count();

            // a. Hanguskan semua voucher aktif milik pelanggan (periode ini)
            $vouchersExpired = $user->userVouchers()
                ->where('status', 'aktif')
                ->count();

            $user->userVouchers()
                ->where('status', 'aktif')
                ->update(['status' => 'hangus']);

            // b. Hanguskan semua promo personal yang belum dipakai
            $promosExpired = $user->personalPromos()
                ->where('is_used', false)
                ->where('is_expired', false)
                ->count();

            $user->personalPromos()
                ->where('is_used', false)
                ->where('is_expired', false)
                ->update(['is_expired' => true]);

            // c. Batalkan semua kode penukaran reward yang pending
            $redemptionsCancelled = $user->rewardRedemptions()
                ->where('status', 'pending')
                ->count();

            // Kembalikan poin untuk redemption yang dibatalkan
            $pendingRedemptions = $user->rewardRedemptions()
                ->where('status', 'pending')
                ->get();

            foreach ($pendingRedemptions as $redemption) {
                $redemption->update(['status' => 'cancelled']);
            }

            // d. Catat mutasi poin RESET
            if ($pointsBefore > 0) {
                PointMutation::create([
                    'user_id' => $user->id,
                    'type' => 'reset',
                    'amount' => $pointsBefore,
                    'balance_after' => 0,
                    'description' => 'Reset poin karena reaktivasi akun',
                ]);
            }

            $now = Carbon::now();

            // e. Update data pelanggan — reset ke kondisi awal
            $user->update([
                'points' => 0,
                'loyalty_level' => 'bronze',
                'status' => 'aktif',
                'current_period_start' => $now,
                'last_reactivation_at' => $now,
                'reactivation_count' => $user->reactivation_count + 1,
            ]);

            // f. Buat log reaktivasi untuk audit admin
            ReactivationLog::create([
                'user_id' => $user->id,
                'reactivated_at' => $now,
                'points_before' => $pointsBefore,
                'level_before' => $levelBefore,
                'transactions_count_before' => $transactionsCount,
                'vouchers_expired_count' => $vouchersExpired,
                'promos_expired_count' => $promosExpired,
                'redemptions_cancelled_count' => $redemptionsCancelled,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Akun berhasil diaktifkan kembali. Selamat datang kembali!',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
                'error' => $e->getMessage(),
            ];
        }
    }
}
