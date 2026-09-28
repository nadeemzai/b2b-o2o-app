<?php

namespace App\Livewire\Public;

use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Product;
use Livewire\Component;

class Homepage extends Component
{
    public string $search = '';
    public int $perPage = 50;

    public function updatedSearch(): void
    {
        $this->perPage = 50;
    }

    public function loadMore(): void
    {
        $this->perPage += 50;
    }

    public function render()
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
            ->orderByRaw('CASE WHEN EXISTS(SELECT 1 FROM product_images WHERE product_images.product_id = products.id) OR (image_path IS NOT NULL AND image_path <> \'\') THEN 0 ELSE 1 END')
            ->latest('products.created_at');

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('products.name_en', 'like', $term)
                  ->orWhere('products.name_zh', 'like', $term);
            });
        }

        $total    = (clone $query)->count();
        $products = $query->take($this->perPage)->get();
        $hasMore  = $total > $this->perPage;

        $categories = Category::orderBy('name')->get(['id', 'name', 'name_zh', 'slug']);

        return view('livewire.public.homepage', [
            'products'    => $products,
            'categories'  => $categories,
            'sections'    => $sections,
            'newArrivals' => $newArrivals,
            'hasMore'     => $hasMore,
        ])->layout('layouts.public', ['title' => 'OZ B2B Wholesale Marketplace']);
    }
}
