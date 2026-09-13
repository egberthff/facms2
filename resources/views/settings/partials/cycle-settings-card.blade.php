@php
    $startYearSetting = $cycleSettings->firstWhere('setting_key', 'claim_cycle_start_year');
    $startMonthSetting = $cycleSettings->firstWhere('setting_key', 'claim_cycle_start_month');
    $yearsSetting = $cycleSettings->firstWhere('setting_key', 'claim_cycle_years');
    $cycleLocked = $cycleSettings->contains(fn ($setting) => \App\Models\ClaimsCycle::where('campus_id', $setting->campus_id)->exists());
    $cycleSetting = $startYearSetting ?? $startMonthSetting ?? $yearsSetting;
    $previewStartYear = (int) old('cycle_start_year', optional($startYearSetting)->setting_value);
    $previewStartMonth = (int) old('cycle_start_month', optional($startMonthSetting)->setting_value);
    $previewYears = (int) old('cycle_years', optional($yearsSetting)->setting_value);
    $previewEnd = $previewStartYear && $previewStartMonth && $previewYears
        ? \Carbon\Carbon::create($previewStartYear, $previewStartMonth, 1)->addYears($previewYears)->subDay()
        : null;
@endphp

@if($cycleSetting)
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50/60 p-5 transition hover:border-blue-200 hover:bg-blue-50/20">
        <div class="mb-5 flex items-start justify-between gap-4">
            <div>
                <h4 class="text-sm font-bold text-slate-900">Claims cycle configuration</h4>
                <p class="mt-1 text-xs leading-5 text-slate-500">Set the cycle start year, start month, and duration together. The end month and year are calculated automatically.</p>
            </div>
            <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-500 shadow-sm ring-1 ring-slate-200">{{ optional($cycleSetting->campus)->acronym ?? 'Campus' }}</span>
        </div>

        <form method="POST" action="{{ route('settings.update', $cycleSetting) }}" class="space-y-4" onsubmit="return {{ $canEdit && !$cycleLocked ? 'confirm(\'Save this cycle configuration?\')' : 'false' }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="campus_filter" value="{{ $selectedCampusId ?? '' }}">

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="cycle-start-year" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Start year</label>
                    <input id="cycle-start-year" name="cycle_start_year" type="number" min="1900" max="2200" value="{{ old('cycle_start_year', optional($startYearSetting)->setting_value) }}" required @disabled(!$canEdit || $cycleLocked) class="block w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-slate-100">
                </div>
                <div>
                    <label for="cycle-start-month" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Start month</label>
                    <select id="cycle-start-month" name="cycle_start_month" required @disabled(!$canEdit || $cycleLocked) class="block w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-slate-100">
                        @foreach(range(1, 12) as $month)
                            <option value="{{ $month }}" @selected((int) old('cycle_start_month', optional($startMonthSetting)->setting_value) === $month)>{{ \Carbon\Carbon::create()->month($month)->format('F') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="cycle-years" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Number of years</label>
                    <input id="cycle-years" name="cycle_years" type="number" min="1" max="20" value="{{ old('cycle_years', optional($yearsSetting)->setting_value) }}" required @disabled(!$canEdit || $cycleLocked) class="block w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-slate-100">
                </div>
            </div>

            <div class="rounded-lg border border-blue-100 bg-blue-50/60 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Calculated end</p>
                <p id="cycle-end-preview" class="mt-1 text-sm font-semibold text-blue-950">{{ $previewEnd?->format('F Y') ?? 'Enter all cycle values' }}</p>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-400">
                    @if($cycleLocked)
                        This configuration is locked because a claims cycle has already been initialized.
                    @else
                        The end date is calculated as the day before the next cycle begins.
                    @endif
                </p>
                <button type="submit" @disabled(!$canEdit || $cycleLocked) class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-300">{{ $cycleLocked ? 'Configuration locked' : ($canEdit ? 'Save cycle configuration' : 'Select campus') }}</button>
            </div>
        </form>
    </section>
@endif

<script>
    (() => {
        const yearInput = document.getElementById('cycle-start-year');
        const monthInput = document.getElementById('cycle-start-month');
        const yearsInput = document.getElementById('cycle-years');
        const preview = document.getElementById('cycle-end-preview');

        if (!yearInput || !monthInput || !yearsInput || !preview) {
            return;
        }

        const updatePreview = () => {
            const year = Number(yearInput.value);
            const month = Number(monthInput.value);
            const years = Number(yearsInput.value);

            if (!year || !month || !years) {
                preview.textContent = 'Enter all cycle values';
                return;
            }

            const endDate = new Date(year + years, month - 1, 0);
            preview.textContent = endDate.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
        };

        [yearInput, monthInput, yearsInput].forEach((input) => input.addEventListener('input', updatePreview));
    })();
</script>
