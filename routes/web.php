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
Route::get('/', ProductCatalogue::class)->name('public.catalogue');

// ── Public catalogue (same component, explicit path) ──────────
Route::get('/catalogue', ProductCatalogue::class)->name('public.catalogue.browse');

// ── Public product detail (no login required) ─────────────────
Route::get('/product/{product}', PublicProductDetail::class)->name('public.product');

// ── Retailer guest routes ──────────────────────────────────────
Route::prefix('retailer')->name('retailer.')->middleware('guest')->group(function () {
    Route::get('/login',    Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

// ── Retailer auth-only (any retailer, pending or approved) ─────
Route::prefix('retailer')->name('retailer.')->middleware(['auth', 'retailer'])->group(function () {
    Route::get('/pending', fn () => view('retailer.pending'))->name('pending');
    Route::get('/kyc', \App\Livewire\Kyc\UploadDocuments::class)->name('kyc');
});

// ── Retailer fully approved routes ────────────────────────────
Route::prefix('retailer')->name('retailer.')->middleware(['auth', 'retailer', 'retailer.approved'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/catalogue', ProductList::class)->name('catalogue');
    Route::get('/catalogue/{product}', RetailerProductDetail::class)->name('catalogue.product');
    Route::get('/cart',      CartPage::class)->name('cart');
    Route::get('/orders',    OrderHistory::class)->name('orders');
});

// ── Logout ────────────────────────────────────────────────────
Route::post('/retailer/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('retailer.login');
})->middleware('auth')->name('retailer.logout');

// ──────────────────────────────────────────────────────────────────────────
// Admin: secure KYC document viewer
// ──────────────────────────────────────────────────────────────────────────
Route::get(
    '/admin/retailers/{retailer}/kyc/{field}',
    \App\Http\Controllers\Admin\KycDocumentController::class
)->middleware(['auth'])->name('admin.kyc.document');

