<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'code'       => $this->code,
            'city'       => $this->city,
            'address'    => $this->address,
            'phone'      => $this->phone,
            'is_active'  => $this->is_active,

            // Staff count — only exposed when eager-loaded (admin endpoints)
            'staff_count' => $this->when(
                $this->relationLoaded('storeStaff'),
                fn () => $this->storeStaff->count()
            ),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
