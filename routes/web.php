<?php

use App\Http\Controllers\Web\Retailer\AuthController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Retailer\Auth\Login;
use App\Livewire\Retailer\Auth\Register;
use App\Livewire\Retailer\Dashboard;
use App\Livewire\Retailer\Catalogue\ProductList;
use App\Livewire\Retailer\Catalogue\ProductDetail as RetailerProductDetail;
use App\Livewire\Retailer\Cart\CartPage;
use App\Livewire\Retailer\Orders\OrderHistory;
use App\Livewire\Public\ProductCatalogue;
use App\Livewire\Public\ProductDetail as PublicProductDetail;
use App\Livewire\Public\Homepage as PublicHome;
use App\Livewire\Retailer\Homepage as RetailerHome;


// ── Locale & currency switching (works for guests and authenticated users) ──
Route::post('/switch-locale/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'zh_CN'], true)) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('switch.locale');

Route::post('/switch-currency/{currency}', function (string $currency) {
    if (in_array($currency, ['PKR', 'USD', 'CNY'], true)) {
        session(['currency' => $currency]);
    }
    return redirect()->back();
})->name('switch.currency');

// ── Root: public catalogue ─────────────────────────────────────
Route::get('/', PublicHome::class)->name('public.home');

// ── Public catalogue (full browse) ───────────────────────────
Route::get('/catalogue', ProductCatalogue::class)->name('public.catalogue');


// ── Public product detail (no login required) ─────────────────
Route::get('/product/{product}', PublicProductDetail::class)->name('public.product');

// ── Retailer guest routes ──────────────────────────────────────
Route::prefix('retailer')->name('retailer.')->middleware('guest:retailer')->group(function () {
    Route::get('/login',    Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

// ── Retailer auth-only (any retailer, pending or approved) ─────
Route::prefix('retailer')->name('retailer.')->middleware(['auth:retailer', 'retailer'])->group(function () {
    Route::get('/pending', fn () => view('retailer.pending'))->name('pending');
    Route::get('/kyc', \App\Livewire\Kyc\UploadDocuments::class)->name('kyc');
});

// ── Retailer fully approved routes ────────────────────────────
Route::prefix('retailer')->name('retailer.')->middleware(['auth:retailer', 'retailer', 'retailer.approved'])->group(function () {
    Route::get('/',          RetailerHome::class)->name('home');
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/catalogue', ProductList::class)->name('catalogue');
    Route::get('/catalogue/{product}', RetailerProductDetail::class)->name('catalogue.product');
    Route::get('/cart',      CartPage::class)->name('cart');
    Route::get('/orders',    OrderHistory::class)->name('orders');
});

// ── Logout ────────────────────────────────────────────────────
Route::post('/retailer/logout', function () {
    auth('retailer')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('retailer.login');
})->middleware('auth:retailer')->name('retailer.logout');

// ──────────────────────────────────────────────────────────────────────────
// Admin: secure KYC document viewer
// ──────────────────────────────────────────────────────────────────────────
Route::get(
    '/admin/retailers/{retailer}/kyc/{field}',
    \App\Http\Controllers\Admin\KycDocumentController::class
)->middleware(['auth:admin'])->name('admin.kyc.document');


// ──────────────────────────────────────────────────────────────────────────
// Order Exports (xlsx / csv / pdf)
// ──────────────────────────────────────────────────────────────────────────
use App\Http\Controllers\Export\OrderExportController;

// Retailer — scoped to authenticated retailer's own orders
Route::prefix('retailer')->name('retailer.')->middleware(['auth:retailer', 'retailer', 'retailer.approved'])->group(function () {
    Route::get('/orders/export',             [OrderExportController::class, 'retailerListing'])  ->name('orders.export');
    Route::get('/orders/{order}/pdf',        [OrderExportController::class, 'retailerOrderPdf']) ->name('orders.pdf');
});

// Admin — all orders (Filament admin guard)
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin-exports/orders',              [OrderExportController::class, 'adminListing'])  ->name('admin.orders.export');
    Route::get('/admin-exports/orders/{order}/pdf',  [OrderExportController::class, 'adminOrderPdf']) ->name('admin.orders.pdf');
});

// Huashu — forHuashu() scope (Filament huashu guard)
Route::middleware(['auth:huashu'])->group(function () {
    Route::get('/huashu-exports/orders',               [OrderExportController::class, 'huashuListing'])  ->name('huashu.orders.export');
    Route::get('/huashu-exports/orders/{order}/pdf',   [OrderExportController::class, 'huashuOrderPdf']) ->name('huashu.orders.pdf');
});
