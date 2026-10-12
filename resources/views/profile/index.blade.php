<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Member Profile') }}
        </h2>
    </x-slot>

    @php
        $inputClass = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20';
        $labelClass = 'block text-xs font-bold text-slate-500 uppercase tracking-wider';
        $valueClass = 'text-sm font-medium text-slate-800 mt-1 block';
    @endphp

    {{-- Reopen the form after a failed save so the errors are visible --}}
    <div class="mx-auto max-w-7xl space-y-6" x-data="{ editing: {{ $errors->any() ? 'true' : 'false' }} }">

        <!-- 1. Hero Overview Card -->
        <div class="flex flex-col gap-4 overflow-hidden rounded-xl border border-gray-200 bg-white p-5 shadow-xs sm:p-6 md:flex-row md:items-center md:justify-between">
            <div class="flex min-w-0 items-center gap-4">
                <!-- Avatar Placeholder with Initial -->
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-slate-800 text-xl font-bold uppercase tracking-wider text-white shadow-inner sm:h-16 sm:w-16 sm:text-2xl">
                    {{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">{{ $member->full_name }}</h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $member->membership_status === 'Active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                            {{ $member->membership_status }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-500 font-medium mt-0.5">
                        {{ $member->position }} &bull; <span class="text-slate-700">{{ $member->department }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 self-start md:self-center">
                <span class="text-xs font-mono font-bold text-slate-400 uppercase tracking-wider bg-slate-50 px-2.5 py-1.5 rounded border border-slate-100">
                    ID: {{ Str::limit($member->id, 8, '') }}
                </span>
                <button type="button" x-show="!editing" @click="editing = true"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-blue-700">
                    <x-heroicon-o-pencil-square class="h-4 w-4"/>
                    Edit Profile
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('members.profile.update') }}" x-ref="profileForm">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- 2. Personal Information Block -->
                <div class="space-y-6 rounded-xl border border-gray-200 bg-white p-5 shadow-xs sm:p-6 lg:col-span-2">
                    <div>
                        <h3 class="mb-4 border-b border-slate-100 pb-2 text-sm font-semibold uppercase tracking-wide text-slate-800">{{ __('Personal Information') }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">
                            <div>
                                <label for="gender" class="{{ $labelClass }}">{{ __('Gender') }}</label>
                                <span x-show="!editing" class="{{ $valueClass }}">{{ $member->gender ?? '—' }}</span>
                                <div x-show="editing" x-cloak>
                                    <select id="gender" name="gender" class="{{ $inputClass }}">
                                        @foreach(['Male', 'Female'] as $gender)
                                            <option value="{{ $gender }}" @selected(old('gender', $member->gender) === $gender)>{{ $gender }}</option>
                                        @endforeach
                                    </select>
                                    @error('gender') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label for="birth_date" class="{{ $labelClass }}">{{ __('Birth Date') }}</label>
                                <span x-show="!editing" class="{{ $valueClass }}">{{ $member->birth_date?->format('F d, Y') ?? '—' }}</span>
                                <div x-show="editing" x-cloak>
                                    <input id="birth_date" type="date" name="birth_date" max="{{ now()->subDay()->toDateString() }}"
                                        value="{{ old('birth_date', $member->birth_date?->format('Y-m-d')) }}" class="{{ $inputClass }}">
                                    @error('birth_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label for="contact_number" class="{{ $labelClass }}">{{ __('Contact Number') }}</label>
                                <span x-show="!editing" class="{{ $valueClass }}">{{ $member->contact_number ?? '—' }}</span>
                                <div x-show="editing" x-cloak>
                                    <input id="contact_number" type="tel" name="contact_number" maxlength="20" required
                                        value="{{ old('contact_number', $member->contact_number) }}" class="{{ $inputClass }}">
                                    @error('contact_number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <span class="{{ $labelClass }}">{{ __('Associated User Account') }}</span>
                                <span class="{{ $valueClass }}">{{ $member->user?->email ?? 'No login account linked' }}</span>
                                <span x-show="editing" x-cloak class="mt-0.5 block text-xs text-slate-400">Managed by your campus officers</span>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="address" class="{{ $labelClass }}">{{ __('Home Address') }}</label>
                                <span x-show="!editing" class="{{ $valueClass }} leading-relaxed">{{ $member->address ?? '—' }}</span>
                                <div x-show="editing" x-cloak>
                                    <textarea id="address" name="address" rows="2" required class="{{ $inputClass }}">{{ old('address', $member->address) }}</textarea>
                                    @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Deployment / Corporate Details (read-only for members) -->
                    <div>
                        <h3 class="mb-4 border-b border-slate-100 pb-2 text-sm font-semibold uppercase tracking-wide text-slate-800">{{ __('Employment & Campus Identity') }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">
                            <div>
                                <span class="{{ $labelClass }}">{{ __('Employee Number') }}</span>
                                <span class="text-sm font-mono font-semibold text-slate-800 mt-1 block">{{ $member->employee_number ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="{{ $labelClass }}">{{ __('Campus Designation') }}</span>
                                <span class="{{ $valueClass }}">{{ $member->campus?->acronym ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="{{ $labelClass }}">{{ __('Employment Status') }}</span>
                                <span class="{{ $valueClass }}">{{ $member->employment_status ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="{{ $labelClass }}">{{ __('Membership Effective Date') }}</span>
                                <span class="{{ $valueClass }}">
                                    @if($member->membership_status === 'Active')
                                        {{ $member->active_member_on?->format('M d, Y') ?? '—' }}
                                    @else
                                        {{ $member->inactive_member_on?->format('M d, Y') ?? '—' }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        <p class="mt-4 text-xs text-slate-400">Contact your campus officers to change your name, email, or employment details.</p>
                    </div>
                </div>

                <!-- 4. Sidebar: Financial/Payout Metadata -->
                <div class="space-y-6">
                    <!-- GCash Payout Setup -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs sm:p-6">
                        <h3 class="mb-4 border-b border-slate-100 pb-2 text-sm font-semibold uppercase tracking-wide text-slate-800">{{ __('GCash Payout Details') }}</h3>
                        <div class="space-y-4">
                            <div x-show="!editing" class="flex items-start gap-3 rounded-lg border border-blue-100 bg-blue-50/50 p-3.5">
                                <div class="mt-0.5 text-blue-600 font-bold text-lg">📱</div>
                                <div>
                                    <span class="{{ $labelClass }}">{{ __('GCash Registered Number') }}</span>
                                    <span class="text-sm font-mono font-bold text-slate-800 block mt-0.5">{{ $member->gcash_number ?: 'Not provided' }}</span>
                                </div>
                            </div>
                            <div x-show="editing" x-cloak>
                                <label for="gcash_number" class="{{ $labelClass }}">{{ __('GCash Registered Number') }}</label>
                                <input id="gcash_number" type="tel" name="gcash_number" inputmode="numeric" maxlength="11" placeholder="09XXXXXXXXX"
                                    value="{{ old('gcash_number', $member->gcash_number) }}" class="{{ $inputClass }} font-mono">
                                @error('gcash_number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="gcash_account_name" class="{{ $labelClass }}">{{ __('Account Name Verification') }}</label>
                                <span x-show="!editing" class="font-medium text-slate-800 mt-1 block uppercase tracking-wide text-xs bg-slate-50 p-2.5 rounded border border-slate-100">
                                    {{ $member->gcash_account_name ?: '—' }}
                                </span>
                                <div x-show="editing" x-cloak>
                                    <input id="gcash_account_name" type="text" name="gcash_account_name" maxlength="255" placeholder="Name registered on GCash"
                                        value="{{ old('gcash_account_name', $member->gcash_account_name) }}" class="{{ $inputClass }}">
                                    @error('gcash_account_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Metrics Summary Panel -->
                    <div class="space-y-4 rounded-xl bg-[#1e293b] p-5 text-slate-200 shadow-xs sm:p-6">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider border-b border-slate-700 pb-2">{{ __('Activity Logs Summaries') }}</h3>
                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div class="rounded-lg border border-slate-700/50 bg-slate-800/50 p-3">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Claims Filed') }}</span>
                                <span class="mt-1 block text-xl font-bold text-white tabular-nums">{{ $member->claims->count() }}</span>
                            </div>
                            <div class="rounded-lg border border-slate-700/50 bg-slate-800/50 p-3">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Contributions') }}</span>
                                <span class="mt-1 block text-xl font-bold text-white tabular-nums">{{ $member->contributions->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save bar, only while editing -->
            <div x-show="editing" x-cloak class="mt-6 flex flex-col-reverse gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-xs sm:flex-row sm:items-center sm:justify-end">
                <button type="button" @click="$refs.profileForm.reset(); editing = false"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-xs transition hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-blue-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
