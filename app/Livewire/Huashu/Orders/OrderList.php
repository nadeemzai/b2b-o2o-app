<?php

namespace App\Livewire\Huashu\Orders;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.huashu')]
#[Title('Huashu Orders')]
class OrderList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    public function updatedSearch(): void    { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

    public function render()
    {
        $orders = Order::query()
            ->with(['retailer', 'store'])
            ->forHuashu()                          // scope: transferred / fulfilling / delivered
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('id', 'like', "%{$this->search}%")
                        ->orWhereHas('retailer', fn ($r) =>
                            $r->where('business_name', 'ilike', "%{$this->search}%")
                        );
                });
            })
            ->orderByRaw("CASE status WHEN 'transferred' THEN 0 WHEN 'fulfilling' THEN 1 ELSE 2 END")
            ->orderByDesc('transferred_to_huashu_at')
            ->paginate(20);

        return view('livewire.huashu.orders.order-list', compact('orders'));
    }
}
