<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
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
 *   "errors":  { ... }   // present on validation failures
 *   "details": { ... }   // present on domain exceptions (e.g. InsufficientStockException)
 * }
 *
 * Register in bootstrap/app.php (Laravel 11):
 *   ->withExceptions(function (Exceptions $exceptions) {
 *       // Laravel 11 uses the renderUsing / renderable hooks instead of overriding Handler
 *       $exceptions->renderable(fn (Throwable $e, $req) => (new Handler(app()))->render($req, $e));
 *   })
 *
 * OR — if still using the traditional App\Exceptions\Handler — just extend this class.
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of exception types whose stack traces should not be reported.
     */
    protected $dontReport = [
        InsufficientStockException::class,
    ];

    // ──────────────────────────────────────────────
    // Render
    // ──────────────────────────────────────────────

    public function render($request, Throwable $e): JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        // Only intercept API routes (or requests expecting JSON)
        if (! $request->expectsJson() && ! $request->is('api/*')) {
            return parent::render($request, $e);
        }

        // ── Validation errors ──────────────────────────────────────────
        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        // ── Unauthenticated ────────────────────────────────────────────
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'message' => 'Unauthenticated. Please log in.',
            ], 401);
        }

        // ── Domain: Insufficient stock ─────────────────────────────────
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

        // ── Generic server errors ──────────────────────────────────────
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
