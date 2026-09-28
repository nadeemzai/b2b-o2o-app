<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\RetailerResource;
use App\Models\Retailer;
use App\Models\TownshipStore;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    // ──────────────────────────────────────────────
    // POST /api/auth/register
    // ──────────────────────────────────────────────

    /**
     * Retailer self-registration.
     *
     * Creates a User (role=retailer) + Retailer profile in one atomic transaction.
     * KYC status starts as 'pending' — retailer cannot place orders until approved.
     *
     * Body:
     * {
     *   "name":          "Ahmed Khan",
     *   "email":         "ahmed@example.com",
     *   "password":      "Secret@123",
     *   "store_id":      1,
     *   "business_name": "Ahmed General Store",
     *   "cnic":          "35202-1234567-1",
     *   "phone":         "0321-4567890",
     *   "address":       "Shop 5, Model Town, Lahore",
     *   "ntn":           null,
     *   "strn":          null
     * }
     *
     * Response 201: { data: { token, user, retailer } }
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Confirm the chosen store is active
        $store = TownshipStore::where('id', $validated['store_id'])
            ->where('is_active', true)
            ->firstOrFail();

        [$user, $retailer, $token] = DB::transaction(function () use ($validated, $store) {

            $user = User::create([
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'password'  => Hash::make($validated['password']),
                'role'      => 'retailer',
                'is_active' => true,
            ]);

            $retailer = Retailer::create([
                'user_id'       => $user->id,
                'store_id'      => $store->id,
                'business_name' => $validated['business_name'],
                'cnic'          => $validated['cnic'],
                'ntn'           => $validated['ntn'] ?? null,
                'strn'          => $validated['strn'] ?? null,
                'phone'         => $validated['phone'],
                'address'       => $validated['address'],
                'kyc_status'    => 'pending',
            ]);

            $token = $user->createToken('api')->plainTextToken;

            return [$user, $retailer, $token];
        });

        return response()->json([
            'data' => [
                'token'   => $token,
                'user'    => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                ],
                'retailer' => new RetailerResource($retailer->load('store')),
            ],
            'message' => 'Registration successful. Your account is pending KYC approval.',
        ], 201);
    }
}
