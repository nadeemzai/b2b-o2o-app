<?php

namespace App\Http\Controllers\Api\Retailer;

use App\Http\Controllers\Controller;
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
     *
     * Response 200:
     * {
     *   "data": { ...RetailerResource }
     * }
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
     * Sensitive fields (cnic, ntn, strn) are NOT updatable here — they were
     * captured at registration and require admin intervention to change.
     *
     * Middleware: auth:sanctum, role:retailer
     *
     * Body (all optional):
     * {
     *   "business_name": "Ahmed General Store (New Branch)",
     *   "phone":         "0321-9876543",
     *   "address":       "Shop 12, Model Town, Lahore"
     * }
     *
     * Response 200:
     * {
     *   "data":    { ...RetailerResource },
     *   "message": "Profile updated successfully."
     * }
     */
    public function update(Request $request): JsonResponse
    {
        $retailer = $request->user()->retailerProfile;

        abort_unless($retailer, 403, 'No retailer profile found for this account.');

        $validated = $request->validate([
            'business_name' => ['sometimes', 'string', 'max:255'],
            'phone'         => ['sometimes', 'string', 'max:20'],
            'address'       => ['sometimes', 'string', 'max:500'],
        ]);

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
