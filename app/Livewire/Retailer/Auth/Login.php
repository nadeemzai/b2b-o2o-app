<?php

namespace App\Livewire\Retailer\Auth;

use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public string $email    = '';
    public string $password = '';

    protected array $rules = [
        'email'    => 'required|email',
        'password' => 'required|string',
    ];

    public function authenticate(): void
    {
        $this->validate();

        // Use the dedicated 'retailer' guard so this session is stored under
        // a separate session key (login_retailer_xxxx) and never conflicts with
        // an admin session (login_admin_xxxx) in the same browser.
        $guard = Auth::guard('retailer');

        if (! $guard->attempt(['email' => $this->email, 'password' => $this->password])) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = $guard->user();

        // Account must be active
        if (! $user->is_active) {
            $guard->logout();
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Please contact support.',
            ]);
        }

        // Must hold the retailer portal role
        if (! $user->hasPortalRole('retailer')) {
            $guard->logout();
            throw ValidationException::withMessages([
                'email' => 'This portal is for retailer accounts only.',
            ]);
        }

        session()->regenerate();

        $retailer = $user->retailerProfile;

        // Restore any cart the retailer had before they logged out.
        if ($retailer) {
            app(CartService::class)->restoreFromDb($retailer);
        }
        if ($retailer && $retailer->isApproved()) {
            $this->redirect(route('retailer.dashboard'), navigate: true);
        } else {
            $this->redirect(route('retailer.pending'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.retailer.auth.login')
            ->layout('layouts.retailer', ['title' => 'Sign In']);
    }
}
