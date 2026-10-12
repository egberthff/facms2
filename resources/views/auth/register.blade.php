@php
    $inputClass = 'block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm py-2.5';
    $labelClass = 'text-xs font-bold text-slate-700 uppercase tracking-wider';
    $sectionClass = 'text-xs font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200 pb-2';
@endphp

<x-guest-layout>
    <!-- Section Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight mb-2">Member Registration</h2>
        <p class="text-sm text-slate-600 leading-relaxed">
            {{ __('Create your account to start managing your academic benefits, tracking medical claims, and accessing association resources.') }}
        </p>
        <p class="text-xs text-slate-500 mt-2">
            {{ __('Fields marked') }} <span class="text-red-500">*</span> {{ __('are required. Your membership will be activated by your campus officers after you verify your email address.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full Name')" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
            <x-text-input 
                id="name" 
                class="block mt-1 w-full rounded-lg border-slate-300 shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm py-2.5" 
                type="text" 
                name="name" 
                :value="old('name')" 
                required 
                autofocus 
                autocomplete="name" 
                placeholder="Professor Jane Doe"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-1 text-xs" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Faculty Email Address')" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
            <x-text-input 
                id="email" 
                class="block mt-1 w-full rounded-lg border-slate-300 shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm py-2.5" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autocomplete="username" 
                placeholder="jane.doe@university.edu"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs" />
        </div>

            <div>
                <x-input-label for="password" :value="__('Password')" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
                <x-text-input 
                    id="password" 
                    class="block mt-1 w-full rounded-lg border-slate-300 shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm py-2.5"
                    type="password"
                    name="password"
                    required 
                    autocomplete="new-password" 
                    placeholder="••••••••"
                />
                <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs" />
            </div>

            <!-- Confirm Password -->
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
                <x-text-input 
                    id="password_confirmation" 
                    class="block mt-1 w-full rounded-lg border-slate-300 shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm py-2.5"
                    type="password"
                    name="password_confirmation" 
                    required 
                    autocomplete="new-password" 
                    placeholder="••••••••"
                />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-xs" />
            </div>
        </fieldset>

        <!-- Submission Action Block -->
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between pt-4">
            <a class="text-sm font-semibold text-slate-600 hover:text-[#1e293b] underline transition-colors" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-[#1e293b] hover:bg-slate-800 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors ease-in-out duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500">
                {{ __('Register Account') }}
            </button>

            <p class="text-center text-xs font-medium text-slate-500">
                {{ __('Already registered?') }}
                <a href="{{ route('login') }}" class="font-semibold text-slate-600 hover:text-[#1e293b] underline transition-colors">
                    {{ __('Sign in') }}
                </a>
            </p>
        </div>
    </form>

    <script>
        function togglePasswordVisibility() {
            const inputs = [document.getElementById('password'), document.getElementById('password_confirmation')];
            const eyeIcon = document.getElementById('eye-icon');
            const eyeOffIcon = document.getElementById('eye-off-icon');
            const reveal = inputs[0].type === 'password';

            inputs.forEach((input) => input.type = reveal ? 'text' : 'password');
            eyeIcon.classList.toggle('hidden', reveal);
            eyeOffIcon.classList.toggle('hidden', ! reveal);
        }
    </script>
</x-guest-layout>
