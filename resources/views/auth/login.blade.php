{{-- <x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout> --}}

<x-guest-layout>
    <!-- Left Pane: University Branding Info -->
    <div class="w-full md:w-1/2 bg-linear-to-br from-indigo-900 via-violet-950 to-slate-900 p-8 flex flex-col justify-between text-white relative overflow-hidden">
        <!-- Subtle decorative background patterns -->
        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-yellow-500 rounded-full opacity-10 blur-2xl"></div>
        <div class="absolute -left-10 -top-10 w-40 h-40 bg-violet-500 rounded-full opacity-20 blur-2xl"></div>
        
        <!-- Header area inside pane -->
        <div class="flex items-center space-x-3 z-10">
            <span class="text-xs font-bold uppercase tracking-widest bg-white/10 px-3 py-1 rounded-full text-yellow-400 backdrop-blur-sm border border-white/5">
                FACMS v2.0
            </span>
        </div>

        
        <div class="my-auto py-12 flex flex-col items-center text-center z-10">
            <!-- BISU Logo Container -->
            <div class="w-30 h-30 rounded-lg bg-white p-2 rounded-full shadow-lg border-2 border-violet-400/30 flex items-center justify-center transition-transform hover:scale-105 duration-300">
                <img src="{{ asset('storage/images/logo/bisu_logo.png') }}" alt="Bohol Island State University Logo" class="object-contain max-h-full">
            </div>
            
            <h1 class="mt-6 text-xl font-bold tracking-tight text-gray-100 uppercase">
                Bohol Island State University
            </h1>
            <p class="text-xs text-violet-300 tracking-wider font-semibold mt-1 max-w-xs">
                Balilihan Campus · Magsija Balilihan Bohol
            </p>
            
            <div class="w-12 h-1 bg-yellow-400 rounded mt-6 mb-4"></div>
            
            <h2 class="text-lg font-medium text-gray-200 leading-snug px-4">
                Faculty Association Claims Monitoring System
            </h2>
        </div>

        <!-- Footer inside branding pane -->
        <div class="text-center md:text-left z-10">
            <p class="text-xs text-violet-300/70">
                &copy; {{ date('Y') }} BISU Faculty Association. All rights reserved.
            </p>
        </div>
    </div>

    <!-- Right Pane: Authentication Form Area -->
    <div class="w-full md:w-1/2 p-8 lg:p-12 flex flex-col justify-center bg-white dark:bg-gray-900">
        <div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">
                Account Sign In
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Please enter your credentials to manage records.
            </p>
        </div>

        <!-- Session Flash Status (e.g., password reset messages) -->
        <x-auth-session-status class="mt-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
            @csrf

            <!-- Email Address Entry Field -->
            <div>
                <x-input-label for="email" :value="__('Institutional Email')" class="font-medium text-gray-700 dark:text-gray-300" />
                <div class="mt-1 relative rounded-md shadow-sm">
                    <x-text-input id="email" class="block w-full px-4 py-2.5 border-gray-300 dark:border-gray-700 focus:border-violet-500 focus:ring focus:ring-violet-500/20 dark:bg-gray-950 transition duration-150 ease-in-out" 
                        type="email" 
                        name="email" 
                        :value="old('email')" 
                        required 
                        autofocus 
                        autocomplete="username" 
                        placeholder="username@bisu.edu.ph" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-red-500" />
            </div>

            <!-- Password Entry Field -->
            <div>
                <div class="flex items-center justify-between">
                    <x-input-label for="password" :value="__('Password')" class="font-medium text-gray-700 dark:text-gray-300" />
                </div>
                {{-- <div class="mt-1 relative rounded-md shadow-sm">
                    <x-text-input id="password" class="block w-full px-4 py-2.5 border-gray-300 dark:border-gray-700 focus:border-violet-500 focus:ring focus:ring-violet-500/20 dark:bg-gray-950 transition duration-150 ease-in-out"
                        type="password"
                        name="password"
                        required 
                        autocomplete="current-password" 
                        placeholder="••••••••" />
                </div> --}}
                <div class="mt-1 relative rounded-md shadow-sm">
                <x-text-input id="password" class="block w-full pl-4 pr-12 py-2.5 border-gray-300 dark:border-gray-700 focus:border-violet-500 focus:ring focus:ring-violet-500/20 dark:bg-gray-950 transition duration-150 ease-in-out"
                    type="password"
                    name="password"
                    required 
                    autocomplete="current-password" 
                    placeholder="••••••••" />

                <!-- Show/Hide Toggle Button -->
                <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                    <!-- Eye Icon (Visible by default) -->
                    <svg id="eye-icon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <!-- Eye Off Icon (Hidden by default) -->
                    <svg id="eye-off-icon" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.025 10.025 0 014.132-5.4M9.9 4.24a9.13 9.13 0 011.55-.24C16.478 4 20.268 6.943 21.542 11a10.054 10.054 0 01-2.244 4.4m-5 5l-4-4m4 4l4-4m-12-4h.01M21 21l-18-18" />
                    </svg>
                </button>
            </div>

                <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-red-500" />
            </div>

            <!-- Remember Me checkbox & Forgot Password link -->
            <div class="flex items-center justify-between pt-1">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-950 border-gray-300 dark:border-gray-700 text-violet-600 shadow-sm focus:ring-violet-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900" name="remember">
                    <span class="ms-2 text-xs text-gray-600 dark:text-gray-400 select-none">{{ __('Keep me logged in') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-xs font-medium text-violet-600 dark:text-violet-400 hover:text-violet-700 dark:hover:text-violet-300 underline underline-offset-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500" href="{{ route('password.request') }}">
                        {{ __('Forgot Password?') }}
                    </a>
                @endif
            </div>

            <!-- Submit Action -->
            <div class="pt-2">
                <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-gradient-to-r from-violet-700 to-indigo-700 hover:from-violet-800 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 dark:focus:ring-offset-gray-900 transition duration-150 ease-in-out transform active:scale-[0.98]">
                    {{ __('Sign In') }}
                </button>
            </div>
        </form>
    </div>
    <script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeOffIcon = document.getElementById('eye-off-icon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.add('hidden');
            eyeOffIcon.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('hidden');
            eyeOffIcon.classList.add('hidden');
        }
    }
</script>

</x-guest-layout>
