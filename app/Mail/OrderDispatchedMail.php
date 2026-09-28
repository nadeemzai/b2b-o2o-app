<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDispatchedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order) {}

    public function envelope(): Envelope
    {
        $orderId = str_pad($this->order->id, 6, '0', STR_PAD_LEFT);

        return new Envelope(
            subject: "Order #{$orderId} Dispatched — En Route to OZ Store",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.dispatched',
        );
    }
}
