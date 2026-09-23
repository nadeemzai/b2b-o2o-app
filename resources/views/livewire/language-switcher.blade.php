<div x-data="{ open: false }" class="relative">

    {{-- Trigger button --}}
    <button @click="open = !open"
            type="button"
            class="flex items-center gap-1.5 text-orange-100 hover:text-white text-sm font-medium transition px-2 py-1 rounded hover:bg-brand-dark"
            :aria-expanded="open">
        <span>{{ $languages[$current]['flag'] }}</span>
        <span class="hidden sm:inline">{{ $languages[$current]['short'] }}</span>
        <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
         @click.away="open = false"
         x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-36 bg-white rounded-lg shadow-lg border border-slate-100 py-1 z-50">

        @foreach ($languages as $locale => $lang)
        <form method="POST" action="{{ route('switch.locale', $locale) }}" class="block">
            @csrf
            <button type="submit"
                    class="w-full text-left flex items-center gap-2.5 px-3 py-2 text-sm transition
                           {{ $current === $locale ? 'bg-orange-50 text-brand font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                <span>{{ $lang['flag'] }}</span>
                <span>{{ $lang['label'] }}</span>
                @if ($current === $locale)
                <svg class="w-3.5 h-3.5 ml-auto text-brand" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                @endif
            </button>
        </form>
        @endforeach

    </div>
</div>
