<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Requires the retailer's KYC to be approved before accessing protected routes.
 * Uses the dedicated 'retailer' guard, not the default web guard.
 */
class EnsureRetailerApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $retailer = Auth::guard('retailer')->user()?->retailerProfile;

        if (! $retailer) {
            return redirect()->route('retailer.pending');
        }

        if ($retailer->isPending() || $retailer->isRejected()) {
            return redirect()->route('retailer.pending');
        }

        return $next($request);
    }
}
