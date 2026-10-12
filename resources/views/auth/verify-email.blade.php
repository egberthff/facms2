<x-guest-layout>
    <!-- Section Header -->
    <div class="mb-6">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-50 text-blue-600 mb-4">
            <!-- Simple Envelope Icon -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://w3.org">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight mb-2">Verify Your Email Address</h2>
        <p class="text-sm text-slate-600 leading-relaxed">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </p>
    </div>

    <!-- Session Status Indicator -->
    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-md text-sm font-semibold text-emerald-800">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <!-- Form Action Controls -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
            @csrf
            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-[#1e293b] hover:bg-slate-800 text-white text-sm font-semibold rounded-md shadow transition-colors ease-in-out duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500">
                {{ __('Resend Verification Email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto text-center sm:text-right">
            @csrf
            <button type="submit" class="text-sm font-semibold text-slate-600 hover:text-[#1e293b] underline transition-colors focus:outline-none rounded-md">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
