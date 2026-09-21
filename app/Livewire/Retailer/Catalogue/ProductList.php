<?php

namespace App\Livewire\Retailer\Catalogue;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public string $search     = '';
    public string $categoryId = '';

    public function updatingSearch(): void  { $this->resetPage(); }
    public function updatingCategoryId(): void { $this->resetPage(); }

    public function addToCart(int $productId, CartService $cart): void
    {
        $storeId = auth()->user()->retailerProfile->store_id;

        $product = Product::with(['storePrices' => fn($q) => $q->where('store_id', $storeId)->where('is_active', true)])
            ->findOrFail($productId);

        $price = $product->storePrices->first()?->price_pkr ?? 0;

        $cart->add(
            productId: $productId,
            qty: 1,
            price: (float) $price,
            name: $product->name_en,
            unit: $product->unit,
        );

        session()->flash('cart_added', $product->name_en);
    }

    public function render()
    {
        $storeId = auth()->user()->retailerProfile->store_id;

        $products = Product::active()
            ->with([
                'category',
                'storePrices' => fn($q) => $q->where('store_id', $storeId)->where('is_active', true),
                'stockLevels' => fn($q) => $q->where('store_id', $storeId),
            ])
            ->whereHas('storePrices', fn($q) => $q->where('store_id', $storeId)->where('is_active', true))
            ->when($this->categoryId, fn($q) => $q->where('category_id', $this->categoryId))
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name_en', 'like', "%{$this->search}%")
                  ->orWhere('sku', 'like', "%{$this->search}%");
            }))
            ->orderBy('name_en')
            ->paginate(12);

        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('livewire.retailer.catalogue.product-list', [
            'products'   => $products,
            'categories' => $categories,
        ])->layout('layouts.retailer', ['title' => 'Catalogue']);
    }
}
