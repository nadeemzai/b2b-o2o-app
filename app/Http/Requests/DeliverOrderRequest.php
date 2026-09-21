<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliverOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'collected_pkr' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'collected_pkr.required' => 'Please enter the COD amount collected.',
            'collected_pkr.min'      => 'Collected amount cannot be negative.',
        ];
    }
}
