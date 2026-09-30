<?php

namespace App\Livewire\Retailer\Catalogue;

use App\Models\Product;
use App\Services\PricingService;
use Livewire\Attributes\Url;
use Livewire\Component;

class CompareProducts extends Component
{
    #[Url]
    public string $ids = '';

    public function removeProduct(int $productId): void
    {
        $parts = collect(explode(',', $this->ids))
            ->map(fn($id) => (int) trim($id))
            ->filter()
            ->reject(fn($id) => $id === $productId)
            ->values();

        $this->ids = $parts->implode(',');
    }

    public function render(PricingService $pricing): \Illuminate\View\View
    {
        $productIds = collect(explode(',', $this->ids))
            ->map(fn($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->take(4)
            ->values();

        $products = Product::with(['category', 'images', 'stockLevels'])
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->sortBy(fn($p) => $productIds->search($p->id))
            ->values();

        if ($products->isNotEmpty()) {
            $categoryIds = $products->pluck('category_id')->unique()->toArray();
            $rates       = $pricing->ratesForCategories($categoryIds);

            $products = $products->map(function ($product) use ($rates) {
                $rate = $rates[$product->category_id] ?? 0.0;
                $product->retailer_price = $product->huashu_base_price_pkr
                    ? round((float) $product->huashu_base_price_pkr * (1 + $rate), 2)
                    : null;
                $product->display_src = $product->primaryImage()?->display_url
                    ?? ($product->image_path ? asset('storage/' . $product->image_path) : null);
                return $product;
            });
        }

        return view('livewire.retailer.catalogue.compare-products', [
            'products'   => $products,
            'productIds' => $productIds,
        ])->layout('layouts.retailer');
    }
}
