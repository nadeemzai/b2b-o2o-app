<div x-data="{ open: false }" class="relative">

    {{-- Trigger button --}}
    <button @click="open = !open"
            type="button"
            class="flex items-center gap-1.5 text-orange-100 hover:text-white text-sm font-medium transition px-2 py-1 rounded hover:bg-brand-dark"
            :aria-expanded="open">
        <span>{{ $currencies[$current]['flag'] }}</span>
        <span class="hidden sm:inline">{{ $current }}</span>
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
         class="absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-slate-100 py-1 z-50">

        {{-- Rate info header --}}
        <div class="px-3 py-1.5 text-[10px] text-slate-400 border-b border-slate-100 uppercase tracking-wide">
            Live rates (base: PKR)
        </div>

        @foreach ($currencies as $code => $info)
        <form method="POST" action="{{ route('switch.currency', $code) }}" class="block">
            @csrf
            <button type="submit"
                    class="w-full text-left flex items-center gap-2.5 px-3 py-2 text-sm transition
                           {{ $current === $code ? 'bg-orange-50 text-brand font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                <span>{{ $info['flag'] }}</span>
                <div class="flex-1">
                    <div class="font-medium">{{ $code }}</div>
                    @if ($code !== 'PKR')
                    <div class="text-[10px] text-slate-400">
                        1 PKR = {{ number_format($rates[$code] ?? 0, 5) }} {{ $code }}
                    </div>
                    @else
                    <div class="text-[10px] text-slate-400">Base currency</div>
                    @endif
                </div>
                @if ($current === $code)
                <svg class="w-3.5 h-3.5 ml-auto text-brand" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                @endif
            </button>
        </form>
        @endforeach

        {{-- Updated date footer --}}
        <div class="px-3 py-1.5 text-[10px] text-slate-400 border-t border-slate-100">
            Updated daily via Frankfurter API
        </div>

    </div>
</div>
