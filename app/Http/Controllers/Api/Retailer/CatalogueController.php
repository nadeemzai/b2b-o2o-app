<?php

namespace App\Http\Controllers\Api\Retailer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\AppSetting;
use App\Models\Product;
use App\Models\ProductReview;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogueController extends Controller
{
    public function __construct(private readonly PricingService $pricing)
    {
    }

    // ──────────────────────────────────────────────
    // GET /api/retailer/catalogue
    // ──────────────────────────────────────────────

    /**
     * Paginated product list for the authenticated retailer.
     *
     * Query params:
     *   - category_id (int, optional)
     *   - search      (string, optional)    — name / SKU search
     *   - sort_by     (string, optional)    — name_asc | price_asc | price_desc | newest
     *   - per_page    (int, default 20)
     *
     * Middleware: auth:sanctum, role:retailer
     */
    public function index(Request $request): JsonResponse
    {
        $user    = $request->user();
        $storeId = $user->retailerProfile->store_id;

        $query = Product::active()
            ->withPrice()
            ->with(['category', 'images',
                'stockLevels' => fn ($q) => $q->where('store_id', $storeId),
            ]);

        // Optional category filter
        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Optional search
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'ilike', "%{$search}%")
                  ->orWhere('sku',    'ilike', "%{$search}%");
            });
        }

        // Sort
        match ($request->query('sort_by', 'name_asc')) {
            'price_asc'  => $query->orderBy('huashu_base_price_pkr'),
            'price_desc' => $query->orderByDesc('huashu_base_price_pkr'),
            'newest'     => $query->latest('products.created_at'),
            default      => $query->orderBy('name_en'),
        };

        $perPage  = (int) $request->query('per_page', 20);
        $paginator = $query->paginate($perPage);

        // Batch commission rates — no N+1
        $categoryIds     = $paginator->getCollection()->pluck('category_id')->unique()->filter()->values()->all();
        $commissionRates = $this->pricing->ratesForCategories($categoryIds);

        $items = $paginator->getCollection()->map(function (Product $p) use ($commissionRates, $storeId) {
            $rate      = $commissionRates[$p->category_id] ?? 0.0;
            $basePrice = (float) ($p->huashu_base_price_pkr ?? 0);
            $price     = $basePrice > 0 ? round($basePrice * (1 + $rate), 2) : null;

            $stock = $p->stockLevels->first();

            return [
                'id'          => $p->id,
                'sku'         => $p->sku,
                'name_en'     => $p->name_en,
                'name_ur'     => $p->name_ur ?? null,
                'unit'        => $p->unit,
                'moq'         => $p->moq,
                'is_deal'     => (bool) ($p->is_deal ?? false),
                'price_pkr'   => $price,
                'image_path'  => $p->image_path,
                'images'      => $p->images->map(fn ($img) => [
                    'url'        => $img->image_url,
                    'is_primary' => $img->is_primary,
                ])->values(),
                'category'    => $p->category ? [
                    'id'   => $p->category->id,
                    'name' => $p->category->name,
                    'slug' => $p->category->slug,
                ] : null,
                'stock' => $stock ? [
                    'qty_on_hand'   => $stock->qty_on_hand,
                    'qty_reserved'  => $stock->qty_reserved,
                    'qty_available' => $stock->qty_available,
                ] : null,
                'updated_at'  => $p->updated_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'store_id'     => $storeId,
            ],
        ]);
    }

    // ──────────────────────────────────────────────
    // GET /api/retailer/catalogue/{product}
    // ──────────────────────────────────────────────

    /**
     * Full product detail including:
     *   - Retailer price (via PricingService)
     *   - Variant types with active options and price adjustments
     *   - Related products (up to 8 from same category)
     *   - Review context (can the retailer still write a review?)
     *
     * Middleware: auth:sanctum, role:retailer
     */
    public function show(Request $request, Product $product): JsonResponse
    {
        $user    = $request->user();
        $storeId = $user->retailerProfile->store_id;

        // Must be active and have a price
        abort_unless($product->is_active && $product->huashu_base_price_pkr, 404, 'Product not available.');

        $product->load([
            'category',
            'images',
            'stockLevels'  => fn ($q) => $q->where('store_id', $storeId),
            'variantTypes.activeOptions',
            'reviews'      => fn ($q) => $q->latest()->limit(20),
        ]);

        // Retailer price via PricingService
        $price = $this->pricing->retailerPrice($product);

        $stock = $product->stockLevels->first();

        // Variant types
        $variantTypes = $product->variantTypes->map(fn ($vt) => [
            'id'      => $vt->id,
            'name'    => $vt->name,
            'options' => $vt->activeOptions->map(fn ($opt) => [
                'id'                   => $opt->id,
                'value'                => $opt->value,
                'sku_suffix'           => $opt->sku_suffix,
                'price_adjustment_pkr' => (float) $opt->price_adjustment_pkr,
                'display_order'        => $opt->display_order,
            ])->values(),
        ])->values();

        // Related products — same category, different product, limit 8
        $related = collect();
        if ($product->category_id) {
            $relatedProducts = Product::active()
                ->withPrice()
                ->with(['category', 'images'])
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->limit(8)
                ->get();

            $relatedCategoryIds = $relatedProducts->pluck('category_id')->unique()->filter()->values()->all();
            $relatedRates       = $this->pricing->ratesForCategories($relatedCategoryIds);

            $related = $relatedProducts->map(function (Product $p) use ($relatedRates) {
                $rate  = $relatedRates[$p->category_id] ?? 0.0;
                $base  = (float) ($p->huashu_base_price_pkr ?? 0);
                $price = $base > 0 ? round($base * (1 + $rate), 2) : null;

                return [
                    'id'         => $p->id,
                    'sku'        => $p->sku,
                    'name_en'    => $p->name_en,
                    'name_ur'    => $p->name_ur ?? null,
                    'unit'       => $p->unit,
                    'moq'        => $p->moq,
                    'price_pkr'  => $price,
                    'image_path' => $p->image_path,
                    'images'     => $p->images->map(fn ($img) => [
                        'url'        => $img->image_url,
                        'is_primary' => $img->is_primary,
                    ])->values(),
                ];
            })->values();
        }

        // Review context
        $reviewLimit         = AppSetting::getInt('max_reviews_per_product', 3);
        $retailerReviewCount = (int) ProductReview::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->count();

        $allReviews = $product->reviews;

        return response()->json([
            'data' => [
                'id'          => $product->id,
                'sku'         => $product->sku,
                'name_en'     => $product->name_en,
                'name_ur'     => $product->name_ur ?? null,
                'description_en' => $product->description_en ?? null,
                'description_ur' => $product->description_ur ?? null,
                'unit'        => $product->unit,
                'moq'         => $product->moq,
                'is_deal'     => (bool) ($product->is_deal ?? false),
                'is_active'   => $product->is_active,
                'price_pkr'   => $price,
                'image_path'  => $product->image_path,
                'images'      => $product->images->map(fn ($img) => [
                    'url'        => $img->image_url,
                    'is_primary' => $img->is_primary,
                    'sort_order' => $img->sort_order,
                ])->values(),
                'category'    => $product->category ? [
                    'id'   => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ] : null,
                'stock' => $stock ? [
                    'qty_on_hand'   => $stock->qty_on_hand,
                    'qty_reserved'  => $stock->qty_reserved,
                    'qty_available' => $stock->qty_available,
                ] : null,
                'variant_types' => $variantTypes,
                'updated_at'  => $product->updated_at?->toIso8601String(),
            ],
            'reviews' => [
                'total'          => $allReviews->count(),
                'avg_rating'     => round($allReviews->avg('rating') ?? 0, 1),
                'retailer_count' => $retailerReviewCount,
                'limit'          => $reviewLimit,
                'can_review'     => $retailerReviewCount < $reviewLimit,
                'items'          => $allReviews->map(fn ($r) => [
                    'id'               => $r->id,
                    'rating'           => $r->rating,
                    'title'            => $r->title,
                    'body'             => $r->body,
                    'reviewer_name'    => $r->reviewer_name,
                    'reviewer_location'=> $r->reviewer_location,
                    'verified_purchase'=> $r->verified_purchase,
                    'created_at'       => $r->created_at?->toIso8601String(),
                ])->values(),
            ],
            'related_products' => $related,
        ]);
    }
}
