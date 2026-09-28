<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards retailer web-portal routes using the dedicated 'retailer' guard.
 *
 * The 'retailer' guard stores its session under a key separate from the
 * 'admin' guard, so an admin user can be simultaneously authenticated in
 * the Filament admin panel and in the retailer portal without conflicts.
 *
 * Checks:
 *   1. Guard authenticated?           → else redirect to /retailer/login
 *   2. Account active?                → else logout + redirect
 *   3. Has 'retailer' portal role?    → else 403
 */
class RetailerAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('retailer');

        if (! $guard->check()) {
            return redirect()->route('retailer.login')
                ->with('error', 'Please sign in to continue.');
        }

        $user = $guard->user();

        if (! $user->is_active) {
            $guard->logout();
            $request->session()->invalidate();
            return redirect()->route('retailer.login')
                ->withErrors(['email' => 'Your account has been deactivated.']);
        }

        if (! $user->hasPortalRole('retailer')) {
            abort(403, 'This portal is for retailer accounts only.');
        }

        return $next($request);
    }
}
