<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\AdminApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for Ken Water Mobile (Flutter)
|--------------------------------------------------------------------------
*/

// Public Authentication
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Authenticated Routes (Bearer Token via Laravel Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ==========================================
    // Pelanggan (Customer Mobile Features)
    // ==========================================
    Route::prefix('customer')->group(function () {
        Route::get('/member-card', [CustomerApiController::class, 'memberCard']);
        Route::get('/orders', [CustomerApiController::class, 'orders']);
        Route::post('/orders', [CustomerApiController::class, 'createOrder']);

        // Profil
        Route::put('/profile', [CustomerApiController::class, 'updateProfile']);

        // Reward & Poin
        Route::get('/rewards', [CustomerApiController::class, 'rewards']);
        Route::post('/rewards/{reward}/redeem', [CustomerApiController::class, 'redeemReward']);

        // Voucher
        Route::get('/vouchers', [CustomerApiController::class, 'vouchers']);

        // Keluhan
        Route::get('/complaints', [CustomerApiController::class, 'complaints']);
        Route::post('/complaints', [CustomerApiController::class, 'createComplaint']);
        Route::get('/complaints/{complaint}', [CustomerApiController::class, 'showComplaint']);

        // Notifikasi
        Route::get('/notifications', [CustomerApiController::class, 'notifications']);
        Route::post('/notifications/{id}/read', [CustomerApiController::class, 'markNotificationRead']);
    });

    // ==========================================
    // Admin / Kasir (Admin Mobile Features)
    // ==========================================
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminApiController::class, 'dashboard']);
        Route::get('/customers/search', [AdminApiController::class, 'searchCustomer']);
        Route::get('/customers', [AdminApiController::class, 'customers']);
        Route::post('/transactions', [AdminApiController::class, 'createTransaction']);
        Route::get('/orders', [AdminApiController::class, 'orders']);
        Route::patch('/orders/{order}/status', [AdminApiController::class, 'updateOrderStatus']);
        Route::get('/complaints', [AdminApiController::class, 'complaints']);
        Route::patch('/complaints/{complaint}/status', [AdminApiController::class, 'updateComplaintStatus']);
        Route::get('/rewards', [AdminApiController::class, 'rewards']);
        Route::post('/rewards', [AdminApiController::class, 'createReward']);
        Route::put('/rewards/{id}', [AdminApiController::class, 'updateReward']);
        Route::delete('/rewards/{id}', [AdminApiController::class, 'deleteReward']);
        Route::get('/redemptions', [AdminApiController::class, 'redemptions']);
        Route::patch('/redemptions/{redemption}/validate', [AdminApiController::class, 'validateRedemption']);
        Route::patch('/redemptions/{redemption}/cancel', [AdminApiController::class, 'cancelRedemption']);
    });
});
