<?php

namespace App\Livewire\Retailer;

use App\Models\Order;
use App\Services\CartService;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(CartService $cart)
    {
        $retailer   = auth()->user()->retailerProfile;
        $retailerId = $retailer->id;

        $baseQuery = Order::where('retailer_id', $retailerId);

        $stats = [
            'pending'     => (clone $baseQuery)->where('status', 'pending')->count(),
            'in_progress' => (clone $baseQuery)->whereIn('status', ['payment_verified', 'transferred', 'fulfilling'])->count(),
            'delivered'   => (clone $baseQuery)->where('status', 'delivered')->count(),
            'total'       => (clone $baseQuery)->count(),
        ];

        $recentOrders = Order::where('retailer_id', $retailerId)
            ->latest()
            ->limit(5)
            ->get();

        return view('livewire.retailer.dashboard', [
            'retailer'     => $retailer,
            'stats'        => $stats,
            'recentOrders' => $recentOrders,
            'cartCount'    => $cart->count(),
        ])->layout('layouts.retailer', ['title' => 'Dashboard']);
    }
}
