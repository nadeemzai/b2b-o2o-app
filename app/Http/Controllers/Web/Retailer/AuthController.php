<?php

namespace App\Http\Controllers\Web\Retailer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    // GET /retailer/login
    public function showLogin(): View|RedirectResponse
    {
        $guard = Auth::guard('retailer');

        if ($guard->check() && $guard->user()->hasPortalRole('retailer')) {
            return redirect()->route('retailer.dashboard');
        }

        return view('retailer.login');
    }

    // POST /retailer/login
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $guard = Auth::guard('retailer');

        if (! $guard->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        $user = $guard->user();

        if (! $user->hasPortalRole('retailer')) {
            $guard->logout();
            return back()->withErrors(['email' => 'This portal is for retailers only.'])->onlyInput('email');
        }

        if (! $user->is_active) {
            $guard->logout();
            return back()->withErrors(['email' => 'Your account has been deactivated.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $retailer = $user->retailerProfile;

        if ($retailer && $retailer->isApproved()) {
            return redirect()->intended(route('retailer.dashboard'));
        }

        return redirect()->route('retailer.pending');
    }

    // POST /retailer/logout
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('retailer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('retailer.login');
    }
}
