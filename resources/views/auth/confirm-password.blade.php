<x-guest-layout>
    <!-- Section Header -->
    <div class="mb-6">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-amber-50 text-amber-600 mb-4">
            <!-- Simple Lock/Security Icon -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://w3.org">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight mb-2">Secure Verification</h2>
        <p class="text-sm text-slate-600 leading-relaxed">
            {{ __('This is a secure area of the application. Please confirm your password before continuing to your account dashboard.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Confirm Your Password')" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
            <x-text-input 
                id="password" 
                class="block mt-1 w-full rounded-lg border-slate-300 shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm py-2.5"
                type="password"
                name="password"
                required 
                autocomplete="current-password" 
                placeholder="••••••••"
                autofocus
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs" />
        </div>

        <!-- Submission Action Block -->
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between pt-2">
            <a href="{{ url()->previous() }}" class="text-sm font-semibold text-slate-600 hover:text-[#1e293b] underline transition-colors">
                {{ __('Cancel and go back') }}
            </a>

            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-[#1e293b] hover:bg-slate-800 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors ease-in-out duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500">
                {{ __('Confirm Password') }}
            </button>
        </div>
    </form>
</x-guest-layout>
