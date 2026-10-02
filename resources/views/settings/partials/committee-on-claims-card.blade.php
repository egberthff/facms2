@php
    $committeeLabels = [
        'claims_committee_chair' => 'Chair, Committee on Claims',
        'claims_committee_member_1' => 'Member, Committee on Claims',
        'claims_committee_member_2' => 'Member, Committee on Claims',
    ];
@endphp

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-6 py-5">
        <h3 class="text-lg font-bold text-slate-950">Committee on Claims</h3>
        <p class="mt-1 text-sm text-slate-500">Search this campus's members to assign the committee. The assigned names print as signatories on the claim forms (Forms 1 &amp; 2).</p>
    </div>

    <div class="grid gap-4 p-6 md:grid-cols-2 lg:grid-cols-3">
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
                    },

                    clear() {
                        this.selectedId = '';
                        this.selectedName = '';
                        this.selectedMeta = '';
                    }
                }"
                class="rounded-xl border border-slate-200 bg-slate-50/60 p-5 transition hover:border-blue-200 hover:bg-blue-50/20"
            >
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">{{ $committeeLabels[$setting->setting_key] ?? $setting->setting_key }}</h4>
                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ $setting->setting_key === 'claims_committee_chair' ? 'Signs Forms 1 & 2.' : 'Signs Form 2.' }}</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-500 shadow-sm ring-1 ring-slate-200">{{ optional($setting->campus)->acronym ?? 'Campus' }}</span>
                </div>

                <form method="POST" action="{{ route('settings.update', $setting) }}" class="setting-ajax-form space-y-3" data-can-edit="{{ $canEdit ? 'true' : 'false' }}" data-confirm-text="Save this Committee on Claims assignment?">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="campus_filter" value="{{ $selectedCampusId ?? '' }}">
                    <input type="hidden" name="setting_value" :value="selectedId">

                    <!-- Assigned member -->
                    <div class="flex min-h-[3.25rem] items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2">
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

                    <!-- Member search -->
                    <div class="relative" @click.outside="open = false">
                        <label for="committee-search-{{ $setting->id }}" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search member</label>
                        <input
                            id="committee-search-{{ $setting->id }}"
                            type="text"
                            x-model="query"
                            @input.debounce.300ms="search()"
                            @keydown.escape="open = false"
                            @disabled(!$canEdit)
                            autocomplete="off"
                            placeholder="Type name or employee ID..."
                            class="block w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-slate-100"
                        />

                        <div x-show="open" x-transition x-cloak class="absolute left-0 right-0 top-full z-20 mt-1 max-h-60 overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg">
                            <template x-if="loading">
                                <p class="px-3 py-2.5 text-sm text-slate-400">Searching...</p>
                            </template>
                            <template x-if="!loading && results.length === 0">
                                <p class="px-3 py-2.5 text-sm text-slate-400">No members found on this campus.</p>
                            </template>
                            <template x-for="member in results" :key="member.id">
                                <button type="button" @click="choose(member)" class="block w-full px-3 py-2 text-left transition hover:bg-blue-50">
                                    <span class="block text-sm font-medium text-slate-900" x-text="member.name"></span>
                                    <span class="block text-xs text-slate-500" x-text="[member.employee_number, member.position].filter(Boolean).join(' · ')"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <button type="submit" @disabled(!$canEdit) class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-300">{{ $canEdit ? 'Save' : 'Select campus' }}</button>
                </form>
            </div>
        @endforeach
    </div>
</section>
