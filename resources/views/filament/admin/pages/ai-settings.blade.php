<x-filament-panels::page>
    <div class="max-w-2xl space-y-6">

        {{-- ── Active provider ── --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6 space-y-5">

                <div>
                    <p class="text-base font-semibold text-gray-950 dark:text-white">Active Provider</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Only one provider is used at a time. Both keys are saved independently — switching providers is instant.
                    </p>
                </div>

                {{-- Provider radio cards --}}
                <div class="grid grid-cols-2 gap-3">

                    {{-- Anthropic --}}
                    <label class="relative flex cursor-pointer rounded-xl border-2 p-4 transition
                                  {{ $aiProvider === 'anthropic'
                                      ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/20'
                                      : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                        <input type="radio" wire:model.live="aiProvider" value="anthropic" class="sr-only" />
                        <div class="flex items-start gap-3 w-full">
                            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg
                                        {{ $aiProvider === 'anthropic' ? 'bg-primary-100 dark:bg-primary-900/40' : 'bg-gray-100 dark:bg-gray-800' }}">
                                <x-heroicon-o-cpu-chip class="h-5 w-5 {{ $aiProvider === 'anthropic' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400' }}" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-950 dark:text-white">Anthropic</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Claude models</p>
                                @if ($hasAnthropicKey)
                                    <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-success-100 dark:bg-success-900/30
                                                 px-2 py-0.5 text-xs font-medium text-success-700 dark:text-success-400">
                                        <x-heroicon-m-check-circle class="h-3 w-3" /> Key saved
                                    </span>
                                @else
                                    <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-800
                                                 px-2 py-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                                        No key
                                    </span>
                                @endif
                            </div>
                            @if ($aiProvider === 'anthropic')
                                <x-heroicon-m-check-circle class="h-5 w-5 text-primary-600 dark:text-primary-400 shrink-0" />
                            @endif
                        </div>
                    </label>

                    {{-- Groq --}}
                    <label class="relative flex cursor-pointer rounded-xl border-2 p-4 transition
                                  {{ $aiProvider === 'groq'
                                      ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/20'
                                      : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                        <input type="radio" wire:model.live="aiProvider" value="groq" class="sr-only" />
                        <div class="flex items-start gap-3 w-full">
                            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg
                                        {{ $aiProvider === 'groq' ? 'bg-primary-100 dark:bg-primary-900/40' : 'bg-gray-100 dark:bg-gray-800' }}">
                                <x-heroicon-o-bolt class="h-5 w-5 {{ $aiProvider === 'groq' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400' }}" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-950 dark:text-white">Groq</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Llama / open-source</p>
                                @if ($hasGroqKey)
                                    <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-success-100 dark:bg-success-900/30
                                                 px-2 py-0.5 text-xs font-medium text-success-700 dark:text-success-400">
                                        <x-heroicon-m-check-circle class="h-3 w-3" /> Key saved
                                    </span>
                                @else
                                    <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-800
                                                 px-2 py-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                                        No key
                                    </span>
                                @endif
                            </div>
                            @if ($aiProvider === 'groq')
                                <x-heroicon-m-check-circle class="h-5 w-5 text-primary-600 dark:text-primary-400 shrink-0" />
                            @endif
                        </div>
                    </label>

                </div>

                {{-- Model selector --}}
                <div class="space-y-1.5">
                    <label for="aiModel" class="block text-sm font-medium text-gray-950 dark:text-white">
                        Model
                    </label>
                    <select id="aiModel" wire:model="aiModel"
                            class="fi-input block w-full rounded-lg border border-gray-300 dark:border-gray-600
                                   bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-950 dark:text-white
                                   shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @foreach ($this->getModelOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @if ($aiProvider === 'groq')
                        <p class="text-xs text-amber-600 dark:text-amber-400">
                            ⚠ Image search requires a vision-capable model — use Llama 4 Maverick.
                        </p>
                    @endif
                </div>

            </div>
        </div>

        {{-- ── API Keys listing ── --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6 space-y-5">

                <div>
                    <p class="text-base font-semibold text-gray-950 dark:text-white">API Keys</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Keys are stored encrypted. Leave a field blank to keep its existing key.
                    </p>
                </div>

                {{-- Anthropic key row --}}
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-800/50">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-cpu-chip class="h-4 w-4 text-gray-400" />
                            <span class="text-sm font-medium text-gray-950 dark:text-white">Anthropic — Claude</span>
                        </div>
                        @if ($hasAnthropicKey)
                            <span class="inline-flex items-center gap-1 rounded-full bg-success-100 dark:bg-success-900/30
                                         px-2.5 py-0.5 text-xs font-medium text-success-700 dark:text-success-400">
                                <x-heroicon-m-check-circle class="h-3.5 w-3.5" /> Configured
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800
                                         px-2.5 py-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                                Not set
                            </span>
                        @endif
                    </div>
                    <div class="p-4 space-y-2">
                        <input type="password"
                               autocomplete="new-password"
                               placeholder="{{ $hasAnthropicKey ? '••••••••  (leave blank to keep current)' : 'Paste Anthropic API key (sk-ant-...)' }}"
                               wire:model="anthropicKeyInput"
                               class="fi-input block w-full rounded-lg border border-gray-300 dark:border-gray-600
                                      bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-950 dark:text-white
                                      shadow-sm font-mono focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
                        @error('anthropicKeyInput')
                            <p class="text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            Get your key at <strong>console.anthropic.com</strong>
                        </p>
                    </div>
                </div>

                {{-- Groq key row --}}
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-800/50">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-bolt class="h-4 w-4 text-gray-400" />
                            <span class="text-sm font-medium text-gray-950 dark:text-white">Groq — Llama / open-source</span>
                        </div>
                        @if ($hasGroqKey)
                            <span class="inline-flex items-center gap-1 rounded-full bg-success-100 dark:bg-success-900/30
                                         px-2.5 py-0.5 text-xs font-medium text-success-700 dark:text-success-400">
                                <x-heroicon-m-check-circle class="h-3.5 w-3.5" /> Configured
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800
                                         px-2.5 py-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                                Not set
                            </span>
                        @endif
                    </div>
                    <div class="p-4 space-y-2">
                        <input type="password"
                               autocomplete="new-password"
                               placeholder="{{ $hasGroqKey ? '••••••••  (leave blank to keep current)' : 'Paste Groq API key (gsk_...)' }}"
                               wire:model="groqKeyInput"
                               class="fi-input block w-full rounded-lg border border-gray-300 dark:border-gray-600
                                      bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-950 dark:text-white
                                      shadow-sm font-mono focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
                        @error('groqKeyInput')
                            <p class="text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            Free tier available at <strong>console.groq.com</strong>
                        </p>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Info note ── --}}
        <div class="rounded-xl bg-blue-50 dark:bg-blue-950/20 ring-1 ring-blue-200 dark:ring-blue-800 p-4 text-sm text-blue-800 dark:text-blue-200">
            <strong>How it works:</strong> The active provider is used immediately for all AI features.
            Both keys stay saved — you can switch providers any time without re-entering keys.
        </div>

    </div>
</x-filament-panels::page>
