<?php

namespace App\Livewire\Kyc;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Retailer KYC Document Upload
 *
 * Handles CNIC front/back + optional business doc uploads,
 * plus CNIC number, NTN number, and STRN number text fields.
 *
 * Files land in:  storage/app/private/kyc/{retailer_id}/
 * Status resets to 'pending' on every submission.
 *
 * Route:  GET /retailer/kyc
 * Usage:  @livewire('kyc.upload-documents')
 */
#[Layout('layouts.retailer')]
class UploadDocuments extends Component
{
    use WithFileUploads;

    // ── Text identifier fields ─────────────────────────────────
    public string $cnicNumber  = '';
    public string $ntnNumber   = '';
    public string $strnNumber  = '';

    // ── Temporary upload properties ───────────────────────────
    public $cnicFront   = null;
    public $cnicBack    = null;
    public $businessDoc = null;

    // ── Derived state ─────────────────────────────────────────
    public string $kycStatus         = 'pending';
    public ?string $rejectionReason  = null;
    public bool $hasExistingDocs     = false;
    public array $existingPaths      = [];

    // Shared file rules
    private const FILE_RULES = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];

    // ─────────────────────────────────────────────────────────
    public function mount(): void
    {
        $retailer = Auth::user()->retailerProfile;

        if ($retailer) {
            $this->kycStatus        = $retailer->kyc_status ?? 'pending';
            $this->rejectionReason  = $retailer->kyc_rejection_reason;
            $this->existingPaths    = $retailer->kyc_documents ?? [];
            $this->hasExistingDocs  = ! empty($this->existingPaths);

            // Pre-fill saved identifier text values
            $this->cnicNumber  = $retailer->cnic  ?? '';
            $this->ntnNumber   = $retailer->ntn   ?? '';
            $this->strnNumber  = $retailer->strn  ?? '';
        }
    }

    // ─────────────────────────────────────────────────────────
    public function rules(): array
    {
        $required = $this->hasExistingDocs ? 'nullable' : 'required';

        return [
            // Text identifiers
            'cnicNumber'  => ['required', 'string', 'min:13', 'max:15', 'regex:/^\d{5}-\d{7}-\d{1}$/'],
            'ntnNumber'   => ['nullable', 'string', 'max:20'],
            'strnNumber'  => ['nullable', 'string', 'max:20'],

            // File uploads
            'cnicFront'   => array_merge([$required], array_slice(self::FILE_RULES, 1)),
            'cnicBack'    => array_merge([$required], array_slice(self::FILE_RULES, 1)),
            'businessDoc' => self::FILE_RULES,
        ];
    }

    public function messages(): array
    {
        return [
            'cnicNumber.required'  => 'CNIC number is required.',
            'cnicNumber.regex'     => 'CNIC must be in the format XXXXX-XXXXXXX-X (e.g. 42101-1234567-9).',
            'cnicNumber.min'       => 'Please enter a valid 13-digit CNIC number.',
            'ntnNumber.max'        => 'NTN number may not exceed 20 characters.',
            'strnNumber.max'       => 'STRN number may not exceed 20 characters.',

            'cnicFront.required'   => 'CNIC front photo is required for first submission.',
            'cnicBack.required'    => 'CNIC back photo is required for first submission.',
            'cnicFront.mimes'      => 'CNIC front must be a JPG, PNG, or PDF (max 5 MB).',
            'cnicBack.mimes'       => 'CNIC back must be a JPG, PNG, or PDF (max 5 MB).',
            'businessDoc.mimes'    => 'Business document must be a JPG, PNG, or PDF (max 5 MB).',
            'cnicFront.max'        => 'CNIC front may not exceed 5 MB.',
            'cnicBack.max'         => 'CNIC back may not exceed 5 MB.',
            'businessDoc.max'      => 'Business document may not exceed 5 MB.',
        ];
    }

    // ── Real-time validation ──────────────────────────────────
    public function updatedCnicNumber(): void   { $this->validateOnly('cnicNumber'); }
    public function updatedNtnNumber(): void    { $this->validateOnly('ntnNumber'); }
    public function updatedStrnNumber(): void   { $this->validateOnly('strnNumber'); }
    public function updatedCnicFront(): void    { $this->validateOnly('cnicFront'); }
    public function updatedCnicBack(): void     { $this->validateOnly('cnicBack'); }
    public function updatedBusinessDoc(): void  { $this->validateOnly('businessDoc'); }

    // ── Submit ────────────────────────────────────────────────
    public function save(): void
    {
        $this->validate();

        $retailer = Auth::user()->retailerProfile;

        abort_unless($retailer, 403);

        $dir      = "kyc/{$retailer->id}";
        $existing = $retailer->kyc_documents ?? [];

        foreach (['cnicFront' => 'cnic_front', 'cnicBack' => 'cnic_back', 'businessDoc' => 'business_doc'] as $livewireProp => $dbKey) {
            if ($this->{$livewireProp}) {
                // Delete old file
                if (isset($existing[$dbKey])) {
                    Storage::disk('private')->delete($existing[$dbKey]);
                }
                $existing[$dbKey] = $this->{$livewireProp}->store($dir, 'private');
            }
        }

        $retailer->update([
            // Text identifiers
            'cnic'                 => $this->cnicNumber ?: null,
            'ntn'                  => $this->ntnNumber  ?: null,
            'strn'                 => $this->strnNumber ?: null,
            // Documents & status
            'kyc_documents'        => $existing,
            'kyc_status'           => 'pending',
            'kyc_rejection_reason' => null,
        ]);

        // Refresh state
        $this->kycStatus       = 'pending';
        $this->rejectionReason = null;
        $this->existingPaths   = $existing;
        $this->hasExistingDocs = true;

        // Clear file inputs (text inputs retain their values)
        $this->cnicFront   = null;
        $this->cnicBack    = null;
        $this->businessDoc = null;
        $this->resetErrorBag();

        session()->flash('success', 'Documents uploaded. Your KYC is under review — we\'ll notify you once processed.');
    }

    // ─────────────────────────────────────────────────────────
    public function render(): \Illuminate\View\View
    {
        return view('livewire.kyc.upload-documents');
    }
}
