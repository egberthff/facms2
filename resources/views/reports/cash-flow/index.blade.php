<x-app-layout>
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-900 sm:text-2xl">Campus Cash Flow</h1>
                <p class="text-sm text-gray-500">Funds received from the FFA, campus disbursements and cash on hand per period. Each finalized period's cash on hand carries over as the next period's forwarded balance.</p>
            </div>
            <a href="{{ route('reports.index') }}" class="shrink-0 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-xs">Back to Reports</a>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($canViewAll)
            <form method="GET" action="{{ route('reports.cash-flow.index') }}" class="flex flex-wrap items-end gap-3 bg-white rounded-xl border border-gray-200 p-4">
                <div class="min-w-64">
                    <label for="campus_filter" class="mb-1.5 block text-sm font-medium text-gray-700">Campus</label>
                    <select id="campus_filter" name="campus_filter" onchange="this.form.submit()" class="border mt-1 block w-full rounded-lg border-gray-300 text-sm px-3 py-2">
                        <option value="">All campuses</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected($selectedCampusId === $campus->id)>{{ $campus->campus_name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        @endif

        @if($canManage)
            <form method="POST" action="{{ route('reports.cash-flow.store') }}" class="bg-white rounded-xl border border-gray-200 p-4 space-y-4" novalidate
                x-data="cashFlowForm({
                    validate: cashFlowRules.period,
                    confirm: (v, h) => ({
                        icon: 'question',
                        title: 'Create this draft?',
                        html: 'A cash flow draft will be created for <b>' + h.date(v.period_from) + ' – ' + h.date(v.period_to) + '</b>'
                            + ('opening_balance' in v ? ', starting with a forwarded balance of <b>P' + h.money(v.opening_balance) + '</b>'
                                + (v.opening_label.trim() ? ' as of <b>' + h.esc(v.opening_label.trim()) + '</b>' : '') : '')
                            + '.<br><br>Approved FFA transfers and released medical assistance in the range will be added automatically.',
                        confirmText: 'Yes, create draft',
                        loadingText: 'Creating the draft and pulling FFA transfers and medical releases...',
                    }),
                })"
                @submit.prevent="submit" @input="clearError($event.target.name)" @change="clearError($event.target.name)">
                @csrf
                <input type="hidden" name="campus_id" value="{{ $selectedCampusId }}">
                <h2 class="text-lg font-semibold text-gray-900">New period</h2>

                <div class="grid gap-4 md:grid-cols-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">From <span class="text-red-500">*</span></label>
                        <input type="date" name="period_from" value="{{ old('period_from', $suggestedFrom) }}" :class="{ 'error-border': errors.period_from }" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-xs">
                        <p x-show="errors.period_from" x-text="errors.period_from" x-cloak class="mt-1.5 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">As of <span class="text-red-500">*</span></label>
                        <input type="date" name="period_to" value="{{ old('period_to', now()->toDateString()) }}" :class="{ 'error-border': errors.period_to }" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-xs">
                        <p x-show="errors.period_to" x-text="errors.period_to" x-cloak class="mt-1.5 text-xs text-red-600"></p>
                    </div>
                    @if($needsOpeningBalance)
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Opening (forwarded) balance <span class="text-red-500">*</span></label>
                            <div class="relative mt-1">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">₱</span>
                                <input type="text" inputmode="decimal" name="opening_balance" data-amount value="{{ old('opening_balance') }}" placeholder="0.00"
                                    x-on:input="formatAmountInput($el, { negative: true })"
                                    :class="{ 'error-border': errors.opening_balance }" class="block w-full rounded-lg border border-gray-300 bg-white pl-7 pr-3 py-2 text-sm shadow-xs tabular-nums">
                            </div>
                            <p x-show="errors.opening_balance" x-text="errors.opening_balance" x-cloak class="mt-1.5 text-xs text-red-600"></p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Balance as of</label>
                            <input type="text" name="opening_label" value="{{ old('opening_label') }}" placeholder="December 2025" :class="{ 'error-border': errors.opening_label }" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-xs">
                            <p x-show="errors.opening_label" x-text="errors.opening_label" x-cloak class="mt-1.5 text-xs text-red-600"></p>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <p class="text-xs text-gray-500">
                        @if($needsOpeningBalance)
                            This is the campus's first cash flow period, so enter the cash on hand it starts with.
                        @else
                            The forwarded balance is taken from the last finalized period.
                        @endif
                        Approved FFA transfers and released medical assistance in the range are added automatically.
                    </p>
                    <button type="submit" class="shrink-0 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-xs transition hover:bg-blue-700">Create draft</button>
                </div>
            </form>
        @elseif($canViewAll && !$selectedCampusId)
            <div class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-5 text-center text-sm text-gray-500">Select a campus to start a new cash flow period.</div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs uppercase bg-gray-50 text-gray-700">
                        <tr>
                            @if($canViewAll && !$selectedCampusId)
                                <th class="px-4 py-3">Campus</th>
                            @endif
                            <th class="px-4 py-3">Period</th>
                            <th class="px-4 py-3 text-right">Forwarded</th>
                            <th class="px-4 py-3 text-right">Total funds</th>
                            <th class="px-4 py-3 text-right">Disbursements</th>
                            <th class="px-4 py-3 text-right">Cash on hand</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($periods as $period)
                            <tr>
                                @if($canViewAll && !$selectedCampusId)
                                    <td class="px-4 py-3">{{ $period->campus?->campus_name }}</td>
                                @endif
                                <td class="px-4 py-3 whitespace-nowrap">{{ $period->period_from->format('M d, Y') }} – {{ $period->period_to->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format((float) $period->forwarded_balance, 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format((float) $period->total_funds, 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format((float) $period->total_disbursements, 2) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ number_format((float) $period->cash_on_hand, 2) }}</td>
                                <td class="px-4 py-3">
                                    @if($period->isFinalized())
                                        <span class="rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Finalized</span>
                                    @else
                                        <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">Draft</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('reports.cash-flow.show', $period) }}" class="font-semibold text-blue-600 hover:text-blue-800">Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-gray-500">No cash flow periods yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('reports.cash-flow.partials.form-scripts')
</x-app-layout>
