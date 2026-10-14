<?php

namespace App\Listeners;

use App\Events\KycRejected;
use App\Mail\KycRejectedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendKycRejectedNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(KycRejected $event): void
    {
        $retailer = $event->retailer->loadMissing('user');
        $email    = $retailer->user?->email;

        if ($email) {
            Mail::to($email)->send(new KycRejectedMail($retailer, $event->reason));
        }
    }
}
