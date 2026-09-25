<?php

namespace App\Livewire\Retailer;

use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Product;
use App\Services\PricingService;
use Livewire\Component;
use Livewire\WithPagination;

class Homepage extends Component
{
    use WithPagination;

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
                ->limit(16)
                ->get();
        }

        if ($sections['new_arrivals']) {
            $newArrivals = Product::active()
                ->withPrice()
                ->with(['category', 'images'])
                ->latest('products.created_at')
                ->limit(16)
                ->get();
        }

        // Full products grid below the strips
        $allProducts = Product::active()
            ->withPrice()
            ->with(['category', 'images'])
            ->latest('products.created_at')
            ->paginate(24);

        $categories = Category::orderBy('name')->get(['id', 'name', 'name_zh', 'slug']);

        // Batch commission rates — no N+1
        $pooled      = $deals->merge($newArrivals)->merge($allProducts->getCollection())->unique('id');
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
            'sections'        => $sections,
            'commissionRates' => $commissionRates,
            'retailerPrices'  => $retailerPrices,
        ])->layout('layouts.retailer', ['title' => 'OZ B2B — Wholesale Marketplace']);
    }
}
