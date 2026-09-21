<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'status'         => $this->status,
            'total_pkr'      => (float) $this->total_pkr,
            'payment_method' => $this->payment_method,
            'collected_pkr'  => $this->collected_pkr !== null ? (float) $this->collected_pkr : null,
            'notes'          => $this->notes,

            // Retailer summary
            'retailer' => $this->when(
                $this->relationLoaded('retailer'),
                fn () => [
                    'id'            => $this->retailer->id,
                    'business_name' => $this->retailer->business_name,
                    'phone'         => $this->retailer->phone,
                ]
            ),

            // Store summary
            'store' => $this->when(
                $this->relationLoaded('store'),
                fn () => [
                    'id'   => $this->store->id,
                    'name' => $this->store->name,
                    'code' => $this->store->code,
                ]
            ),

            // Line items
            'items' => $this->when(
                $this->relationLoaded('items'),
                fn () => OrderItemResource::collection($this->items)
            ),

            // Status history (newest first)
            'status_history' => $this->when(
                $this->relationLoaded('statusHistory'),
                fn () => $this->statusHistory
                    ->sortByDesc('created_at')
                    ->values()
                    ->map(fn ($h) => [
                        'from'       => $h->from_status,
                        'to'         => $h->to_status,
                        'note'       => $h->note,
                        'changed_by' => $h->relationLoaded('changedBy') ? $h->changedBy?->name : null,
                        'at'         => $h->created_at?->toIso8601String(),
                    ])
            ),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
