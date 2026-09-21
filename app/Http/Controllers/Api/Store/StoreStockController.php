<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreStockController extends Controller
{
    // ──────────────────────────────────────────────
    // GET /api/store/stock
    // ──────────────────────────────────────────────

    /**
     * Return stock levels for the authenticated staff member's store.
     *
     * Query params (all optional):
     *   - category_id   filter by product category
     *   - low_stock     boolean — only rows where qty_available <= 10
     *   - search        partial match on product name or SKU
     *   - per_page      default 50
     *
     * Middleware: auth:sanctum, role:store_staff
     *
     * Response 200:
     * {
     *   "store": { id, name },
     *   "data":  [
     *     {
     *       "product_id": 1,
     *       "sku":        "FMCG-001",
     *       "name":       "Surf Excel 1kg",
     *       "category":   "Detergents",
     *       "qty_on_hand":   100,
     *       "qty_reserved":  10,
     *       "qty_available": 90,
     *       "unit_price_pkr": "250.00"
     *     }
     *   ],
     *   "meta":  { current_page, last_page, total, per_page }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $staff   = $request->user()->storeStaff;
        abort_unless($staff, 403, 'No store staff profile found for this account.');

        $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'low_stock'   => ['nullable', 'boolean'],
            'search'      => ['nullable', 'string', 'max:100'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = StockLevel::with(['product.category'])
            ->where('store_id', $staff->store_id)
            ->when($request->category_id, fn ($q, $cat) =>
                $q->whereHas('product', fn ($p) => $p->where('category_id', $cat))
            )
            ->when($request->boolean('low_stock'), fn ($q) =>
                $q->whereRaw('qty_available <= 10')
            )
            ->when($request->search, function ($q, $search) {
                $q->whereHas('product', fn ($p) =>
                    $p->where('name', 'ilike', "%{$search}%")
                      ->orWhere('sku',  'ilike', "%{$search}%")
                );
            })
            ->orderByDesc('qty_available');

        $paginator = $query->paginate($request->integer('per_page', 50));

        $items = collect($paginator->items())->map(fn (StockLevel $sl) => [
            'product_id'      => $sl->product_id,
            'sku'             => $sl->product->sku,
            'name'            => $sl->product->name,
            'category'        => $sl->product->category?->name,
            'qty_on_hand'     => $sl->qty_on_hand,
            'qty_reserved'    => $sl->qty_reserved,
            'qty_available'   => $sl->qty_available,
            'unit_price_pkr'  => $sl->product->unit_price_pkr,
        ]);

        return response()->json([
            'store' => [
                'id'   => $staff->store_id,
                'name' => $staff->store->name,
            ],
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
            ],
        ]);
    }

    // ──────────────────────────────────────────────
    // POST /api/store/stock/inbound
    // ──────────────────────────────────────────────

    /**
     * Record inbound stock from a Tier 1 delivery (warehouse receipt).
     *
     * Increments qty_on_hand on the StockLevel row (upsert if missing) and
     * logs a 'inbound' movement in stock_movements.
     *
     * Middleware: auth:sanctum, role:store_staff
     *
     * Body:
     * {
     *   "items": [
     *     { "product_id": 1, "qty": 50, "note": "Oct batch from warehouse" },
     *     { "product_id": 3, "qty": 20 }
     *   ]
     * }
     *
     * Response 200:
     * {
     *   "message": "Inbound stock recorded.",
     *   "updated": [ { "product_id": 1, "new_qty_on_hand": 150, "new_qty_available": 140 } ]
     * }
     */
    public function inbound(Request $request): JsonResponse
    {
        $staff = $request->user()->storeStaff;
        abort_unless($staff, 403, 'No store staff profile found for this account.');

        $request->validate([
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'          => ['required', 'integer', 'min:1'],
            'items.*.note'         => ['nullable', 'string', 'max:500'],
        ]);

        $updated = DB::transaction(function () use ($request, $staff) {
            $results = [];

            foreach ($request->items as $item) {
                // Upsert the stock level row
                $level = StockLevel::firstOrNew([
                    'product_id' => $item['product_id'],
                    'store_id'   => $staff->store_id,
                ]);
                $level->qty_on_hand = ($level->qty_on_hand ?? 0) + $item['qty'];
                // qty_reserved stays unchanged; qty_available is a generated column
                $level->save();
                $level->refresh(); // pick up the DB-computed qty_available

                // Audit log
                StockMovement::create([
                    'product_id'        => $item['product_id'],
                    'store_id'          => $staff->store_id,
                    'type'              => 'inbound',
                    'qty'               => $item['qty'],
                    'note'              => $item['note'] ?? null,
                    'created_by_user_id'=> $request->user()->id,
                ]);

                $results[] = [
                    'product_id'        => $item['product_id'],
                    'new_qty_on_hand'   => $level->qty_on_hand,
                    'new_qty_available' => $level->qty_available,
                ];
            }

            return $results;
        });

        return response()->json([
            'message' => 'Inbound stock recorded.',
            'updated' => $updated,
        ]);
    }

    // ──────────────────────────────────────────────
    // POST /api/store/stock/adjust
    // ──────────────────────────────────────────────

    /**
     * Manual stock adjustment (write-off, damage, count correction).
     *
     * Accepts a signed `delta` — positive to add, negative to subtract.
     * Will not allow qty_on_hand to go below qty_reserved (would break
     * the generated qty_available constraint check: qty_on_hand >= qty_reserved).
     *
     * Middleware: auth:sanctum, role:store_staff
     *
     * Body:
     * {
     *   "product_id": 1,
     *   "delta":      -5,
     *   "reason":     "Damaged during handling"
     * }
     *
     * Response 200:
     * {
     *   "message":           "Stock adjusted.",
     *   "product_id":        1,
     *   "delta":             -5,
     *   "new_qty_on_hand":   95,
     *   "new_qty_available": 85
     * }
     */
    public function adjust(Request $request): JsonResponse
    {
        $staff = $request->user()->storeStaff;
        abort_unless($staff, 403, 'No store staff profile found for this account.');

        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'delta'      => ['required', 'integer', 'not_in:0'],
            'reason'     => ['required', 'string', 'max:500'],
        ]);

        $result = DB::transaction(function () use ($request, $staff) {
            $level = StockLevel::where('product_id', $request->product_id)
                ->where('store_id', $staff->store_id)
                ->lockForUpdate()
                ->firstOrFail();

            $newQtyOnHand = $level->qty_on_hand + $request->integer('delta');

            abort_if(
                $newQtyOnHand < 0,
                422,
                "Adjustment would result in negative qty_on_hand ({$newQtyOnHand})."
            );

            abort_if(
                $newQtyOnHand < $level->qty_reserved,
                422,
                "Adjustment would set qty_on_hand ({$newQtyOnHand}) below qty_reserved ({$level->qty_reserved})."
            );

            $level->update(['qty_on_hand' => $newQtyOnHand]);
            $level->refresh();

            StockMovement::create([
                'product_id'         => $request->product_id,
                'store_id'           => $staff->store_id,
                'type'               => $request->integer('delta') > 0 ? 'adjustment_in' : 'adjustment_out',
                'qty'                => abs($request->integer('delta')),
                'note'               => $request->reason,
                'created_by_user_id' => $request->user()->id,
            ]);

            return [
                'product_id'        => $request->product_id,
                'delta'             => $request->integer('delta'),
                'new_qty_on_hand'   => $level->qty_on_hand,
                'new_qty_available' => $level->qty_available,
            ];
        });

        return response()->json([
            'message' => 'Stock adjusted.',
            ...$result,
        ]);
    }
}
