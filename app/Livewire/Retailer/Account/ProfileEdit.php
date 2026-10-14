<?php

namespace App\Livewire\Retailer\Account;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Retailer Account / Profile Edit
 *
 * Lets the authenticated retailer update:
 *   - Personal details  : display name, email (User model)
 *   - Business details  : business name, phone, address (Retailer model)
 *   - Password          : current + new + confirm (User model)
 *
 * KYC identity numbers (CNIC, NTN, STRN) are read-only here; changes
 * go through the KYC resubmission flow at /retailer/kyc.
 *
 * Route: GET /retailer/account  (name: retailer.account)
 */
#[Layout('layouts.retailer')]
class ProfileEdit extends Component
{
    // ── Personal details ──────────────────────────────────────
    public string $name  = '';
    public string $email = '';

    // ── Business details ──────────────────────────────────────
    public string $businessName = '';
    public string $phone        = '';
    public string $address      = '';

    // ── KYC numbers (display-only) ────────────────────────────
    public string $cnicNumber = '';
    public string $ntnNumber  = '';
    public string $strnNumber = '';

    // ── Password change ───────────────────────────────────────
    public string $currentPassword  = '';
    public string $newPassword       = '';
    public string $confirmPassword   = '';

    // ── UI state ──────────────────────────────────────────────
    public bool $profileSaved  = false;
    public bool $passwordSaved = false;

    // ─────────────────────────────────────────────────────────
    public function mount(): void
    {
        $user     = Auth::guard('retailer')->user();
        $retailer = $user->retailerProfile;

        $this->name  = $user->name  ?? '';
        $this->email = $user->email ?? '';

        if ($retailer) {
            $this->businessName = $retailer->business_name ?? '';
            $this->phone        = $retailer->phone         ?? '';
            $this->address      = $retailer->address       ?? '';
            $this->cnicNumber   = $retailer->cnic          ?? '';
            $this->ntnNumber    = $retailer->ntn           ?? '';
            $this->strnNumber   = $retailer->strn          ?? '';
        }
    }

    // ─────────────────────────────────────────────────────────
    public function saveProfile(): void
    {
        $user = Auth::guard('retailer')->user();

        $this->validate([
            'name'         => ['required', 'string', 'min:2', 'max:100'],
            'email'        => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'businessName' => ['required', 'string', 'min:2', 'max:200'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'address'      => ['nullable', 'string', 'max:500'],
        ]);

        $user->update([
            'name'  => trim($this->name),
            'email' => trim($this->email),
        ]);

        $retailer = $user->retailerProfile;
        if ($retailer) {
            $retailer->update([
                'business_name' => trim($this->businessName),
                'phone'         => trim($this->phone) ?: null,
                'address'       => trim($this->address) ?: null,
            ]);
        }

        $this->profileSaved  = true;
        $this->passwordSaved = false;

        $this->dispatch('profile-saved');
    }

    // ─────────────────────────────────────────────────────────
    public function savePassword(): void
    {
        $user = Auth::guard('retailer')->user();

        $this->validate([
            'currentPassword' => ['required', 'string'],
            'newPassword'     => ['required', 'string', 'min:8', 'confirmed:confirmPassword'],
            'confirmPassword' => ['required', 'string'],
        ]);

        if (! Hash::check($this->currentPassword, $user->password)) {
            $this->addError('currentPassword', 'The current password you entered is incorrect.');
            return;
        }

        $user->update(['password' => Hash::make($this->newPassword)]);

        $this->currentPassword = '';
        $this->newPassword     = '';
        $this->confirmPassword = '';

        $this->profileSaved  = false;
        $this->passwordSaved = true;
    }

    // ── Real-time validation for profile fields ───────────────
    public function updatedName(): void       { $this->validateOnly('name'); }
    public function updatedEmail(): void      { $this->validateOnly('email'); }
    public function updatedBusinessName(): void { $this->validateOnly('businessName'); }

    // ─────────────────────────────────────────────────────────
    public function render()
    {
        return view('livewire.retailer.account.profile-edit')
            ->title('My Account');
    }
}
