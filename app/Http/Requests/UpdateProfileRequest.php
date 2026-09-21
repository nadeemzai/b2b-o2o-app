<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'retailer';
    }

    /**
     * Only allow the three fields the retailer may self-update.
     * Sensitive fields (cnic, ntn, strn) are immutable via this endpoint.
     */
    public function rules(): array
    {
        return [
            'business_name' => ['sometimes', 'string', 'max:255'],
            'phone'         => ['sometimes', 'string', 'max:20'],
            'address'       => ['sometimes', 'string', 'max:500'],
        ];
    }
}
