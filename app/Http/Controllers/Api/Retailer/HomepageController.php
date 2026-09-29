<?php

namespace App\Http\Controllers\Api\Retailer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\HomepageSection;
use App\Models\Product;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomepageController extends Controller
{
    public function __construct(private readonly PricingService $pricing)
    {
    }

    // ──────────────────────────────────────────────
    // GET /api/retailer/home
    // ──────────────────────────────────────────────

    /**
     * Homepage data for the mobile shell.
     *
     * Returns section visibility flags, deals strip, new-arrivals strip,
     * and the show_stock_badge setting — everything the web homepage shows.
     *
     * Response shape:
     * {
     *   "sections": {
     *     "deals":            true,
     *     "new_arrivals":     true,
     *     "show_stock_badge": false
     *   },
     *   "deals":       [ ...ProductResource(10) ],
     *   "new_arrivals": [ ...ProductResource(10) ]
     * }
     *
     * Middleware: auth:sanctum, role:retailer
     */
    public function __invoke(Request $request): JsonResponse
    {
        $sections = HomepageSection::activeSections();

        $deals       = collect();
        $newArrivals = collect();

        if ($sections['deals']) {
            $deals = Product::active()
                ->withPrice()
                ->with(['category', 'images'])
                ->where('is_deal', true)
                ->latest('products.created_at')
                ->limit(10)
                ->get();
        }

        if ($sections['new_arrivals']) {
            $newArrivals = Product::active()
                ->withPrice()
                ->with(['category', 'images'])
                ->latest('products.created_at')
                ->limit(10)
                ->get();
        }

        // Attach retailer prices to each product via PricingService
        $pooled          = $deals->merge($newArrivals)->unique('id');
        $categoryIds     = $pooled->pluck('category_id')->unique()->filter()->values()->all();
        $commissionRates = $this->pricing->ratesForCategories($categoryIds);

        $attachPrice = function (Product $p) use ($commissionRates): array {
            $rate      = $commissionRates[$p->category_id] ?? 0.0;
            $basePrice = (float) ($p->huashu_base_price_pkr ?? 0);
            $price     = $basePrice > 0 ? round($basePrice * (1 + $rate), 2) : null;

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
                ] : null,
                'updated_at'  => $p->updated_at?->toIso8601String(),
            ];
        };

        return response()->json([
            'sections'    => $sections,
            'deals'       => $deals->map($attachPrice)->values(),
            'new_arrivals' => $newArrivals->map($attachPrice)->values(),
        ]);
    }
}
