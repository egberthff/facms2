@php
    $startYearSetting = $cycleSettings->first(function ($setting) {
        return $setting->setting_key === 'claim_cycle_start_year'
             && $setting->cycle_label === null; 
    });


    $startMonthSetting = $cycleSettings->first(function ($setting) {
        return $setting->setting_key === 'claim_cycle_start_month'
             && $setting->cycle_label === null;
    });

    $yearsSetting = $cycleSettings->first(function ($setting) {
        return $setting->setting_key === 'claim_cycle_years'
             && $setting->cycle_label === null;
    });

    // 1. Map your settings into pairs of campus IDs and cycle labels
    $pairs = $cycleSettings->map(fn($setting) => [
        'campus_id' => $setting->campus_id,
        'cycle_label' => $setting->cycle_label
    ]); 
    // ->filter(fn($pair) => !is_null($pair['cycle_label']));

    // 2. Perform a single database query to see if any of those specific pairs exist
    $cycleLocked = \App\Models\ClaimsCycle::where(function ($query) use ($pairs) {
        foreach ($pairs as $pair) {
            $query->orWhere(function ($q) use ($pair) {
                $q->where('campus_id', $pair['campus_id'])
                  ->where('cycle_label', $pair['cycle_label']);
            });
        }
    })->exists();


    // $cycleLocked = $cycleSettings->contains(fn ($setting) => \App\Models\ClaimsCycle::where('campus_id', $setting->campus_id)->exists());
    $cycleSetting = $startYearSetting ?? $startMonthSetting ?? $yearsSetting;
    $cycleCampusId = optional($cycleSetting)->campus_id;
    $hiddenStartYear = filled(optional($startYearSetting)->setting_value)
        ? $startYearSetting->setting_value
        : \App\Models\SystemSetting::getCycleStartYear($cycleCampusId);
    $hiddenStartMonth = filled(optional($startMonthSetting)->setting_value)
        ? $startMonthSetting->setting_value
        : \App\Models\SystemSetting::getCycleStartMonth($cycleCampusId);
    $previewStartYear = (int) old('cycle_start_year', $hiddenStartYear);
    $previewStartMonth = (int) old('cycle_start_month', $hiddenStartMonth);
    $previewYears = (int) old('cycle_years', optional($yearsSetting)->setting_value);
    $previewEnd = $previewStartYear && $previewStartMonth && $previewYears
        ? \Carbon\Carbon::create($previewStartYear, $previewStartMonth, 1)->addYears($previewYears)->subDay()
        : null;
@endphp

@if($cycleSetting)
    <div class="flex flex-col rounded-lg border border-slate-200 bg-slate-50/60 p-3.5 transition hover:border-blue-200">
        <div class="mb-3 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h4 class="text-sm font-semibold text-slate-900">Claims Cycle Duration</h4>
                <p class="mt-0.5 text-xs leading-5 text-slate-500">Years per member claim cycle. Changes apply to new member cycles only.</p>
            </div>
            <span class="shrink-0 rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-500 ring-1 ring-slate-200">{{ optional($cycleSetting->campus)->acronym ?? 'Campus' }}</span>
        </div>

        <form method="POST" action="{{ route('settings.update', $cycleSetting) }}" class="cycle-config-form mt-auto space-y-1.5" onsubmit="saveCycleConfig(event, this)" novalidate>
            @csrf
            @method('PUT')
            <input type="hidden" name="campus_filter" value="{{ $selectedCampusId ?? '' }}">

            {{-- Start year and month are no longer edited here, but the update endpoint still requires them, so submit the stored values unchanged --}}
            <input id="cycle-start-year" name="cycle_start_year" type="hidden" value="{{ old('cycle_start_year', $hiddenStartYear) }}">
            <input id="cycle-start-month" name="cycle_start_month" type="hidden" value="{{ old('cycle_start_month', $hiddenStartMonth) }}">

            <label for="cycle-years" class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Number of years</label>
            <div class="flex gap-2">
                <input id="cycle-years" name="cycle_years" type="text" inputmode="numeric" data-integer data-min="1" data-max="20" maxlength="2" data-label="number of years" value="{{ old('cycle_years', optional($yearsSetting)->setting_value) }}" required @disabled(!$canEdit || $cycleLocked) class="block min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-blue-500 focus:ring-blue-500 disabled:bg-slate-100">
                <button type="submit" @disabled(!$canEdit || $cycleLocked) class="shrink-0 rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-300">{{ $cycleLocked ? 'Locked' : ($canEdit ? 'Save' : 'Select campus') }}</button>
            </div>
            <p data-error-for="cycle_years" class="mt-1.5 text-xs text-red-600 hidden"></p>
            <p class="text-xs text-slate-400">
                @if($cycleLocked)
                    Locked because a claims cycle has already been initialized.
                @else
                    Current cycle ends <span id="cycle-end-preview" class="font-semibold text-slate-600">{{ $previewEnd?->format('F Y') ?? 'Enter all cycle values' }}</span>.
                @endif
            </p>
        </form>
    </div>
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
