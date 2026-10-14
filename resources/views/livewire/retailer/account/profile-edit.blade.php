{{--
  Retailer Account / Profile Edit
  Route: GET /retailer/account  (retailer.account)
--}}
<div class="min-h-screen bg-slate-50">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 space-y-6">

    {{-- Page header --}}
    <div>
      <h1 class="text-2xl font-bold text-slate-900">My Account</h1>
      <p class="mt-1 text-sm text-slate-500">Manage your profile, business details, and password.</p>
    </div>

    {{-- ── Success banners ──────────────────────────────────── --}}
    @if($profileSaved)
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">
      <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      Profile updated successfully.
    </div>
    @endif

    @if($passwordSaved)
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">
      <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      Password changed successfully.
    </div>
    @endif

    {{-- ── Section 1: Personal & Business Details ───────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-brand/10 flex items-center justify-center shrink-0">
          <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <div>
          <h2 class="text-base font-semibold text-slate-900">Personal &amp; Business Details</h2>
          <p class="text-xs text-slate-400">Your name, email, and business information.</p>
        </div>
      </div>

      <form wire:submit="saveProfile" class="px-6 py-5 space-y-5">

        {{-- Personal --}}
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Personal</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          {{-- Display Name --}}
          <div>
            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Display Name <span class="text-red-500">*</span></label>
            <input id="name" type="text" wire:model.live.debounce.400ms="name"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-brand @error('name') border-red-400 bg-red-50 @enderror"
                   placeholder="Your full name">
            @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
          </div>

          {{-- Email --}}
          <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address <span class="text-red-500">*</span></label>
            <input id="email" type="email" wire:model.live.debounce.400ms="email"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-brand @error('email') border-red-400 bg-red-50 @enderror"
                   placeholder="you@example.com">
            @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
          </div>
        </div>

        <hr class="border-slate-100">

        {{-- Business --}}
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Business</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          {{-- Business Name --}}
          <div class="sm:col-span-2">
            <label for="businessName" class="block text-sm font-medium text-slate-700 mb-1">Business Name <span class="text-red-500">*</span></label>
            <input id="businessName" type="text" wire:model.live.debounce.400ms="businessName"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-brand @error('businessName') border-red-400 bg-red-50 @enderror"
                   placeholder="Trading / Company name">
            @error('businessName')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
          </div>

          {{-- Phone --}}
          <div>
            <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
            <input id="phone" type="text" wire:model="phone"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-brand @error('phone') border-red-400 bg-red-50 @enderror"
                   placeholder="03XX-XXXXXXX">
            @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
          </div>

          {{-- Address --}}
          <div>
            <label for="address" class="block text-sm font-medium text-slate-700 mb-1">Business Address</label>
            <input id="address" type="text" wire:model="address"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-brand @error('address') border-red-400 bg-red-50 @enderror"
                   placeholder="Shop / street / city">
            @error('address')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
          </div>
        </div>

        {{-- KYC read-only --}}
        @if($cnicNumber || $ntnNumber || $strnNumber)
        <hr class="border-slate-100">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">KYC Identity Numbers
          <span class="normal-case font-normal ml-1">— <a href="{{ route('retailer.kyc') }}" class="text-brand hover:underline">update via KYC form</a></span>
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          @if($cnicNumber)
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">CNIC</label>
            <p class="text-sm font-mono text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">{{ $cnicNumber }}</p>
          </div>
          @endif
          @if($ntnNumber)
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">NTN</label>
            <p class="text-sm font-mono text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">{{ $ntnNumber }}</p>
          </div>
          @endif
          @if($strnNumber)
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">STRN / GST</label>
            <p class="text-sm font-mono text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">{{ $strnNumber }}</p>
          </div>
          @endif
        </div>
        @endif

        {{-- Submit --}}
        <div class="flex justify-end pt-2">
          <button type="submit"
                  wire:loading.attr="disabled" wire:target="saveProfile"
                  class="inline-flex items-center gap-2 bg-brand hover:bg-brand/90 disabled:opacity-60 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition shadow-sm">
            <span wire:loading.remove wire:target="saveProfile">Save Profile</span>
            <span wire:loading wire:target="saveProfile">Saving…</span>
          </button>
        </div>

      </form>
    </div>

    {{-- ── Section 2: Change Password ───────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
          <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
        </div>
        <div>
          <h2 class="text-base font-semibold text-slate-900">Change Password</h2>
          <p class="text-xs text-slate-400">Use a strong password of at least 8 characters.</p>
        </div>
      </div>

      <form wire:submit="savePassword" class="px-6 py-5 space-y-4">
        {{-- Current password --}}
        <div>
          <label for="currentPassword" class="block text-sm font-medium text-slate-700 mb-1">Current Password <span class="text-red-500">*</span></label>
          <input id="currentPassword" type="password" wire:model="currentPassword" autocomplete="current-password"
                 class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-brand @error('currentPassword') border-red-400 bg-red-50 @enderror">
          @error('currentPassword')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          {{-- New password --}}
          <div>
            <label for="newPassword" class="block text-sm font-medium text-slate-700 mb-1">New Password <span class="text-red-500">*</span></label>
            <input id="newPassword" type="password" wire:model="newPassword" autocomplete="new-password"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-brand @error('newPassword') border-red-400 bg-red-50 @enderror">
            @error('newPassword')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
          </div>

          {{-- Confirm password --}}
          <div>
            <label for="confirmPassword" class="block text-sm font-medium text-slate-700 mb-1">Confirm New Password <span class="text-red-500">*</span></label>
            <input id="confirmPassword" type="password" wire:model="confirmPassword" autocomplete="new-password"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-brand @error('confirmPassword') border-red-400 bg-red-50 @enderror">
            @error('confirmPassword')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
          </div>
        </div>

        <div class="flex justify-end pt-1">
          <button type="submit"
                  wire:loading.attr="disabled" wire:target="savePassword"
                  class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 disabled:opacity-60 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition shadow-sm">
            <span wire:loading.remove wire:target="savePassword">Update Password</span>
            <span wire:loading wire:target="savePassword">Updating…</span>
          </button>
        </div>
      </form>
    </div>

  </div>
</div>
