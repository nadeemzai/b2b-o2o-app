<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? 'OZ Wholesale' }} — OZ B2B Portal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: { DEFAULT: '#ff5b00', light: '#ff7a33', dark: '#e04a00' }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f5f5f5; }
        [x-cloak] { display: none !important; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .writing-vertical { writing-mode: vertical-rl; text-orientation: mixed; }
    </style>

    @livewireStyles
</head>
<body class="h-full flex flex-col">

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- GLOBAL LEFT SIDEBAR — Quick-nav drawer (public)                            --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div x-data="{ navOpen: false }" class="relative">

    {{-- Collapsed tab --}}
    <div @click="navOpen = true"
         x-show="!navOpen"
         class="fixed left-0 top-1/2 -translate-y-1/2 z-50 cursor-pointer select-none hidden md:flex">
        <div class="bg-brand hover:bg-brand-dark text-white py-10 px-2.5 rounded-r-xl shadow-xl flex flex-col items-center gap-3 transition-colors">
            <svg class="w-4 h-4 text-orange-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <span class="writing-vertical text-[11px] font-bold tracking-[3px] text-orange-100 uppercase">OZ Menu</span>
        </div>
    </div>

    {{-- Backdrop --}}
    <div x-show="navOpen" @click="navOpen = false" x-cloak
         class="fixed inset-0 bg-black/40 z-40"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>

    {{-- Sidebar panel --}}
    <div x-show="navOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="-translate-x-full opacity-0"
         x-transition:enter-end="translate-x-0 opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-x-0 opacity-100"
         x-transition:leave-end="-translate-x-full opacity-0"
         class="fixed left-0 top-0 h-full w-72 bg-white z-50 shadow-2xl flex flex-col">

        {{-- Header --}}
        <div class="bg-brand px-5 py-4 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 rounded-lg p-1.5">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </div>
                <div>
                    <span class="text-white font-black text-lg leading-none">OZ Wholesale</span>
                    <p class="text-orange-200 text-[10px] font-medium tracking-wide uppercase mt-0.5">B2B Portal</p>
                </div>
            </div>
            <button @click="navOpen = false" class="text-white/60 hover:text-white transition p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Nav items --}}
        <nav class="flex-1 overflow-y-auto py-3">

            {{-- Home --}}
            <a href="{{ route('public.catalogue') }}" @click="navOpen = false"
               class="flex items-center gap-4 px-5 py-4 group transition
                      {{ request()->routeIs('public.catalogue') && !request('category') ? 'bg-orange-50 border-l-4 border-brand' : 'border-l-4 border-transparent hover:bg-orange-50 hover:border-brand/40' }}">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                            {{ request()->routeIs('public.catalogue') && !request('category') ? 'bg-brand text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-orange-100 group-hover:text-brand' }} transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold {{ request()->routeIs('public.catalogue') && !request('category') ? 'text-brand' : 'text-slate-800 group-hover:text-brand' }} transition">Home</p>
                    <p class="text-[11px] text-slate-400">Browse wholesale</p>
                </div>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-brand/60 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            {{-- Products --}}
            <a href="{{ route('public.catalogue') }}" @click="navOpen = false"
               class="flex items-center gap-4 px-5 py-4 group transition
                      {{ request()->routeIs('public.catalogue*') || request()->routeIs('public.product*') ? 'bg-orange-50 border-l-4 border-brand' : 'border-l-4 border-transparent hover:bg-orange-50 hover:border-brand/40' }}">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                            {{ request()->routeIs('public.catalogue*') || request()->routeIs('public.product*') ? 'bg-brand text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-orange-100 group-hover:text-brand' }} transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold {{ request()->routeIs('public.catalogue*') || request()->routeIs('public.product*') ? 'text-brand' : 'text-slate-800 group-hover:text-brand' }} transition">Products</p>
                    <p class="text-[11px] text-slate-400">Full catalogue</p>
                </div>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-brand/60 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            {{-- Divider --}}
            <div class="mx-5 my-2 border-t border-slate-100"></div>
            <p class="px-5 pb-1 text-[10px] font-semibold text-slate-400 uppercase tracking-widest">Retailer Access</p>

            {{-- Sign In --}}
            <a href="{{ route('retailer.login') }}" @click="navOpen = false"
               class="flex items-center gap-4 px-5 py-4 group transition border-l-4 border-transparent hover:bg-orange-50 hover:border-brand/40">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 group-hover:bg-orange-100 group-hover:text-brand flex items-center justify-center shrink-0 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 group-hover:text-brand transition">Sign In</p>
                    <p class="text-[11px] text-slate-400">Retailer login</p>
                </div>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-brand/60 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            {{-- Register --}}
            <a href="{{ route('retailer.register') }}" @click="navOpen = false"
               class="flex items-center gap-4 px-5 py-4 group transition border-l-4 border-transparent hover:bg-orange-50 hover:border-brand/40">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 group-hover:bg-orange-100 group-hover:text-brand flex items-center justify-center shrink-0 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 group-hover:text-brand transition">Register</p>
                    <p class="text-[11px] text-slate-400">Apply as retailer</p>
                </div>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-brand/60 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

        </nav>

        {{-- Footer --}}
        <div class="shrink-0 border-t border-slate-100">
            <div class="px-5 py-3 bg-slate-50">
                <p class="text-[10px] text-slate-400 text-center">OZ Tech · B2B Wholesale Portal</p>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- TOP UTILITY BAR                                                            --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="bg-slate-800 text-slate-400 text-xs border-b border-slate-700">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-8 gap-4">
            <span class="hidden sm:inline text-slate-500 font-medium truncate">OZ Tech · B2B Wholesale Portal</span>
            <div class="flex items-center gap-4 ml-auto">
                @livewire('language-switcher')
                @livewire('currency-switcher')
                <span class="text-slate-600 hidden sm:inline">|</span>
                <a href="{{ route('retailer.login') }}" class="hidden sm:inline hover:text-white transition">Sign In</a>
                <a href="{{ route('retailer.register') }}" class="hidden sm:inline hover:text-white transition text-orange-400">Register</a>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MAIN HEADER — Logo + Search + Icons                                        --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<header class="bg-white shadow-sm sticky top-0 z-30">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4 lg:gap-6 h-[60px]">

            {{-- Logo --}}
            <a href="{{ route('public.catalogue') }}" class="shrink-0 flex items-baseline gap-1">
                <span class="text-brand font-black text-2xl tracking-tight leading-none">OZ</span>
                <span class="text-slate-700 font-bold text-[13px] leading-none">Wholesale</span>
                <span class="text-brand/40 text-[9px] font-semibold tracking-widest uppercase ml-0.5 self-end mb-0.5">B2B</span>
            </a>

            {{-- Search bar --}}
            <form action="{{ route('public.catalogue') }}" method="GET" class="flex-1 flex min-w-0">
                <div class="flex w-full rounded-sm overflow-hidden border-2 border-brand">
                    {{-- Category select --}}
                    <div class="relative shrink-0 border-r border-slate-200">
                        <select name="category"
                                class="h-[42px] appearance-none bg-slate-50 text-slate-600 text-xs pl-3 pr-7 focus:outline-none border-0 cursor-pointer font-medium min-w-[110px] max-w-[140px]">
                            <option value="">All Categories</option>
                            @php $headerCats = \App\Models\Category::where('is_active', true)->orderBy('name')->get(); @endphp
                            @foreach($headerCats as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2">
                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    {{-- Text input --}}
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search products, brands, suppliers..."
                           class="flex-1 h-[42px] px-4 text-sm text-slate-800 placeholder-slate-400 focus:outline-none min-w-0 bg-white" />
                    {{-- Search button --}}
                    <button type="submit"
                            class="h-[42px] px-5 lg:px-7 bg-brand hover:bg-brand-dark text-white font-semibold text-sm transition shrink-0 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span class="hidden sm:inline">Search</span>
                    </button>
                </div>
            </form>

            {{-- Right-side action icons --}}
            <div class="flex items-center shrink-0 gap-1">

                {{-- Sign In --}}
                <a href="{{ route('retailer.login') }}"
                   class="flex flex-col items-center gap-0.5 px-2.5 py-2 text-slate-500 hover:text-brand rounded hover:bg-orange-50 transition group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span class="text-[9px] font-semibold whitespace-nowrap group-hover:text-brand">Sign In</span>
                </a>

                {{-- Register --}}
                <a href="{{ route('retailer.register') }}"
                   class="flex flex-col items-center gap-0.5 px-2.5 py-2 text-slate-500 hover:text-brand rounded hover:bg-orange-50 transition group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span class="text-[9px] font-semibold whitespace-nowrap group-hover:text-brand">Register</span>
                </a>

            </div>
        </div>
    </div>

    {{-- Category navigation bar --}}
    <div class="border-t border-slate-100 bg-slate-50/80">
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center h-9 overflow-x-auto scrollbar-hide gap-0">
                <a href="{{ route('public.catalogue') }}"
                   class="flex items-center gap-1 px-3 h-full text-xs font-semibold whitespace-nowrap border-b-2 transition
                          {{ request()->routeIs('public.catalogue') && !request('category') ? 'border-brand text-brand' : 'border-transparent text-slate-600 hover:text-brand hover:border-brand/50' }}">
                    🏪 All Products
                </a>
                @php $navCats = \App\Models\Category::where('is_active', true)->orderBy('name')->take(10)->get(); @endphp
                @foreach($navCats as $cat)
                <a href="{{ route('public.catalogue') }}?category={{ $cat->id }}"
                   class="flex items-center px-3 h-full text-xs whitespace-nowrap border-b-2 transition
                          {{ request('category') == $cat->id ? 'border-brand text-brand font-semibold' : 'border-transparent text-slate-600 hover:text-brand hover:border-brand/50' }}">
                    {{ app()->getLocale() === 'zh_CN' && $cat->name_zh ? $cat->name_zh : $cat->name }}
                </a>
                @endforeach
            </nav>
        </div>
    </div>
</header>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- FLASH MESSAGES                                                             --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@if(session('success') || session('error'))
<div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 pt-3"
     x-data x-init="setTimeout(() => $el.remove(), 4000)">
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        {{ session('error') }}
    </div>
    @endif
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- PAGE CONTENT                                                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<main class="flex-1 max-w-screen-xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-5">
    {{ $slot }}
</main>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- FOOTER                                                                     --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<footer class="text-center text-slate-400 text-xs py-4 border-t border-slate-200 bg-white mt-4">
    OZ Tech &mdash; B2B Wholesale Portal &copy; {{ date('Y') }}
</footer>

@livewireScripts
</body>
</html>
