<?php

namespace App\Livewire\Retailer;

use App\Models\Order;
use App\Services\CartService;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(CartService $cart)
    {
        $retailer  = auth()->user()->retailerProfile;
        $retailerId = $retailer->id;

        $stats = [
            'pending'   => Order::where('retailer_id', $retailerId)->where('status', 'pending')->count(),
            'preparing' => Order::where('retailer_id', $retailerId)->where('status', 'preparing')->count(),
            'delivered' => Order::where('retailer_id', $retailerId)->where('status', 'delivered')->count(),
            'total'     => Order::where('retailer_id', $retailerId)->count(),
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
