<x-layouts.retailer title="Account Pending">
    <div class="min-h-[60vh] flex items-center justify-center">
        <div class="text-center max-w-md">
            @php $retailer = auth()->user()->retailerProfile; @endphp

            @if($retailer && $retailer->isRejected())
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-slate-800 mb-2">Application Rejected</h2>
            <p class="text-slate-500 mb-4">Your KYC application was not approved.</p>
            @if($retailer->kyc_rejection_reason)
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700 text-left mb-4">
                <strong>Reason:</strong> {{ $retailer->kyc_rejection_reason }}
            </div>
            @endif
            <p class="text-slate-500 text-sm">Please contact support at <a href="mailto:support@oztech.com" class="text-brand">support@oztech.com</a></p>
            @else
            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-slate-800 mb-2">Application Under Review</h2>
            <p class="text-slate-500 mb-4">Your account is being verified. You will receive an email once approved.</p>
            <p class="text-slate-400 text-sm">This usually takes 1–2 business days.</p>
            @endif

            <form method="POST" action="{{ route('retailer.logout') }}" class="mt-6">
                @csrf
                <button type="submit" class="text-sm text-slate-500 hover:text-slate-700 underline">Sign out</button>
            </form>
        </div>
    </div>
</x-layouts.retailer>
