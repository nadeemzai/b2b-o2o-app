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
        .writing-vertical { writing-mode: vertical-rl; text-orientation: mixed; }
    </style>


    {{-- ── Compare store (localStorage-backed Alpine store) ────────────── --}}
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('compare', {
            items: [],
            maxItems: 4,

            init() {
                try {
                    const saved = localStorage.getItem('oz_compare');
                    if (saved) this.items = JSON.parse(saved) || [];
                } catch(e) { this.items = []; }
            },

            save() {
                try {
                    localStorage.setItem('oz_compare', JSON.stringify(this.items));
                } catch(e) {}
            },

            add(product) {
                if (this.items.length >= this.maxItems) return;
                if (this.has(product.id)) return;
                this.items.push(product);
                this.save();
            },

            remove(id) {
                this.items = this.items.filter(p => p.id != id);
                this.save();
            },

            toggle(product) {
                this.has(product.id) ? this.remove(product.id) : this.add(product);
            },

            has(id) {
                return this.items.some(p => p.id == id);
            },

            clear() {
                this.items = [];
                this.save();
            },

            goCompare() {
                if (this.items.length < 2) return;
                const ids = this.items.map(p => p.id).join(',');
                window.location.href = '/retailer/compare?ids=' + ids;
            }
        });
    });
    </script>

    @livewireStyles
</head>
<body class="h-full flex flex-col">

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- GLOBAL LEFT SIDEBAR — Quick-nav drawer                                     --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@auth
<div x-data="{ navOpen: false }" class="relative">

    {{-- Collapsed tab (always visible on left edge) --}}
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
            <a href="{{ route('retailer.home') }}" @click="navOpen = false"
               class="flex items-center gap-4 px-5 py-4 group transition
                      {{ request()->routeIs('retailer.home') ? 'bg-orange-50 border-l-4 border-brand' : 'border-l-4 border-transparent hover:bg-orange-50 hover:border-brand/40' }}">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                            {{ request()->routeIs('retailer.home') ? 'bg-brand text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-orange-100 group-hover:text-brand' }} transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold {{ request()->routeIs('retailer.home') ? 'text-brand' : 'text-slate-800 group-hover:text-brand' }} transition">Home</p>
                    <p class="text-[11px] text-slate-400">Dashboard overview</p>
                </div>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-brand/60 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            {{-- Orders --}}
            <a href="{{ route('retailer.orders') }}" @click="navOpen = false"
               class="flex items-center gap-4 px-5 py-4 group transition
                      {{ request()->routeIs('retailer.orders*') ? 'bg-orange-50 border-l-4 border-brand' : 'border-l-4 border-transparent hover:bg-orange-50 hover:border-brand/40' }}">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                            {{ request()->routeIs('retailer.orders*') ? 'bg-brand text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-orange-100 group-hover:text-brand' }} transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold {{ request()->routeIs('retailer.orders*') ? 'text-brand' : 'text-slate-800 group-hover:text-brand' }} transition">Orders</p>
                    <p class="text-[11px] text-slate-400">My order history</p>
                </div>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-brand/60 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            {{-- Wishlist --}}
            <a href="#" @click="navOpen = false"
               class="flex items-center gap-4 px-5 py-4 group transition border-l-4 border-transparent hover:bg-orange-50 hover:border-brand/40">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 group-hover:bg-orange-100 group-hover:text-brand flex items-center justify-center shrink-0 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 group-hover:text-brand transition">Wishlist</p>
                    <p class="text-[11px] text-slate-400">Saved products</p>
                </div>
                <span class="text-[9px] text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded font-medium shrink-0">Soon</span>
            </a>

            {{-- Products --}}
            <a href="{{ route('retailer.catalogue') }}" @click="navOpen = false"
               class="flex items-center gap-4 px-5 py-4 group transition
                      {{ request()->routeIs('retailer.catalogue*') ? 'bg-orange-50 border-l-4 border-brand' : 'border-l-4 border-transparent hover:bg-orange-50 hover:border-brand/40' }}">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                            {{ request()->routeIs('retailer.catalogue*') ? 'bg-brand text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-orange-100 group-hover:text-brand' }} transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold {{ request()->routeIs('retailer.catalogue*') ? 'text-brand' : 'text-slate-800 group-hover:text-brand' }} transition">Products</p>
                    <p class="text-[11px] text-slate-400">Browse catalogue</p>
                </div>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-brand/60 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            {{-- Me / Profile --}}
            <a href="#" @click="navOpen = false"
               class="flex items-center gap-4 px-5 py-4 group transition border-l-4 border-transparent hover:bg-orange-50 hover:border-brand/40">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 group-hover:bg-orange-100 group-hover:text-brand flex items-center justify-center shrink-0 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 group-hover:text-brand transition">Me</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->name }}</p>
                </div>
                <span class="text-[9px] text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded font-medium shrink-0">Soon</span>
            </a>

        </nav>

        {{-- Footer: user info + sign out --}}
        <div class="shrink-0 border-t border-slate-100">
            <div class="px-5 py-3 bg-slate-50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-brand/10 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
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
    </div>
</div>
@endauth

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- TOP UTILITY BAR  (WiseMarket-style — dark bar)                            --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@auth
<div class="bg-[#1a2035] text-xs text-slate-400 border-b border-[#252d45]">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-9 gap-4">

            {{-- Left: delivery promise + tagline --}}
            <div class="hidden sm:flex items-center gap-4 text-slate-400">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-brand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 10a2 2 0 002 2h8a2 2 0 002-2L19 8m-9 4h4"/>
                    </svg>
                    <span class="text-slate-300 font-medium">Free delivery on orders over $500</span>
                </span>
                <span class="text-slate-700">·</span>
                <span class="text-slate-500">B2B Wholesale · Bulk Orders Welcome</span>
            </div>

            {{-- Right: lang/currency + nav links + user --}}
            <div class="flex items-center gap-3 ml-auto">
                @livewire('language-switcher')
                @livewire('currency-switcher')
                <span class="text-slate-700 hidden sm:inline">|</span>
                <a href="{{ route('retailer.home') }}"
                   class="hidden sm:inline hover:text-white transition {{ request()->routeIs('retailer.home') ? 'text-white' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('retailer.orders') }}"
                   class="hidden sm:inline hover:text-white transition {{ request()->routeIs('retailer.orders*') ? 'text-white' : '' }}">
                    My Orders
                </a>
                <span class="text-slate-700">|</span>
                <span class="flex items-center gap-1.5 text-slate-400">
                    <svg class="w-3 h-3 text-brand/70 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    <span class="truncate max-w-[100px]">{{ auth()->user()->name }}</span>
                </span>
                <form method="POST" action="{{ route('retailer.logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="hover:text-red-400 transition">Sign Out</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MAIN HEADER — WiseMarket style: Logo · Search · Action Icons              --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<header class="bg-white shadow-sm sticky top-0 z-30">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4 lg:gap-6 h-[68px]">

            {{-- ── Logo ─────────────────────────────────────────────────── --}}
            <a href="{{ route('public.home') }}" class="shrink-0 flex items-center gap-2.5 min-w-fit">
                {{-- Orange badge --}}
                <div class="w-9 h-9 bg-brand rounded-lg flex items-center justify-center shadow-sm shrink-0">
                    <span class="text-white font-black text-[13px] tracking-tight leading-none select-none">OZ</span>
                </div>
                {{-- Wordmark --}}
                <div class="hidden sm:block leading-none">
                    <div class="font-black text-slate-800 text-[18px] tracking-tight leading-tight">
                        Wholesale
                    </div>
                    <div class="text-[9px] text-brand font-bold tracking-[3px] uppercase mt-0.5">B2B Portal</div>
                </div>
            </a>

            {{-- ── Search bar ───────────────────────────────────────────── --}}
            <form action="{{ route('retailer.catalogue') }}" method="GET" class="flex-1 flex min-w-0">
                <div class="flex w-full rounded overflow-hidden border-2 border-brand shadow-sm">
                    {{-- Category select --}}
                    <div class="relative shrink-0 border-r border-slate-200 bg-slate-50">
                        <select name="category"
                                class="h-[44px] appearance-none bg-transparent text-slate-600 text-xs pl-3 pr-7 focus:outline-none border-0 cursor-pointer font-medium min-w-[110px] max-w-[140px]">
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
                           id="header-search-input"
                           value="{{ request('search') }}"
                           placeholder="Search products, brands, suppliers..."
                           class="flex-1 h-[44px] px-4 text-sm text-slate-800 placeholder-slate-400 focus:outline-none min-w-0 bg-white" />

                    {{-- Camera / image search --}}
                    <button type="button"
                            id="image-search-btn"
                            title="Search by image"
                            class="h-[44px] px-3 border-l border-slate-200 bg-white hover:bg-orange-50 text-slate-400 hover:text-brand transition shrink-0 flex items-center">
                        <svg id="img-search-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <svg id="img-search-spinner" class="w-5 h-5 animate-spin hidden text-brand" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                    </button>
                    <input type="file" id="image-search-file" accept="image/*" class="hidden" />

                    {{-- Search button --}}
                    <button type="submit"
                            class="h-[44px] px-5 lg:px-8 bg-brand hover:bg-brand-dark text-white font-bold text-sm transition shrink-0 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span class="hidden sm:inline font-semibold tracking-wide">Search</span>
                    </button>
                </div>
            </form>

            {{-- Image-search error toast --}}
            @if(session('image_search_error'))
            <div id="img-search-error"
                 class="absolute top-full left-0 right-0 mt-1 mx-2 bg-red-50 border border-red-200 text-red-700 text-xs rounded px-3 py-2 shadow z-50">
                {{ session('image_search_error') }}
            </div>
            @endif

            {{-- ── Right-side action icons ──────────────────────────────── --}}
            <div class="flex items-center gap-0.5 shrink-0">

                {{-- Compare (desktop only) --}}
                <div x-data class="relative hidden lg:flex">
                    <button @click="$store.compare.goCompare()"
                       :class="$store.compare.items.length > 0 ? 'text-brand bg-orange-50' : 'text-slate-500'"
                       title="Compare products"
                       class="flex flex-col items-center gap-0.5 px-2.5 py-2.5 hover:text-brand rounded-lg hover:bg-orange-50 transition group cursor-pointer">
                        <div class="relative">
                            <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span x-show="$store.compare.items.length > 0"
                                  x-text="$store.compare.items.length"
                                  x-cloak
                                  class="absolute -top-1.5 -right-1.5 w-[15px] h-[15px] bg-brand text-white text-[8px] font-black rounded-full flex items-center justify-center leading-none"></span>
                        </div>
                        <span class="text-[9px] font-semibold whitespace-nowrap">Compare</span>
                    </button>
                </div>

                {{-- Orders --}}
                <a href="{{ route('retailer.orders') }}"
                   class="flex flex-col items-center gap-0.5 px-2.5 py-2.5 rounded-lg hover:bg-orange-50 transition group
                          {{ request()->routeIs('retailer.orders*') ? 'text-brand bg-orange-50' : 'text-slate-500 hover:text-brand' }}">
                    <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="text-[9px] font-semibold whitespace-nowrap group-hover:text-brand">Orders</span>
                </a>

                {{-- Cart (reactive Livewire counter) --}}
                @livewire('cart-count')

                {{-- Messages (reactive badge) --}}
                @livewire('retailer.chat.message-count')

                {{-- Account dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="flex flex-col items-center gap-0.5 px-2.5 py-2.5 rounded-lg transition group
                                   {{ request()->routeIs('retailer.home') ? 'text-brand bg-orange-50' : 'text-slate-500 hover:text-brand hover:bg-orange-50' }}">
                        {{-- Avatar initial --}}
                        <div class="w-[22px] h-[22px] flex items-center justify-center">
                            <div class="w-6 h-6 rounded-full bg-brand/10 border border-brand/25 flex items-center justify-center text-brand font-black text-[10px] leading-none shrink-0">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        </div>
                        <span class="text-[9px] font-semibold whitespace-nowrap group-hover:text-brand max-w-[60px] truncate leading-tight">
                            {{ explode(' ', auth()->user()->name)[0] }}
                        </span>
                    </button>

                    {{-- Dropdown --}}
                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute right-0 top-full mt-2 w-60 bg-white rounded-xl shadow-2xl border border-slate-100 z-50 overflow-hidden">
                        {{-- User info header --}}
                        <div class="px-4 py-3 bg-slate-50 border-b border-slate-100 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-brand/10 border-2 border-brand/20 flex items-center justify-center text-brand font-black text-base shrink-0">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-800 text-sm truncate">{{ auth()->user()->name }}</p>
                                <p class="text-slate-400 text-[11px] truncate">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        {{-- Nav items --}}
                        <div class="py-1">
                            <a href="{{ route('retailer.home') }}" class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-orange-50 hover:text-brand transition text-sm">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                Dashboard
                            </a>
                            <a href="{{ route('retailer.orders') }}" class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-orange-50 hover:text-brand transition text-sm">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                My Orders
                            </a>
                        </div>
                        {{-- Sign out --}}
                        <div class="border-t border-slate-100">
                            <form method="POST" action="{{ route('retailer.logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-red-500 hover:bg-red-50 transition text-sm">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Category navigation bar ──────────────────────────────────────── --}}
    <div class="border-t border-slate-100 bg-slate-50/80">
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center h-9 overflow-x-auto scrollbar-hide gap-0">
                <a href="{{ route('retailer.catalogue') }}"
                   class="flex items-center gap-1 px-3 h-full text-xs font-semibold whitespace-nowrap border-b-2 transition
                          {{ request()->routeIs('retailer.catalogue') && !request('category') ? 'border-brand text-brand' : 'border-transparent text-slate-600 hover:text-brand hover:border-brand/50' }}">
                    🏪 All Products
                </a>
                @php $navCats = \App\Models\Category::where('is_active', true)->orderBy('name')->take(10)->get(); @endphp
                @foreach($navCats as $cat)
                <a href="{{ route('retailer.catalogue') }}?category={{ $cat->id }}"
                   class="flex items-center px-3 h-full text-xs whitespace-nowrap border-b-2 transition
                          {{ request('category') == $cat->id ? 'border-brand text-brand font-semibold' : 'border-transparent text-slate-600 hover:text-brand hover:border-brand/50' }}">
                    {{ app()->getLocale() === 'zh_CN' && $cat->name_zh ? $cat->name_zh : $cat->name }}
                </a>
                @endforeach
            </nav>
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
{{-- FOOTER  (WiseMarket-style — dark multi-column)                             --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<footer class="bg-[#1a2035] text-slate-400 mt-8">

    {{-- ── Main footer columns ─────────────────────────────────────────── --}}
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-10">

            {{-- Column 1 — Brand / About --}}
            <div class="lg:col-span-1">
                {{-- Logo --}}
                <a href="{{ route('public.home') }}" class="inline-flex items-center gap-2.5 mb-4">
                    <div class="w-9 h-9 bg-brand rounded-lg flex items-center justify-center shadow-sm shrink-0">
                        <span class="text-white font-black text-[13px] tracking-tight leading-none">OZ</span>
                    </div>
                    <div class="leading-none">
                        <div class="font-black text-white text-[17px] tracking-tight">Wholesale</div>
                        <div class="text-[9px] text-brand font-bold tracking-[3px] uppercase mt-0.5">B2B Portal</div>
                    </div>
                </a>
                <p class="text-slate-400 text-sm leading-relaxed mb-5">
                    Australia's trusted B2B wholesale marketplace — connecting verified retailers with quality suppliers since 2020.
                </p>
                <div class="flex flex-col gap-2 text-sm">
                    <span class="flex items-center gap-2 text-slate-400">
                        <svg class="w-4 h-4 text-brand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        support@ozwholesale.com.au
                    </span>
                    <span class="flex items-center gap-2 text-slate-400">
                        <svg class="w-4 h-4 text-brand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        1800 OZ TRADE
                    </span>
                </div>
            </div>

            {{-- Column 2 — For Buyers --}}
            <div>
                <h4 class="text-white font-semibold text-sm mb-4 pb-2 border-b border-slate-700">For Buyers</h4>
                <ul class="space-y-2.5 text-sm">
                    <li>
                        <a href="{{ route('retailer.home') }}" class="hover:text-white hover:translate-x-1 transition-all inline-flex items-center gap-1.5 group">
                            <svg class="w-3 h-3 text-brand/60 group-hover:text-brand transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('retailer.catalogue') }}" class="hover:text-white hover:translate-x-1 transition-all inline-flex items-center gap-1.5 group">
                            <svg class="w-3 h-3 text-brand/60 group-hover:text-brand transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Browse Products
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('retailer.orders') }}" class="hover:text-white hover:translate-x-1 transition-all inline-flex items-center gap-1.5 group">
                            <svg class="w-3 h-3 text-brand/60 group-hover:text-brand transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            My Orders
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('retailer.messages') }}" class="hover:text-white hover:translate-x-1 transition-all inline-flex items-center gap-1.5 group">
                            <svg class="w-3 h-3 text-brand/60 group-hover:text-brand transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Messages
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('retailer.catalogue') }}" class="hover:text-white hover:translate-x-1 transition-all inline-flex items-center gap-1.5 group">
                            <svg class="w-3 h-3 text-brand/60 group-hover:text-brand transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Compare Products
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('retailer.cart') }}" class="hover:text-white hover:translate-x-1 transition-all inline-flex items-center gap-1.5 group">
                            <svg class="w-3 h-3 text-brand/60 group-hover:text-brand transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            My Cart
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Column 3 — Help & Support --}}
            <div>
                <h4 class="text-white font-semibold text-sm mb-4 pb-2 border-b border-slate-700">Help & Support</h4>
                <ul class="space-y-2.5 text-sm">
                    <li>
                        <a href="#" class="hover:text-white hover:translate-x-1 transition-all inline-flex items-center gap-1.5 group">
                            <svg class="w-3 h-3 text-brand/60 group-hover:text-brand transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            How to Order
                        </a>
                    </li>
                    <li>
                        <a href="#" class="hover:text-white hover:translate-x-1 transition-all inline-flex items-center gap-1.5 group">
                            <svg class="w-3 h-3 text-brand/60 group-hover:text-brand transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Shipping & Delivery
                        </a>
                    </li>
                    <li>
                        <a href="#" class="hover:text-white hover:translate-x-1 transition-all inline-flex items-center gap-1.5 group">
                            <svg class="w-3 h-3 text-brand/60 group-hover:text-brand transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Returns & Refunds
                        </a>
                    </li>
                    <li>
                        <a href="#" class="hover:text-white hover:translate-x-1 transition-all inline-flex items-center gap-1.5 group">
                            <svg class="w-3 h-3 text-brand/60 group-hover:text-brand transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            FAQs
                        </a>
                    </li>
                    <li>
                        <a href="#" class="hover:text-white hover:translate-x-1 transition-all inline-flex items-center gap-1.5 group">
                            <svg class="w-3 h-3 text-brand/60 group-hover:text-brand transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Contact Us
                        </a>
                    </li>
                    <li>
                        <a href="#" class="hover:text-white hover:translate-x-1 transition-all inline-flex items-center gap-1.5 group">
                            <svg class="w-3 h-3 text-brand/60 group-hover:text-brand transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Privacy Policy
                        </a>
                    </li>
                    <li>
                        <a href="#" class="hover:text-white hover:translate-x-1 transition-all inline-flex items-center gap-1.5 group">
                            <svg class="w-3 h-3 text-brand/60 group-hover:text-brand transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Terms & Conditions
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Column 4 — Top Categories (dynamic) --}}
            <div>
                <h4 class="text-white font-semibold text-sm mb-4 pb-2 border-b border-slate-700">Top Categories</h4>
                <ul class="space-y-2.5 text-sm">
                    @php $footerCats = \App\Models\Category::where('is_active', true)->orderBy('name')->take(8)->get(); @endphp
                    @foreach($footerCats as $cat)
                    <li>
                        <a href="{{ route('retailer.catalogue') }}?category={{ $cat->id }}"
                           class="hover:text-white hover:translate-x-1 transition-all inline-flex items-center gap-1.5 group">
                            <svg class="w-3 h-3 text-brand/60 group-hover:text-brand transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            {{ $cat->name }}
                        </a>
                    </li>
                    @endforeach
                    @if($footerCats->isEmpty())
                    <li class="text-slate-500 text-xs italic">Categories loading…</li>
                    @endif
                </ul>
            </div>

        </div>
    </div>

    {{-- ── Trust badges row ────────────────────────────────────────────── --}}
    <div class="border-t border-slate-700/60">
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-10 text-xs text-slate-500">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Verified Suppliers
                </span>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 10a2 2 0 002 2h8a2 2 0 002-2L19 8m-9 4h4"/>
                    </svg>
                    Fast B2B Shipping
                </span>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Bulk Order Discounts
                </span>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Net Terms Available
                </span>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Dedicated Support
                </span>
            </div>
        </div>
    </div>

    {{-- ── Bottom copyright bar ─────────────────────────────────────────── --}}
    <div class="border-t border-slate-700/60 bg-[#141828]">
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
                <span>&copy; {{ date('Y') }} OZ Tech &mdash; B2B Wholesale Portal. All rights reserved.</span>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-slate-300 transition">Privacy</a>
                    <span class="text-slate-700">·</span>
                    <a href="#" class="hover:text-slate-300 transition">Terms</a>
                    <span class="text-slate-700">·</span>
                    <a href="#" class="hover:text-slate-300 transition">Sitemap</a>
                </div>
            </div>
        </div>
    </div>

</footer>


<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var btn      = document.getElementById('image-search-btn');
        var fileInput= document.getElementById('image-search-file');
        var icon     = document.getElementById('img-search-icon');
        var spinner  = document.getElementById('img-search-spinner');
        var errorBox = document.getElementById('img-search-error');

        if (!btn || !fileInput) return;

        btn.addEventListener('click', function () { fileInput.click(); });

        fileInput.addEventListener('change', function () {
            var file = fileInput.files[0];
            if (!file) return;

            icon.classList.add('hidden');
            spinner.classList.remove('hidden');
            btn.disabled = true;

            var formData = new FormData();
            formData.append('image', file);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route("image.search") }}', {
                method: 'POST',
                body: formData,
                redirect: 'follow',
                credentials: 'same-origin',
            })
            .then(function (response) {
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    showError('Image search failed. Please try again.');
                }
            })
            .catch(function () { showError('Network error. Please try again.'); })
            .finally(function () {
                icon.classList.remove('hidden');
                spinner.classList.add('hidden');
                btn.disabled = false;
                fileInput.value = '';
            });
        });

        function showError(msg) {
            var box = document.createElement('div');
            box.style.cssText = 'position:fixed;top:80px;left:50%;transform:translateX(-50%);background:#fef2f2;border:1px solid #fca5a5;color:#b91c1c;padding:8px 16px;border-radius:6px;font-size:13px;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,.12)';
            box.textContent = msg;
            document.body.appendChild(box);
            setTimeout(function () { box.remove(); }, 4000);
        }

        if (errorBox) { setTimeout(function () { errorBox.style.display = 'none'; }, 5000); }
    });
})();
</script>


{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- COMPARE BAR — sticky bottom, appears when 1+ products in compare list      --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@auth
<div x-data
     x-show="$store.compare.items.length > 0"
     x-cloak
     x-transition:enter="transition ease-out duration-250"
     x-transition:enter-start="translate-y-full"
     x-transition:enter-end="translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="translate-y-0"
     x-transition:leave-end="translate-y-full"
     class="fixed bottom-0 inset-x-0 z-50 bg-white border-t-2 border-brand shadow-2xl"
     style="padding-bottom: env(safe-area-inset-bottom, 0px)">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center gap-3 sm:gap-4">

        {{-- Thumbnails + empty slots --}}
        <div class="flex items-center gap-1.5 sm:gap-2 flex-1 min-w-0">
            <template x-for="item in $store.compare.items" :key="item.id">
                <div class="relative group shrink-0">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-lg border border-slate-200 bg-slate-50 overflow-hidden">
                        <template x-if="item.image">
                            <img :src="item.image" :alt="item.name"
                                 class="w-full h-full object-cover"
                                 @@error="$el.style.display='none'"/>
                        </template>
                        <template x-if="!item.image">
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-50 to-white">
                                <svg class="w-4 h-4 text-brand/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                </svg>
                            </div>
                        </template>
                    </div>
                    {{-- Remove on hover --}}
                    <button @click="$store.compare.remove(item.id)"
                            class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-slate-600 hover:bg-red-500 text-white rounded-full items-center justify-center text-[9px] font-black hidden group-hover:flex transition">
                        ✕
                    </button>
                </div>
            </template>

            {{-- Empty slots --}}
            <template x-for="i in (4 - $store.compare.items.length)" :key="'e'+i">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 shrink-0 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
            </template>

            {{-- Label --}}
            <div class="hidden sm:block ml-1">
                <p class="text-sm font-bold text-slate-800">
                    <span x-text="$store.compare.items.length"></span>
                    <span class="font-normal text-slate-500">/ 4 products</span>
                </p>
                <p class="text-[11px] text-slate-400">
                    <template x-if="$store.compare.items.length < 2">
                        <span>Add 1 more to compare</span>
                    </template>
                    <template x-if="$store.compare.items.length >= 2">
                        <span>Ready to compare</span>
                    </template>
                </p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 shrink-0">
            <button @click="$store.compare.clear()"
                    class="text-xs text-slate-400 hover:text-slate-600 px-3 py-2 rounded-lg hover:bg-slate-100 transition font-medium">
                Clear
            </button>
            <button @click="$store.compare.goCompare()"
                    :disabled="$store.compare.items.length < 2"
                    class="flex items-center gap-1.5 px-4 sm:px-5 py-2.5 bg-brand hover:bg-orange-600 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-bold rounded-xl transition shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="hidden sm:inline">Compare Now</span>
                <span class="sm:hidden">Compare</span>
            </button>
        </div>
    </div>
</div>
@endauth

</body>
</html>
