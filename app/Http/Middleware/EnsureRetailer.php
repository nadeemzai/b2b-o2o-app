<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user is a retailer (role = 'retailer').
 * Does NOT require KYC to be approved — use EnsureRetailerApproved for that.
 */
class EnsureRetailer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isRetailer()) {
            abort(403, 'Access restricted to retailer accounts.');
        }

        return $next($request);
    }
}
