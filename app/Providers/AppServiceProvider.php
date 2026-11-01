<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\Complaint;
use App\Models\RewardRedemption;
use App\Models\Order;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(app_path('helpers.php'))) {
            require_once app_path('helpers.php');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // View composer untuk sidebar admin agar badge pesanan, keluhan, dan reward selalu realtime & akurat
        View::composer('layouts.admin', function ($view) {
            $pendingOrdersCount = 0;
            $pendingComplaintsCount = 0;
            $pendingRedemptionsCount = 0;

            try {
                if (Schema::hasTable('orders')) {
                    // Pesanan aktif yang belum selesai/batal
                    $pendingOrdersCount = Order::whereNotIn('status', ['selesai', 'dibatalkan'])->count();
                }
                if (Schema::hasTable('complaints')) {
                    $pendingComplaintsCount = Complaint::whereIn('status', ['baru', 'diproses'])->count();
                }
                if (Schema::hasTable('reward_redemptions')) {
                    $pendingRedemptionsCount = RewardRedemption::where('status', 'pending')->count();
                }
            } catch (\Throwable $e) {
                // Fail-safe jika DB belum siap atau error koneksi
            }

            $view->with([
                'sidebarPendingOrders'      => $pendingOrdersCount,
                'sidebarPendingComplaints'  => $pendingComplaintsCount,
                'sidebarPendingRedemptions' => $pendingRedemptionsCount,
            ]);
        });
    }
}
