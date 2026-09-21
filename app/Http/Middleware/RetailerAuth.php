<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards retailer web portal routes.
 *
 * - Unauthenticated → redirect to /retailer/login
 * - Wrong role      → 403
 * - Inactive        → logout + redirect to login
 */
class RetailerAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('retailer.login')
                ->with('error', 'Please sign in to continue.');
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            return redirect()->route('retailer.login')
                ->withErrors(['email' => 'Your account has been deactivated.']);
        }

        if ($user->role !== 'retailer') {
            abort(403, 'This portal is for retailers only.');
        }

        return $next($request);
    }
}
