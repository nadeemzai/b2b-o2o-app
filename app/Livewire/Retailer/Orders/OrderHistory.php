<?php

namespace App\Livewire\Retailer\Orders;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.retailer')]
#[Title('My Orders')]
class OrderHistory extends Component
{
    use WithPagination, WithFileUploads;

    public string $statusFilter = '';

    /** Which order is currently showing the upload panel */
    public ?int $uploadingFor = null;

    #[Validate('required|image|mimes:jpg,jpeg,png,webp|max:5120')]
    public $proofFile = null;

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openUpload(int $orderId): void
    {
        $this->uploadingFor = $orderId;
        $this->proofFile    = null;
        $this->resetValidation('proofFile');
    }

    public function cancelUpload(): void
    {
        $this->uploadingFor = null;
        $this->proofFile    = null;
        $this->resetValidation('proofFile');
    }

    public function uploadProof(int $orderId): void
    {
        $this->validate(['proofFile' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120']);

        $retailer = auth()->user()->retailerProfile;
        $order    = Order::where('id', $orderId)
                         ->where('retailer_id', $retailer->id)
                         ->where('status', Order::STATUS_PENDING)
                         ->firstOrFail();

        $path = $this->proofFile->store('payment-proofs', 'public');

        $order->update(['payment_proof_path' => $path]);

        $this->uploadingFor = null;
        $this->proofFile    = null;
        session()->flash('proof_uploaded', $orderId);
    }

    /** Re-order: push items from a past order back into cart */
    public function reorder(int $orderId): void
    {
        $retailer = auth()->user()->retailerProfile;
        $order    = Order::with('items.product')
                         ->where('id', $orderId)
                         ->where('retailer_id', $retailer->id)
                         ->firstOrFail();

        /** @var \App\Services\CartService $cart */
        $cart    = app(\App\Services\CartService::class);
        /** @var \App\Services\PricingService $pricing */
        $pricing = app(\App\Services\PricingService::class);

        $added = 0;
        foreach ($order->items as $item) {
            $product = $item->product;
            if (! $product || ! $product->is_active) {
                continue;
            }

            $product->loadMissing('storePrice');
            $price = $pricing->retailerPrice($product);
            if (! $price) {
                continue;
            }

            $moq = max(1, (int) $product->moq);
            $cart->add(
                productId: $product->id,
                qty:       max($moq, (int) $item->qty),
                price:     $price,
                name:      $product->name_en,
                unit:      $product->unit,
                moq:       $moq,
            );
            $added++;
        }

        if ($added > 0) {
            $this->dispatch('cart-updated');
            session()->flash('reorder_success', "Added {$added} item(s) to your cart.");
        } else {
            session()->flash('reorder_error', 'No available items could be added from that order.');
        }
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
            'pending'          => 'Pending',
            'payment_verified' => 'Payment Verified',
            'transferred'      => 'Processing',
            'fulfilling'       => 'On Its Way',
            'delivered'        => 'Delivered',
            'cancelled'        => 'Cancelled',
            default            => ucwords(str_replace('_', ' ', $status)),
        };
    }

    public function statusColor(string $status): string
    {
        return match ($status) {
            'pending'          => 'bg-yellow-100 text-yellow-800',
            'payment_verified' => 'bg-blue-100 text-blue-800',
            'transferred'      => 'bg-indigo-100 text-indigo-800',
            'fulfilling'       => 'bg-amber-100 text-amber-800',
            'delivered'        => 'bg-green-100 text-green-800',
            'cancelled'        => 'bg-red-100 text-red-800',
            default            => 'bg-gray-100 text-gray-800',
        };
    }

    /** Returns ordered list of all statuses for the timeline */
    public function timelineSteps(): array
    {
        return [
            'pending'          => 'Order Placed',
            'payment_verified' => 'Payment Verified',
            'transferred'      => 'Processing',
            'fulfilling'       => 'On Its Way',
            'delivered'        => 'Delivered',
        ];
    }

    /** Index of a status in the timeline (cancelled sits outside) */
    public function timelineIndex(string $status): int
    {
        $map = array_keys($this->timelineSteps());
        $idx = array_search($status, $map, true);
        return $idx === false ? -1 : $idx;
    }
}
