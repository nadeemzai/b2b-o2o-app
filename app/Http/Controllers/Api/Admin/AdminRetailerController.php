<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RetailerResource;
use App\Models\Retailer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminRetailerController extends Controller
{
    // ──────────────────────────────────────────────
    // GET /api/admin/retailers
    // ──────────────────────────────────────────────

    /**
     * List retailers — primarily used as the KYC review queue.
     *
     * Query params (all optional):
     *   - kyc_status  filter by status: pending | approved | rejected
     *   - search      partial match on business_name, cnic, or phone
     *   - per_page    default 20
     *
     * Middleware: auth:sanctum, role:admin
     *
     * Response 200:
     * {
     *   "data":  [ ...RetailerResource ],
     *   "meta":  { current_page, last_page, total, per_page }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Retailer::class);

        $request->validate([
            'kyc_status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'search'     => ['nullable', 'string', 'max:100'],
            'per_page'   => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Retailer::with(['user', 'store'])
            ->when($request->kyc_status, fn ($q, $s) => $q->where('kyc_status', $s))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('business_name', 'ilike', "%{$search}%")
                          ->orWhere('cnic',          'ilike', "%{$search}%")
                          ->orWhere('phone',         'ilike', "%{$search}%");
                });
            })
            ->latest();

        $paginator = $query->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => RetailerResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
            ],
        ]);
    }

    // ──────────────────────────────────────────────
    // POST /api/admin/retailers/{retailer}/approve
    // ──────────────────────────────────────────────

    /**
     * Approve a retailer's KYC submission.
     *
     * Sets kyc_status = 'approved' and clears any prior rejection reason.
     * Idempotent — approving an already-approved retailer returns 200.
     *
     * Middleware: auth:sanctum, role:admin
     *
     * Response 200:
     * {
     *   "data":    { ...RetailerResource },
     *   "message": "Retailer approved successfully."
     * }
     */
    public function approve(Request $request, Retailer $retailer): JsonResponse
    {
        $this->authorize('approve', $retailer);

        $retailer->update([
            'kyc_status'           => 'approved',
            'kyc_rejection_reason' => null,
        ]);

        return response()->json([
            'data'    => new RetailerResource($retailer->fresh()->load(['user', 'store'])),
            'message' => 'Retailer approved successfully.',
        ]);
    }

    // ──────────────────────────────────────────────
    // POST /api/admin/retailers/{retailer}/reject
    // ──────────────────────────────────────────────

    /**
     * Reject a retailer's KYC submission.
     *
     * Sets kyc_status = 'rejected' and records the rejection reason so the
     * retailer can see why and re-upload corrected documents.
     *
     * Middleware: auth:sanctum, role:admin
     *
     * Body:
     * {
     *   "reason": "CNIC image is blurry — please re-upload a clear scan."
     * }
     *
     * Response 200:
     * {
     *   "data":    { ...RetailerResource },
     *   "message": "Retailer rejected."
     * }
     */
    public function reject(Request $request, Retailer $retailer): JsonResponse
    {
        $this->authorize('reject', $retailer);

        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $retailer->update([
            'kyc_status'           => 'rejected',
            'kyc_rejection_reason' => $request->reason,
        ]);

        return response()->json([
            'data'    => new RetailerResource($retailer->fresh()->load(['user', 'store'])),
            'message' => 'Retailer rejected.',
        ]);
    }
}
