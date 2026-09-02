<x-app-layout>
    <div class="min-h-screen bg-slate-50 p-4 text-slate-800 sm:p-6">
        <div class="mx-auto max-w-7xl space-y-6">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-5 border-b border-slate-200 px-6 py-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-blue-600">Administration</p>
                    <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-950">System settings</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Manage the live configuration used for member benefits, contributions, and claims cycles.</p>
                </div>
                <form method="GET" action="{{ route('settings.index') }}" class="w-full lg:w-72">
                    @if($isPresident)
                        <label for="campus_filter" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Campus scope</label>
                        <select id="campus_filter" name="campus_filter" onchange="this.form.submit()" class="block w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Campuses (Global View)</option>
                            @foreach($allCampuses as $campus)
                                <option value="{{ $campus->id }}" {{ ($selectedCampusId ?? '') == $campus->id ? 'selected' : '' }}>{{ $campus->acronym }}</option>
                            @endforeach
                        </select>
                    @endif
                </form>
            </div>

            @if($isPresident && $selectedCampusId)
                <div class="border-t border-slate-100 bg-slate-50 px-6 py-3">
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Editing live settings for <strong class="text-slate-900">{{ optional($allCampuses->firstWhere('id', $selectedCampusId))->acronym }}</strong>
                    </div>
                </div>
            @elseif($isPresident)
                <div class="border-t border-slate-100 bg-slate-50 px-6 py-3">
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        Global view. Select a campus to edit its settings.
                    </div>
                </div>
            @endif
        </div>

        @if(session('success'))
            <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                <span class="font-semibold">Saved.</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                <p class="font-semibold">Settings could not be saved.</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $cycleSettings = $settings->whereIn('setting_key', ['claim_cycle_years', 'claim_cycle_start_month']);
            $generalSettings = $settings;
            $canEdit = !$isPresident || filled($selectedCampusId);
        @endphp

        @if($settings->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-sm text-slate-500">No live settings are available for the selected campus.</div>
        @else
            @if($cyclesByCampus->isNotEmpty())
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-950">Claims cycles</h3>
                                <p class="mt-1 text-sm text-slate-500">The schedule below is generated from each campus's cycle duration and start month.</p>
                            </div>
                            <span class="text-xs font-medium text-slate-400">Current and previous 3 cycles</span>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($cyclesByCampus as $cycleGroup)
                            <div class="p-6">
                                @if(!$selectedCampusId)
                                    <h4 class="mb-3 text-sm font-bold text-slate-900">{{ $cycleGroup['campus']->acronym }}</h4>
                                @endif
                                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                    @foreach($cycleGroup['cycles'] as $cycle)
                                        <div class="rounded-xl border {{ $cycle['is_current'] ? 'border-blue-200 bg-blue-50/50' : 'border-slate-200 bg-slate-50/50' }} p-4">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="text-sm font-bold text-slate-900">{{ $cycle['label'] }}</p>
                                                @if($cycle['is_current'])
                                                    <span class="rounded-full bg-blue-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-blue-700">Current</span>
                                                @endif
                                            </div>
                                            <p class="mt-2 text-xs text-slate-500">{{ $cycle['start']->format('M d, Y') }} to {{ $cycle['end']->format('M d, Y') }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($generalSettings->isNotEmpty())
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h3 class="text-lg font-bold text-slate-950">Campus settings</h3>
                        <p class="mt-1 text-sm text-slate-500">Edit each live setting independently. Claims cycle values are used immediately by claims, dashboard balances, and reports.</p>
                    </div>
                    <div class="grid gap-4 p-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($generalSettings as $setting)
                            @include('settings.partials.setting-card', ['setting' => $setting, 'canEdit' => $canEdit])
                        @endforeach
                    </div>
                </section>
            @endif
        @endif
        </div>
    </div>
</x-app-layout>
