<?php

namespace App\Livewire\Public;

use App\Models\Category;
use App\Models\Product;
use App\Services\PricingService;
use Livewire\Component;
use Livewire\WithPagination;

class ProductCatalogue extends Component
{
    use WithPagination;

    public string $search     = '';
    public string $categoryId = '';
    public string $sortBy     = 'name_asc';

    public function updatingSearch(): void     { $this->resetPage(); }
    public function updatingCategoryId(): void { $this->resetPage(); }
    public function updatingSortBy(): void     { $this->resetPage(); }

    public function render(PricingService $pricing)
    {
        $query = Product::active()
            ->withPrice()
            ->with(['category'])
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

        $products = $query->paginate(24);

        // Batch commission rates — no N+1
        $categoryIds     = $products->pluck('category_id')->unique()->filter()->values()->all();
        $commissionRates = $pricing->ratesForCategories($categoryIds);

        $categories    = Category::orderBy('name')->get(['id', 'name', 'name_zh', 'slug']);
        $totalProducts = $products->total();

        return view('livewire.public.product-catalogue', [
            'products'        => $products,
            'categories'      => $categories,
            'totalProducts'   => $totalProducts,
            'commissionRates' => $commissionRates,
        ])->layout('layouts.public', ['title' => 'Product Catalogue']);
    }
}
