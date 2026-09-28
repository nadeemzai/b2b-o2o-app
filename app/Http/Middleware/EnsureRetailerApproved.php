<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Requires the retailer's KYC to be approved before accessing protected routes.
 * Pending retailers are redirected to the pending page.
 * Rejected retailers see a rejection message.
 */
class EnsureRetailerApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $retailer = $request->user()?->retailerProfile;

        if (! $retailer) {
            return redirect()->route('retailer.pending');
        }

        if ($retailer->isPending()) {
            return redirect()->route('retailer.pending');
        }

        if ($retailer->isRejected()) {
            return redirect()->route('retailer.pending');
        }

        return $next($request);
    }
}
