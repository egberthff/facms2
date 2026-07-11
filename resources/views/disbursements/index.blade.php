<x-app-layout>
<div class="p-6 bg-gray-50 min-h-screen text-gray-800 font-sans"
    x-data="{ openModal: false, activeDisbursement: {} }"
    @close-modal.window="openModal = false">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900 tracking-tight">Ready for Disbursement</h2>
                <p class="text-sm text-gray-500 mt-1">Filter disbursements, search a specific record, and open the details in a modal.</p>
            </div>
            <form method="GET" action="{{ route('disbursement.index') }}" class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
                <div class="relative w-full sm:w-64">
                    <input
                        type="search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Search by ref, member, type..."
                        class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                    <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0a7 7 0 0114 0z" />
                    </svg>
                </div>
                <select name="year" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                    <option value="">All years</option>
                    @foreach ($yearOptions as $year)
                        <option value="{{ $year }}" {{ ($filters['year'] ?? '') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
                <select name="claim_type" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                    <option value="">All claim types</option>
                    @foreach ($claimTypeOptions as $claimType)
                        <option value="{{ $claimType }}" {{ ($filters['claim_type'] ?? '') == $claimType ? 'selected' : '' }}>{{ $claimType }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                    <option value="">All statuses</option>
                    <option value="released" {{ ($filters['status'] ?? '') === 'released' ? 'selected' : '' }}>Released</option>
                    <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Apply</button>
                <a href="{{ route('disbursement.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
            </form>
        </div>

        <div class="overflow-x-auto min-h-[240px]">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 text-gray-500 font-semibold tracking-wider text-xs uppercase">
                        <th class="px-6 py-3">Ref#</th>
                        <th class="px-6 py-3">Member</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Amount</th>
                        <th class="px-6 py-3">Bank</th>
                        <th class="px-6 py-3">Account</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($disbursements as $row)
                        @php
                            $member = $row->claim?->member;
                            $claim = $row->claim;
                            $claimType = $claim?->claimType;
                            $statusText = $row->released_at ? 'Released' : ($claim?->status ?? 'Pending');
                            $accountValue = $member?->gcash_account_name ? trim($member->gcash_account_name . ' • ' . $member->gcash_number) : ($member?->gcash_number ?? 'N/A');
                            $modalData = [
                                'id' => $row->id,
                                'claim_number' => $claim?->claim_number ?? 'N/A',
                                'member_name' => trim(sprintf('%s %s %s', $member?->first_name, $member?->middle_name, $member?->last_name)) ?: 'N/A',
                                'claim_type' => $claimType?->name ?? 'N/A',
                                'amount' => number_format((float) $row->amount, 2),
                                'payment_method' => $row->paymentMethod?->name ?? 'N/A',
                                'account' => $accountValue,
                                'status' => $statusText,
                                'released_on' => optional($row->released_at)->format('M d, Y') ?: 'Pending',
                                'reference_number' => $row->reference_number ?? 'N/A',
                                'remarks' => $row->remarks ?? 'No remarks provided.',
                            ];
                        @endphp
                        <tr>
                            <td class="px-6 py-4 text-center">{{ $claim?->claim_number ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-center">{{ trim(sprintf('%s %s %s', $member?->first_name, $member?->middle_name, $member?->last_name)) ?: 'N/A' }}</td>
                            <td class="px-6 py-4 text-center">{{ $claimType?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-center">₱{{ number_format((float) $row->amount, 2) }}</td>
                            <td class="px-6 py-4 text-center">{{ $row->paymentMethod?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-center">{{ $accountValue }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $row->released_at ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    type="button"
                                    @click="activeDisbursement = @js($modalData); openModal = true;"
                                    class="inline-flex items-center rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400 italic">
                                No disbursements ready at this time.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="fixed inset-0 z-50 overflow-y-auto" x-show="openModal" style="display: none;" @keydown.escape.window="openModal = false">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" x-show="openModal" @click="openModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-2xl rounded-2xl border border-slate-100 bg-white p-6 shadow-2xl" x-show="openModal">
                <div class="mb-5 flex items-start justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Disbursement Details</h3>
                        <p class="mt-1 text-sm text-slate-500" x-text="activeDisbursement.claim_number ? 'Reference: ' + activeDisbursement.claim_number : 'Select a disbursement to review it.'"></p>
                    </div>
                    <button type="button" @click="openModal = false" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-50 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 text-sm">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <span class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Claim Ref#</span>
                            <span class="mt-1 block text-base font-semibold text-slate-900" x-text="activeDisbursement.claim_number || '—'"></span>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <span class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Amount</span>
                            <span class="mt-1 block text-base font-semibold text-slate-900" x-text="activeDisbursement.amount ? '₱' + activeDisbursement.amount : '—'"></span>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <span class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Member</span>
                            <span class="mt-1 block text-base font-semibold text-slate-900" x-text="activeDisbursement.member_name || '—'"></span>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <span class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Claim Type</span>
                            <span class="mt-1 block text-base font-semibold text-slate-900" x-text="activeDisbursement.claim_type || '—'"></span>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-100 p-4">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <span class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Payment Method</span>
                                <span class="mt-1 block text-sm font-semibold text-slate-800" x-text="activeDisbursement.payment_method || '—'"></span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Account</span>
                                <span class="mt-1 block text-sm font-semibold text-slate-800" x-text="activeDisbursement.account || '—'"></span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Status</span>
                                <span class="mt-1 block text-sm font-semibold text-slate-800" x-text="activeDisbursement.status || '—'"></span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Released On</span>
                                <span class="mt-1 block text-sm font-semibold text-slate-800" x-text="activeDisbursement.released_on || '—'"></span>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <span class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Reference Number</span>
                        <span class="mt-1 block text-sm font-semibold text-slate-800" x-text="activeDisbursement.reference_number || '—'"></span>
                    </div>
                    <div class="rounded-xl border border-slate-100 p-4">
                        <span class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Remarks</span>
                        <p class="mt-1 whitespace-pre-line text-sm text-slate-700" x-text="activeDisbursement.remarks || 'No remarks provided.'"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Disbursement Summary</h3>
            </div>
            <div class="divide-y divide-gray-100">
                <div class="flex justify-between items-center px-6 py-4">
                    <span class="text-sm text-gray-600 font-medium">Approved Claims</span>
                    <span class="text-base font-semibold text-gray-900">{{ $summary['approved_claims'] }}</span>
                </div>
                <div class="flex justify-between items-center px-6 py-4">
                    <span class="text-sm text-gray-600 font-medium">Already Disbursed</span>
                    <span class="text-base font-semibold text-emerald-600">₱{{ number_format($summary['released_amount'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center px-6 py-4">
                    <span class="text-sm text-gray-600 font-medium">Pending Disbursement</span>
                    <span class="text-base font-semibold text-amber-600">₱{{ number_format($summary['pending_amount'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Fund Allocation</h3>
            </div>
            <div class="divide-y divide-gray-100">
                <div class="flex justify-between items-center px-6 py-4">
                    <span class="text-sm text-gray-600 font-medium">Approved Amount</span>
                    <span class="text-base font-semibold text-gray-900">₱{{ number_format($summary['approved_amount'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center px-6 py-4">
                    <span class="text-sm text-gray-600 font-medium">Released Entries</span>
                    <span class="text-base font-semibold text-blue-600">{{ $summary['released_count'] }}</span>
                </div>
                <div class="flex justify-between items-center px-6 py-4 bg-gray-50/30">
                    <span class="text-sm text-gray-900 font-bold">Available for Release</span>
                    <span class="text-lg font-extrabold text-indigo-600">₱{{ number_format($summary['pending_amount'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>