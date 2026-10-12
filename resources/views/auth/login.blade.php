<x-guest-layout>
    <!-- Authentication Form Area Header -->
    <div>
        <h3 class="text-2xl font-bold text-slate-800 tracking-tight">
            Account Sign In
        </h3>
        <p class="text-sm text-slate-600 mt-1">
            Please enter your credentials to manage your benefits and claims.
        </p>
    </div>

    <!-- Session Flash Status (e.g., password reset messages) -->
    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
        @csrf

        <!-- Email Address Entry Field -->
        <div>
            <x-input-label for="email" :value="__('Institutional Email')" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
            <div class="mt-1 relative rounded-md shadow-sm">
                <x-text-input id="email" class="block w-full px-4 py-2.5 border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm transition duration-150 ease-in-out" 
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
                <x-input-label for="password" :value="__('Password')" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
            </div>
            
            <div class="mt-1 relative rounded-md shadow-sm">
                <x-text-input id="password" class="block w-full pl-4 pr-12 py-2.5 border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm transition duration-150 ease-in-out"
                    type="password"
                    name="password"
                    required 
                    autocomplete="current-password" 
                    placeholder="••••••••" />

                <!-- Show/Hide Toggle Button -->
                <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
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
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-slate-700 shadow-sm focus:ring-[#1e293b] focus:ring-offset-2" name="remember">
                <span class="ms-2 text-xs font-semibold text-slate-600 select-none">{{ __('Keep me logged in') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-xs font-semibold text-slate-600 hover:text-[#1e293b] underline underline-offset-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500" href="{{ route('password.request') }}">
                    {{ __('Forgot Password?') }}
                </a>
            @endif
        </div>

        <!-- Submit Action Button -->
        <div class="pt-2">
            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-[#1e293b] hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition duration-150 ease-in-out transform active:scale-[0.98]">
                {{ __('Sign In') }}
            </button>
        </div>
        
        <!-- Registration Route Link -->
        @if (Route::has('register'))
            <div class="relative flex items-center pt-1" aria-hidden="true">
                <div class="grow border-t border-slate-200"></div>
                <span class="mx-3 text-xs font-medium text-slate-400 uppercase tracking-wider">{{ __('or') }}</span>
                <div class="grow border-t border-slate-200"></div>
            </div>

            <div class="text-center space-y-2">
                <a href="{{ route('register') }}" class="w-full flex justify-center py-2.5 px-4 border border-slate-300 rounded-lg shadow-sm text-sm font-semibold text-[#1e293b] bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition duration-150 ease-in-out">
                    {{ __('Create an Account') }}
                </a>
                <p class="text-xs font-medium text-slate-500">
                    {{ __('New to the association? Sign up and verify your email to get started.') }}
                </p>
            </div>
        @endif
    </form>

    <!-- Vanilla Javascript Password Toggle -->
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
