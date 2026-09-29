<x-filament-panels::page>
    <div class="max-w-lg space-y-6">

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6 space-y-5">

                <div>
                    <p class="text-base font-semibold text-gray-950 dark:text-white">Product Review Limits</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Control how many reviews each retailer can submit for a single product.
                    </p>
                </div>

                <div class="space-y-4 pt-1">

                    {{-- Max Reviews Per Product --}}
                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <label for="maxReviewsPerProduct"
                               class="block text-sm font-medium text-gray-950 dark:text-white mb-1">
                            Max reviews per product (per retailer)
                        </label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                            Once a retailer reaches this number of reviews on a product, the
                            "Write a Review" form is hidden and they cannot submit more.
                            Default: <strong>3</strong>.
                        </p>
                        <div class="flex items-center gap-3">
                            <input
                                id="maxReviewsPerProduct"
                                type="number"
                                min="1"
                                max="50"
                                wire:model.live="maxReviewsPerProduct"
                                class="fi-input block w-24 rounded-lg border border-gray-300 dark:border-gray-600
                                       bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-950 dark:text-white
                                       shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                                       dark:focus:ring-primary-400 dark:focus:border-primary-400"
                            />
                            <span class="text-sm text-gray-500 dark:text-gray-400">reviews</span>
                        </div>
                        @error('maxReviewsPerProduct')
                            <p class="mt-2 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        <div class="fi-section rounded-xl bg-blue-50 dark:bg-blue-950/20 ring-1 ring-blue-200 dark:ring-blue-800 p-4 text-sm text-blue-800 dark:text-blue-200">
            <strong>Note:</strong> This limit applies going forward. Existing reviews already in the database
            are not deleted when you lower this number.
        </div>

    </div>
</x-filament-panels::page>
