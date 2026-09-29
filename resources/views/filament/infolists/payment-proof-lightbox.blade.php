@php
    $record = $getRecord();
    $proofUrl = $record->payment_proof_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($record->payment_proof_path)
        : null;
@endphp

@if($proofUrl)
<div
    x-data="{ open: false, src: '' }"
    @keydown.escape.window="open = false"
>
    {{-- ── Thumbnail (click to open) ─────────────────────────────── --}}
    <div class="fi-in-entry-wrp">
        <div class="fi-in-entry-wrp-label flex items-center gap-x-3">
            <span class="fi-in-entry-wrp-label-text text-sm font-medium leading-6 text-gray-950 dark:text-white">
                Uploaded Proof Image
            </span>
        </div>
        <div class="fi-in-entry-wrp-content mt-1.5">
            <button
                type="button"
                @click="src = '{{ $proofUrl }}'; open = true"
                class="group relative inline-block focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-600 rounded-lg"
                title="Click to enlarge"
            >
                <img
                    src="{{ $proofUrl }}"
                    alt="Payment proof"
                    class="rounded-lg border border-gray-200 shadow-sm object-contain w-auto transition-all duration-150 group-hover:opacity-90 group-hover:shadow-md"
                    style="height: 320px; max-width: 100%; cursor: zoom-in;"
                />
                {{-- Zoom hint overlay --}}
                <span class="pointer-events-none absolute bottom-2 right-2 flex items-center gap-1 rounded-md bg-black/50 px-2 py-1 text-xs text-white opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0zM11 8v6M8 11h6"/>
                    </svg>
                    Enlarge
                </span>
            </button>
        </div>
    </div>

    {{-- ── Lightbox Overlay ─────────────────────────────────────────── --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/80 p-4"
        @click.self="open = false"
        style="display: none;"
    >
        {{-- Close button --}}
        <button
            type="button"
            @click="open = false"
            class="absolute top-4 right-4 flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
            title="Close"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Full-size image --}}
        <div
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative max-h-[90vh] max-w-[90vw]"
        >
            <img
                :src="src"
                alt="Payment proof (full size)"
                class="max-h-[90vh] max-w-[90vw] rounded-lg shadow-2xl object-contain"
            />

            {{-- Open in new tab link --}}
            <a
                :href="src"
                target="_blank"
                rel="noopener"
                class="absolute bottom-3 right-3 flex items-center gap-1.5 rounded-md bg-black/50 px-3 py-1.5 text-xs text-white hover:bg-black/70 transition"
                title="Open original in new tab"
            >
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Open original
            </a>
        </div>
    </div>
</div>
@endif
