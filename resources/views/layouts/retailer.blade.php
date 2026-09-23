<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? 'OZ B2B Portal' }} — OZ Wholesale</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    {{-- Tailwind via CDN (swap for compiled Vite build in production) --}}
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
        body { font-family: 'Inter', sans-serif; }
        [wire\:loading] { opacity: .6; pointer-events: none; }
        [x-cloak] { display: none !important; }
    </style>

    @livewireStyles
</head>
<body class="h-full flex flex-col">

{{-- ── Top Navigation ───────────────────────────────────────── --}}
@auth
<nav class="bg-brand shadow-sm sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14">

            {{-- Logo --}}
            <a href="{{ route('retailer.dashboard') }}" class="flex items-center gap-2 shrink-0">
                <span class="text-white font-bold text-lg tracking-tight">OZ Wholesale</span>
                <span class="text-orange-200 text-xs font-medium">B2B</span>
            </a>

            {{-- Nav links --}}
            <div class="hidden sm:flex items-center gap-1">
                <a href="{{ route('retailer.dashboard') }}"
                   class="text-orange-100 hover:text-white hover:bg-brand-dark px-3 py-2 rounded text-sm font-medium transition
                          {{ request()->routeIs('retailer.dashboard') ? 'bg-brand-dark text-white' : '' }}">
                    {{ __('ui.nav_dashboard') }}
                </a>
                <a href="{{ route('retailer.catalogue') }}"
                   class="text-orange-100 hover:text-white hover:bg-brand-dark px-3 py-2 rounded text-sm font-medium transition
                          {{ request()->routeIs('retailer.catalogue*') ? 'bg-brand-dark text-white' : '' }}">
                    {{ __('ui.nav_catalogue') }}
                </a>
                <a href="{{ route('retailer.orders') }}"
                   class="text-orange-100 hover:text-white hover:bg-brand-dark px-3 py-2 rounded text-sm font-medium transition
                          {{ request()->routeIs('retailer.orders*') ? 'bg-brand-dark text-white' : '' }}">
                    {{ __('ui.nav_orders') }}
                </a>
            </div>

            {{-- Right side: switchers + cart + user --}}
            <div class="flex items-center gap-2">

                {{-- Language switcher --}}
                @livewire('language-switcher')

                {{-- Currency switcher --}}
                @livewire('currency-switcher')

                {{-- Divider --}}
                <div class="w-px h-5 bg-orange-400 opacity-50 hidden sm:block"></div>

                {{-- Cart icon --}}
                @php $cartCount = app(\App\Services\CartService::class)->count(); @endphp
                <a href="{{ route('retailer.cart') }}" class="relative text-orange-100 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    @if($cartCount > 0)
                    <span class="absolute -top-1.5 -right-1.5 bg-white text-brand text-xs font-bold rounded-full w-4 h-4 flex items-center justify-center">
                        {{ $cartCount }}
                    </span>
                    @endif
                </a>

                {{-- User dropdown (Alpine) --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2 text-orange-100 hover:text-white text-sm font-medium transition">
                        <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg border border-slate-100 py-1 text-sm z-50">
                        <div class="px-4 py-2 text-slate-500 text-xs border-b border-slate-100">
                            {{ auth()->user()->email }}
                        </div>
                        <form method="POST" action="{{ route('retailer.logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-slate-700 hover:bg-slate-50 transition">
                                {{ __('ui.sign_out') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</nav>
@endauth

{{-- ── Flash messages ───────────────────────────────────────── --}}
@if(session('success') || session('error'))
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4" x-data x-init="setTimeout(() => $el.remove(), 4000)">
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-3 text-sm">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
        {{ session('error') }}
    </div>
    @endif
</div>
@endif

{{-- ── Page content ─────────────────────────────────────────── --}}
<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{ $slot }}
</main>

{{-- ── Footer ───────────────────────────────────────────────── --}}
<footer class="text-center text-slate-400 text-xs py-4 border-t border-slate-100">
    OZ Tech &mdash; B2B Wholesale Portal &copy; {{ date('Y') }}
</footer>

@livewireScripts
</body>
</html>
