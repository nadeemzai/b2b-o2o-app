<?php

namespace App\Livewire\Retailer\Orders;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.retailer')]
#[Title('My Orders')]
class OrderHistory extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $retailer = auth()->user()->retailerProfile;

        $query = Order::with(['items.product'])
            ->where('retailer_id', $retailer->id)
            ->latest();

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        $orders = $query->paginate(10);

        return view('livewire.retailer.orders.order-history', [
            'orders' => $orders,
        ]);
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            Order::STATUS_PENDING            => 'Pending',
            Order::STATUS_PREPARING          => 'Preparing',
            Order::STATUS_READY_FOR_DELIVERY => 'Ready for Delivery',
            Order::STATUS_DELIVERED          => 'Delivered',
            Order::STATUS_CANCELLED          => 'Cancelled',
            default                          => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    public function statusColor(string $status): string
    {
        return match ($status) {
            Order::STATUS_PENDING            => 'bg-yellow-100 text-yellow-800',
            Order::STATUS_PREPARING          => 'bg-purple-100 text-purple-800',
            Order::STATUS_READY_FOR_DELIVERY => 'bg-indigo-100 text-indigo-800',
            Order::STATUS_DELIVERED          => 'bg-green-100 text-green-800',
            Order::STATUS_CANCELLED          => 'bg-red-100 text-red-800',
            default                          => 'bg-gray-100 text-gray-800',
        };
    }
}
