<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\StoresController;
use App\Http\Controllers\Api\Retailer\CatalogueController;
use App\Http\Controllers\Api\Retailer\CategoryController;
use App\Http\Controllers\Api\Retailer\DashboardController;
use App\Http\Controllers\Api\Retailer\HomepageController;
use App\Http\Controllers\Api\Retailer\KycController;
use App\Http\Controllers\Api\Retailer\OrderController;
use App\Http\Controllers\Api\Retailer\RetailerProfileController;
use App\Http\Controllers\Api\Retailer\ReviewController;
use App\Http\Controllers\Api\Admin\AdminRetailerController;
use App\Http\Controllers\Api\Store\StoreOrderController;
use App\Http\Controllers\Api\Store\StoreStockController;
use Illuminate\Support\Facades\Route;

// ══════════════════════════════════════════════════════════════
//  Public routes
// ══════════════════════════════════════════════════════════════

Route::prefix('auth')->group(function () {
    Route::post('login',    [AuthController::class,    'login']);
    Route::post('register', [RegisterController::class, 'register']);
});
Route::get('stores', StoresController::class);                             // GET /api/stores — active stores list for registration

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
    Route::prefix('retailer')->middleware('role:retailer')->group(function () {

        // Profile
        Route::get('profile',   [RetailerProfileController::class, 'show']);
        Route::patch('profile', [RetailerProfileController::class, 'update']);

        // KYC document upload
        Route::post('kyc/upload', [KycController::class, 'upload']);

        // Dashboard — order stats + recent orders
        Route::get('dashboard', DashboardController::class);                   // GET /api/retailer/dashboard

        // Homepage — deals, new arrivals, section flags
        Route::get('home', HomepageController::class);                         // GET /api/retailer/home

        // Categories — standalone list for filter UI
        Route::get('categories', CategoryController::class);                   // GET /api/retailer/categories

        // Catalogue
        // Query params: category_id, search, sort_by (name_asc|price_asc|price_desc|newest), per_page
        Route::get('catalogue',           [CatalogueController::class, 'index']); // GET /api/retailer/catalogue
        Route::get('catalogue/{product}', [CatalogueController::class, 'show']);  // GET /api/retailer/catalogue/{product}

        // Product reviews
        Route::get('catalogue/{product}/reviews',  [ReviewController::class, 'index']); // GET  /api/retailer/catalogue/{product}/reviews
        Route::post('catalogue/{product}/reviews', [ReviewController::class, 'store']); // POST /api/retailer/catalogue/{product}/reviews

        // Orders
        Route::get('orders',                          [OrderController::class, 'index']);
        Route::post('orders',                         [OrderController::class, 'store']);
        Route::get('orders/{order}',                  [OrderController::class, 'show']);
        Route::post('orders/{order}/cancel',          [OrderController::class, 'cancel']);
        Route::post('orders/{order}/payment-proof',   [OrderController::class, 'uploadPaymentProof']); // POST /api/retailer/orders/{order}/payment-proof
        Route::post('orders/{order}/reorder',         [OrderController::class, 'reorder']);             // POST /api/retailer/orders/{order}/reorder
    });

    // ── Admin routes ──────────────────────────────────────────
    Route::prefix('admin')->middleware('role:admin')->group(function () {

        // KYC / Retailer management
        Route::get('retailers',                     [AdminRetailerController::class, 'index']);
        Route::post('retailers/{retailer}/approve', [AdminRetailerController::class, 'approve']);
        Route::post('retailers/{retailer}/reject',  [AdminRetailerController::class, 'reject']);
    });

    // ── Store Staff routes ────────────────────────────────────
    Route::prefix('store')->middleware('role:store_staff')->group(function () {

        // Order visibility
        Route::get('orders',         [StoreOrderController::class, 'index']);
        Route::get('orders/{order}', [StoreOrderController::class, 'show']);

        // Stock management
        Route::get('stock',           [StoreStockController::class, 'index']);
        Route::post('stock/inbound',  [StoreStockController::class, 'inbound']);
        Route::post('stock/adjust',   [StoreStockController::class, 'adjust']);
    });

});
