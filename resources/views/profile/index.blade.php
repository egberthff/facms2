<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Member Profile') }}
        </h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6">

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

            <!-- Action Block (e.g., Link to Password Change or Edit) -->
            <div class="flex items-center gap-3 self-start md:self-center">
                <span class="text-xs font-mono font-bold text-slate-400 uppercase tracking-wider bg-slate-50 px-2.5 py-1.5 rounded border border-slate-100">
                    ID: {{ Str::limit($member->id, 8, '') }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- 2. Personal Information Block -->
            <div class="space-y-6 rounded-xl border border-gray-200 bg-white p-5 shadow-xs sm:p-6 lg:col-span-2">
                <div>
                    <h3 class="mb-4 border-b border-slate-100 pb-2 text-sm font-semibold uppercase tracking-wide text-slate-800">{{ __('Personal Information') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">
                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Gender') }}</span>
                            <span class="text-sm font-medium text-slate-800 mt-1 block">{{ $member->gender ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Birth Date') }}</span>
                            <span class="text-sm font-medium text-slate-800 mt-1 block">{{ $member->birth_date?->format('F d, Y') ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Contact Number') }}</span>
                            <span class="text-sm font-medium text-slate-800 mt-1 block">{{ $member->contact_number ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Associated User Account') }}</span>
                            <span class="text-sm font-medium text-slate-800 mt-1 block">{{ $member->user?->email ?? 'No login account linked' }}</span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Home Address') }}</span>
                            <span class="text-sm font-medium text-slate-800 mt-1 block leading-relaxed">{{ $member->address ?? '—' }}</span>
                        </div>
                    </div>
                </div>

                <!-- 3. Deployment / Corporate Details -->
                <div>
                    <h3 class="mb-4 border-b border-slate-100 pb-2 text-sm font-semibold uppercase tracking-wide text-slate-800">{{ __('Employment & Campus Identity') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">
                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Employee Number') }}</span>
                            <span class="text-sm font-mono font-semibold text-slate-800 mt-1 block">{{ $member->employee_number ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Campus Designation') }}</span>
                            <span class="text-sm font-medium text-slate-800 mt-1 block">{{ $member->campus?->acronym ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Employment Status') }}</span>
                            <span class="text-sm font-medium text-slate-800 mt-1 block">{{ $member->employment_status ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Membership Effective Date') }}</span>
                            <span class="text-sm font-medium text-slate-800 mt-1 block">
                                @if($member->membership_status === 'Active')
                                    {{ $member->active_member_on?->format('M d, Y') ?? '—' }}
                                @else
                                    {{ $member->inactive_member_on?->format('M d, Y') ?? '—' }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Sidebar: Financial/Payout Metadata -->
            <div class="space-y-6">
                <!-- GCash Payout Setup -->
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs sm:p-6">
                    <h3 class="mb-4 border-b border-slate-100 pb-2 text-sm font-semibold uppercase tracking-wide text-slate-800">{{ __('GCash Payout Details') }}</h3>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3 rounded-lg border border-blue-100 bg-blue-50/50 p-3.5">
                            <div class="mt-0.5 text-blue-600 font-bold text-lg">📱</div>
                            <div>
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('GCash Registered Number') }}</span>
                                <span class="text-sm font-mono font-bold text-slate-800 block mt-0.5">{{ $member->gcash_number ?? 'Not provided' }}</span>
                            </div>
                        </div>

                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Account Name Verification') }}</span>
                            <span class="text-sm font-medium text-slate-800 mt-1 block uppercase tracking-wide text-xs bg-slate-50 p-2.5 rounded border border-slate-100">
                                {{ $member->gcash_account_name ?? '—' }}
                            </span>
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
    </div>
</x-app-layout>
