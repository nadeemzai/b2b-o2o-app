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

    public string $statusFilter    = '';
    public string $displayCurrency = 'PKR';

    /** ID of the order whose slide-over drawer is open */
    public ?int $detailOrderId = null;

    /** Which order is currently showing the inline upload panel */
    public ?int $uploadingFor = null;

    #[Validate('required|image|mimes:jpg,jpeg,png,webp|max:5120')]
    public $proofFile = null;

    // ──────────────────────────────────────────────
    // Currency toggle
    // ──────────────────────────────────────────────

    public function setCurrency(string $currency): void
    {
        if (in_array($currency, ['PKR', 'USD', 'CNY'], true)) {
            $this->displayCurrency = $currency;
        }
    }

    // ──────────────────────────────────────────────
    // Order detail drawer
    // ──────────────────────────────────────────────

    public function openDetail(int $orderId): void
    {
        $retailer = auth()->user()->retailerProfile;
        if (Order::where('id', $orderId)->where('retailer_id', $retailer->id)->exists()) {
            $this->detailOrderId = $orderId;
        }
    }

    public function closeDetail(): void
    {
        $this->detailOrderId = null;
    }

    // ──────────────────────────────────────────────
    // Proof upload
    // ──────────────────────────────────────────────

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

        $this->uploadingFor  = null;
        $this->proofFile     = null;

        // If the drawer is open for this order, refresh it
        if ($this->detailOrderId === $orderId) {
            // Livewire will re-render; detailOrder is reloaded in render()
        }

        session()->flash('proof_uploaded', $orderId);
    }

    // ──────────────────────────────────────────────
    // Re-order
    // ──────────────────────────────────────────────

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

    // ──────────────────────────────────────────────
    // Render
    // ──────────────────────────────────────────────

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

        // Load detail order separately with full relations for the drawer
        $detailOrder = $this->detailOrderId
            ? Order::with(['items.product', 'statusHistory.changedBy'])
                   ->where('retailer_id', $retailer->id)
                   ->find($this->detailOrderId)
            : null;

        return view('livewire.retailer.orders.order-history', [
            'orders'      => $orders,
            'detailOrder' => $detailOrder,
        ]);
    }

    // ──────────────────────────────────────────────
    // Display helpers
    // ──────────────────────────────────────────────

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

    public function timelineIndex(string $status): int
    {
        $map = array_keys($this->timelineSteps());
        $idx = array_search($status, $map, true);
        return $idx === false ? -1 : $idx;
    }

    /** Currency symbol for the selected display currency */
    public function currencySymbol(string $currency): string
    {
        return match ($currency) {
            'USD'   => '$',
            'CNY'   => '¥',
            default => 'PKR',
        };
    }
}
