<?php

namespace App\Livewire\Public;

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.public')]
class ProductDetail extends Component
{
    public Product $product;

    public function mount(Product $product): void
    {
        abort_unless($product->is_active, 404);

        $this->product = $product->load([
            'category',
            'images',
            'variantTypes.activeOptions',
            'storePrices' => fn ($q) => $q->where('is_active', true)->orderBy('price_pkr'),
        ]);
    }

    public function render()
    {
        $related = Product::query()
            ->where('is_active', true)
            ->where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->with(['category', 'images'])
            ->addSelect([
                'min_price' => \App\Models\ProductStorePrice::selectRaw('MIN(price_pkr)')
                    ->whereColumn('product_id', 'products.id')
                    ->where('is_active', true)
                    ->limit(1),
            ])
            ->limit(8)
            ->get();

        return view('livewire.public.product-detail', [
            'related' => $related,
        ]);
    }
}
