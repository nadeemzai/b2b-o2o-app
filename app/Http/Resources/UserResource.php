<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'role'       => $this->role,
            'is_active'  => $this->is_active,

            // Retailer profile (loaded when role = retailer)
            'retailer' => $this->when(
                $this->relationLoaded('retailerProfile') && $this->retailerProfile,
                fn () => [
                    'id'            => $this->retailerProfile->id,
                    'store_id'      => $this->retailerProfile->store_id,
                    'business_name' => $this->retailerProfile->business_name,
                    'kyc_status'    => $this->retailerProfile->kyc_status,
                ]
            ),

            // Store staff context (loaded when role = store_staff)
            'store_staff' => $this->when(
                $this->relationLoaded('activeStoreStaff') && $this->activeStoreStaff,
                fn () => [
                    'store_id' => $this->activeStoreStaff->store_id,
                    'position' => $this->activeStoreStaff->position,
                ]
            ),

            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
