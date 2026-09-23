<?php

namespace App\Livewire\Retailer\Catalogue;

use App\Models\Product;
use App\Models\ProductVariantOption;
use App\Services\CartService;
use App\Services\PricingService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.retailer')]
class ProductDetail extends Component
{
    public Product $product;
    public int $qty = 1;

    public ?float $price      = null;
    public int    $moq        = 1;
    public int    $available  = 0;
    public bool   $stockTracked = false;

    /**
     * Variant qty inputs: keyed by ProductVariantOption id.
     * e.g.  ['3' => 5, '7' => 2]  (user wants 5 of option #3, 2 of option #7)
     * Only populated when the product has active variant types.
     *
     * @var array<string, int>
     */
    public array $variantQtys = [];

    /**
     * True when the product has at least one variant type with active options.
     */
    public bool $hasVariants = false;

    public function mount(Product $product, PricingService $pricing): void
    {
        abort_unless($product->is_active, 404);

        $storeId = auth()->user()->retailerProfile->store_id;

        $this->product = $product->load(['category', 'variantTypes.activeOptions']);

        // Check for variants
        $this->hasVariants = $this->product->variantTypes
            ->filter(fn ($t) => $t->activeOptions->isNotEmpty())
            ->isNotEmpty();

        // Retailer price via PricingService
        $this->price = $pricing->retailerPrice($product);
        $this->moq   = max(1, (int) $product->moq);
        $this->qty   = $this->moq; // Start qty at MOQ

        // Initialise variant qtys at 0 so wire:model binds cleanly
        if ($this->hasVariants) {
            foreach ($this->product->variantTypes as $type) {
                foreach ($type->activeOptions as $option) {
                    $this->variantQtys[(string) $option->id] = 0;
                }
            }
        }

        // Stock for this retailer's store
        $stock = $product->stockLevels()
            ->where('store_id', $storeId)
            ->first();

        $this->stockTracked = $stock !== null;
        $this->available    = $stock ? max(0, $stock->qty_on_hand - $stock->qty_reserved) : 0;
    }

    // ──────────────────────────────────────────────
    // Adding to cart
    // ──────────────────────────────────────────────

    public function addToCart(CartService $cart): void
    {
        if (! $this->price) {
            return;
        }

        if ($this->hasVariants) {
            $this->addVariantsToCart($cart);
        } else {
            $this->addSimpleToCart($cart);
        }
    }

    private function addSimpleToCart(CartService $cart): void
    {
        // Block only when stock IS tracked and we don't have enough
        if ($this->stockTracked && $this->available < $this->moq) {
            return;
        }

        $safeQty = $this->stockTracked
            ? min($this->qty, $this->available)
            : $this->qty;

        $safeQty = max($this->moq, $safeQty);

        $cart->add(
            productId: $this->product->id,
            qty:       $safeQty,
            price:     $this->price,
            name:      $this->product->name_en,
            unit:      $this->product->unit,
            moq:       $this->moq,
        );

        session()->flash('cart_added', $this->product->name_en);
        $this->dispatch('cart-updated');
    }

    private function addVariantsToCart(CartService $cart): void
    {
        $added = 0;

        foreach ($this->variantQtys as $optionId => $qty) {
            $qty = (int) $qty;
            if ($qty <= 0) {
                continue;
            }

            /** @var ProductVariantOption|null $option */
            $option = $this->product->variantTypes
                ->flatMap(fn ($t) => $t->activeOptions)
                ->firstWhere('id', (int) $optionId);

            if (! $option) {
                continue;
            }

            // Price adjustment per option
            $adjustedPrice = $this->price + (float) $option->price_adjustment_pkr;

            $cart->addVariant(
                productId:       $this->product->id,
                variantOptionId: $option->id,
                variantLabel:    $option->label(),
                qty:             $qty,
                price:           $adjustedPrice,
                name:            $this->product->name_en,
                unit:            $this->product->unit,
                moq:             1,  // Per-variant lines use qty=1 as minimum
            );

            $added++;
        }

        if ($added > 0) {
            session()->flash('cart_added', $this->product->name_en . ' (variants)');
            $this->dispatch('cart-updated');
        }
    }

    public function incrementVariant(int $optionId): void
    {
        $key = (string) $optionId;
        $this->variantQtys[$key] = (int) ($this->variantQtys[$key] ?? 0) + 1;
    }

    public function decrementVariant(int $optionId): void
    {
        $key = (string) $optionId;
        $current = (int) ($this->variantQtys[$key] ?? 0);
        $this->variantQtys[$key] = max(0, $current - 1);
    }

    public function updatedQty(string $value): void
    {
        $int = (int) $value;
        $max = $this->stockTracked ? max($this->moq, $this->available) : 9999;
        $this->qty = max($this->moq, min($int, $max));
    }

    public function render(PricingService $pricing)
    {
        $related = Product::query()
            ->active()
            ->withPrice()
            ->where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->with(['category'])
            ->limit(8)
            ->get();

        $catIds          = $related->pluck('category_id')->unique()->filter()->values()->all();
        $commissionRates = $pricing->ratesForCategories($catIds);

        return view('livewire.retailer.catalogue.product-detail', [
            'related'         => $related,
            'commissionRates' => $commissionRates,
        ]);
    }
}
