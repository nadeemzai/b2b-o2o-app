<?php

namespace App\Livewire\Retailer\Catalogue;

use App\Models\Product;
use App\Models\ProductVariantOption;
use App\Services\CartService;
use App\Services\PricingService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\ProductReview;
use App\Models\AppSetting;

#[Layout('layouts.retailer')]
class ProductDetail extends Component
{
    public Product $product;
    public int $qty = 1;

    public ?float $price      = null;
    public int    $moq        = 1;
    public int    $available  = 0;
    public bool   $stockTracked = false;
    /** @var array<int, array{min_qty:int,max_qty:int|null,price_pkr:float,label:string|null}> */
    public array  $priceTiers   = [];

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

    // ── Review form ──────────────────────────────────────────────────────
    #[Rule('required|integer|between:1,5')]
    public int $reviewRating = 0;

    #[Rule('nullable|string|max:120')]
    public ?string $reviewTitle = null;

    #[Rule('required|string|min:10|max:1000')]
    public string $reviewBody = '';

    public bool $reviewSubmitted     = false;
    /** How many reviews THIS retailer has left for this product. */
    public int  $retailerReviewCount = 0;
    /** Admin-configured max reviews per product (from app_settings). */
    public int  $reviewLimit         = 3;

    public function mount(Product $product, PricingService $pricing): void
    {
        abort_unless($product->is_active, 404);

        $storeId = auth('retailer')->user()->retailerProfile->store_id;

        $this->product = $product->load(['category', 'variantTypes.activeOptions', 'images', 'priceTiers']);

        // Check for variants
        $this->hasVariants = $this->product->variantTypes
            ->filter(fn ($t) => $t->activeOptions->isNotEmpty())
            ->isNotEmpty();

        // Retailer price via PricingService (tier-aware)
        $this->moq        = max(1, (int) $product->moq);
        $this->qty        = 1; // Show entry-tier price on load; MOQ enforced at add-to-cart
        $this->priceTiers = $pricing->tiersArray($product);
        $this->price      = $pricing->tierPrice($product, 1)
                         ?? $pricing->retailerPrice($product);

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

        // Load admin-configured review limit and count this retailer's existing reviews
        $this->reviewLimit = AppSetting::getInt('max_reviews_per_product', 3);

        $userId = auth('retailer')->id();
        try {
            $this->retailerReviewCount = $userId
                ? (int) ProductReview::where('product_id', $product->id)
                                     ->where('user_id', $userId)
                                     ->count()
                : 0;
        } catch (\Throwable $e) {
            // product_reviews table may not be migrated yet — fail gracefully
            Log::warning('[ProductDetail] retailerReviewCount query failed: ' . $e->getMessage());
            $this->retailerReviewCount = 0;
        }
    }

    // ──────────────────────────────────────────────
    // Adding to cart
    // ──────────────────────────────────────────────

    public function addToCart(CartService $cart, PricingService $pricing): void
    {
        if (! $this->price) {
            return;
        }

        if ($this->hasVariants) {
            $this->addVariantsToCart($cart, $pricing);
        } else {
            $this->addSimpleToCart($cart, $pricing);
        }
    }

    private function addSimpleToCart(CartService $cart, PricingService $pricing): void
    {
        // Block only when stock IS tracked and we don't have enough
        if ($this->stockTracked && $this->available < $this->moq) {
            return;
        }

        $safeQty = $this->stockTracked
            ? min($this->qty, $this->available)
            : $this->qty;

        $safeQty = max($this->moq, $safeQty);

        // Resolve tier-aware unit price at the actual qty being ordered
        $unitPrice = $pricing->tierPrice($this->product, $safeQty) ?? $this->price;

        $cart->add(
            productId: $this->product->id,
            qty:       $safeQty,
            price:     $unitPrice,
            name:      $this->product->name_en,
            unit:      $this->product->unit,
            moq:       $this->moq,
        );

        session()->flash('cart_added', $this->product->name_en);
        $this->dispatch('cart-updated');
    }

    private function addVariantsToCart(CartService $cart, PricingService $pricing): void
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

            // Tier-aware base price at this variant line qty, plus any option adjustment
            $basePrice     = $pricing->tierPrice($this->product, $qty) ?? $this->price;
            $adjustedPrice = $basePrice + (float) $option->price_adjustment_pkr;

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

    // ── Review submission ────────────────────────────────────────────────

    public function submitReview(): void
    {
        // Block if the retailer has already hit the configured limit
        if ($this->retailerReviewCount >= $this->reviewLimit) {
            return;
        }

        $this->validate();

        $user    = auth('retailer')->user();
        $profile = $user->retailerProfile;

        ProductReview::create([
            'product_id'        => $this->product->id,
            'user_id'           => $user->id,
            'reviewer_name'     => $profile->business_name ?? $user->name,
            'reviewer_location' => $profile->store?->city ?? null,
            'rating'            => $this->reviewRating,
            'title'             => $this->reviewTitle ?: null,
            'body'              => $this->reviewBody,
            'verified_purchase' => true,
        ]);

        $this->retailerReviewCount++;
        $this->reviewSubmitted = true;
        $this->reviewRating    = 0;
        $this->reviewTitle     = null;
        $this->reviewBody      = '';
    }

    public function render(PricingService $pricing)
    {
        $related = Product::query()
            ->active()
            ->withPrice()
            ->where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->with(['category', 'images'])
            ->limit(8)
            ->get();

        $catIds          = $related->pluck('category_id')->unique()->filter()->values()->all();
        $commissionRates = $pricing->ratesForCategories($catIds);

        return view('livewire.retailer.catalogue.product-detail', [
            'related'         => $related,
            'commissionRates' => $commissionRates,
            'showStockBadge'  => \App\Models\HomepageSection::activeSections()['show_stock_badge'],
        ]);
    }
}
