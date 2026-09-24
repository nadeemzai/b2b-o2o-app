<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? 'OZ B2B Portal' }} — OZ Wholesale</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

    @livewireScripts
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
        [wire\:loading] { opacity: .6; pointer-events: none; }
        [x-cloak] { display: none !important; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    @livewireStyles
</head>
<body class="h-full flex flex-col">

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- TOP UTILITY BAR                                                            --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@auth
<div class="bg-slate-800 text-slate-400 text-xs border-b border-slate-700">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-8 gap-4">
            <span class="hidden sm:inline text-slate-500 font-medium truncate">OZ Tech · B2B Wholesale Portal</span>
            <div class="flex items-center gap-4 ml-auto">
                @livewire('language-switcher')
                @livewire('currency-switcher')
                <span class="text-slate-600 hidden sm:inline">|</span>
                <a href="{{ route('retailer.dashboard') }}"
                   class="hidden sm:inline hover:text-white transition {{ request()->routeIs('retailer.dashboard') ? 'text-white' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('retailer.orders') }}"
                   class="hidden sm:inline hover:text-white transition {{ request()->routeIs('retailer.orders*') ? 'text-white' : '' }}">
                    My Orders
                </a>
                <span class="text-slate-600">|</span>
                <span class="text-slate-500 truncate max-w-[120px]">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('retailer.logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="hover:text-red-400 transition">Sign Out</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MAIN HEADER — Logo + Search + Icons + Category bar with mega-dropdown     --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<header x-data="{ mobileNav: false }" class="bg-white shadow-sm sticky top-0 z-30">

    {{-- ── Mobile nav backdrop ─────────────────────────────────────────── --}}
    <div x-show="mobileNav" @click="mobileNav = false" x-cloak
         class="fixed inset-0 bg-black/40 z-40 md:hidden"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>

    {{-- ── Mobile nav slide-out panel ─────────────────────────────────── --}}
    <div x-show="mobileNav" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed left-0 top-0 h-full w-72 bg-white z-50 shadow-2xl flex flex-col md:hidden">

        <div class="bg-brand px-5 py-4 flex items-center justify-between shrink-0">
            <span class="text-white font-black text-lg leading-none">OZ Wholesale</span>
            <button @click="mobileNav = false" class="text-white/70 hover:text-white transition p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto py-2">
            <a href="{{ route('retailer.dashboard') }}" @click="mobileNav = false"
               class="flex items-center gap-3 px-5 py-3.5 text-sm border-l-4 transition
                      {{ request()->routeIs('retailer.dashboard') ? 'bg-orange-50 text-brand font-semibold border-brand' : 'text-slate-700 border-transparent hover:bg-orange-50 hover:text-brand hover:border-brand/40' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>
            <a href="{{ route('retailer.catalogue') }}" @click="mobileNav = false"
               class="flex items-center gap-3 px-5 py-3.5 text-sm border-l-4 transition
                      {{ request()->routeIs('retailer.catalogue*') ? 'bg-orange-50 text-brand font-semibold border-brand' : 'text-slate-700 border-transparent hover:bg-orange-50 hover:text-brand hover:border-brand/40' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
                Products
            </a>
            <a href="{{ route('retailer.orders') }}" @click="mobileNav = false"
               class="flex items-center gap-3 px-5 py-3.5 text-sm border-l-4 transition
                      {{ request()->routeIs('retailer.orders*') ? 'bg-orange-50 text-brand font-semibold border-brand' : 'text-slate-700 border-transparent hover:bg-orange-50 hover:text-brand hover:border-brand/40' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                My Orders
            </a>

            <div class="px-5 pt-4 pb-1.5">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Categories</p>
            </div>
            @php $mobileCats = \App\Models\Category::where('is_active', true)->orderBy('name')->get(); @endphp
            @foreach($mobileCats as $mcat)
            <a href="{{ route('retailer.catalogue') }}?category={{ $mcat->id }}" @click="mobileNav = false"
               class="flex items-center gap-3 px-5 py-2.5 text-sm text-slate-700 border-l-4 border-transparent hover:bg-orange-50 hover:text-brand hover:border-brand/40 transition">
                {{ $mcat->name }}
            </a>
            @endforeach
        </nav>

        <div class="shrink-0 border-t border-slate-100 px-5 py-3 bg-slate-50 flex items-center gap-3">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-slate-700 truncate">{{ auth()->user()->name }}</p>
                <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('retailer.logout') }}">
                @csrf
                <button type="submit" title="Sign Out"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    {{-- ── Header row: hamburger + logo + search + icons ──────────────── --}}
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3 lg:gap-6 h-[60px]">

            {{-- Mobile hamburger --}}
            <button @click="mobileNav = true"
                    class="md:hidden shrink-0 p-2 -ml-1 text-slate-500 hover:text-brand rounded hover:bg-orange-50 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Logo --}}
            <a href="{{ route('retailer.dashboard') }}" class="shrink-0 flex items-baseline gap-1">
                <span class="text-brand font-black text-2xl tracking-tight leading-none">OZ</span>
                <span class="text-slate-700 font-bold text-[13px] leading-none">Wholesale</span>
                <span class="text-brand/40 text-[9px] font-semibold tracking-widest uppercase ml-0.5 self-end mb-0.5">B2B</span>
            </a>

            {{-- Search bar (1688 style) --}}
            <form action="{{ route('retailer.catalogue') }}" method="GET" class="flex-1 flex min-w-0">
                <div class="flex w-full rounded-sm overflow-hidden border-2 border-brand">
                    {{-- Category select (hidden on mobile to save space) --}}
                    <div class="relative shrink-0 border-r border-slate-200 hidden sm:block">
                        <select name="category"
                                class="h-[42px] appearance-none bg-slate-50 text-slate-600 text-xs pl-3 pr-7 focus:outline-none border-0 cursor-pointer font-medium min-w-[110px] max-w-[140px]">
                            <option value="">All Categories</option>
                            @php $headerCats = \App\Models\Category::where('is_active', true)->orderBy('name')->get(); @endphp
                            @foreach($headerCats as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
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
            <div class="flex items-center shrink-0">

                {{-- Price Comparison --}}
                <a href="#" title="Price Comparison"
                   class="hidden lg:flex flex-col items-center gap-0.5 px-2.5 py-2 text-slate-500 hover:text-brand rounded hover:bg-orange-50 transition group cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="text-[9px] font-semibold whitespace-nowrap group-hover:text-brand">Compare</span>
                </a>

                {{-- Orders --}}
                <a href="{{ route('retailer.orders') }}"
                   class="flex flex-col items-center gap-0.5 px-2.5 py-2 rounded hover:bg-orange-50 transition group
                          {{ request()->routeIs('retailer.orders*') ? 'text-brand' : 'text-slate-500 hover:text-brand' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="text-[9px] font-semibold whitespace-nowrap group-hover:text-brand">Orders</span>
                </a>

                {{-- Cart (reactive Livewire counter) --}}
                @livewire('cart-count')

                {{-- Messages --}}
                <a href="#" title="Messages"
                   class="hidden sm:flex flex-col items-center gap-0.5 px-2.5 py-2 text-slate-500 hover:text-brand rounded hover:bg-orange-50 transition group">
                    <div class="relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <span class="text-[9px] font-semibold whitespace-nowrap group-hover:text-brand">Messages</span>
                </a>

                {{-- Account dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="flex flex-col items-center gap-0.5 px-2.5 py-2 text-slate-500 hover:text-brand rounded hover:bg-orange-50 transition group">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-[9px] font-semibold whitespace-nowrap group-hover:text-brand">Account</span>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute right-0 top-full mt-1 w-52 bg-white rounded-xl shadow-xl border border-slate-100 py-2 text-sm z-50">
                        <div class="px-4 py-2.5 border-b border-slate-100">
                            <p class="font-semibold text-slate-800 text-xs truncate">{{ auth()->user()->name }}</p>
                            <p class="text-slate-400 text-[10px] truncate mt-0.5">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('retailer.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-slate-700 hover:bg-slate-50 transition text-sm">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            Dashboard
                        </a>
                        <a href="{{ route('retailer.orders') }}" class="flex items-center gap-2.5 px-4 py-2 text-slate-700 hover:bg-slate-50 transition text-sm">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            My Orders
                        </a>
                        <div class="border-t border-slate-100 mt-1 pt-1">
                            <form method="POST" action="{{ route('retailer.logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-red-500 hover:bg-red-50 transition text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Category nav bar with "All Categories" mega-dropdown ─────────── --}}
    <div class="border-t border-slate-100 bg-white">
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-stretch h-10 overflow-x-auto scrollbar-hide">

                {{-- All Categories button + mega-dropdown (desktop only) --}}
                <div x-data="{ open: false }"
                     @mouseenter="open = true"
                     @mouseleave="open = false"
                     class="relative shrink-0 hidden md:flex items-stretch mr-2">
                    <button class="flex items-center gap-2 px-4 bg-brand hover:bg-brand-dark text-white text-xs font-bold whitespace-nowrap transition h-full focus:outline-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        All Categories
                        <svg class="w-3 h-3 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Mega-dropdown panel --}}
                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="absolute left-0 top-full z-50 bg-white shadow-2xl border border-slate-200 border-t-2 border-t-brand rounded-b-xl"
                         style="min-width: 540px; transform-origin: top left;">
                        <div class="px-5 pt-3.5 pb-2 border-b border-slate-100">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Browse by Category</p>
                        </div>
                        <div class="p-4 grid grid-cols-3 gap-0.5">
                            @php $megaCats = \App\Models\Category::where('is_active', true)->orderBy('name')->get(); @endphp
                            @foreach($megaCats as $mcat)
                            <a href="{{ route('retailer.catalogue') }}?category={{ $mcat->id }}"
                               class="flex items-center gap-2 px-3 py-2.5 rounded-lg hover:bg-orange-50 group transition">
                                <svg class="w-3.5 h-3.5 shrink-0 text-slate-300 group-hover:text-brand transition" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                                </svg>
                                <span class="text-xs text-slate-700 group-hover:text-brand truncate transition">
                                    {{ app()->getLocale() === 'zh_CN' && $mcat->name_zh ? $mcat->name_zh : $mcat->name }}
                                </span>
                            </a>
                            @endforeach
                        </div>
                        <div class="px-5 py-2.5 bg-orange-50 border-t border-slate-100 rounded-b-xl">
                            <a href="{{ route('retailer.catalogue') }}"
                               class="text-xs font-semibold text-brand hover:underline">
                                View all products →
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Divider --}}
                <div class="hidden md:block w-px bg-slate-200 my-2 mr-1 shrink-0"></div>

                {{-- All Products link --}}
                <a href="{{ route('retailer.catalogue') }}"
                   class="flex items-center px-3 h-full text-xs font-semibold whitespace-nowrap border-b-2 transition shrink-0
                          {{ request()->routeIs('retailer.catalogue') && !request('category') ? 'border-brand text-brand' : 'border-transparent text-slate-600 hover:text-brand hover:border-brand/50' }}">
                    All Products
                </a>

                {{-- Category quick-links --}}
                @php $navCats = \App\Models\Category::where('is_active', true)->orderBy('name')->take(10)->get(); @endphp
                @foreach($navCats as $cat)
                <a href="{{ route('retailer.catalogue') }}?category={{ $cat->id }}"
                   class="flex items-center px-3 h-full text-xs whitespace-nowrap border-b-2 transition shrink-0
                          {{ request('category') == $cat->id ? 'border-brand text-brand font-semibold' : 'border-transparent text-slate-600 hover:text-brand hover:border-brand/50' }}">
                    {{ app()->getLocale() === 'zh_CN' && $cat->name_zh ? $cat->name_zh : $cat->name }}
                </a>
                @endforeach

            </div>
        </div>
    </div>

</header>
@endauth

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- FLASH MESSAGES                                                             --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@php $orderSuccess = session()->pull('order_placed_success'); @endphp
@if($orderSuccess || session('success') || session('error'))
<div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 pt-3"
     x-data x-init="setTimeout(() => $el.remove(), 4000)">
    @if($orderSuccess || session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ $orderSuccess ?: session('success') }}
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

</body>
</html>
