<div x-data="{ open: false }" class="relative flex items-center">

    {{-- Trigger button --}}
    <button @click="open = !open"
            type="button"
            :aria-expanded="open"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-700
                   bg-white dark:bg-gray-800 px-3 py-1.5 text-sm font-medium
                   text-gray-700 dark:text-gray-300
                   hover:bg-gray-50 dark:hover:bg-gray-700
                   shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-sky-500">
        <span class="text-base leading-none">{{ $languages[$current]['flag'] }}</span>
        <span>{{ $languages[$current]['short'] }}</span>
        <svg class="w-3.5 h-3.5 opacity-60 transition-transform duration-150"
             :class="{ 'rotate-180': open }"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown panel --}}
    <div x-show="open"
         @click.away="open = false"
         x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
         class="absolute right-0 top-full mt-2 w-40 origin-top-right
                rounded-xl bg-white dark:bg-gray-800
                shadow-lg ring-1 ring-black/5 dark:ring-white/10
                py-1 z-50">

        @foreach ($languages as $locale => $lang)
            <button wire:click="switchLanguage('{{ $locale }}')"
                    type="button"
                    class="w-full flex items-center gap-2.5 px-3 py-2.5 text-sm transition-colors duration-100
                           {{ $current === $locale
                               ? 'bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 font-semibold'
                               : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                <span class="text-base leading-none">{{ $lang['flag'] }}</span>
                <span>{{ $lang['label'] }}</span>
                @if ($current === $locale)
                    <svg class="w-3.5 h-3.5 ml-auto text-sky-500 dark:text-sky-400 shrink-0"
                         fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                              clip-rule="evenodd"/>
                    </svg>
                @endif
            </button>
        @endforeach

    </div>
</div>
