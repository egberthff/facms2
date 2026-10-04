@php
    $isCycleStartYear = $setting->setting_key === 'claim_cycle_start_year';
    $isCycleYears = $setting->setting_key === 'claim_cycle_years';
    $isCycleMonth = $setting->setting_key === 'claim_cycle_start_month';
    $isAssociationFund = $setting->setting_key === 'association_fund';
    $inputType = $isCycleMonth ? 'text' : 'number';
@endphp

<div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 transition hover:border-blue-200 hover:bg-blue-50/20 sm:p-5">
    <div class="mb-4 flex items-start justify-between gap-4">
        <div>
            <h4 class="text-sm font-bold text-slate-900">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $setting->setting_key)) }}</h4>
            <p class="mt-1 text-xs leading-5 text-slate-500">{{ $setting->description }}</p>
        </div>
        <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-500 shadow-sm ring-1 ring-slate-200">{{ optional($setting->campus)->acronym ?? 'Campus' }}</span>
    </div>

    <form method="POST" action="{{ route('settings.update', $setting) }}" class="setting-ajax-form space-y-3" data-can-edit="{{ $canEdit ? 'true' : 'false' }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="campus_filter" value="{{ $selectedCampusId ?? '' }}">

        <label for="setting-{{ $setting->id }}" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $isAssociationFund ? 'Additional cash-in' : 'Value' }}</label>
        <div class="flex gap-2">
            @if($isCycleMonth)
                <select id="setting-{{ $setting->id }}" name="setting_value" required @disabled(!$canEdit) class="border block min-w-0 flex-1 rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-slate-100">
                    @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}" @selected((int) old('setting_value', $setting->setting_value) === $month)>{{ \Carbon\Carbon::create()->month($month)->format('F') }}</option>
                    @endforeach
                </select>
            @else
                <input
                    id="setting-{{ $setting->id }}"
                    name="setting_value"
                    type="{{ $inputType }}"
                    value="{{ old('setting_value', $setting->setting_value) }}"
                    @if($isCycleStartYear) min="1900" max="2200" @endif
                    @if($isCycleYears) min="1" max="20" @endif
                    @if($isAssociationFund) min="0.01" step="0.01" @endif
                    required
                    @disabled(!$canEdit)
                    class="border block min-w-0 flex-1 rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-slate-100"
                />
            @endif
            <button type="submit" @disabled(!$canEdit) class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-300">{{ $canEdit ? 'Save' : 'Select campus' }}</button>
        </div>
        @if($isCycleStartYear)
            <p class="text-xs text-slate-400">Initial year of the claims cycle. The end year and month are calculated automatically.</p>
        @elseif($isCycleYears)
            <p class="text-xs text-slate-400">Claims cycle duration, from 1 to 20 years.</p>
        @elseif($isCycleMonth)
            <p class="text-xs text-slate-400">Claims cycle starts on the first day of this month.</p>
        @elseif($isAssociationFund)
            <p class="text-xs text-slate-400">Enter the additional fund amount to add to the treasury.</p>
        @endif
    </form>
</div>
