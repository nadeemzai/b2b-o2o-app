<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TownshipStore;
use Illuminate\Http\JsonResponse;

class StoresController extends Controller
{
    // ──────────────────────────────────────────────
    // GET /api/stores  (public — no auth required)
    // ──────────────────────────────────────────────

    /**
     * Return all active township stores for the mobile registration screen.
     *
     * The web Register page calls TownshipStore::orderBy('name')->get(['id','name','city'])
     * to populate the store picker; this endpoint mirrors that behaviour.
     *
     * Response:
     * {
     *   "data": [
     *     { "id": 1, "name": "Model Town Store", "city": "Lahore" },
     *     ...
     *   ]
     * }
     */
    public function __invoke(): JsonResponse
    {
        $stores = TownshipStore::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'city']);

        return response()->json(['data' => $stores]);
    }
}
