<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Global exception handler — enforces a consistent JSON envelope for all API errors.
 *
 * Envelope shape:
 * {
 *   "message": "Human-readable summary",
 *   "errors":  { ... }   // present on validation failures (422)
 *   "details": { ... }   // present on domain exceptions (e.g. InsufficientStockException)
 * }
 */
class Handler extends ExceptionHandler
{
    /**
     * Exception types whose stack traces are never reported to Bugsnag / Sentry.
     */
    protected $dontReport = [
        InsufficientStockException::class,
    ];

    // ──────────────────────────────────────────────
    // Render
    // ──────────────────────────────────────────────

    public function render($request, Throwable $e): JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        // Only intercept API routes or requests expecting JSON
        if (! $request->expectsJson() && ! $request->is('api/*')) {
            return parent::render($request, $e);
        }

        // ── Validation errors (422) ────────────────────────────────────
        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        // ── Unauthenticated (401) ──────────────────────────────────────
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'message' => 'Unauthenticated. Please log in.',
            ], 401);
        }

        // ── Authorisation (403) ────────────────────────────────────────
        if ($e instanceof AuthorizationException) {
            return response()->json([
                'message' => $e->getMessage() ?: 'You do not have permission to perform this action.',
            ], 403);
        }

        // ── Model not found (404) ──────────────────────────────────────
        if ($e instanceof ModelNotFoundException) {
            $model   = last(explode('\\', $e->getModel()));
            return response()->json([
                'message' => "{$model} not found.",
            ], 404);
        }

        // ── Domain: Insufficient stock (422) ───────────────────────────
        if ($e instanceof InsufficientStockException) {
            return response()->json([
                'message' => $e->getMessage(),
                'details' => $e->toArray(),
            ], 422);
        }

        // ── HTTP exceptions (404, 403, 422 from abort()) ───────────────
        if ($e instanceof HttpException) {
            return response()->json([
                'message' => $e->getMessage() ?: 'HTTP error.',
            ], $e->getStatusCode());
        }

        // ── Generic server errors (500) ────────────────────────────────
        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

        if (config('app.debug')) {
            return response()->json([
                'message'   => $e->getMessage(),
                'exception' => get_class($e),
                'trace'     => collect($e->getTrace())->take(10)->toArray(),
            ], $statusCode);
        }

        return response()->json([
            'message' => 'Server error. Please try again later.',
        ], 500);
    }
}
