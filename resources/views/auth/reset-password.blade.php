<x-guest-layout>
    <!-- Section Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight mb-2">Create New Password</h2>
        <p class="text-sm text-slate-600 leading-relaxed">
            {{ __('Please enter your registered email address and choose a secure new password for your account.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Member Email Address')" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
            <x-text-input 
                id="email" 
                class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm py-2.5 bg-slate-50 cursor-not-allowed" 
                type="email" 
                name="email" 
                :value="old('email', $request->email)" 
                required 
                readonly 
                autocomplete="username" 
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('New Password')" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
            <x-text-input 
                id="password" 
                class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm py-2.5" 
                type="password" 
                name="password" 
                required 
                autofocus
                autocomplete="new-password" 
                placeholder="••••••••"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm New Password')" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
            <x-text-input 
                id="password_confirmation" 
                class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm py-2.5"
                type="password"
                name="password_confirmation" 
                required 
                autocomplete="new-password" 
                placeholder="••••••••"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-xs" />
        </div>

        <!-- Submission Action Block -->
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-[#1e293b] underline transition-colors">
                {{ __('Cancel and login') }}
            </a>

            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-[#1e293b] hover:bg-slate-800 text-white text-sm font-semibold rounded-md shadow transition-colors ease-in-out duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500">
                {{ __('Reset Password') }}
            </button>
        </div>
    </form>
</x-guest-layout>
