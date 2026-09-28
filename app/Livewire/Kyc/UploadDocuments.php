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
 * Handles CNIC front/back + optional business doc uploads.
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
        }
    }

    // ─────────────────────────────────────────────────────────
    public function rules(): array
    {
        $required = $this->hasExistingDocs ? 'nullable' : 'required';

        return [
            'cnicFront'   => array_merge([$required], array_slice(self::FILE_RULES, 1)),
            'cnicBack'    => array_merge([$required], array_slice(self::FILE_RULES, 1)),
            'businessDoc' => self::FILE_RULES,
        ];
    }

    public function messages(): array
    {
        return [
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
    public function updatedCnicFront(): void   { $this->validateOnly('cnicFront'); }
    public function updatedCnicBack(): void    { $this->validateOnly('cnicBack'); }
    public function updatedBusinessDoc(): void { $this->validateOnly('businessDoc'); }

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
            'kyc_documents'        => $existing,
            'kyc_status'           => 'pending',
            'kyc_rejection_reason' => null,
        ]);

        // Refresh state
        $this->kycStatus       = 'pending';
        $this->rejectionReason = null;
        $this->existingPaths   = $existing;
        $this->hasExistingDocs = true;

        // Clear file inputs
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
