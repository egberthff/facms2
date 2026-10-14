{{-- Member-picker card for settings that store a campus member id. Defaults to the Committee on Claims;
     pass cardTitle / cardDescription / slotLabels / slotHints to reuse it (e.g. report signatories). --}}
@php
    $committeeLabels = $slotLabels ?? [
        'claims_committee_chair' => 'Chair, Committee on Claims',
        'claims_committee_member_1' => 'Member, Committee on Claims',
        'claims_committee_member_2' => 'Member, Committee on Claims',
    ];
    $committeeHints = $slotHints ?? [
        'claims_committee_chair' => 'Signs Forms 1 & 2.',
        'claims_committee_member_1' => 'Signs Form 2.',
        'claims_committee_member_2' => 'Signs Form 2.',
    ];
    $cardTitle = $cardTitle ?? 'Committee on Claims';
    $cardDescription = $cardDescription ?? "Search this campus's members to assign the committee. Assigned names print as signatories on claim Forms 1 & 2.";
@endphp

<x-settings-section :title="$cardTitle" :description="$cardDescription" :clip="false">
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        @foreach($committeeSettings as $setting)
            @php
                $assigned = $committeeMembers->get($setting->setting_value);
                $assignedName = $assigned ? \Illuminate\Support\Str::squish($assigned->full_name) : '';
            @endphp

            <div
                x-data="{
                    query: '',
                    results: [],
                    open: false,
                    loading: false,
                    selectedId: @js($assigned?->id ?? ''),
                    selectedName: @js($assignedName),
                    selectedMeta: @js($assigned ? trim(($assigned->employee_number ?? '') . ' · ' . ($assigned->position ?? ''), ' ·') : ''),

                    async search() {
                        const term = this.query.trim();
                        if (term.length < 2) {
                            this.results = [];
                            this.open = false;
                            return;
                        }

                        this.loading = true;
                        const params = new URLSearchParams({ campus_id: @js($setting->campus_id), search: term });

                        try {
                            const response = await fetch(`{{ route('settings.committee-members.search') }}?${params}`, {
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                            });
                            this.results = response.ok ? await response.json() : [];
                        } catch (e) {
                            this.results = [];
                        } finally {
                            this.loading = false;
                            this.open = true;
                        }
                    },

                    choose(member) {
                        this.selectedId = member.id;
                        this.selectedName = member.name;
                        this.selectedMeta = [member.employee_number, member.position].filter(Boolean).join(' · ');
                        this.query = '';
                        this.results = [];
                        this.open = false;
                        this.clearError();
                    },

                    clear() {
                        this.selectedId = '';
                        this.selectedName = '';
                        this.selectedMeta = '';
                        this.clearError();
                    },

                    // Picking or removing a member clears the server error shown under the card
                    clearError() {
                        const message = this.$root.querySelector('[data-error-for=setting_value]');
                        if (message) {
                            message.textContent = '';
                            message.classList.add('hidden');
                        }
                    }
                }"
                class="flex flex-col rounded-lg border border-slate-200 bg-slate-50/60 p-3.5 transition hover:border-blue-200"
            >
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h4 class="text-sm font-semibold text-slate-900">{{ $committeeLabels[$setting->setting_key] ?? $setting->setting_key }}</h4>
                        <p class="mt-0.5 text-xs leading-5 text-slate-500">{{ $committeeHints[$setting->setting_key] ?? '' }}</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-500 ring-1 ring-slate-200">{{ optional($setting->campus)->acronym ?? 'Campus' }}</span>
                </div>

                <form method="POST" action="{{ route('settings.update', $setting) }}" class="setting-ajax-form mt-auto space-y-2" data-can-edit="{{ $canEdit ? 'true' : 'false' }}" data-confirm-text="Save this {{ $cardTitle }} assignment?" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="campus_filter" value="{{ $selectedCampusId ?? '' }}">
                    <input type="hidden" name="setting_value" :value="selectedId">

                    <!-- Assigned member -->
                    <div class="flex min-h-11 items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-3 py-1.5">
                        <template x-if="selectedId">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900" x-text="selectedName"></p>
                                <p class="truncate text-xs text-slate-500" x-text="selectedMeta"></p>
                            </div>
                        </template>
                        <template x-if="!selectedId">
                            <p class="text-sm italic text-slate-400">No member assigned</p>
                        </template>
                        <button type="button" x-show="selectedId" @click="clear()" @disabled(!$canEdit) title="Remove assignment" class="shrink-0 rounded-md p-1 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600">
                            <x-heroicon-o-x-mark class="size-4" />
                        </button>
                    </div>
                    <p data-error-for="setting_value" class="mt-1.5 text-xs text-red-600 hidden"></p>

                    <!-- Member search + save -->
                    <label for="committee-search-{{ $setting->id }}" class="sr-only">Search member</label>
                    <div class="flex gap-2">
                        <div class="relative min-w-0 flex-1" @click.outside="open = false">
                            <input
                                id="committee-search-{{ $setting->id }}"
                                type="text"
                                x-model="query"
                                @input.debounce.300ms="search()"
                                @keydown.escape="open = false"
                                @disabled(!$canEdit)
                                autocomplete="off"
                                placeholder="Search name or employee ID..."
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-blue-500 focus:ring-blue-500 disabled:bg-slate-100"
                            />

                            <div x-show="open" x-transition x-cloak class="absolute left-0 right-0 top-full z-30 mt-1 max-h-60 overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg">
                                <template x-if="loading">
                                    <p class="px-3 py-2 text-sm text-slate-400">Searching...</p>
                                </template>
                                <template x-if="!loading && results.length === 0">
                                    <p class="px-3 py-2 text-sm text-slate-400">No members found on this campus.</p>
                                </template>
                                <template x-for="member in results" :key="member.id">
                                    <button type="button" @click="choose(member)" class="block w-full px-3 py-2 text-left transition hover:bg-blue-50">
                                        <span class="block text-sm font-medium text-slate-900" x-text="member.name"></span>
                                        <span class="block text-xs text-slate-500" x-text="[member.employee_number, member.position].filter(Boolean).join(' · ')"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <button type="submit" @disabled(!$canEdit) class="shrink-0 rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-300">{{ $canEdit ? 'Save' : 'Select campus' }}</button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</x-settings-section>
