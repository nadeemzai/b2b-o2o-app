<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Retailer Login — WiseMarket B2B</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'media' }</script>
</head>
<body class="h-full bg-zinc-50 dark:bg-zinc-950 flex items-center justify-center px-4">

<div class="w-full max-w-md">

    {{-- Brand --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-600 mb-4">
            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">WiseMarket B2B</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Retailer Portal</p>
    </div>

    {{-- Card --}}
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800 px-8 py-8">

        <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-6">Sign in to your account</h2>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950/40 px-4 py-3 text-sm text-red-700 dark:text-red-400">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('retailer.login.post') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Email address</label>
                <input
                    id="email" name="email" type="email" autocomplete="email" required
                    value="{{ old('email') }}"
                    class="block w-full rounded-lg border border-zinc-300 dark:border-zinc-600
                           bg-white dark:bg-zinc-800 px-4 py-2.5 text-sm
                           text-zinc-900 dark:text-zinc-100
                           placeholder:text-zinc-400 dark:placeholder:text-zinc-500
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="you@example.com"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Password</label>
                <input
                    id="password" name="password" type="password" autocomplete="current-password" required
                    class="block w-full rounded-lg border border-zinc-300 dark:border-zinc-600
                           bg-white dark:bg-zinc-800 px-4 py-2.5 text-sm
                           text-zinc-900 dark:text-zinc-100
                           placeholder:text-zinc-400 dark:placeholder:text-zinc-500
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="••••••••"
                >
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                    <input type="checkbox" name="remember" class="rounded border-zinc-300 dark:border-zinc-600 text-indigo-600">
                    Remember me
                </label>
            </div>

            <button type="submit"
                class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white
                       hover:bg-indigo-500 active:bg-indigo-700 transition-colors">
                Sign in
            </button>
        </form>

    </div>

    <p class="text-center text-xs text-zinc-400 dark:text-zinc-600 mt-6">
        Not a retailer? Contact your store manager to get registered.
    </p>

</div>

</body>
</html>
