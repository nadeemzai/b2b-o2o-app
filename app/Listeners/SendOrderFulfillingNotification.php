<?php

namespace App\Listeners;

use App\Events\OrderFulfilling;
use App\Mail\OrderFulfillingMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderFulfillingNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(OrderFulfilling $event): void
    {
        $order = $event->order->loadMissing('retailer.user');
        $email = $order->retailer?->user?->email;

        if ($email) {
            Mail::to($email)->send(new OrderFulfillingMail($order));
        }
    }
}
