<?php

namespace App\Livewire\Retailer\Orders;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.retailer')]
class OrderConfirmation extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        // Ensure this order belongs to the authenticated retailer
        $retailer = Auth::guard('retailer')->user()?->retailerProfile;

        if (! $retailer || $order->retailer_id !== $retailer->id) {
            abort(403);
        }

        $this->order = $order->loadMissing('items.product', 'store');
    }

    public function render()
    {
        return view('livewire.retailer.orders.order-confirmation');
    }
}
