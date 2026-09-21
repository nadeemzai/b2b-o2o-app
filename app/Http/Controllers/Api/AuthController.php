<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ──────────────────────────────────────────────
    // POST /api/auth/login
    // ──────────────────────────────────────────────

    /**
     * Authenticate and return a Sanctum token.
     *
     * Body: { email, password, device_name? }
     * Response: { data: { token, user } }
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Contact support.'],
            ]);
        }

        // Revoke any previous tokens for this device name (prevents orphaned tokens)
        $deviceName = $request->input('device_name', 'api');
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user'  => new UserResource($user),
            ],
        ]);
    }

    // ──────────────────────────────────────────────
    // GET /api/auth/me
    // ──────────────────────────────────────────────

    /**
     * Return the authenticated user's profile.
     *
     * Middleware: auth:sanctum
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()->load('retailerProfile', 'activeStoreStaff')),
        ]);
    }

    // ──────────────────────────────────────────────
    // POST /api/auth/logout
    // ──────────────────────────────────────────────

    /**
     * Revoke the current token.
     *
     * Middleware: auth:sanctum
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
