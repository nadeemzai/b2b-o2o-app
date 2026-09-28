<?php

namespace App\Http\Controllers\Api\Retailer;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // ────────────────────────────────────────────────────────────────────────
    // GET /api/retailer/catalogue/{product}/reviews
    // List all approved reviews for a product (public-ish — any authenticated retailer).
    // ────────────────────────────────────────────────────────────────────────
    public function index(Product $product): JsonResponse
    {
        abort_unless($product->is_active, 404);

        $reviews = $product->reviews()
            ->orderByDesc('created_at')
            ->get([
                'id', 'reviewer_name', 'reviewer_location',
                'rating', 'title', 'body', 'verified_purchase', 'created_at',
            ]);

        $avgRating = round($reviews->avg('rating') ?? 0, 1);

        return response()->json([
            'data'      => $reviews,
            'meta'      => [
                'total'      => $reviews->count(),
                'avg_rating' => $avgRating,
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/retailer/catalogue/{product}/reviews
    // Submit a new review — enforces the admin-configured per-retailer limit.
    // ────────────────────────────────────────────────────────────────────────
    public function store(Request $request, Product $product): JsonResponse
    {
        abort_unless($product->is_active, 404);

        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'title'  => 'nullable|string|max:120',
            'body'   => 'required|string|min:10|max:1000',
        ]);

        /** @var \App\Models\User $user */
        $user  = $request->user();
        $limit = AppSetting::getInt('max_reviews_per_product', 3);

        $existing = ProductReview::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->count();

        if ($existing >= $limit) {
            return response()->json([
                'message' => "You have already submitted {$existing} review(s) for this product. Maximum allowed: {$limit}.",
                'errors'  => [
                    'limit' => ["Review limit of {$limit} reached for this product."],
                ],
            ], 422);
        }

        $profile = $user->retailerProfile;

        $review = ProductReview::create([
            'product_id'        => $product->id,
            'user_id'           => $user->id,
            'reviewer_name'     => $profile->business_name ?? $user->name,
            'reviewer_location' => $profile->store?->city ?? null,
            'rating'            => $validated['rating'],
            'title'             => $validated['title'] ?? null,
            'body'              => $validated['body'],
            'verified_purchase' => true,
        ]);

        return response()->json([
            'message'         => 'Review submitted successfully.',
            'data'            => $review,
            'reviews_count'   => $existing + 1,
            'reviews_remaining' => max(0, $limit - ($existing + 1)),
        ], 201);
    }
}
