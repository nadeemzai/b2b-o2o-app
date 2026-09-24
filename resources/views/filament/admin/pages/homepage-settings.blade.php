<x-filament-panels::page>
    <div class="max-w-lg space-y-6">

        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-5">
            <h2 class="text-base font-semibold text-slate-800">Homepage Section Visibility</h2>
            <p class="text-sm text-slate-500 -mt-3">
                Toggle which sections appear on the public and retailer home pages.
                When only one section is active it spans the full width.
                When both are off, no product sections are shown.
            </p>

            <div class="space-y-4 pt-1">

                {{-- Deals --}}
                <label class="flex items-center justify-between p-4 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition">
                    <div>
                        <p class="font-medium text-slate-800 text-sm">Sourcing Top Deals</p>
                        <p class="text-xs text-slate-500 mt-0.5">Shows up to 4 products marked as "Featured Deal" in the product list.</p>
                    </div>
                    <input type="checkbox" wire:model="dealsActive"
                           class="w-5 h-5 text-orange-500 rounded border-slate-300 focus:ring-orange-400">
                </label>

                {{-- New Arrivals --}}
                <label class="flex items-center justify-between p-4 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition">
                    <div>
                        <p class="font-medium text-slate-800 text-sm">New Arrivals</p>
                        <p class="text-xs text-slate-500 mt-0.5">Automatically shows the 4 most recently added active products.</p>
                    </div>
                    <input type="checkbox" wire:model="newArrivalsActive"
                           class="w-5 h-5 text-orange-500 rounded border-slate-300 focus:ring-orange-400">
                </label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button wire:click="save"
                        class="px-5 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-lg transition shadow-sm">
                    Save Settings
                </button>
                <div wire:loading class="text-sm text-slate-400">Saving…</div>
            </div>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
            <strong>Tip:</strong> To manage which products appear in the Deals section, open the
            <a href="{{ route('filament.admin.resources.products.index') }}" class="underline font-medium">Products list</a>
            and toggle the "Featured Deal" switch on individual products.
        </div>
    </div>
</x-filament-panels::page>
