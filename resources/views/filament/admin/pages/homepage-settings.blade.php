<x-filament-panels::page>
    <div class="max-w-lg space-y-6">

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6 space-y-5">

                <div>
                    <p class="text-base font-semibold text-gray-950 dark:text-white">Homepage Section Visibility</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Toggle which sections and badges appear on the public and retailer pages.
                    </p>
                </div>

                <div class="space-y-3 pt-1">

                    {{-- Deals toggle --}}
                    <label class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        <div>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">Sourcing Top Deals</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Shows up to 4 products marked as "Featured Deal".</p>
                        </div>
                        <button
                            wire:click="$toggle('dealsActive')"
                            type="button"
                            role="switch"
                            aria-checked="{{ $dealsActive ? 'true' : 'false' }}"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-warning-500 focus:ring-offset-2
                                   {{ $dealsActive ? 'bg-warning-500' : 'bg-gray-200 dark:bg-gray-700' }}">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                         {{ $dealsActive ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </label>

                    {{-- New Arrivals toggle --}}
                    <label class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        <div>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">New Arrivals</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Automatically shows the 4 most recently added active products.</p>
                        </div>
                        <button
                            wire:click="$toggle('newArrivalsActive')"
                            type="button"
                            role="switch"
                            aria-checked="{{ $newArrivalsActive ? 'true' : 'false' }}"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-warning-500 focus:ring-offset-2
                                   {{ $newArrivalsActive ? 'bg-warning-500' : 'bg-gray-200 dark:bg-gray-700' }}">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                         {{ $newArrivalsActive ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </label>

                    {{-- Show In-Stock Badge toggle --}}
                    <label class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        <div>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">Show "In Stock" Badge</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Displays the green "In Stock" label on product cards and detail pages.</p>
                        </div>
                        <button
                            wire:click="$toggle('showStockBadge')"
                            type="button"
                            role="switch"
                            aria-checked="{{ $showStockBadge ? 'true' : 'false' }}"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-warning-500 focus:ring-offset-2
                                   {{ $showStockBadge ? 'bg-warning-500' : 'bg-gray-200 dark:bg-gray-700' }}">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                         {{ $showStockBadge ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </label>

                </div>
            </div>
        </div>

        <div class="fi-section rounded-xl bg-amber-50 dark:bg-amber-950/20 ring-1 ring-amber-200 dark:ring-amber-800 p-4 text-sm text-amber-800 dark:text-amber-200">
            <strong>Tip:</strong> To control which products appear in Deals, go to the
            <a href="{{ route('filament.admin.resources.products.index') }}" class="underline font-medium">Products list</a>
            and toggle the <strong>Featured Deal</strong> switch on each product.
        </div>

    </div>
</x-filament-panels::page>
