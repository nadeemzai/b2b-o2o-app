<?php

namespace App\Listeners;

use App\Events\PaymentVerified;
use App\Mail\PaymentVerifiedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendPaymentVerifiedNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(PaymentVerified $event): void
    {
        $order  = $event->order->loadMissing('retailer.user');
        $email  = $order->retailer?->user?->email;

        if ($email) {
            Mail::to($email)->send(new PaymentVerifiedMail($order));
        }
    }
}
