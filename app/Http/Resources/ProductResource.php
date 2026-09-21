<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // First loaded store price and stock level (the controller pre-filters by store)
        $storePrice = $this->whenLoaded('storePrices', fn () => $this->storePrices->first());
        $stockLevel = $this->whenLoaded('stockLevels', fn () => $this->stockLevels->first());

        return [
            'id'          => $this->id,
            'sku'         => $this->sku,
            'name'        => $this->name,
            'description' => $this->description,
            'unit'        => $this->unit,
            'image_path'  => $this->image_path,
            'is_active'   => $this->is_active,

            'categories' => $this->whenLoaded('categories', fn () =>
                $this->categories->map(fn ($c) => [
                    'id'   => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                ])
            ),

            // Pricing for the retailer's store
            'price_pkr'  => $storePrice ? (float) $storePrice->price_pkr : null,

            // Live availability
            'stock' => $stockLevel ? [
                'qty_on_hand'   => $stockLevel->qty_on_hand,
                'qty_reserved'  => $stockLevel->qty_reserved,
                'qty_available' => $stockLevel->qty_available,
            ] : null,

            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
