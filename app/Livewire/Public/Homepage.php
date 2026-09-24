<?php

namespace App\Livewire\Public;

use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Product;
use App\Services\PricingService;
use Livewire\Component;

class Homepage extends Component
{
    public function render(PricingService $pricing)
    {
        $sections = HomepageSection::activeSections();

        $deals       = collect();
        $newArrivals = collect();

        if ($sections['deals']) {
            $deals = Product::active()
                ->withPrice()
                ->with(['category'])
                ->where('is_deal', true)
                ->latest('products.created_at')
                ->limit(4)
                ->get();
        }

        if ($sections['new_arrivals']) {
            $newArrivals = Product::active()
                ->withPrice()
                ->with(['category'])
                ->latest('products.created_at')
                ->limit(4)
                ->get();
        }

        $categories = Category::orderBy('name')->get(['id', 'name', 'name_zh', 'slug']);

        // Batch commission rates for display pricing
        $allProducts = $deals->merge($newArrivals);
        $categoryIds = $allProducts->pluck('category_id')->unique()->filter()->values()->all();
        $commissionRates = $pricing->ratesForCategories($categoryIds);

        return view('livewire.public.homepage', [
            'categories'      => $categories,
            'deals'           => $deals,
            'newArrivals'     => $newArrivals,
            'sections'        => $sections,
            'commissionRates' => $commissionRates,
        ])->layout('layouts.public', ['title' => 'OZ B2B Wholesale Marketplace']);
    }
}
