<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-2">

            {{-- Row 1: Calendar icon + active label + preset buttons --}}
            <div class="flex flex-wrap items-center gap-2">

                <div class="flex items-center gap-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 shrink-0">
                    <x-heroicon-m-calendar-days class="w-4 h-4" />
                    <span>Date Range:</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $this->getRangeLabel() }}</span>
                </div>

                <div class="flex flex-wrap gap-1.5">
                    @foreach([
                        'today'  => 'Today',
                        'week'   => 'This Week',
                        'month'  => 'This Month',
                        'days30' => 'Last 30 Days',
                        'days90' => 'Last 90 Days',
                        'all'    => 'All Time',
                    ] as $preset => $label)
                    <button
                        wire:click="applyPreset('{{ $preset }}')"
                        type="button"
                        @if($activePreset === $preset)
                            style="background-color:#0284c7;color:#ffffff;border-color:#0284c7;"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border shadow-sm transition-all duration-150"
                        @else
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 hover:border-sky-400 hover:text-sky-600 transition-all duration-150"
                        @endif>
                        {{ $label }}
                    </button>
                    @endforeach
                </div>

            </div>

            {{-- Row 2: Custom date range — always on its own line, always fully visible --}}
            <div class="flex items-center gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 shrink-0">Custom:</span>
                <input
                    type="date"
                    wire:model="dateFrom"
                    @if($activePreset === 'custom') style="border-color:#0284c7;" @endif
                    class="text-xs rounded-lg border border-gray-200 dark:border-gray-700 px-2.5 py-1.5 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 transition focus:ring-2 focus:ring-sky-500 focus:outline-none"
                    max="{{ now()->toDateString() }}"
                    placeholder="From"
                />
                <span class="text-gray-400 text-xs shrink-0">→</span>
                <input
                    type="date"
                    wire:model="dateTo"
                    @if($activePreset === 'custom') style="border-color:#0284c7;" @endif
                    class="text-xs rounded-lg border border-gray-200 dark:border-gray-700 px-2.5 py-1.5 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 transition focus:ring-2 focus:ring-sky-500 focus:outline-none"
                    max="{{ now()->toDateString() }}"
                    placeholder="To"
                />
                <button
                    wire:click="applyCustom"
                    type="button"
                    style="background-color:#0284c7;color:#ffffff;"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold shadow-sm hover:opacity-90 transition shrink-0">
                    Apply
                </button>
            </div>

        </div>
    </x-filament::section>
</x-filament-widgets::widget>
