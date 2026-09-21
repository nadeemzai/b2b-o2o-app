<?php

namespace App\Livewire\Retailer\Auth;

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

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = Auth::user();

        if (! $user->isRetailer()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'This portal is for retailer accounts only.',
            ]);
        }

        session()->regenerate();

        $retailer = $user->retailerProfile;
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
