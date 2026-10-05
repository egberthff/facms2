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

        <!-- Account -->
        <fieldset class="space-y-4">
            <legend class="{{ $sectionClass }} w-full">{{ __('Account') }}</legend>

            <div>
                <x-input-label for="email" :class="$labelClass">{{ __('Email Address') }} <span class="text-red-500">*</span></x-input-label>
                <x-text-input id="email" :class="$inputClass" type="email" name="email" :value="old('email')"
                    required autofocus autocomplete="username" placeholder="jane.doe@bisu.edu.ph" />
                <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="password" :class="$labelClass">{{ __('Password') }} <span class="text-red-500">*</span></x-input-label>
                    <div class="relative">
                        <x-text-input id="password" class="{{ $inputClass }} pr-11" type="password" name="password"
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
                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :class="$labelClass">{{ __('Confirm Password') }} <span class="text-red-500">*</span></x-input-label>
                    <x-text-input id="password_confirmation" :class="$inputClass" type="password" name="password_confirmation"
                        required autocomplete="new-password" placeholder="••••••••" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-xs" />
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
                        required maxlength="100" autocomplete="given-name" placeholder="Jane" />
                    <x-input-error :messages="$errors->get('first_name')" class="mt-1 text-xs" />
                </div>

                <div>
                    <x-input-label for="middle_name" :value="__('Middle Name')" :class="$labelClass" />
                    <x-text-input id="middle_name" :class="$inputClass" type="text" name="middle_name" :value="old('middle_name')"
                        maxlength="100" autocomplete="additional-name" placeholder="Santos" />
                    <x-input-error :messages="$errors->get('middle_name')" class="mt-1 text-xs" />
                </div>

                <div>
                    <x-input-label for="last_name" :class="$labelClass">{{ __('Last Name') }} <span class="text-red-500">*</span></x-input-label>
                    <x-text-input id="last_name" :class="$inputClass" type="text" name="last_name" :value="old('last_name')"
                        required maxlength="100" autocomplete="family-name" placeholder="Doe" />
                    <x-input-error :messages="$errors->get('last_name')" class="mt-1 text-xs" />
                </div>

                <div>
                    <x-input-label for="suffix" :value="__('Suffix')" :class="$labelClass" />
                    <x-text-input id="suffix" :class="$inputClass" type="text" name="suffix" :value="old('suffix')"
                        maxlength="20" autocomplete="honorific-suffix" placeholder="Jr. / III" />
                    <x-input-error :messages="$errors->get('suffix')" class="mt-1 text-xs" />
                </div>

                <div>
                    <x-input-label for="gender" :class="$labelClass">{{ __('Gender') }} <span class="text-red-500">*</span></x-input-label>
                    <select id="gender" name="gender" required class="{{ $inputClass }}">
                        <option value="" disabled @selected(! old('gender'))>{{ __('Select gender') }}</option>
                        @foreach (['Male', 'Female'] as $gender)
                            <option value="{{ $gender }}" @selected(old('gender') === $gender)>{{ $gender }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('gender')" class="mt-1 text-xs" />
                </div>

                <div>
                    <x-input-label for="birth_date" :value="__('Birth Date')" :class="$labelClass" />
                    <x-text-input id="birth_date" :class="$inputClass" type="date" name="birth_date" :value="old('birth_date')"
                        max="{{ now()->subDay()->toDateString() }}" autocomplete="bday" />
                    <x-input-error :messages="$errors->get('birth_date')" class="mt-1 text-xs" />
                </div>

                <div>
                    <x-input-label for="contact_number" :class="$labelClass">{{ __('Contact Number') }} <span class="text-red-500">*</span></x-input-label>
                    <x-text-input id="contact_number" :class="$inputClass" type="tel" name="contact_number" :value="old('contact_number')"
                        required maxlength="20" autocomplete="tel" placeholder="09XXXXXXXXX" />
                    <x-input-error :messages="$errors->get('contact_number')" class="mt-1 text-xs" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="address" :class="$labelClass">{{ __('Address') }} <span class="text-red-500">*</span></x-input-label>
                    <x-text-input id="address" :class="$inputClass" type="text" name="address" :value="old('address')"
                        required maxlength="500" autocomplete="street-address" placeholder="Street, Barangay, Municipality, Province" />
                    <x-input-error :messages="$errors->get('address')" class="mt-1 text-xs" />
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
                        required maxlength="255" placeholder="EMP-2026-0001" />
                    <x-input-error :messages="$errors->get('employee_number')" class="mt-1 text-xs" />
                </div>

                <div>
                    <x-input-label for="campus_id" :class="$labelClass">{{ __('Campus') }} <span class="text-red-500">*</span></x-input-label>
                    <select id="campus_id" name="campus_id" required class="{{ $inputClass }}">
                        <option value="" disabled @selected(! old('campus_id'))>{{ __('Select your campus') }}</option>
                        @foreach ($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected(old('campus_id') === $campus->id)>
                                {{ $campus->campus_name }}{{ $campus->acronym ? " ({$campus->acronym})" : '' }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('campus_id')" class="mt-1 text-xs" />
                </div>

                <div>
                    <x-input-label for="department" :class="$labelClass">{{ __('Department') }} <span class="text-red-500">*</span></x-input-label>
                    <x-text-input id="department" :class="$inputClass" type="text" name="department" :value="old('department')"
                        required maxlength="255" placeholder="College of Engineering" />
                    <x-input-error :messages="$errors->get('department')" class="mt-1 text-xs" />
                </div>

                <div>
                    <x-input-label for="position" :class="$labelClass">{{ __('Position') }} <span class="text-red-500">*</span></x-input-label>
                    <x-text-input id="position" :class="$inputClass" type="text" name="position" :value="old('position')"
                        required maxlength="255" placeholder="Instructor I" />
                    <x-input-error :messages="$errors->get('position')" class="mt-1 text-xs" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="employment_status" :class="$labelClass">{{ __('Employment Status') }} <span class="text-red-500">*</span></x-input-label>
                    <select id="employment_status" name="employment_status" required class="{{ $inputClass }}">
                        <option value="" disabled @selected(! old('employment_status'))>{{ __('Select status') }}</option>
                        @foreach (['Permanent', 'Probationary', 'Contractual', 'Part-time'] as $status)
                            <option value="{{ $status }}" @selected(old('employment_status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('employment_status')" class="mt-1 text-xs" />
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
                        maxlength="20" placeholder="09XXXXXXXXX" />
                    <x-input-error :messages="$errors->get('gcash_number')" class="mt-1 text-xs" />
                </div>

                <div>
                    <x-input-label for="gcash_account_name" :value="__('GCash Account Name')" :class="$labelClass" />
                    <x-text-input id="gcash_account_name" :class="$inputClass" type="text" name="gcash_account_name" :value="old('gcash_account_name')"
                        maxlength="255" placeholder="Jane S. Doe" />
                    <x-input-error :messages="$errors->get('gcash_account_name')" class="mt-1 text-xs" />
                </div>
            </div>
        </fieldset>

        <!-- Actions -->
        <div class="pt-2 space-y-4">
            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-[#1e293b] hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition duration-150 ease-in-out transform active:scale-[0.98]">
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
    </script>
</x-guest-layout>
