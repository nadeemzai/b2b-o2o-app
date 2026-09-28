<div class="min-h-[70vh] flex items-center justify-center">
    <div class="w-full max-w-sm">

        <div class="text-center mb-8">
            <span class="text-3xl font-bold text-white">Huashu</span>
            <p class="text-blue-200 text-sm mt-1">Supplier Portal — Sign in to your account</p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-8">
            <form wire:submit="authenticate" class="space-y-5">

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email address</label>
                    <input type="email" id="email" wire:model="email" autocomplete="email"
                           class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900
                                  focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent
                                  @error('email') border-red-400 @enderror" />
                    @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                    <input type="password" id="password" wire:model="password" autocomplete="current-password"
                           class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900
                                  focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent
                                  @error('password') border-red-400 @enderror" />
                    @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        wire:loading.attr="disabled" wire:loading.class="opacity-60"
                        class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2.5 rounded-lg
                               transition text-sm">
                    <span wire:loading.remove>Sign In</span>
                    <span wire:loading>Signing in…</span>
                </button>

            </form>
        </div>

        <p class="text-center text-blue-300 text-xs mt-6">OZ Wholesale &mdash; Supplier Access</p>

    </div>
</div>
