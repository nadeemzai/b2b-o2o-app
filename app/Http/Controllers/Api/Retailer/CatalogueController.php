<?php

namespace App\Http\Controllers\Api\Retailer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogueController extends Controller
{
    // ──────────────────────────────────────────────
    // GET /api/retailer/catalogue
    // ──────────────────────────────────────────────

    /**
     * List all active products available in the authenticated retailer's store,
     * with live stock availability and the store's price.
     *
     * Query params:
     *   - category_id (int, optional) — filter by category
     *   - search (string, optional)   — name/SKU ILIKE search
     *   - per_page (int, default 20)
     *
     * Middleware: auth:sanctum, role:retailer, kyc_approved
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var \App\Models\User $user */
        $user    = $request->user();
        $storeId = $user->retailerProfile->store_id;

        $query = Product::active()
            ->availableInStore($storeId)
            ->with([
                'storePrices'  => fn ($q) => $q->where('store_id', $storeId)->where('is_active', true),
                'stockLevels'  => fn ($q) => $q->where('store_id', $storeId),
                'categories',
            ]);

        // Optional category filter
        if ($categoryId = $request->query('category_id')) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $categoryId));
        }

        // Optional search
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('sku', 'ilike', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')
            ->paginate($request->query('per_page', 20));

        // Pass store context so the Resource can pick the right price & stock
        ProductResource::withoutWrapping();

        return ProductResource::collection($products)->additional([
            'meta' => ['store_id' => $storeId],
        ]);
    }

    // ──────────────────────────────────────────────
    // GET /api/retailer/catalogue/{product}
    // ──────────────────────────────────────────────

    /**
     * Show a single product with full detail for the retailer's store.
     */
    public function show(Request $request, Product $product): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user    = $request->user();
        $storeId = $user->retailerProfile->store_id;

        // Ensure the product is available in this store
        $price = $product->storePrices()
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->first();

        abort_unless($price, 404, 'Product not available in your store.');

        $product->load([
            'storePrices'  => fn ($q) => $q->where('store_id', $storeId),
            'stockLevels'  => fn ($q) => $q->where('store_id', $storeId),
            'categories',
        ]);

        return response()->json(['data' => new ProductResource($product)]);
    }
}
