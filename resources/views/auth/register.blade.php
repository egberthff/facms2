@php
    $inputClass = 'block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm py-2.5';
    $labelClass = 'text-xs font-bold text-slate-700 uppercase tracking-wider';
    $sectionClass = 'text-xs font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200 pb-2';
    $errorClass = 'mt-1.5 text-xs text-red-600';
@endphp

<x-guest-layout :stacked="true">
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

    <form
        id="register-form"
        method="POST"
        action="{{ route('register') }}"
        class="space-y-6"
        novalidate
        x-data="registerForm(@js((object) collect($errors->getMessages())->map(fn ($messages) => $messages[0])->all()))"
        @input="clearError($event.target.name)"
        @change="clearError($event.target.name)"
        @submit.prevent="submitForm">
        @csrf

        <!-- Account -->
        <fieldset class="space-y-4">
            <legend class="{{ $sectionClass }} w-full">{{ __('Account') }}</legend>

            <div>
                <x-input-label for="email" :class="$labelClass">{{ __('Email Address') }} <span class="text-red-500">*</span></x-input-label>
                <x-text-input id="email" :class="$inputClass" type="email" name="email" :value="old('email')"
                    x-bind:class="{ 'error-border': errors.email }"
                    required autofocus autocomplete="username" placeholder="jane.doe@bisu.edu.ph" />
                <p id="email-error" x-show="errors.email" x-text="errors.email" x-cloak class="{{ $errorClass }}"></p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="password" :class="$labelClass">{{ __('Password') }} <span class="text-red-500">*</span></x-input-label>
                    <div class="relative">
                        <x-text-input id="password" class="{{ $inputClass }} pr-11" type="password" name="password"
                            x-bind:class="{ 'error-border': errors.password }"
                            required autocomplete="new-password" placeholder="••••••••" />
                        <button type="button" onclick="togglePasswordVisibility()" aria-label="{{ __('Show or hide password') }}"
                            class="absolute inset-y-0 right-0 mt-1 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                            <svg id="eye-icon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-off-icon" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.025 10.025 0 014.132-5.4M9.9 4.24a9.13 9.13 0 011.55-.24C16.478 4 20.268 6.943 21.542 11a10.054 10.054 0 01-2.244 4.4m-5 5l-4-4m4 4l4-4m-12-4h.01M21 21l-18-18" />
                            </svg>
                        </button>
                    </div>
                    <p id="password-error" x-show="errors.password" x-text="errors.password" x-cloak class="{{ $errorClass }}"></p>
                </div>

                <div>
                    <x-input-label for="password_confirmation" :class="$labelClass">{{ __('Confirm Password') }} <span class="text-red-500">*</span></x-input-label>
                    <x-text-input id="password_confirmation" :class="$inputClass" type="password" name="password_confirmation"
                        x-bind:class="{ 'error-border': errors.password_confirmation }"
                        required autocomplete="new-password" placeholder="••••••••" />
                    <p id="password_confirmation-error" x-show="errors.password_confirmation" x-text="errors.password_confirmation" x-cloak class="{{ $errorClass }}"></p>
                </div>
            </div>
        </fieldset>

        <!-- Personal Information -->
        <fieldset class="space-y-4">
            <legend class="{{ $sectionClass }} w-full">{{ __('Personal Information') }}</legend>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="first_name" :class="$labelClass">{{ __('First Name') }} <span class="text-red-500">*</span></x-input-label>
                    <x-text-input id="first_name" :class="$inputClass" type="text" name="first_name" :value="old('first_name')"
                        x-bind:class="{ 'error-border': errors.first_name }"
                        required maxlength="100" autocomplete="given-name" placeholder="Jane" />
                    <p id="first_name-error" x-show="errors.first_name" x-text="errors.first_name" x-cloak class="{{ $errorClass }}"></p>
                </div>

                <div>
                    <x-input-label for="middle_name" :value="__('Middle Name')" :class="$labelClass" />
                    <x-text-input id="middle_name" :class="$inputClass" type="text" name="middle_name" :value="old('middle_name')"
                        x-bind:class="{ 'error-border': errors.middle_name }"
                        maxlength="100" autocomplete="additional-name" placeholder="Santos" />
                    <p id="middle_name-error" x-show="errors.middle_name" x-text="errors.middle_name" x-cloak class="{{ $errorClass }}"></p>
                </div>

                <div>
                    <x-input-label for="last_name" :class="$labelClass">{{ __('Last Name') }} <span class="text-red-500">*</span></x-input-label>
                    <x-text-input id="last_name" :class="$inputClass" type="text" name="last_name" :value="old('last_name')"
                        x-bind:class="{ 'error-border': errors.last_name }"
                        required maxlength="100" autocomplete="family-name" placeholder="Doe" />
                    <p id="last_name-error" x-show="errors.last_name" x-text="errors.last_name" x-cloak class="{{ $errorClass }}"></p>
                </div>

                <div>
                    <x-input-label for="suffix" :value="__('Suffix')" :class="$labelClass" />
                    <x-text-input id="suffix" :class="$inputClass" type="text" name="suffix" :value="old('suffix')"
                        x-bind:class="{ 'error-border': errors.suffix }"
                        maxlength="20" autocomplete="honorific-suffix" placeholder="Jr. / III" />
                    <p id="suffix-error" x-show="errors.suffix" x-text="errors.suffix" x-cloak class="{{ $errorClass }}"></p>
                </div>

                <div>
                    <x-input-label for="gender" :class="$labelClass">{{ __('Gender') }} <span class="text-red-500">*</span></x-input-label>
                    <select id="gender" name="gender" required class="{{ $inputClass }}"
                        x-bind:class="{ 'error-border': errors.gender }">
                        <option value="" disabled @selected(! old('gender'))>{{ __('Select gender') }}</option>
                        @foreach (['Male', 'Female'] as $gender)
                            <option value="{{ $gender }}" @selected(old('gender') === $gender)>{{ $gender }}</option>
                        @endforeach
                    </select>
                    <p id="gender-error" x-show="errors.gender" x-text="errors.gender" x-cloak class="{{ $errorClass }}"></p>
                </div>

                <div>
                    <x-input-label for="birth_date" :value="__('Birth Date')" :class="$labelClass" />
                    <x-text-input id="birth_date" :class="$inputClass" type="date" name="birth_date" :value="old('birth_date')"
                        x-bind:class="{ 'error-border': errors.birth_date }"
                        max="{{ now()->subDay()->toDateString() }}" autocomplete="bday" />
                    <p id="birth_date-error" x-show="errors.birth_date" x-text="errors.birth_date" x-cloak class="{{ $errorClass }}"></p>
                </div>

                <div>
                    <x-input-label for="contact_number" :class="$labelClass">{{ __('Contact Number') }} <span class="text-red-500">*</span></x-input-label>
                    <x-text-input id="contact_number" :class="$inputClass" type="tel" name="contact_number" :value="old('contact_number')"
                        x-bind:class="{ 'error-border': errors.contact_number }"
                        required maxlength="11" inputmode="numeric" autocomplete="tel" placeholder="09XXXXXXXXX" />
                    <p id="contact_number-error" x-show="errors.contact_number" x-text="errors.contact_number" x-cloak class="{{ $errorClass }}"></p>
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="address" :class="$labelClass">{{ __('Address') }} <span class="text-red-500">*</span></x-input-label>
                    <x-text-input id="address" :class="$inputClass" type="text" name="address" :value="old('address')"
                        x-bind:class="{ 'error-border': errors.address }"
                        required maxlength="500" autocomplete="street-address" placeholder="Street, Barangay, Municipality, Province" />
                    <p id="address-error" x-show="errors.address" x-text="errors.address" x-cloak class="{{ $errorClass }}"></p>
                </div>
            </div>
        </fieldset>

        <!-- Employment -->
        <fieldset class="space-y-4">
            <legend class="{{ $sectionClass }} w-full">{{ __('Employment') }}</legend>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="employee_number" :class="$labelClass">{{ __('Employee Number') }} <span class="text-red-500">*</span></x-input-label>
                    <x-text-input id="employee_number" :class="$inputClass" type="text" name="employee_number" :value="old('employee_number')"
                        x-bind:class="{ 'error-border': errors.employee_number }"
                        required maxlength="255" placeholder="EMP-2026-0001" />
                    <p id="employee_number-error" x-show="errors.employee_number" x-text="errors.employee_number" x-cloak class="{{ $errorClass }}"></p>
                </div>

                <div>
                    <x-input-label for="campus_id" :class="$labelClass">{{ __('Campus') }} <span class="text-red-500">*</span></x-input-label>
                    <select id="campus_id" name="campus_id" required class="{{ $inputClass }}"
                        x-bind:class="{ 'error-border': errors.campus_id }">
                        <option value="" disabled @selected(! old('campus_id'))>{{ __('Select your campus') }}</option>
                        @foreach ($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected(old('campus_id') === $campus->id)>
                                {{ $campus->campus_name }}{{ $campus->acronym ? " ({$campus->acronym})" : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p id="campus_id-error" x-show="errors.campus_id" x-text="errors.campus_id" x-cloak class="{{ $errorClass }}"></p>
                </div>

                <div>
                    <x-input-label for="department" :class="$labelClass">{{ __('Department') }} <span class="text-red-500">*</span></x-input-label>
                    <x-text-input id="department" :class="$inputClass" type="text" name="department" :value="old('department')"
                        x-bind:class="{ 'error-border': errors.department }"
                        required maxlength="255" placeholder="College of Engineering" />
                    <p id="department-error" x-show="errors.department" x-text="errors.department" x-cloak class="{{ $errorClass }}"></p>
                </div>

                <div>
                    <x-input-label for="position" :class="$labelClass">{{ __('Position') }} <span class="text-red-500">*</span></x-input-label>
                    <x-text-input id="position" :class="$inputClass" type="text" name="position" :value="old('position')"
                        x-bind:class="{ 'error-border': errors.position }"
                        required maxlength="255" placeholder="Instructor I" />
                    <p id="position-error" x-show="errors.position" x-text="errors.position" x-cloak class="{{ $errorClass }}"></p>
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="employment_status" :class="$labelClass">{{ __('Employment Status') }} <span class="text-red-500">*</span></x-input-label>
                    <select id="employment_status" name="employment_status" required class="{{ $inputClass }}"
                        x-bind:class="{ 'error-border': errors.employment_status }">
                        <option value="" disabled @selected(! old('employment_status'))>{{ __('Select status') }}</option>
                        @foreach (['Permanent', 'Probationary', 'Contractual', 'Part-time'] as $status)
                            <option value="{{ $status }}" @selected(old('employment_status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <p id="employment_status-error" x-show="errors.employment_status" x-text="errors.employment_status" x-cloak class="{{ $errorClass }}"></p>
                </div>
            </div>
        </fieldset>

        <!-- GCash Payout (optional) -->
        <fieldset class="space-y-4">
            <legend class="{{ $sectionClass }} w-full">{{ __('GCash Payout') }} <span class="normal-case font-medium tracking-normal">({{ __('optional') }})</span></legend>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="gcash_number" :value="__('GCash Number')" :class="$labelClass" />
                    <x-text-input id="gcash_number" :class="$inputClass" type="tel" name="gcash_number" :value="old('gcash_number')"
                        x-bind:class="{ 'error-border': errors.gcash_number }"
                        maxlength="11" inputmode="numeric" placeholder="09XXXXXXXXX" />
                    <p id="gcash_number-error" x-show="errors.gcash_number" x-text="errors.gcash_number" x-cloak class="{{ $errorClass }}"></p>
                </div>

                <div>
                    <x-input-label for="gcash_account_name" :value="__('GCash Account Name')" :class="$labelClass" />
                    <x-text-input id="gcash_account_name" :class="$inputClass" type="text" name="gcash_account_name" :value="old('gcash_account_name')"
                        x-bind:class="{ 'error-border': errors.gcash_account_name }"
                        maxlength="255" placeholder="Jane S. Doe" />
                    <p id="gcash_account_name-error" x-show="errors.gcash_account_name" x-text="errors.gcash_account_name" x-cloak class="{{ $errorClass }}"></p>
                </div>
            </div>
        </fieldset>

        <!-- Actions -->
        <div class="pt-2 space-y-4">
            <button type="submit" x-bind:disabled="loading" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-[#1e293b] hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition duration-150 ease-in-out transform active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed">
                {{ __('Create Account') }}
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

        function registerForm(initialErrors = {}) {

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

                    const form = document.getElementById('register-form');
                    const formData = new FormData(form);

                    Swal.fire({
                        title: 'Create your account?',
                        text: 'Please make sure your details are correct before submitting.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, register me!'
                    }).then(async (result) => {
                        if (!result.isConfirmed) {
                            return;
                        }

                        this.loading = true;

                        Swal.fire({
                            title: 'Creating your account...',
                            text: 'Please wait.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

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

                            // Convert the loader into the success state; OK continues to email verification
                            Swal.hideLoading();
                            Swal.update({
                                icon: 'success',
                                title: 'Registration successful!',
                                text: data.message,
                                showConfirmButton: true,
                                confirmButtonText: 'Continue'
                            });

                            Swal.getConfirmButton().addEventListener('click', () => {
                                window.location.href = data.redirect;
                            });

                        } catch (error) {
                            this.loading = false;

                            // Field validation errors: show them under their inputs
                            if (error && error.errors) {
                                Swal.close();
                                this.showServerErrors(error.errors);
                                return;
                            }

                            let errorMessage = 'Something went wrong. Please try again.';
                            if (error && error.message) {
                                errorMessage = error.message;
                            }

                            Swal.hideLoading();
                            Swal.update({
                                icon: 'error',
                                title: 'Oops...',
                                text: errorMessage,
                                showConfirmButton: true,
                                allowOutsideClick: true,
                                allowEscapeKey: true
                            });
                        }
                    });
                },

                /* =====================================================
                 * VALIDATION
                 * ===================================================== */

                validate() {

                    const value = (name) => (document.querySelector(`#register-form [name="${name}"]`)?.value || '').trim();
                    const errors = {};
                    const mobilePattern = /^09\d{9}$/;

                    const required = {
                        email: 'Please enter your email address.',
                        password: 'Please enter a password.',
                        password_confirmation: 'Please confirm your password.',
                        first_name: 'Please enter your first name.',
                        last_name: 'Please enter your last name.',
                        gender: 'Please select your gender.',
                        contact_number: 'Please enter your contact number.',
                        address: 'Please enter your address.',
                        employee_number: 'Please enter your employee number.',
                        campus_id: 'Please select your campus.',
                        department: 'Please enter your department.',
                        position: 'Please enter your position.',
                        employment_status: 'Please select your employment status.',
                    };

                    Object.entries(required).forEach(([field, message]) => {
                        if (!value(field)) {
                            errors[field] = message;
                        }
                    });

                    if (value('email') && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value('email'))) {
                        errors.email = 'Please enter a valid email address.';
                    }

                    if (value('password') && value('password').length < 8) {
                        errors.password = 'Password must be at least 8 characters.';
                    }

                    if (value('password_confirmation') && value('password_confirmation') !== value('password')) {
                        errors.password_confirmation = 'The password confirmation does not match.';
                    }

                    if (value('birth_date') && value('birth_date') >= new Date().toLocaleDateString('en-CA')) {
                        errors.birth_date = 'The birth date must be a date in the past.';
                    }

                    if (value('contact_number') && !mobilePattern.test(value('contact_number'))) {
                        errors.contact_number = 'Contact number must be 11 digits (09XXXXXXXXX).';
                    }

                    // GCash payout is optional, but both fields go together
                    if (value('gcash_number') && !mobilePattern.test(value('gcash_number'))) {
                        errors.gcash_number = 'GCash number must be 11 digits (09XXXXXXXXX).';
                    } else if (!value('gcash_number') && value('gcash_account_name')) {
                        errors.gcash_number = 'Please enter the GCash number for this account name.';
                    }

                    if (value('gcash_number') && !value('gcash_account_name')) {
                        errors.gcash_account_name = 'Please enter the GCash account name.';
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
                        const form = document.getElementById('register-form');
                        const first = Array.from(form.elements).find(el => el.name && this.errors[el.name]);

                        if (first) {
                            first.focus();
                            first.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
