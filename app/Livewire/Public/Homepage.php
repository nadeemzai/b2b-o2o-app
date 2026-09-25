<?php

namespace App\Livewire\Public;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class Homepage extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::active()
            ->withPrice()
            ->with(['category', 'images'])
            // Products with images first, then newest
            ->orderByRaw('CASE WHEN EXISTS(SELECT 1 FROM product_images WHERE product_images.product_id = products.id) OR (image_path IS NOT NULL AND image_path <> \'\') THEN 0 ELSE 1 END')
            ->latest('products.created_at');

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('products.name_en', 'like', $term)
                  ->orWhere('products.name_zh', 'like', $term);
            });
        }

        $products   = $query->paginate(24);
        $categories = Category::orderBy('name')->get(['id', 'name', 'name_zh', 'slug']);

        return view('livewire.public.homepage', [
            'products'   => $products,
            'categories' => $categories,
        ])->layout('layouts.public', ['title' => 'OZ B2B Wholesale Marketplace']);
    }
}
