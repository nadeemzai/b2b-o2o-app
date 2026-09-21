<?php

namespace App\Livewire\Retailer\Auth;

use App\Models\Retailer;
use App\Models\TownshipStore;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;

class Register extends Component
{
    use WithFileUploads;

    // Personal
    public string $name         = '';
    public string $email        = '';
    public string $password     = '';
    public string $phone        = '';

    // Business
    public string $business_name = '';
    public string $cnic          = '';
    public string $ntn           = '';
    public string $strn          = '';
    public string $address       = '';
    public ?int   $store_id      = null;

    // KYC docs
    public $cnic_doc   = null;
    public $ntn_doc    = null;

    protected function rules(): array
    {
        return [
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:8',
            'phone'         => 'required|string|max:20',
            'business_name' => 'required|string|max:150',
            'cnic'          => 'required|string|regex:/^\d{5}-\d{7}-\d{1}$/|unique:retailers,cnic',
            'ntn'           => 'nullable|string|max:20',
            'strn'          => 'nullable|string|max:20',
            'address'       => 'required|string|max:500',
            'store_id'      => 'required|exists:township_stores,id',
            'cnic_doc'      => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'ntn_doc'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }

    protected array $messages = [
        'cnic.regex' => 'CNIC format must be: 42201-1234567-1',
    ];

    public function register(): void
    {
        $this->validate();

        // Store KYC docs
        $documents = [];
        if ($this->cnic_doc) {
            $documents['cnic_doc'] = $this->cnic_doc->store('kyc', 'public');
        }
        if ($this->ntn_doc) {
            $documents['ntn_doc'] = $this->ntn_doc->store('kyc', 'public');
        }

        $user = User::create([
            'name'      => $this->name,
            'email'     => $this->email,
            'password'  => Hash::make($this->password),
            'role'      => 'retailer',
            'is_active' => true,
        ]);

        Retailer::create([
            'user_id'       => $user->id,
            'store_id'      => $this->store_id,
            'business_name' => $this->business_name,
            'cnic'          => $this->cnic,
            'ntn'           => $this->ntn ?: null,
            'strn'          => $this->strn ?: null,
            'phone'         => $this->phone,
            'address'       => $this->address,
            'kyc_status'    => 'pending',
            'kyc_documents' => $documents,
        ]);

        Auth::login($user);
        session()->regenerate();

        $this->redirect(route('retailer.pending'), navigate: true);
    }

    public function render()
    {
        return view('livewire.retailer.auth.register', [
            'stores' => TownshipStore::orderBy('name')->get(['id', 'name', 'city']),
        ])->layout('layouts.retailer', ['title' => 'Create Account']);
    }
}
