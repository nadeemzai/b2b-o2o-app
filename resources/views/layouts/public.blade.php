<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? 'OZ Wholesale' }} — OZ B2B Portal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            DEFAULT: '#ff5b00',
                            light:   '#ff7a33',
                            dark:    '#e04a00',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>

    @livewireStyles
</head>
<body class="h-full flex flex-col">

{{-- ── Navigation ──────────────────────────────────────────── --}}
<nav class="bg-brand shadow-sm sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14">

            {{-- Logo --}}
            <a href="{{ route('public.catalogue') }}" class="flex items-center gap-2">
                <span class="text-white font-bold text-lg tracking-tight">OZ Wholesale</span>
                <span class="text-orange-200 text-xs font-medium">B2B</span>
            </a>

            {{-- Right: language / currency + login / register --}}
            <div class="flex items-center gap-2">

                {{-- Language switcher --}}
                @livewire('language-switcher')

                {{-- Currency switcher --}}
                @livewire('currency-switcher')

                {{-- Divider --}}
                <div class="w-px h-5 bg-orange-400 opacity-50 hidden sm:block"></div>

                <a href="{{ route('retailer.login') }}"
                   class="text-orange-100 hover:text-white hover:bg-brand-dark px-4 py-2 rounded text-sm font-medium transition">
                    {{ __('ui.sign_in') }}
                </a>
                <a href="{{ route('retailer.register') }}"
                   class="bg-white text-brand hover:bg-orange-50 px-4 py-2 rounded text-sm font-semibold transition shadow-sm">
                    {{ __('ui.register') }}
                </a>
            </div>
        </div>
    </div>
</nav>

{{-- ── Retailer registration banner ───────────────────────── --}}
<div class="bg-orange-50 border-b border-orange-200 px-4 py-2.5 text-center text-sm text-orange-900">
    <strong>{{ __('ui.wholesale_buyers') }}:</strong> {{ __('ui.b2b_rates_notice') }}
    <a href="{{ route('retailer.register') }}" class="underline font-semibold hover:text-orange-700">{{ __('ui.register_as_retailer') }}</a>
    {{ __('ui.to_place_orders') }}
</div>

{{-- ── Page content ─────────────────────────────────────────── --}}
<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{ $slot }}
</main>

{{-- ── Footer ──────────────────────────────────────────────── --}}
<footer class="bg-gray-100 text-gray-500 text-xs py-8 mt-auto border-t border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between gap-4">
        <div>
            <p class="text-gray-800 font-semibold text-sm mb-1">OZ Wholesale B2B Portal</p>
            <p>{{ __('ui.footer_tagline') }}</p>
        </div>
        <div class="flex gap-6 text-xs">
            <a href="{{ route('retailer.login') }}" class="hover:text-gray-800 transition">{{ __('ui.retailer_login') }}</a>
            <a href="{{ route('retailer.register') }}" class="hover:text-gray-800 transition">{{ __('ui.apply_as_retailer') }}</a>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 pt-4 border-t border-gray-200 text-center text-gray-400">
        &copy; {{ date('Y') }} OZ Tech &mdash; All rights reserved
    </div>
</footer>

@livewireScripts
</body>
</html>
