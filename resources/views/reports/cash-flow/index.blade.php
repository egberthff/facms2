<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Campus Cash Flow</h1>
                <p class="text-sm text-gray-500">Funds received from the FFA, campus disbursements and cash on hand per period. Each finalized period's cash on hand carries over as the next period's forwarded balance.</p>
            </div>
            <a href="{{ route('reports.index') }}" class="shrink-0 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700">Back to Reports</a>
        </div>

        @if(session('success'))
            <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
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
                    <label for="campus_filter" class="block text-sm font-medium text-gray-700">Campus</label>
                    <select id="campus_filter" name="campus_filter" onchange="this.form.submit()" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                        <option value="">All campuses</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected($selectedCampusId === $campus->id)>{{ $campus->campus_name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        @endif

        @if($canManage)
            <form method="POST" action="{{ route('reports.cash-flow.store') }}" class="bg-white rounded-xl border border-gray-200 p-4 space-y-4">
                @csrf
                <input type="hidden" name="campus_id" value="{{ $selectedCampusId }}">
                <h2 class="text-lg font-semibold text-gray-900">New period</h2>

                <div class="grid gap-4 md:grid-cols-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">From</label>
                        <input type="date" name="period_from" value="{{ old('period_from', $suggestedFrom) }}" class="mt-1 w-full rounded-lg border-gray-300" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">As of</label>
                        <input type="date" name="period_to" value="{{ old('period_to', now()->toDateString()) }}" class="mt-1 w-full rounded-lg border-gray-300" required>
                    </div>
                    @if($needsOpeningBalance)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Opening (forwarded) balance</label>
                            <input type="number" name="opening_balance" step="0.01" value="{{ old('opening_balance') }}" class="mt-1 w-full rounded-lg border-gray-300" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Balance as of</label>
                            <input type="text" name="opening_label" value="{{ old('opening_label') }}" placeholder="December 2025" class="mt-1 w-full rounded-lg border-gray-300">
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
                    <button type="submit" class="shrink-0 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">Create draft</button>
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
                                        <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Finalized</span>
                                    @else
                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Draft</span>
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
</x-app-layout>
