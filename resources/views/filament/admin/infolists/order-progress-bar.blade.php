@php
    $steps = ['pending', 'payment_verified', 'transferred', 'fulfilling', 'delivered'];
    $labels = [
        'pending'          => 'Order Placed',
        'payment_verified' => 'Payment Verified',
        'transferred'      => 'Transferred',
        'fulfilling'       => 'Fulfilling',
        'delivered'        => 'Delivered',
    ];
    $currentStatus = $record->status ?? 'pending';
    $isCancelled   = $currentStatus === 'cancelled';
    $currentIndex  = $isCancelled ? -1 : (array_search($currentStatus, $steps, true) ?? 0);
    if ($currentIndex === false) { $currentIndex = 0; }
@endphp

<div class="px-4 py-4">
    @if ($isCancelled)
        <div class="flex items-center gap-2 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 rounded-lg px-4 py-2.5 text-sm text-red-700 dark:text-red-300">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <span>This order has been cancelled.</span>
        </div>
    @else
        <div class="relative">
            <div class="flex items-center justify-between">
                @foreach ($steps as $i => $stepKey)
                    <div class="flex flex-col items-center flex-1 relative">
                        {{-- Connector line (not on first step) --}}
                        @if ($i > 0)
                            <div class="absolute top-3 right-1/2 w-full h-0.5 -translate-y-1/2 {{ $i <= $currentIndex ? 'bg-primary-600 dark:bg-primary-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                        @endif

                        {{-- Dot --}}
                        <div class="relative z-10 w-6 h-6 rounded-full border-2 flex items-center justify-center text-white text-xs
                            {{ $i < $currentIndex
                                ? 'bg-primary-600 border-primary-600 dark:bg-primary-500 dark:border-primary-500'
                                : ($i === $currentIndex
                                    ? 'bg-primary-600 border-primary-600 dark:bg-primary-500 dark:border-primary-500 ring-4 ring-primary-600/20 dark:ring-primary-500/20'
                                    : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600') }}">
                            @if ($i < $currentIndex)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            @elseif ($i === $currentIndex)
                                <div class="w-2 h-2 rounded-full bg-white dark:bg-white"></div>
                            @endif
                        </div>

                        {{-- Label --}}
                        <p class="mt-1.5 text-center text-xs leading-tight
                            {{ $i <= $currentIndex
                                ? 'text-primary-600 dark:text-primary-400 font-semibold'
                                : 'text-gray-400 dark:text-gray-500' }}"
                           style="max-width:80px">
                            {{ $labels[$stepKey] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
