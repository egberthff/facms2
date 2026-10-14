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

    <form
        id="login-form"
        method="POST"
        action="{{ route('login') }}"
        class="mt-6 space-y-5"
        novalidate
        x-data="loginForm(@js((object) collect($errors->getMessages())->map(fn ($messages) => $messages[0])->all()))"
        @input="clearError($event.target.name)"
        @change="clearError($event.target.name)"
        @submit.prevent="submitForm">
        @csrf

        <!-- Email Address Entry Field -->
        <div>
            <x-input-label for="email" :value="__('Institutional Email')" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
            <div class="mt-1 relative rounded-md shadow-sm">
                <x-text-input id="email" class="block w-full px-4 py-2.5 border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm transition duration-150 ease-in-out" 
                    type="email" 
                    name="email" 
                    :value="old('email')"
                    x-bind:class="{ 'error-border': errors.email }"
                    required
                    autofocus 
                    autocomplete="username" 
                    placeholder="username@bisu.edu.ph" />
            </div>
            <p id="email-error" x-show="errors.email" x-text="errors.email" x-cloak class="mt-1.5 text-xs text-red-600"></p>
        </div>

        <!-- Password Entry Field -->
        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
            </div>
            
            <div class="mt-1 relative rounded-md shadow-sm">
                <x-text-input id="password" class="block w-full pl-4 pr-12 py-2.5 border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm transition duration-150 ease-in-out"
                    type="password"
                    name="password"
                    x-bind:class="{ 'error-border': errors.password }"
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

            <p id="password-error" x-show="errors.password" x-text="errors.password" x-cloak class="mt-1.5 text-xs text-red-600"></p>
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
            <button type="submit" x-bind:disabled="loading" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-[#1e293b] hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition duration-150 ease-in-out transform active:scale-[0.98] disabled:opacity-80 disabled:cursor-wait">
                <svg x-show="loading" style="display: none;" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="loading ? '{{ __('Signing in...') }}' : '{{ __('Sign In') }}'">{{ __('Sign In') }}</span>
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

        function loginForm(initialErrors = {}) {

            return {

                /* =====================================================
                 * VALIDATION ERRORS (field name => message)
                 * ===================================================== */

                errors: { ...initialErrors },

                loading: false,

                init() {
                    // Errors from a non-JS fallback submit: focus the first one
                    if (Object.keys(this.errors).length) {
                        this.focusFirstError();
                    }
                },

                async submitForm() {

                    if (!this.validate()) {
                        return;
                    }

                    const form = document.getElementById('login-form');
                    const formData = new FormData(form);

                    this.loading = true;

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw data;
                        }

                        // Keep the button spinning until the next page loads
                        window.location.href = data.redirect;

                    } catch (error) {
                        this.loading = false;

                        // Field validation errors (incl. wrong credentials / throttling): show them under their inputs
                        if (error && error.errors) {
                            form.querySelector('[name="password"]').value = '';
                            this.showServerErrors(error.errors);
                            return;
                        }

                        let errorMessage = 'Something went wrong. Please try again.';
                        if (error && error.message) {
                            errorMessage = error.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: errorMessage
                        });
                    }
                },

                /* =====================================================
                 * VALIDATION
                 * ===================================================== */

                validate() {

                    const value = (name) => (document.querySelector(`#login-form [name="${name}"]`)?.value || '').trim();
                    const errors = {};

                    if (!value('email')) {
                        errors.email = 'Please enter your email address.';
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value('email'))) {
                        errors.email = 'Please enter a valid email address.';
                    }

                    if (!value('password')) {
                        errors.password = 'Please enter your password.';
                    }

                    return this.setErrors(errors);
                },

                /**
                 * Replace all field errors, then focus the first invalid
                 * one. Returns true when the form has no errors.
                 */
                setErrors(errors) {
                    this.errors = errors;

                    if (Object.keys(errors).length) {
                        this.focusFirstError();
                        return false;
                    }

                    return true;
                },

                focusFirstError() {
                    this.$nextTick(() => {
                        const form = document.getElementById('login-form');
                        const first = Array.from(form.elements).find(el => el.name && this.errors[el.name]);

                        if (first) {
                            first.focus();
                        }
                    });
                },

                clearError(name) {
                    if (name && this.errors[name]) {
                        this.errors[name] = '';
                    }
                },

                /**
                 * Show server-side (422) validation errors under their fields.
                 */
                showServerErrors(serverErrors) {
                    const errors = {};

                    Object.entries(serverErrors).forEach(([field, messages]) => {
                        errors[field] = [].concat(messages)[0];
                    });

                    this.setErrors(errors);
                },

            }

        }
    </script>
</x-guest-layout>
