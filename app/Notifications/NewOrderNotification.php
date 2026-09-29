<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $queue = 'notifications';

    public function __construct(public readonly Order $order)
    {
        //
    }

    /**
     * Channels: database (for the bell icon) + mail.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    // ──────────────────────────────────────────────────────────
    // Database payload — stored in the `notifications` table
    // ──────────────────────────────────────────────────────────

    public function toDatabase(object $notifiable): array
    {
        return [
            'order_id'      => $this->order->id,
            'retailer_name' => $this->order->retailer?->business_name ?? 'Retailer #'.$this->order->retailer_id,
            'total_pkr'     => $this->order->total_pkr,
            'item_count'    => $this->order->items->count(),
            'message'       => "New order #{$this->order->id} received from {$this->order->retailer?->business_name}",
        ];
    }

    // ──────────────────────────────────────────────────────────
    // Mail — sent to the store manager / active staff
    // ──────────────────────────────────────────────────────────

    public function toMail(object $notifiable): MailMessage
    {
        $order        = $this->order;
        $retailerName = $order->retailer?->business_name ?? 'Retailer #'.$order->retailer_id;
        $storeName    = $order->store?->name           ?? 'Your Store';

        return (new MailMessage)
            ->subject("New Order #{$order->id} — {$storeName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A new order has been placed by **{$retailerName}** and is assigned to **{$storeName}**.")
            ->line("**Order #:** #{$order->id}")
            ->line("**Items:** {$order->items->count()} line(s)")
            ->line('**Total:** PKR ' . number_format($order->total_pkr, 2))
            ->line("**Status:** Pending Payment Verification")
            ->action('View Order in Portal', url('/'))
            ->line('Please prepare for fulfilment once payment is verified.');
    }
}
