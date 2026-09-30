<?php

namespace App\Livewire\Public;

use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Product;
use App\Services\PricingService;
use Livewire\Component;
use Livewire\Attributes\Url;


class ProductCatalogue extends Component
{

    #[Url]
    public string $search     = '';
    #[Url(as: 'category')]
    public string $categoryId = '';
    public string $sortBy     = 'name_asc';
    public int    $perPage   = 50;

    public function updatingSearch(): void     { $this->perPage = 50; }
    public function updatingCategoryId(): void { $this->perPage = 50; }
    public function updatingSortBy(): void     { $this->perPage = 50; }
    public function loadMore(): void { $this->perPage += 50; }

    public function render(PricingService $pricing)
    {
        $sections = HomepageSection::activeSections();

        $newArrivals = collect();
        if ($sections['new_arrivals']) {
            $newArrivals = Product::active()
                ->withPrice()
                ->with(['category', 'images'])
                ->latest('products.created_at')
                ->limit(10)
                ->get();
        }

        $query = Product::active()
            ->withPrice()
            ->with(['category', 'images'])
            ->when($this->categoryId, fn($q) => $q->where('category_id', $this->categoryId))
            ->when($this->search, function ($q) {
                // Support comma-separated keyword lists (from image search)
                $terms = array_filter(array_map('trim', explode(',', $this->search)));
                $q->where(function ($q) use ($terms) {
                    foreach ($terms as $term) {
                        $q->orWhere('name_en',        'ilike', "%{$term}%")
                          ->orWhere('name_ur',         'ilike', "%{$term}%")
                          ->orWhere('description_en',  'ilike', "%{$term}%")
                          ->orWhere('sku',              'ilike', "%{$term}%");
                    }
                });
            });

        match ($this->sortBy) {
            'price_asc'  => $query->orderBy('huashu_base_price_pkr'),
            'price_desc' => $query->orderByDesc('huashu_base_price_pkr'),
            'newest'     => $query->latest('products.created_at'),
            default      => $query->orderBy('name_en'),
        };

        $total    = (clone $query)->count();
        $products = $query->take($this->perPage)->get();
        $hasMore  = $total > $this->perPage;

        // Batch commission rates — no N+1
        $categoryIds     = $products->pluck('category_id')->unique()->filter()->values()->all();
        $commissionRates = $pricing->ratesForCategories($categoryIds);

        $categories    = Category::orderBy('name')->get(['id', 'name', 'name_zh', 'slug']);
        $totalProducts = $total;

        return view('livewire.public.product-catalogue', [
            'sections'    => $sections,
            'newArrivals' => $newArrivals,
            'products'        => $products,
            'categories'      => $categories,
            'totalProducts'   => $totalProducts,
            'commissionRates' => $commissionRates,
            'hasMore'         => $hasMore,
            'productRoute'    => auth('retailer')->check() ? 'retailer.catalogue.product' : 'public.product',
        ])->layout('layouts.public', ['title' => 'Product Catalogue']);
    }
}
