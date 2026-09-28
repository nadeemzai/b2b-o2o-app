<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint — anyone may attempt registration
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:254', 'unique:users,email'],
            'password'      => ['required', 'string', Password::min(8)->mixedCase()->numbers()],
            'store_id'      => ['required', 'integer', 'exists:township_stores,id'],
            'business_name' => ['required', 'string', 'max:255'],
            'cnic'          => ['required', 'string', 'regex:/^\d{5}-\d{7}-\d$/', 'unique:retailers,cnic'],
            'phone'         => ['required', 'string', 'max:20'],
            'address'       => ['required', 'string', 'max:500'],
            'ntn'           => ['nullable', 'string', 'max:20', 'unique:retailers,ntn'],
            'strn'          => ['nullable', 'string', 'max:20', 'unique:retailers,strn'],
        ];
    }

    public function messages(): array
    {
        return [
            'cnic.regex'  => 'CNIC must be in the format XXXXX-XXXXXXX-X (e.g. 35202-1234567-1).',
            'cnic.unique' => 'This CNIC is already registered.',
            'ntn.unique'  => 'This NTN number is already registered.',
            'strn.unique' => 'This STRN number is already registered.',
            'email.unique' => 'An account with this email already exists.',
        ];
    }
}
