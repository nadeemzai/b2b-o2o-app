<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'retailer';
    }

    public function rules(): array
    {
        /** @var \App\Models\User $user */
        $user     = $this->user();
        $retailer = $user?->retailerProfile;

        // On first submission all docs are required.
        // On re-submission (docs already exist) the field becomes nullable —
        // only the files the retailer actually selects are replaced.
        $hasExisting = $retailer && ! empty($retailer->kyc_documents);

        $required = $hasExisting ? 'nullable' : 'required';

        return [
            'cnic_front'   => [$required, 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'cnic_back'    => [$required, 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'business_doc' => ['nullable',  'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'cnic_front.required' => 'A photo of the front of your CNIC is required.',
            'cnic_back.required'  => 'A photo of the back of your CNIC is required.',
            'cnic_front.mimes'    => 'CNIC front must be a JPG, PNG, or PDF file.',
            'cnic_back.mimes'     => 'CNIC back must be a JPG, PNG, or PDF file.',
            'business_doc.mimes'  => 'Business document must be a JPG, PNG, or PDF file.',
            'cnic_front.max'      => 'CNIC front must be under 5 MB.',
            'cnic_back.max'       => 'CNIC back must be under 5 MB.',
            'business_doc.max'    => 'Business document must be under 5 MB.',
        ];
    }
}
