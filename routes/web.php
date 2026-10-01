<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ComplaintController;
use App\Http\Controllers\Admin\SegmentationController;
use App\Http\Controllers\Admin\LoyaltyController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Portal\HomeController;
use App\Http\Controllers\Portal\OrderPortalController;
use App\Http\Controllers\Portal\RewardPortalController;
use App\Http\Controllers\Portal\VoucherPortalController;
use App\Http\Controllers\Portal\ComplaintPortalController;
use App\Http\Controllers\Portal\ProfilePortalController;
use App\Http\Controllers\Portal\ReactivationController;
use App\Http\Controllers\Portal\NotificationController;
use App\Http\Controllers\Portal\MemberCardController;
use App\Http\Controllers\Portal\RegisterController;
use Illuminate\Support\Facades\Route;

// =====================
// AUTH ROUTES
// =====================
Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;
        return match($role) {
            'admin', 'staf' => redirect()->route('admin.dashboard'),
            'pelanggan' => redirect()->route('portal.home'),
            default => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// =====================
// PORTAL PELANGGAN - Register
// =====================
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');
});

// =====================
// BACK OFFICE (Admin & Staf)
// =====================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,staf'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manajemen Pelanggan
    Route::resource('customers', CustomerController::class);
    Route::get('/customers/{customer}/print-card', [CustomerController::class, 'printCard'])->name('customers.print-card');

    // Pesanan
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');

    // Transaksi Langsung (input staf)
    Route::get('/transactions/create/{customer}', [OrderController::class, 'createDirect'])->name('transactions.create');
    Route::post('/transactions/store/{customer}', [OrderController::class, 'storeDirect'])->name('transactions.store');

    // Segmentasi
    Route::get('/segmentation', [SegmentationController::class, 'index'])->name('segmentation.index');

    // Manajemen Keluhan
    Route::resource('complaints', ComplaintController::class)->only(['index', 'show', 'update']);

    // Program Loyalitas
    Route::prefix('loyalty')->name('loyalty.')->group(function () {
        Route::resource('rewards', LoyaltyController::class)->names([
            'index' => 'rewards.index', 'create' => 'rewards.create',
            'store' => 'rewards.store', 'edit' => 'rewards.edit',
            'update' => 'rewards.update', 'destroy' => 'rewards.destroy',
        ]);
        Route::get('/vouchers', [LoyaltyController::class, 'vouchersIndex'])->name('vouchers.index');
        Route::get('/vouchers/create', [LoyaltyController::class, 'vouchersCreate'])->name('vouchers.create');
        Route::post('/vouchers', [LoyaltyController::class, 'vouchersStore'])->name('vouchers.store');
        Route::get('/vouchers/{voucher}/assign', [LoyaltyController::class, 'assignVoucher'])->name('vouchers.assign');
        Route::post('/vouchers/{voucher}/assign', [LoyaltyController::class, 'doAssignVoucher'])->name('vouchers.do-assign');
        Route::get('/promos', [LoyaltyController::class, 'promosIndex'])->name('promos.index');
        Route::get('/promos/create', [LoyaltyController::class, 'promosCreate'])->name('promos.create');
        Route::post('/promos', [LoyaltyController::class, 'promosStore'])->name('promos.store');
        Route::get('/redemptions', [LoyaltyController::class, 'redemptionsIndex'])->name('redemptions.index');
        Route::patch('/redemptions/{redemption}/validate', [LoyaltyController::class, 'validateRedemption'])->name('redemptions.validate');
        Route::patch('/redemptions/{redemption}/cancel', [LoyaltyController::class, 'cancelRedemption'])->name('redemptions.cancel');
    });

    // Kampanye CRM
    Route::resource('campaigns', CampaignController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    // Pengaturan (khusus Admin)
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index')->middleware('role:admin');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update')->middleware('role:admin');

    // Notifikasi - mark as read
    Route::get('/notifications/data', function () {
        return response()->json([
            'count' => 0, // admin tidak punya notifikasi pelanggan
        ]);
    })->name('notifications.data');
});

// =====================
// PORTAL PELANGGAN
// =====================
Route::prefix('portal')->name('portal.')->middleware(['auth', 'role:pelanggan'])->group(function () {

    // Beranda
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Reaktivasi (bisa diakses meski tidak aktif)
    Route::get('/reactivate', [ReactivationController::class, 'show'])->name('reactivate');
    Route::post('/reactivate', [ReactivationController::class, 'confirm'])->name('reactivate.confirm');

    // Routes berikut hanya untuk pelanggan AKTIF
    Route::middleware('customer.active')->group(function () {

        // Pesan Galon
        Route::get('/order', [OrderPortalController::class, 'create'])->name('order.create');
        Route::post('/order', [OrderPortalController::class, 'store'])->name('order.store');
        Route::get('/orders', [OrderPortalController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderPortalController::class, 'show'])->name('orders.show');

        // Riwayat Transaksi
        Route::get('/transactions', [OrderPortalController::class, 'transactions'])->name('transactions');

        // Reward & Poin
        Route::get('/rewards', [RewardPortalController::class, 'index'])->name('rewards.index');
        Route::post('/rewards/{reward}/redeem', [RewardPortalController::class, 'redeem'])->name('rewards.redeem');

        // Voucher
        Route::get('/vouchers', [VoucherPortalController::class, 'index'])->name('vouchers.index');
    });

    // Keluhan (aktif maupun tidak aktif bisa lihat, tapi hanya aktif yang bisa buat baru)
    Route::get('/complaints', [ComplaintPortalController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/{complaint}', [ComplaintPortalController::class, 'show'])->name('complaints.show');
    Route::middleware('customer.active')->group(function () {
        Route::get('/complaints/create', [ComplaintPortalController::class, 'create'])->name('complaints.create');
        Route::post('/complaints', [ComplaintPortalController::class, 'store'])->name('complaints.store');
    });

    // Kartu Member Digital
    Route::get('/member-card', [MemberCardController::class, 'show'])->name('member-card');
    Route::get('/member-card/download', [MemberCardController::class, 'download'])->name('member-card.download');

    // Profil
    Route::get('/profile', [ProfilePortalController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfilePortalController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfilePortalController::class, 'updatePassword'])->name('profile.password');

    // Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/data', [NotificationController::class, 'data'])->name('notifications.data');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});
