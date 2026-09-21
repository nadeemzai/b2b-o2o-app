<?php

namespace App\Http\Controllers\Api\Retailer;

use App\Http\Controllers\Controller;
use App\Http\Resources\RetailerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KycController extends Controller
{
    // ──────────────────────────────────────────────
    // POST /api/retailer/kyc/upload
    // ──────────────────────────────────────────────

    /**
     * Upload KYC documents for a pending retailer.
     *
     * Accepted files (multipart/form-data):
     *   - cnic_front   (image/pdf, max 5 MB)
     *   - cnic_back    (image/pdf, max 5 MB)
     *   - business_doc (image/pdf, max 5 MB, optional — NTN/STRN certificate)
     *
     * Files are stored under:  storage/app/private/kyc/{retailer_id}/
     *
     * The retailer's kyc_status stays 'pending' until an admin approves.
     * If already 'approved' or 'rejected' (re-submission), status resets to 'pending'.
     *
     * Middleware: auth:sanctum, role:retailer
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'cnic_front'   => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'cnic_back'    => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'business_doc' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        /** @var \App\Models\User $user */
        $user     = $request->user();
        $retailer = $user->retailerProfile;

        abort_unless($retailer, 403, 'No retailer profile found for this account.');

        $dir   = "kyc/{$retailer->id}";
        $paths = [];

        foreach (['cnic_front', 'cnic_back', 'business_doc'] as $field) {
            if ($request->hasFile($field)) {
                // Delete old file if it exists
                $oldKey = "kyc_docs.{$field}";
                if ($retailer->getAttributeValue('kyc_documents') &&
                    isset($retailer->kyc_documents[$field])) {
                    Storage::disk('private')->delete($retailer->kyc_documents[$field]);
                }

                $paths[$field] = $request->file($field)->store($dir, 'private');
            }
        }

        // Merge new paths with any previously stored paths
        $existing = $retailer->kyc_documents ?? [];
        $merged   = array_merge($existing, $paths);

        $retailer->update([
            'kyc_documents' => $merged,
            'kyc_status'    => 'pending',           // reset to pending on re-upload
            'kyc_rejection_reason' => null,
        ]);

        return response()->json([
            'data'    => new RetailerResource($retailer->fresh()),
            'message' => 'KYC documents uploaded successfully. Awaiting admin review.',
        ]);
    }
}
