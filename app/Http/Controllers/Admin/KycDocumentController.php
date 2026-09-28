<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Retailer;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KycDocumentController extends Controller
{
    private const ALLOWED_FIELDS = ['cnic_front', 'cnic_back', 'business_doc'];

    public function __invoke(Retailer $retailer, string $field): StreamedResponse
    {
        // Only admin and store_staff may view KYC documents
        abort_if(auth()->user()->role === 'retailer', 403);

        abort_unless(in_array($field, self::ALLOWED_FIELDS, true), 404);

        $docs = $retailer->kyc_documents ?? [];
        $path = $docs[$field] ?? null;

        abort_unless($path && Storage::disk('kyc')->exists($path), 404);

        return Storage::disk('kyc')->response($path);
    }
}
