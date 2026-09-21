<x-layouts.retailer title="Dashboard">

    {{-- Page heading --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
            Hello, {{ Auth::user()->name }} 👋
        </h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
            {{ Auth::user()->retailerProfile?->business_name ?? 'Your retailer account' }}
        </p>
    </div>

    {{-- KYC quick-status card --}}
    @php
        $retailer  = Auth::user()->retailerProfile;
        $kycStatus = $retailer?->kyc_status ?? 'pending';
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

        {{-- KYC Status tile --}}
        <a href="{{ route('retailer.kyc') }}"
           class="group rounded-xl border bg-white dark:bg-zinc-900 p-6 hover:shadow-md transition-shadow
                  {{ $kycStatus === 'approved' ? 'border-emerald-200 dark:border-emerald-800' : ($kycStatus === 'rejected' ? 'border-red-200 dark:border-red-800' : 'border-amber-200 dark:border-amber-800') }}">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-widest text-zinc-400 dark:text-zinc-500">KYC Status</p>
                    <p class="mt-2 text-xl font-bold
                        {{ $kycStatus === 'approved' ? 'text-emerald-700 dark:text-emerald-400' : ($kycStatus === 'rejected' ? 'text-red-700 dark:text-red-400' : 'text-amber-700 dark:text-amber-400') }}">
                        {{ ucfirst($kycStatus) }}
                    </p>
                    <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                        @if ($kycStatus === 'approved') Identity verified
                        @elseif ($kycStatus === 'rejected') Action required
                        @else Awaiting review
                        @endif
                    </p>
                </div>
                <div class="rounded-lg p-2
                    {{ $kycStatus === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950' : ($kycStatus === 'rejected' ? 'bg-red-50 dark:bg-red-950' : 'bg-amber-50 dark:bg-amber-950') }}">
                    @if ($kycStatus === 'approved')
                        <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                        </svg>
                    @elseif ($kycStatus === 'rejected')
                        <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </div>
            </div>
            <p class="mt-4 text-xs text-indigo-600 dark:text-indigo-400 group-hover:underline">
                @if (in_array($kycStatus, ['pending', 'rejected'])) Upload / update documents →
                @else View KYC details →
                @endif
            </p>
        </a>

        {{-- Account info tile --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
            <p class="text-xs font-medium uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Account</p>
            <p class="mt-2 text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ Auth::user()->email }}</p>
            @if ($retailer)
                <div class="mt-3 space-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                    @if ($retailer->phone)    <p>📞 {{ $retailer->phone }}</p> @endif
                    @if ($retailer->cnic)     <p>🪪 {{ $retailer->cnic }}</p> @endif
                    @if ($retailer->address)  <p>📍 {{ $retailer->address }}</p> @endif
                </div>
            @endif
        </div>

    </div>

</x-layouts.retailer>
