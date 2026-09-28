<div class="space-y-6">

    {{-- ── Status Banner ──────────────────────────────── --}}
    @if ($kycStatus === 'approved')
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-950/40 px-5 py-4 flex items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="font-semibold text-emerald-800 dark:text-emerald-300">KYC Approved</p>
                <p class="mt-0.5 text-sm text-emerald-700 dark:text-emerald-400">Your identity has been verified. You have full access to place orders.</p>
            </div>
        </div>
    @elseif ($kycStatus === 'rejected')
        <div class="rounded-xl border border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950/40 px-5 py-4 flex items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="font-semibold text-red-800 dark:text-red-300">KYC Rejected — Re-submission Required</p>
                @if ($rejectionReason)
                    <p class="mt-1 text-sm text-red-700 dark:text-red-400">
                        <span class="font-medium">Reason:</span> {{ $rejectionReason }}
                    </p>
                @endif
                <p class="mt-1 text-sm text-red-600 dark:text-red-500">Please upload clearer documents below and resubmit.</p>
            </div>
        </div>
    @elseif ($hasExistingDocs)
        <div class="rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/40 px-5 py-4 flex items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="font-semibold text-amber-800 dark:text-amber-300">Under Review</p>
                <p class="mt-0.5 text-sm text-amber-700 dark:text-amber-400">Your documents are with our team. Typically reviewed within 1–2 business days.</p>
            </div>
        </div>
    @endif

    {{-- ── Flash success ────────────────────────────────── --}}
    @if (session()->has('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-950/40 px-5 py-3 text-sm text-emerald-800 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Upload Form ─────────────────────────────────── --}}
    <form wire:submit.prevent="save" class="space-y-5">

        {{-- CNIC Front --}}
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                CNIC Front
                <span class="text-red-500">*</span>
                @if (!empty($existingPaths['cnic_front']))
                    <span class="ml-2 text-xs font-normal text-emerald-600 dark:text-emerald-400">✓ Already uploaded</span>
                @endif
            </label>
            <div class="relative">
                <input
                    type="file"
                    wire:model="cnicFront"
                    accept=".jpg,.jpeg,.png,.pdf"
                    class="block w-full text-sm text-zinc-600 dark:text-zinc-400
                           file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                           file:text-sm file:font-medium
                           file:bg-zinc-100 dark:file:bg-zinc-800
                           file:text-zinc-700 dark:file:text-zinc-300
                           hover:file:bg-zinc-200 dark:hover:file:bg-zinc-700
                           border border-zinc-300 dark:border-zinc-600 rounded-lg
                           bg-white dark:bg-zinc-900 p-2 cursor-pointer"
                >
            </div>
            <div wire:loading wire:target="cnicFront" class="mt-1 text-xs text-zinc-400">Uploading…</div>
            @error('cnicFront')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            @if ($cnicFront)
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Selected: {{ $cnicFront->getClientOriginalName() }}</p>
            @endif
        </div>

        {{-- CNIC Back --}}
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                CNIC Back
                <span class="text-red-500">*</span>
                @if (!empty($existingPaths['cnic_back']))
                    <span class="ml-2 text-xs font-normal text-emerald-600 dark:text-emerald-400">✓ Already uploaded</span>
                @endif
            </label>
            <input
                type="file"
                wire:model="cnicBack"
                accept=".jpg,.jpeg,.png,.pdf"
                class="block w-full text-sm text-zinc-600 dark:text-zinc-400
                       file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                       file:text-sm file:font-medium
                       file:bg-zinc-100 dark:file:bg-zinc-800
                       file:text-zinc-700 dark:file:text-zinc-300
                       hover:file:bg-zinc-200 dark:hover:file:bg-zinc-700
                       border border-zinc-300 dark:border-zinc-600 rounded-lg
                       bg-white dark:bg-zinc-900 p-2 cursor-pointer"
            >
            <div wire:loading wire:target="cnicBack" class="mt-1 text-xs text-zinc-400">Uploading…</div>
            @error('cnicBack')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            @if ($cnicBack)
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Selected: {{ $cnicBack->getClientOriginalName() }}</p>
            @endif
        </div>

        {{-- Business Document (optional) --}}
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                Business Registration / NTN Certificate
                <span class="ml-1 text-xs font-normal text-zinc-400">(optional)</span>
                @if (!empty($existingPaths['business_doc']))
                    <span class="ml-2 text-xs font-normal text-emerald-600 dark:text-emerald-400">✓ Already uploaded</span>
                @endif
            </label>
            <input
                type="file"
                wire:model="businessDoc"
                accept=".jpg,.jpeg,.png,.pdf"
                class="block w-full text-sm text-zinc-600 dark:text-zinc-400
                       file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                       file:text-sm file:font-medium
                       file:bg-zinc-100 dark:file:bg-zinc-800
                       file:text-zinc-700 dark:file:text-zinc-300
                       hover:file:bg-zinc-200 dark:hover:file:bg-zinc-700
                       border border-zinc-300 dark:border-zinc-600 rounded-lg
                       bg-white dark:bg-zinc-900 p-2 cursor-pointer"
            >
            <div wire:loading wire:target="businessDoc" class="mt-1 text-xs text-zinc-400">Uploading…</div>
            @error('businessDoc')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            @if ($businessDoc)
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Selected: {{ $businessDoc->getClientOriginalName() }}</p>
            @endif
        </div>

        {{-- ── Accepted formats note ──────────────────── --}}
        <p class="text-xs text-zinc-400 dark:text-zinc-500">
            Accepted formats: JPG, PNG, PDF — max 5 MB per file.
            Photos must be clear, unobstructed, and fully visible.
        </p>

        {{-- ── Submit ──────────────────────────────────── --}}
        <div class="pt-1">
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2
                       rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white
                       shadow-sm hover:bg-indigo-500 active:bg-indigo-700
                       disabled:opacity-60 disabled:cursor-not-allowed
                       transition-colors duration-150"
            >
                <span wire:loading.remove wire:target="save">
                    {{ $hasExistingDocs ? 'Resubmit Documents' : 'Submit for Verification' }}
                </span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Saving…
                </span>
            </button>
        </div>

    </form>

</div>
