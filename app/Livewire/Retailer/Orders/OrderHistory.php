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
            'pending'    => 'Pending',
            'confirmed'  => 'Confirmed',
            'preparing'  => 'Preparing',
            'dispatched' => 'Dispatched',
            'delivered'  => 'Delivered',
            'cancelled'  => 'Cancelled',
            default      => ucfirst($status),
        };
    }

    public function statusColor(string $status): string
    {
        return match ($status) {
            'pending'    => 'bg-yellow-100 text-yellow-800',
            'confirmed'  => 'bg-blue-100 text-blue-800',
            'preparing'  => 'bg-purple-100 text-purple-800',
            'dispatched' => 'bg-indigo-100 text-indigo-800',
            'delivered'  => 'bg-green-100 text-green-800',
            'cancelled'  => 'bg-red-100 text-red-800',
            default      => 'bg-gray-100 text-gray-800',
        };
    }
}
