<?php

namespace App\Livewire\Retailer;

use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Product;
use App\Services\PricingService;
use Livewire\Component;

class Homepage extends Component
{
    public int $perPage = 50;

    public function loadMore(): void
    {
        $this->perPage += 50;
    }

    public function render(PricingService $pricing)
    {
        $sections    = HomepageSection::activeSections();
        $deals       = collect();
        $newArrivals = collect();

        if ($sections['deals']) {
            $deals = Product::active()
                ->withPrice()
                ->with(['category', 'images'])
                ->where('is_deal', true)
                ->latest('products.created_at')
                ->limit(10)
                ->get();
        }

        if ($sections['new_arrivals']) {
            $newArrivals = Product::active()
                ->withPrice()
                ->with(['category', 'images'])
                ->latest('products.created_at')
                ->limit(10)
                ->get();
        }

        // Full products grid below the strips
        $allQuery    = Product::active()
            ->withPrice()
            ->with(['category', 'images'])
            ->latest('products.created_at');
        $total       = (clone $allQuery)->count();
        $allProducts = $allQuery->take($this->perPage)->get();
        $hasMore     = $total > $this->perPage;

        $categories = Category::orderBy('name')->get(['id', 'name', 'name_zh', 'slug']);

        // Batch commission rates — no N+1
        $pooled      = $deals->merge($newArrivals)->merge($allProducts)->unique('id');
        $categoryIds = $pooled->pluck('category_id')->unique()->filter()->values()->all();
        $commissionRates = $pricing->ratesForCategories($categoryIds);

        $retailerPrices = [];
        foreach ($pooled as $product) {
            $retailerPrices[$product->id] = $pricing->retailerPrice($product);
        }

        return view('livewire.retailer.homepage', [
            'categories'      => $categories,
            'deals'           => $deals,
            'newArrivals'     => $newArrivals,
            'allProducts'     => $allProducts,
            'hasMore'         => $hasMore,
            'totalAllProducts' => $total,
            'sections'        => $sections,
            'commissionRates' => $commissionRates,
            'retailerPrices'  => $retailerPrices,
        ])->layout('layouts.retailer', ['title' => 'OZ B2B — Wholesale Marketplace']);
    }
}
