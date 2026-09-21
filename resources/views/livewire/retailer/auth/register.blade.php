<div class="py-6">
    <div class="max-w-xl mx-auto">

        <div class="text-center mb-8">
            <span class="text-2xl font-bold text-brand">OZ Wholesale</span>
            <p class="text-slate-500 text-sm mt-1">Create your retailer account</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
            <form wire:submit="register" class="space-y-6">

                {{-- Section: Personal Info --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Account Details</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                            <input type="text" wire:model="name" class="input-field @error('name') border-red-400 @enderror" />
                            @error('name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                            <input type="text" wire:model="phone" placeholder="03XX-XXXXXXX" class="input-field @error('phone') border-red-400 @enderror" />
                            @error('phone') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                            <input type="email" wire:model="email" class="input-field @error('email') border-red-400 @enderror" />
                            @error('email') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                            <input type="password" wire:model="password" class="input-field @error('password') border-red-400 @enderror" />
                            @error('password') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100" />

                {{-- Section: Business Info --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Business Details</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Business / Shop Name</label>
                            <input type="text" wire:model="business_name" class="input-field @error('business_name') border-red-400 @enderror" />
                            @error('business_name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">CNIC</label>
                            <input type="text" wire:model="cnic" placeholder="42201-1234567-1" class="input-field @error('cnic') border-red-400 @enderror" />
                            @error('cnic') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">NTN <span class="text-slate-400">(optional)</span></label>
                            <input type="text" wire:model="ntn" class="input-field @error('ntn') border-red-400 @enderror" />
                            @error('ntn') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">STRN <span class="text-slate-400">(optional)</span></label>
                            <input type="text" wire:model="strn" class="input-field @error('strn') border-red-400 @enderror" />
                            @error('strn') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nearest Township Store</label>
                            <select wire:model="store_id" class="input-field @error('store_id') border-red-400 @enderror">
                                <option value="">Select store…</option>
                                @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }} — {{ $store->city }}</option>
                                @endforeach
                            </select>
                            @error('store_id') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Shop Address</label>
                            <textarea wire:model="address" rows="2" class="input-field @error('address') border-red-400 @enderror"></textarea>
                            @error('address') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100" />

                {{-- Section: KYC Documents --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">KYC Documents</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">CNIC Copy <span class="text-red-500">*</span></label>
                            <input type="file" wire:model="cnic_doc" accept=".jpg,.jpeg,.png,.pdf"
                                   class="block w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-slate-100 file:text-slate-700 file:font-medium hover:file:bg-slate-200 cursor-pointer" />
                            @error('cnic_doc') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">NTN Document <span class="text-slate-400">(optional)</span></label>
                            <input type="file" wire:model="ntn_doc" accept=".jpg,.jpeg,.png,.pdf"
                                   class="block w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-slate-100 file:text-slate-700 file:font-medium hover:file:bg-slate-200 cursor-pointer" />
                            @error('ntn_doc') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <button type="submit" wire:loading.attr="disabled"
                        class="w-full bg-brand hover:bg-blue-800 text-white font-semibold py-2.5 rounded-lg transition disabled:opacity-60 text-sm">
                    <span wire:loading.remove>Submit Application</span>
                    <span wire:loading>Submitting…</span>
                </button>

            </form>
        </div>

        <p class="text-center text-sm text-slate-500 mt-6">
            Already have an account?
            <a href="{{ route('retailer.login') }}" class="text-brand hover:underline font-medium">Sign in</a>
        </p>

    </div>
</div>

<style>
    .input-field {
        width: 100%; border-radius: 0.5rem; border: 1px solid #e2e8f0;
        padding: 0.625rem 0.875rem; font-size: 0.875rem; color: #0f172a;
        outline: none; transition: border-color 0.15s;
    }
    .input-field:focus { border-color: #1d4ed8; box-shadow: 0 0 0 3px rgba(29,78,216,.15); }
    .form-error { margin-top: 0.25rem; font-size: 0.75rem; color: #dc2626; }
</style>
