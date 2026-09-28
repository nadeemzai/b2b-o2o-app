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
            'items'                        => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id'           => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'                  => ['required', 'integer', 'min:1', 'max:1000'],
            'items.*.variant_option_id'    => ['nullable', 'integer', 'exists:product_variant_options,id'],
            'items.*.variant_label'        => ['nullable', 'string', 'max:120'],
            'notes'                        => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'                     => 'Order must contain at least one item.',
            'items.*.product_id.exists'          => 'One or more products do not exist.',
            'items.*.qty.min'                    => 'Quantity must be at least 1.',
            'items.*.qty.max'                    => 'Single-line quantity cannot exceed 1,000 units.',
            'items.*.variant_option_id.exists'   => 'One or more variant options do not exist.',
        ];
    }

    /**
     * Return items array de-duplicated by (product_id + variant_option_id).
     * Different variants of the same product are treated as separate line items.
     */
    public function mergedItems(): array
    {
        $merged = [];
        foreach ($this->validated()['items'] as $item) {
            // Composite key: product_id + variant (null variants merge together)
            $key = $item['product_id'] . '_' . ($item['variant_option_id'] ?? 'null');
            if (isset($merged[$key])) {
                $merged[$key]['qty'] += $item['qty'];
            } else {
                $merged[$key] = $item;
            }
        }
        return array_values($merged);
    }
}
