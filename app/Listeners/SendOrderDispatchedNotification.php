<?php

namespace App\Listeners;

use App\Events\OrderDispatched;
use App\Mail\OrderDispatchedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderDispatchedNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(OrderDispatched $event): void
    {
        $order = $event->order->loadMissing('retailer.user');
        $email = $order->retailer?->user?->email;

        if ($email) {
            Mail::to($email)->send(new OrderDispatchedMail($order));
        }
    }
}
