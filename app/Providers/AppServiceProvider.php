<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Retailer;
use App\Models\StoreStaff;
use App\Policies\AdminRetailerPolicy;
use App\Policies\RetailerPolicy;
use App\Policies\StoreStaffPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Policy registrations ───────────────────────────────────────
        //
        // RetailerPolicy  : gates retailer-owned resources (orders, etc.)
        // StoreStaffPolicy: gates store-staff actions on orders/stock
        // AdminRetailerPolicy: gates admin KYC actions (list / approve / reject)
        Gate::policy(Order::class,      RetailerPolicy::class);
        Gate::policy(StoreStaff::class, StoreStaffPolicy::class);
        Gate::policy(Retailer::class,   AdminRetailerPolicy::class);

        // Allows <x-layouts.retailer> to resolve resources/views/layouts/retailer.blade.php,
        // the same file Livewire full-page components use via ->layout('layouts.retailer').
        Blade::component('layouts.retailer', 'layouts.retailer');
    }
}
