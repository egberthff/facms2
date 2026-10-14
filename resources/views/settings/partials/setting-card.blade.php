@php
    $isCycleStartYear = $setting->setting_key === 'claim_cycle_start_year';
    $isCycleYears = $setting->setting_key === 'claim_cycle_years';
    $isCycleMonth = $setting->setting_key === 'claim_cycle_start_month';
    $isAssociationFund = $setting->setting_key === 'association_fund';
    $isAmount = in_array($setting->setting_key, \App\Http\Controllers\SettingController::AMOUNT_KEYS, true);
    $settingLabel = \Illuminate\Support\Str::lower(str_replace('_', ' ', $setting->setting_key));
    $fieldLabel = $isAssociationFund ? 'additional cash-in' : $settingLabel;
    $settingValue = old('setting_value', $setting->setting_value);
    $inputClass = 'block w-full min-w-0 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-blue-500 focus:ring-blue-500 disabled:bg-slate-100';
@endphp

<div class="flex flex-col rounded-lg border border-slate-200 bg-slate-50/60 p-3.5 transition hover:border-blue-200">
    <div class="mb-3 flex items-start justify-between gap-3">
        <div class="min-w-0">
            <h4 class="text-sm font-semibold text-slate-900">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $setting->setting_key)) }}</h4>
            <p class="mt-0.5 text-xs leading-5 text-slate-500">{{ $setting->description }}</p>
        </div>
        <span class="shrink-0 rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-500 ring-1 ring-slate-200">{{ optional($setting->campus)->acronym ?? 'Campus' }}</span>
    </div>

    <form method="POST" action="{{ route('settings.update', $setting) }}" class="setting-ajax-form mt-auto space-y-1.5" data-can-edit="{{ $canEdit ? 'true' : 'false' }}" novalidate>
        @csrf
        @method('PUT')
        <input type="hidden" name="campus_filter" value="{{ $selectedCampusId ?? '' }}">

        <label for="setting-{{ $setting->id }}" class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $isAssociationFund ? 'Additional cash-in' : 'Value' }}</label>
        <div class="flex gap-2">
            @if($isCycleMonth)
                <select id="setting-{{ $setting->id }}" name="setting_value" required data-label="start month" @disabled(!$canEdit) class="{{ $inputClass }} flex-1">
                    @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}" @selected((int) $settingValue === $month)>{{ \Carbon\Carbon::create()->month($month)->format('F') }}</option>
                    @endforeach
                </select>
            @elseif($isAmount)
                <div class="relative min-w-0 flex-1">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500">₱</span>
                    <input
                        id="setting-{{ $setting->id }}"
                        name="setting_value"
                        type="text"
                        inputmode="decimal"
                        autocomplete="off"
                        placeholder="0.00"
                        data-amount-format
                        data-label="{{ $fieldLabel }}"
                        @if($isAssociationFund) data-positive @endif
                        value="{{ is_numeric($settingValue) ? number_format((float) $settingValue, 2) : $settingValue }}"
                        required
                        @disabled(!$canEdit)
                        class="{{ $inputClass }} pl-7 tabular-nums"
                    />
                </div>
            @else
                <input
                    id="setting-{{ $setting->id }}"
                    name="setting_value"
                    @if($isCycleStartYear || $isCycleYears)
                        type="text"
                        inputmode="numeric"
                        data-integer
                        @if($isCycleStartYear) data-min="1900" data-max="2200" maxlength="4" @endif
                        @if($isCycleYears) data-min="1" data-max="20" maxlength="2" @endif
                    @else
                        type="text"
                    @endif
                    data-label="{{ $fieldLabel }}"
                    value="{{ $settingValue }}"
                    required
                    @disabled(!$canEdit)
                    class="{{ $inputClass }} flex-1"
                />
            @endif
            <button type="submit" @disabled(!$canEdit) class="shrink-0 rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-300">{{ $canEdit ? 'Save' : 'Select campus' }}</button>
        </div>
        <p data-error-for="setting_value" class="mt-1.5 text-xs text-red-600 hidden"></p>
        @if($isCycleStartYear)
            <p class="text-xs text-slate-400">Initial year of the claims cycle. The end year and month are calculated automatically.</p>
        @elseif($isCycleYears)
            <p class="text-xs text-slate-400">Claims cycle duration, from 1 to 20 years.</p>
        @elseif($isCycleMonth)
            <p class="text-xs text-slate-400">Claims cycle starts on the first day of this month.</p>
        @elseif($isAssociationFund)
            <p class="text-xs text-slate-400">Amount to add to the treasury.</p>
        @endif
    </form>
</div>
