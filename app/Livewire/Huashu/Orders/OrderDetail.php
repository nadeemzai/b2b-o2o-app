<?php

namespace App\Livewire\Huashu\Orders;

use App\Models\Order;
use App\Services\OrderService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.huashu')]
#[Title('Order Detail')]
class OrderDetail extends Component
{
    public Order $order;

    public string $huashuRef = '';
    public bool   $showRefForm = false;

    public function mount(Order $order): void
    {
        // Huashu can only see orders that have been transferred
        abort_unless($order->isTransferred(), 403);

        $this->order     = $order;
        $this->huashuRef = $order->huashu_ref ?? '';
    }

    // ── Save Huashu ref ────────────────────────────────────────────────
    public function saveRef(): void
    {
        $this->validate(['huashuRef' => 'required|string|max:80']);

        $this->order->update(['huashu_ref' => trim($this->huashuRef)]);
        $this->order->refresh();
        $this->showRefForm = false;

        session()->flash('success', 'Reference number saved.');
    }

    // ── Mark Fulfilling ────────────────────────────────────────────────
    public function markFulfilling(): void
    {
        abort_unless($this->order->canMarkFulfilling(), 422);

        app(OrderService::class)->markFulfilling(
            $this->order,
            auth()->id(),
            $this->huashuRef ?: null,
        );

        $this->order->refresh();
        session()->flash('success', 'Order marked as fulfilling.');
    }

    // ── Mark Delivered ─────────────────────────────────────────────────
    public function markDelivered(): void
    {
        abort_unless($this->order->canMarkDelivered(), 422);

        app(OrderService::class)->deliverOrder(
            $this->order,
            (float) $this->order->total_pkr,  // Huashu records at order total; OZ handles actual COD
            auth()->id(),
        );

        $this->order->refresh();
        session()->flash('success', 'Order marked as delivered.');
    }

    public function render()
    {
        return view('livewire.huashu.orders.order-detail', [
            'order' => $this->order->load('items.product', 'statusHistory'),
        ]);
    }
}
