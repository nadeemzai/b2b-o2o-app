<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Mail\OrderPlacedMail;
use App\Notifications\NewOrderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderPlacedNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(OrderPlaced $event): void
    {
        $order = $event->order->loadMissing(
            'retailer.user',
            'items.product',
            'store.staff.user',
            'store.manager',
        );

        // ── 1. Email the retailer ────────────────────────────────────────
        $retailerEmail = $order->retailer?->user?->email;
        if ($retailerEmail) {
            Mail::to($retailerEmail)->send(new OrderPlacedMail($order));
        }

        // ── 2. Notify store manager (DB + email) ─────────────────────────
        $manager = $order->store?->manager;
        if ($manager) {
            $manager->notify(new NewOrderNotification($order));
        }

        // ── 3. Notify active store staff (DB + email) ────────────────────
        $staffUsers = $order->store?->staff
            ->where('is_active', true)
            ->map(fn ($s) => $s->user)
            ->filter()
            // Don't double-notify the manager if they're also in the staff table
            ->reject(fn ($u) => $manager && $u->id === $manager->id);

        foreach ($staffUsers ?? [] as $staffUser) {
            $staffUser->notify(new NewOrderNotification($order));
        }
    }
}
