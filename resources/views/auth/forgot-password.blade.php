<x-guest-layout>
    <!-- Section Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight mb-2">Password Assistance</h2>
        <p class="text-sm text-slate-600 leading-relaxed">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Member Email Address')" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
            <x-text-input 
                id="email" 
                class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm py-2.5" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autofocus 
                placeholder="name@university.edu"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs" />
        </div>

        <!-- Submission Action Block -->
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-[#1e293b] underline transition-colors">
                {{ __('Back to login') }}
            </a>

            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-[#1e293b] hover:bg-slate-800 text-white text-sm font-semibold rounded-md shadow transition-colors ease-in-out duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500">
                {{ __('Send Reset Link') }}
            </button>
        </div>
    </form>
</x-guest-layout>
