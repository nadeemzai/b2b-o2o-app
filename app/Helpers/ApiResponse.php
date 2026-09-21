<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Consistent JSON envelope for all API responses.
 *
 * Shape:
 *   success  { "success": true,  "data": {...|[...]}, "message": "...", "meta": {...} }
 *   error    { "success": false, "message": "...",   "errors": {...} }
 */
class ApiResponse
{
    // ─────────────────────────────────────────────
    // Success responses
    // ─────────────────────────────────────────────

    public static function success(mixed $data = null, string $message = '', int $status = 200): JsonResponse
    {
        $body = ['success' => true];

        if ($data !== null) {
            $body['data'] = $data;
        }

        if ($message !== '') {
            $body['message'] = $message;
        }

        return response()->json($body, $status);
    }

    public static function created(mixed $data = null, string $message = 'Created successfully.'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    /**
     * Wrap a paginator (pass the result of ->paginate()) with a meta block.
     *
     * @param  iterable  $items   Already-transformed items (e.g. from a Resource collection)
     */
    public static function paginated(LengthAwarePaginator $paginator, iterable $items, string $message = ''): JsonResponse
    {
        $body = [
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ];

        if ($message !== '') {
            $body['message'] = $message;
        }

        return response()->json($body);
    }

    // ─────────────────────────────────────────────
    // Error responses
    // ─────────────────────────────────────────────

    public static function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $body = ['success' => false, 'message' => $message];

        if ($errors) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }

    public static function notFound(string $message = 'Resource not found.'): JsonResponse
    {
        return self::error($message, 404);
    }

    public static function unauthorized(string $message = 'Unauthenticated. Please log in.'): JsonResponse
    {
        return self::error($message, 401);
    }

    public static function forbidden(string $message = 'You do not have permission to perform this action.'): JsonResponse
    {
        return self::error($message, 403);
    }
}
