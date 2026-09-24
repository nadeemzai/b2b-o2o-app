<a href="{{ route('retailer.cart') }}"
   class="flex flex-col items-center gap-0.5 px-2.5 py-2 rounded hover:bg-orange-50 transition group
          {{ request()->routeIs('retailer.cart') ? 'text-brand' : 'text-slate-500 hover:text-brand' }}">
    <div class="relative">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        @if($count > 0)
        <span class="absolute -top-2 -right-2.5 bg-brand text-white text-[9px] font-black rounded-full min-w-[15px] h-[15px] flex items-center justify-center px-0.5 leading-none">
            {{ $count > 99 ? '99+' : $count }}
        </span>
        @endif
    </div>
    <span class="text-[9px] font-semibold whitespace-nowrap group-hover:text-brand">Cart</span>
</a>
