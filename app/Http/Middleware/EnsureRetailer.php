<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user (retailer guard) holds the 'retailer' role.
 * Does NOT require KYC approval — use EnsureRetailerApproved for that.
 */
class EnsureRetailer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('retailer')->user();

        if (! $user || ! $user->hasPortalRole('retailer')) {
            abort(403, 'Access restricted to retailer accounts.');
        }

        return $next($request);
    }
}
