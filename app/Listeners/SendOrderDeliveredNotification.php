<?php

namespace App\Listeners;

use App\Events\OrderDelivered;
use App\Mail\OrderDeliveredMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderDeliveredNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(OrderDelivered $event): void
    {
        $order = $event->order->loadMissing('retailer.user');
        $email = $order->retailer?->user?->email;

        if ($email) {
            Mail::to($email)->send(new OrderDeliveredMail($order));
        }
    }
}
