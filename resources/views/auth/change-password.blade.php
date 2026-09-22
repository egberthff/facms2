<x-guest-layout>
    <!-- Section Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight mb-2">Change Password</h2>
        <p class="text-sm text-slate-600 leading-relaxed">
            {{ __('Please enter your current password followed by your new password to update your account security credentials.') }}
        </p>
    </div>

    <!-- Session Status / Success Alert -->
    @if (session('status') === 'password-updated')
        <div class="mb-5 text-sm font-medium text-emerald-600">
            {{ __('Your password has been successfully updated.') }}
        </div>
    @endif

    <!-- Ensure your route name matches your web.php route definition -->
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5" id="passwordChangeForm">
        @csrf
        @method('put')
        <!-- Inside your password change form, right under @csrf -->
        <input type="hidden" name="redirect_to" value="{{ old('redirect_to', url()->previous()) }}">

        <!-- Current Password -->
        <div>
            <x-input-label for="current_password" :value="__('Current Password')" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
            <x-text-input 
                id="current_password" 
                class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm py-2.5" 
                type="password" 
                name="current_password" 
                required 
                autocomplete="current-password"
                placeholder="••••••••"
            />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-xs" />
        </div>

        <!-- New Password -->
        <div>
            <x-input-label for="password" :value="__('New Password')" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
            <x-text-input 
                id="password" 
                class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm py-2.5" 
                type="password" 
                name="password" 
                required 
                autocomplete="new-password"
                placeholder="••••••••"
            />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-xs" />
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
            <!-- Client-side real-time match indicator -->
            <div id="match-message" class="mt-2 text-xs font-semibold hidden"></div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-xs" />
        </div>

        <!-- Submission Action Block -->
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-slate-600 hover:text-[#1e293b] underline transition-colors">
                {{ __('Cancel') }}
            </a>

            <button 
                type="submit" 
                id="submit-button"
                class="inline-flex items-center px-5 py-2.5 bg-[#1e293b] hover:bg-slate-800 text-white text-sm font-semibold rounded-md shadow transition-colors ease-in-out duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {{ __('Update Password') }}
            </button>
        </div>
    </form>

    <!-- Client-side Real-time Matching Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('password_confirmation');
            const messageDiv = document.getElementById('match-message');
            const submitBtn = document.getElementById('submit-button');

            function validatePasswords() {
                const password = passwordInput.value;
                const confirmation = confirmInput.value;

                // If fields are completely empty, clear messaging and enable button
                if (!password && !confirmation) {
                    messageDiv.classList.add('hidden');
                    submitBtn.removeAttribute('disabled');
                    return;
                }

                messageDiv.classList.remove('hidden');

                if (password === confirmation) {
                    messageDiv.textContent = '✓ Passwords match';
                    messageDiv.className = 'mt-2 text-xs font-semibold text-emerald-600';
                    submitBtn.removeAttribute('disabled');
                } else {
                    messageDiv.textContent = '✗ Passwords do not match';
                    messageDiv.className = 'mt-2 text-xs font-semibold text-rose-600';
                    submitBtn.setAttribute('disabled', 'true');
                }
            }

            passwordInput.addEventListener('input', validatePasswords);
            confirmInput.addEventListener('input', validatePasswords);
        });
    </script>
</x-guest-layout>
