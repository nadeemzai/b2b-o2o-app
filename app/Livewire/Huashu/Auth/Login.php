<?php

namespace App\Livewire\Huashu\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.huashu')]
#[Title('Huashu — Sign In')]
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

        if ($user->role !== 'huashu') {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'This portal is for Huashu supplier accounts only.',
            ]);
        }

        session()->regenerate();

        $this->redirect(route('huashu.orders'), navigate: true);
    }

    public function render()
    {
        return view('livewire.huashu.auth.login');
    }
}
