<?php

namespace App\Http\Controllers\Api\Retailer;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\RetailerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RetailerProfileController extends Controller
{
    // ──────────────────────────────────────────────
    // GET /api/retailer/profile
    // ──────────────────────────────────────────────

    /**
     * Return the authenticated retailer's own profile.
     *
     * Middleware: auth:sanctum, role:retailer
     */
    public function show(Request $request): JsonResponse
    {
        $retailer = $request->user()->retailerProfile;

        abort_unless($retailer, 403, 'No retailer profile found for this account.');

        return response()->json([
            'data' => new RetailerResource($retailer->load('store')),
        ]);
    }

    // ──────────────────────────────────────────────
    // PATCH /api/retailer/profile
    // ──────────────────────────────────────────────

    /**
     * Update the authenticated retailer's own profile.
     *
     * Updatable fields: phone, address, business_name.
     * Sensitive fields (cnic, ntn, strn) are NOT updatable here — they require
     * admin intervention to change.
     *
     * Middleware: auth:sanctum, role:retailer
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $retailer = $request->user()->retailerProfile;

        abort_unless($retailer, 403, 'No retailer profile found for this account.');

        $validated = $request->validated();

        if (empty($validated)) {
            return response()->json([
                'data'    => new RetailerResource($retailer->load('store')),
                'message' => 'No changes provided.',
            ]);
        }

        $retailer->update($validated);

        return response()->json([
            'data'    => new RetailerResource($retailer->fresh()->load('store')),
            'message' => 'Profile updated successfully.',
        ]);
    }
}
