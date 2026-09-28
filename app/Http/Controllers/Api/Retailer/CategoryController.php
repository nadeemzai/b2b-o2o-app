<?php

namespace App\Http\Controllers\Api\Retailer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    // ──────────────────────────────────────────────
    // GET /api/retailer/categories
    // ──────────────────────────────────────────────

    /**
     * Return all categories ordered by name — used by mobile to build
     * the catalogue filter UI.
     *
     * Response shape:
     * {
     *   "data": [
     *     { "id": 1, "name": "Electronics", "name_zh": "电子产品", "slug": "electronics" },
     *     ...
     *   ]
     * }
     *
     * Middleware: auth:sanctum, role:retailer
     */
    public function __invoke(): JsonResponse
    {
        $categories = Category::orderBy('name')
            ->get(['id', 'name', 'name_zh', 'slug']);

        return response()->json(['data' => $categories]);
    }
}
