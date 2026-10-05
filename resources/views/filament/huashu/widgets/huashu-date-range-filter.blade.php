<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-wrap items-center gap-3">

            {{-- Calendar icon + label --}}
            <div class="flex items-center gap-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 shrink-0">
                <x-heroicon-m-calendar-days class="w-4 h-4" />
                <span>Date Range:</span>
                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $this->getRangeLabel() }}</span>
            </div>

            {{-- Preset buttons --}}
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
                    @class([
                        'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150 border',
                        'bg-sky-600 text-white border-sky-600 shadow-sm'     => $activePreset === $preset,
                        'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-sky-400 hover:text-sky-600' => $activePreset !== $preset,
                    ])>
                    {{ $label }}
                </button>
                @endforeach
            </div>

            {{-- Divider --}}
            <div class="hidden sm:block h-5 w-px bg-gray-200 dark:bg-gray-700"></div>

            {{-- Custom range inputs --}}
            <div class="flex items-center gap-2">
                <input
                    type="date"
                    wire:model="dateFrom"
                    @class([
                        'text-xs rounded-lg border px-2.5 py-1.5 bg-white dark:bg-gray-800 dark:text-gray-200 transition focus:ring-2 focus:ring-sky-500 focus:outline-none',
                        'border-sky-400' => $activePreset === 'custom',
                        'border-gray-200 dark:border-gray-700' => $activePreset !== 'custom',
                    ])
                    max="{{ now()->toDateString() }}"
                    placeholder="From"
                />
                <span class="text-gray-400 text-xs">→</span>
                <input
                    type="date"
                    wire:model="dateTo"
                    @class([
                        'text-xs rounded-lg border px-2.5 py-1.5 bg-white dark:bg-gray-800 dark:text-gray-200 transition focus:ring-2 focus:ring-sky-500 focus:outline-none',
                        'border-sky-400' => $activePreset === 'custom',
                        'border-gray-200 dark:border-gray-700' => $activePreset !== 'custom',
                    ])
                    max="{{ now()->toDateString() }}"
                    placeholder="To"
                />
                <button
                    wire:click="applyCustom"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-sky-600 text-white hover:bg-sky-700 transition shadow-sm">
                    Apply
                </button>
            </div>

        </div>
    </x-filament::section>
</x-filament-widgets::widget>
