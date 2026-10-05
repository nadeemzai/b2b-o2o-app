<?php

namespace App\Livewire\Retailer\Catalogue;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use App\Services\PricingService;
use Livewire\Attributes\Url;
use Livewire\Component;

class ProductList extends Component
{
    #[Url]
    public string $search     = '';
    #[Url(as: 'category')]
    public string $categoryId = '';
    #[Url]
    public string $sortBy     = 'name_asc';
    public int $perPage       = 50;

    public function updatingSearch(): void     { $this->perPage = 50; }
    public function updatingCategoryId(): void { $this->perPage = 50; }
    public function updatingSortBy(): void     { $this->perPage = 50; }

    public function loadMore(): void
    {
        $this->perPage += 50;
    }

    public function addToCart(int $productId, CartService $cart, PricingService $pricing): void
    {
        $product = Product::withPrice()->findOrFail($productId);

        $price = $pricing->retailerPrice($product);
        if (! $price) {
            return;
        }

        $moq = max(1, (int) $product->moq);

        $cart->add(
            productId: $productId,
            qty:       $moq,
            price:     $price,
            name:      $product->name_en,
            unit:      $product->unit,
            moq:       $moq,
        );

        $this->dispatch('cart-updated');
        session()->flash('cart_added', $product->name_en);
    }

    public function render(PricingService $pricing)
    {
        $storeId = auth('retailer')->user()->retailerProfile->store_id;

        $query = Product::active()
            ->withPrice()
            ->with([
                'category',
                'images',
                'stockLevels' => fn($q) => $q->where('store_id', $storeId),
                'priceTiers',
            ])
            ->when($this->categoryId, fn($q) => $q->where('category_id', $this->categoryId))
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name_en', 'like', "%{$this->search}%")
                  ->orWhere('sku', 'like', "%{$this->search}%");
            }));

        match ($this->sortBy) {
            'price_asc'  => $query->orderBy('huashu_base_price_pkr'),
            'price_desc' => $query->orderByDesc('huashu_base_price_pkr'),
            'newest'     => $query->latest('products.created_at'),
            default      => $query->orderBy('name_en'),
        };

        $total    = (clone $query)->count();
        $products = $query->take($this->perPage)->get();
        $hasMore  = $total > $this->perPage;

        // Batch-load commission rates for all categories on this page (no N+1)
        $categoryIds     = $products->pluck('category_id')->unique()->filter()->values()->all();
        $commissionRates = $pricing->ratesForCategories($categoryIds);

        $categories   = Category::orderBy('name')->get(['id', 'name', 'name_zh', 'slug']);
        $totalProducts = $total;

        return view('livewire.retailer.catalogue.product-list', [
            'products'        => $products,
            'categories'      => $categories,
            'totalProducts'   => $totalProducts,
            'hasMore'         => $hasMore,
            'commissionRates' => $commissionRates,
            'showStockBadge'  => \App\Models\HomepageSection::activeSections()['show_stock_badge'],
        ])->layout('layouts.retailer', ['title' => 'Catalogue']);
    }
}
