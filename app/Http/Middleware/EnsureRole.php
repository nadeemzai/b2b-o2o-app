<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks that the authenticated user has one of the allowed roles.
 *
 * Usage in routes:  ->middleware('role:retailer')
 *                   ->middleware('role:store_staff,admin')
 *
 * Register in bootstrap/app.php (Laravel 11):
 *   ->withMiddleware(function (Middleware $middleware) {
 *       $middleware->alias(['role' => EnsureRole::class]);
 *   })
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles)) {
            abort(403, 'Forbidden: insufficient role.');
        }

        return $next($request);
    }
}
