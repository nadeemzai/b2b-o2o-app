<?php

namespace App\Listeners;

use App\Events\KycApproved;
use App\Mail\KycApprovedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendKycApprovedNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(KycApproved $event): void
    {
        $retailer = $event->retailer->loadMissing('user');
        $email    = $retailer->user?->email;

        if ($email) {
            Mail::to($email)->send(new KycApprovedMail($retailer));
        }
    }
}
