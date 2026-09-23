<?php

namespace App\Listeners;

use App\Events\OrderTransferred;
use App\Mail\OrderTransferredMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderTransferredNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(OrderTransferred $event): void
    {
        $order = $event->order->loadMissing('retailer', 'items.product');

        // Notify all active Huashu users
        $huashuEmails = User::where('role', 'huashu')
            ->where('is_active', true)
            ->pluck('email')
            ->toArray();

        if (! empty($huashuEmails)) {
            Mail::to($huashuEmails)->send(new OrderTransferredMail($order));
        }
    }
}
