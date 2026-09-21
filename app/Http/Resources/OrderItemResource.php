<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'product_id'     => $this->product_id,
            'qty'            => $this->qty,
            'unit_price_pkr' => (float) $this->unit_price_pkr,
            'line_total_pkr' => (float) $this->line_total_pkr,    // DB-generated column

            'product' => $this->when(
                $this->relationLoaded('product'),
                fn () => [
                    'id'   => $this->product->id,
                    'sku'  => $this->product->sku,
                    'name' => $this->product->name,
                    'unit' => $this->product->unit,
                ]
            ),
        ];
    }
}
