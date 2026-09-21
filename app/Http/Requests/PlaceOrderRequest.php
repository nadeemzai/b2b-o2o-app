<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Policy check is still done in the controller via $this->authorize()
        // Here we just confirm the user is authenticated.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'items'              => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'        => ['required', 'integer', 'min:1', 'max:1000'],
            'notes'              => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'              => 'Order must contain at least one item.',
            'items.*.product_id.exists'   => 'One or more products do not exist.',
            'items.*.qty.min'             => 'Quantity must be at least 1.',
            'items.*.qty.max'             => 'Single-line quantity cannot exceed 1,000 units.',
        ];
    }

    /**
     * Return items array (de-duplicated by product_id, summing qty).
     */
    public function mergedItems(): array
    {
        $merged = [];
        foreach ($this->validated()['items'] as $item) {
            $pid = $item['product_id'];
            if (isset($merged[$pid])) {
                $merged[$pid]['qty'] += $item['qty'];
            } else {
                $merged[$pid] = $item;
            }
        }
        return array_values($merged);
    }
}
