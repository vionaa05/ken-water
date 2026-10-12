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
    });

    // ==========================================
    // Admin / Kasir (Admin Mobile Features)
    // ==========================================
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminApiController::class, 'dashboard']);
        Route::get('/customers/search', [AdminApiController::class, 'searchCustomer']);
        Route::post('/transactions', [AdminApiController::class, 'createTransaction']);
        Route::get('/orders', [AdminApiController::class, 'orders']);
    });
});
