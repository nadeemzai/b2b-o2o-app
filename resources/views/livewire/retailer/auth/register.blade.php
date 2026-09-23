<div class="py-6">
    <div class="max-w-xl mx-auto">

        <div class="text-center mb-8">
            <span class="text-2xl font-bold text-brand">OZ Wholesale</span>
            <p class="text-slate-500 text-sm mt-1">{{ __('ui.create_account') }}</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
            <form wire:submit="register" class="space-y-6">

                {{-- Section: Personal Info --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">{{ __('ui.account_details') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('ui.name') }}</label>
                            <input type="text" wire:model="name" class="input-field @error('name') border-red-400 @enderror" />
                            @error('name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('ui.phone') }}</label>
                            <input type="text" wire:model="phone" placeholder="03XX-XXXXXXX" class="input-field @error('phone') border-red-400 @enderror" />
                            @error('phone') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('ui.email') }}</label>
                            <input type="email" wire:model="email" class="input-field @error('email') border-red-400 @enderror" />
                            @error('email') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('ui.password') }}</label>
                            <input type="password" wire:model="password" class="input-field @error('password') border-red-400 @enderror" />
                            @error('password') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100" />

                {{-- Section: Business Info --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">{{ __('ui.business_details') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('ui.business_name') }}</label>
                            <input type="text" wire:model="business_name" class="input-field @error('business_name') border-red-400 @enderror" />
                            @error('business_name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('ui.cnic') }}</label>
                            <input type="text" wire:model="cnic" placeholder="42201-1234567-1" class="input-field @error('cnic') border-red-400 @enderror" />
                            @error('cnic') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('ui.ntn') }} <span class="text-slate-400">({{ __('ui.optional') }})</span></label>
                            <input type="text" wire:model="ntn" class="input-field @error('ntn') border-red-400 @enderror" />
                            @error('ntn') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('ui.strn') }} <span class="text-slate-400">({{ __('ui.optional') }})</span></label>
                            <input type="text" wire:model="strn" class="input-field @error('strn') border-red-400 @enderror" />
                            @error('strn') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('ui.nearest_store') }}</label>
                            <select wire:model="store_id" class="input-field @error('store_id') border-red-400 @enderror">
                                <option value="">{{ __('ui.select_store') }}</option>
                                @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }} — {{ $store->city }}</option>
                                @endforeach
                            </select>
                            @error('store_id') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('ui.shop_address') }}</label>
                            <textarea wire:model="address" rows="2" class="input-field @error('address') border-red-400 @enderror"></textarea>
                            @error('address') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100" />

                {{-- Section: KYC Documents --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">{{ __('ui.kyc_documents') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('ui.cnic_copy') }} <span class="text-red-500">*</span></label>
                            <input type="file" wire:model="cnic_doc" accept=".jpg,.jpeg,.png,.pdf"
                                   class="block w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-slate-100 file:text-slate-700 file:font-medium hover:file:bg-slate-200 cursor-pointer" />
                            @error('cnic_doc') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('ui.ntn_document') }} <span class="text-slate-400">({{ __('ui.optional') }})</span></label>
                            <input type="file" wire:model="ntn_doc" accept=".jpg,.jpeg,.png,.pdf"
                                   class="block w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-slate-100 file:text-slate-700 file:font-medium hover:file:bg-slate-200 cursor-pointer" />
                            @error('ntn_doc') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <button type="submit" wire:loading.attr="disabled"
                        class="w-full bg-brand hover:bg-brand-dark text-white font-semibold py-2.5 rounded-lg transition disabled:opacity-60 text-sm">
                    <span wire:loading.remove>{{ __('ui.submit_application') }}</span>
                    <span wire:loading>{{ __('ui.submitting') }}</span>
                </button>

            </form>
        </div>

        <p class="text-center text-sm text-slate-500 mt-6">
            {{ __('ui.have_account') }}
            <a href="{{ route('retailer.login') }}" class="text-brand hover:underline font-medium">{{ __('ui.sign_in') }}</a>
        </p>

    </div>

    <style>
        .input-field {
            width: 100%; border-radius: 0.5rem; border: 1px solid #e2e8f0;
            padding: 0.625rem 0.875rem; font-size: 0.875rem; color: #0f172a;
            outline: none; transition: border-color 0.15s;
        }
        .input-field:focus { border-color: #ff5b00; box-shadow: 0 0 0 3px rgba(255,91,0,.15); }
        .form-error { margin-top: 0.25rem; font-size: 0.75rem; color: #dc2626; }
    </style>
</div>
