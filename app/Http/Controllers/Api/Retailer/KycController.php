<?php

namespace App\Http\Controllers\Api\Retailer;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadKycRequest;
use App\Http\Resources\RetailerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class KycController extends Controller
{
    // ──────────────────────────────────────────────
    // POST /api/retailer/kyc/upload
    // ──────────────────────────────────────────────

    /**
     * Upload KYC documents for a retailer.
     *
     * Accepted files (multipart/form-data):
     *   - cnic_front   (image/pdf, max 5 MB, required on first submission)
     *   - cnic_back    (image/pdf, max 5 MB, required on first submission)
     *   - business_doc (image/pdf, max 5 MB, optional)
     *
     * Files are stored under:  storage/app/private/kyc/{retailer_id}/
     *
     * kyc_status is reset to 'pending' on every upload (re-submission supported).
     *
     * Middleware: auth:sanctum, role:retailer
     */
    public function upload(UploadKycRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user     = $request->user();
        $retailer = $user->retailerProfile;

        abort_unless($retailer, 403, 'No retailer profile found for this account.');

        $dir   = "kyc/{$retailer->id}";
        $paths = [];

        foreach (['cnic_front', 'cnic_back', 'business_doc'] as $field) {
            if ($request->hasFile($field)) {
                // Delete old file if it exists
                if ($retailer->kyc_documents && isset($retailer->kyc_documents[$field])) {
                    Storage::disk('private')->delete($retailer->kyc_documents[$field]);
                }

                $paths[$field] = $request->file($field)->store($dir, 'private');
            }
        }

        // Merge new paths with any previously stored paths
        $existing = $retailer->kyc_documents ?? [];
        $merged   = array_merge($existing, $paths);

        $retailer->update([
            'kyc_documents'        => $merged,
            'kyc_status'           => 'pending',
            'kyc_rejection_reason' => null,
        ]);

        return response()->json([
            'data'    => new RetailerResource($retailer->fresh()),
            'message' => 'KYC documents uploaded successfully. Awaiting admin review.',
        ]);
    }
}
