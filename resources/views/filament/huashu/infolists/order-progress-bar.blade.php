@php
    $steps = ['transferred', 'fulfilling', 'delivered'];
    $labels = [
        'transferred' => 'Received',
        'fulfilling'  => 'Fulfilling',
        'delivered'   => 'Delivered',
    ];
    $currentStatus = $record->status ?? 'transferred';
    $currentIndex  = array_search($currentStatus, $steps, true);
    if ($currentIndex === false) { $currentIndex = 0; }
@endphp

<div class="px-4 py-4">
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
</div>
