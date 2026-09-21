<x-layouts.retailer title="KYC Verification">

    <div class="max-w-2xl">

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">KYC Verification</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                Upload your identity documents to enable ordering. All files are stored securely and only visible to our review team.
            </p>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm px-6 py-7">
            @livewire('kyc.upload-documents')
        </div>

    </div>

</x-layouts.retailer>
