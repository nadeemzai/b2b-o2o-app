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
        if (Auth::check() && Auth::user()->role === 'retailer') {
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

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        $user = Auth::user();

        if ($user->role !== 'retailer') {
            Auth::logout();
            return back()->withErrors(['email' => 'This portal is for retailers only.'])->onlyInput('email');
        }

        if (! $user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account has been deactivated.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('retailer.dashboard'));
    }

    // POST /retailer/logout
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('retailer.login');
    }
}
