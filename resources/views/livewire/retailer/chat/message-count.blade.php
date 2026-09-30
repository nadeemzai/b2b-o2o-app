<a href="{{ route('retailer.messages') }}"
   title="Messages"
   class="hidden sm:flex flex-col items-center gap-0.5 px-2.5 py-2 rounded transition group
          {{ request()->routeIs('retailer.messages')
              ? 'text-brand bg-orange-50'
              : 'text-slate-500 hover:text-brand hover:bg-orange-50' }}">
    <div class="relative">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
        </svg>
        @if($count > 0)
            <span class="absolute -top-1.5 -right-1.5 min-w-[16px] h-4 px-0.5
                         bg-red-500 text-white text-[9px] font-bold rounded-full
                         flex items-center justify-center leading-none">
                {{ $count > 99 ? '99+' : $count }}
            </span>
        @endif
    </div>
    <span class="text-[9px] font-semibold whitespace-nowrap group-hover:text-brand">Messages</span>
</a>
