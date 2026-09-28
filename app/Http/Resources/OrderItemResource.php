<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'product_id'        => $this->product_id,
            'variant_option_id' => $this->variant_option_id,
            'variant_label'     => $this->variant_label,
            'qty'               => $this->qty,
            'unit_price_pkr'    => (float) $this->unit_price_pkr,
            'line_total_pkr'    => (float) $this->line_total_pkr,    // DB-generated column

            'product' => $this->when(
                $this->relationLoaded('product'),
                fn () => $this->product ? [
                    'id'      => $this->product->id,
                    'sku'     => $this->product->sku,
                    'name_en' => $this->product->name_en,
                    'name_ur' => $this->product->name_ur,
                    'unit'    => $this->product->unit,
                ] : null
            ),
        ];
    }
}
