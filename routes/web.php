<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Retailer\Auth\Login;
use App\Livewire\Retailer\Auth\Register;
use App\Livewire\Retailer\Dashboard;
use App\Livewire\Retailer\Catalogue\ProductList;
use App\Livewire\Retailer\Cart\CartPage;
use App\Livewire\Retailer\Orders\OrderHistory;

Route::get('/', fn () => redirect()->route('retailer.login'));

// ── Retailer guest routes ──────────────────────────────────────
Route::prefix('retailer')->name('retailer.')->middleware('guest')->group(function () {
    Route::get('/login',    Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

// ── Retailer auth-only (any retailer, pending or approved) ─────
Route::prefix('retailer')->name('retailer.')->middleware(['auth', 'retailer'])->group(function () {
    Route::get('/pending', fn () => view('retailer.pending'))->name('pending');
});

// ── Retailer fully approved routes ────────────────────────────
Route::prefix('retailer')->name('retailer.')->middleware(['auth', 'retailer', 'retailer.approved'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/catalogue', ProductList::class)->name('catalogue');
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
