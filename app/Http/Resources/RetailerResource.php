<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Retailer API resource.
 *
 * Exposes a safe subset of the retailers table — file paths in kyc_documents
 * are intentionally excluded from the public representation; only the
 * kyc_status and kyc_rejection_reason are surfaced to the retailer.
 *
 * Admin endpoints may extend this or access the model directly when they
 * need the raw kyc_documents paths.
 */
class RetailerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'business_name'        => $this->business_name,
            'cnic'                 => $this->cnic,
            'phone'                => $this->phone,
            'address'              => $this->address,
            'ntn'                  => $this->ntn,
            'strn'                 => $this->strn,
            'kyc_status'           => $this->kyc_status,
            'kyc_rejection_reason' => $this->kyc_rejection_reason,

            // Indicate which documents have been uploaded (without exposing paths)
            'kyc_documents_uploaded' => $this->kycDocumentsUploaded(),

            // Eager-loaded store
            'store' => $this->whenLoaded('store', fn () => [
                'id'   => $this->store->id,
                'name' => $this->store->name,
                'city' => $this->store->city,
            ]),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Returns which document keys have been uploaded, without leaking file paths.
     * e.g. { "cnic_front": true, "cnic_back": true, "business_doc": false }
     */
    private function kycDocumentsUploaded(): array
    {
        $docs = $this->kyc_documents ?? [];

        return [
            'cnic_front'   => isset($docs['cnic_front']),
            'cnic_back'    => isset($docs['cnic_back']),
            'business_doc' => isset($docs['business_doc']),
        ];
    }
}
