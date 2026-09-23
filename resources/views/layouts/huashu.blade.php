<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? 'Huashu Fulfillment Portal' }}</title>

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
                        brand: { DEFAULT: '#1d4ed8', light: '#3b82f6', dark: '#1e40af' }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        [wire\:loading] { opacity: .6; pointer-events: none; }
    </style>

    @livewireStyles
</head>
<body class="h-full flex flex-col">

{{-- ── Top Navigation ── --}}
<nav class="bg-brand shadow-sm sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14">
            <div class="flex items-center gap-6">
                <span class="text-white font-bold text-lg tracking-tight">Huashu Portal</span>
                <a href="{{ route('huashu.orders') }}"
                   class="text-blue-100 hover:text-white text-sm font-medium {{ request()->routeIs('huashu.orders*') ? 'text-white' : '' }}">
                    Orders
                </a>
            </div>
            @auth
            <div class="flex items-center gap-4 text-sm text-blue-100">
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('huashu.logout') }}">
                    @csrf
                    <button type="submit" class="hover:text-white">Logout</button>
                </form>
            </div>
            @endauth
        </div>
    </div>
</nav>

{{-- ── Page Content ── --}}
<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{ $slot }}
</main>

@livewireScripts
</body>
</html>
