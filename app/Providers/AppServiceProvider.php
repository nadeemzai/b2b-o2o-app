<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Retailer;
use App\Models\StoreStaff;
use App\Policies\AdminRetailerPolicy;
use App\Policies\RetailerPolicy;
use App\Policies\StoreStaffPolicy;
use App\Events\OrderDelivered;
use App\Events\OrderFulfilling;
use App\Events\OrderPlaced;
use App\Events\OrderTransferred;
use App\Events\PaymentVerified;
use App\Listeners\SendOrderDeliveredNotification;
use App\Listeners\SendOrderFulfillingNotification;
use App\Listeners\SendOrderPlacedNotification;
use App\Listeners\SendOrderTransferredNotification;
use App\Listeners\SendPaymentVerifiedNotification;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
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

        // ── Event → Listener bindings ──────────────────────────────────
        Event::listen(OrderPlaced::class,      SendOrderPlacedNotification::class);
        Event::listen(PaymentVerified::class,  SendPaymentVerifiedNotification::class);
        Event::listen(OrderTransferred::class, SendOrderTransferredNotification::class);
        Event::listen(OrderFulfilling::class,  SendOrderFulfillingNotification::class);
        Event::listen(OrderDelivered::class,   SendOrderDeliveredNotification::class);

        // Allows <x-layouts.retailer> to resolve resources/views/layouts/retailer.blade.php,
        // the same file Livewire full-page components use via ->layout('layouts.retailer').
        Blade::component('layouts.retailer', 'layouts.retailer');
    }
}
