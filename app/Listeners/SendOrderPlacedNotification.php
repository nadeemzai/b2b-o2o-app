<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Mail\OrderPlacedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderPlacedNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(OrderPlaced $event): void
    {
        $order   = $event->order->loadMissing('retailer.user', 'items.product');
        $email   = $order->retailer?->user?->email;

        if ($email) {
            Mail::to($email)->send(new OrderPlacedMail($order));
        }
    }
}
