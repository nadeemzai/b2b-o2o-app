<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\Retailer\CatalogueController;
use App\Http\Controllers\Api\Retailer\KycController;
use App\Http\Controllers\Api\Retailer\OrderController;
use App\Http\Controllers\Api\Retailer\RetailerProfileController;
use App\Http\Controllers\Api\Admin\AdminRetailerController;
use App\Http\Controllers\Api\Store\StoreOrderController;
use App\Http\Controllers\Api\Store\StoreStockController;
use Illuminate\Support\Facades\Route;

// ══════════════════════════════════════════════════════════════
//  Public routes
// ══════════════════════════════════════════════════════════════

Route::prefix('auth')->group(function () {
    Route::post('login',    [AuthController::class,    'login']);
    Route::post('register', [RegisterController::class, 'register']); // POST /api/auth/register
});

// ══════════════════════════════════════════════════════════════
//  Authenticated routes (Sanctum token required)
// ══════════════════════════════════════════════════════════════

Route::middleware('auth:sanctum')->group(function () {

    // ── Auth ──────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::get('me',      [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    // ── Retailer routes ───────────────────────────────────────
    //    Middleware 'role:retailer' ensures only retailer tokens
    //    reach these controllers. Wire this in bootstrap/app.php:
    //    ->withMiddleware(fn ($m) => $m->alias(['role' => \App\Http\Middleware\EnsureRole::class]))
    Route::prefix('retailer')->middleware('role:retailer')->group(function () {

        // Profile
        Route::get('profile',   [RetailerProfileController::class, 'show']);   // GET  /api/retailer/profile
        Route::patch('profile', [RetailerProfileController::class, 'update']); // PATCH /api/retailer/profile

        // KYC document upload
        Route::post('kyc/upload', [KycController::class, 'upload']); // POST /api/retailer/kyc/upload

        // Catalogue
        Route::get('catalogue',          [CatalogueController::class, 'index']);
        Route::get('catalogue/{product}', [CatalogueController::class, 'show']);

        // Orders
        Route::get('orders',                  [OrderController::class, 'index']);
        Route::post('orders',                 [OrderController::class, 'store']);
        Route::get('orders/{order}',          [OrderController::class, 'show']);
        Route::post('orders/{order}/cancel',  [OrderController::class, 'cancel']);
    });

    // ── Admin routes ──────────────────────────────────────────
    Route::prefix('admin')->middleware('role:admin')->group(function () {

        // KYC / Retailer management
        Route::get('retailers',                           [AdminRetailerController::class, 'index']);   // GET  /api/admin/retailers
        Route::post('retailers/{retailer}/approve',       [AdminRetailerController::class, 'approve']); // POST /api/admin/retailers/{retailer}/approve
        Route::post('retailers/{retailer}/reject',        [AdminRetailerController::class, 'reject']);  // POST /api/admin/retailers/{retailer}/reject
    });

    // ── Store Staff routes ────────────────────────────────────
    Route::prefix('store')->middleware('role:store_staff')->group(function () {

        // Order queue
        Route::get('orders',                   [StoreOrderController::class, 'index']);
        Route::get('orders/{order}',           [StoreOrderController::class, 'show']);
        Route::post('orders/{order}/status',   [StoreOrderController::class, 'updateStatus']);
        Route::post('orders/{order}/deliver',  [StoreOrderController::class, 'deliver']);

        // Stock management
        Route::get('stock',           [StoreStockController::class, 'index']);   // GET  /api/store/stock
        Route::post('stock/inbound',  [StoreStockController::class, 'inbound']); // POST /api/store/stock/inbound
        Route::post('stock/adjust',   [StoreStockController::class, 'adjust']);  // POST /api/store/stock/adjust
    });

});
