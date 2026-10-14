<x-app-layout>
<div class="min-h-screen font-sans text-gray-800"
    x-data="{
        openModal: false,
        activeDisbursement: {},
        filePreviewOpen: false,
        filePreviewUrl: '',
        filePreviewName: '',
        previewFile(url, name) {
            this.filePreviewUrl = url;
            this.filePreviewName = name || 'File preview';
            this.filePreviewOpen = true;
        }
    }"
    @close-modal.window="openModal = false">
    {{-- @if($isPresident) --}}
    @if(Auth::user()->hasAnyRole(['Federated Presidnent', 'Federated Treasurer']) || Auth::user()->can('view all campus data'))
        <div class="mb-6 rounded-xl border border-gray-200 bg-gray-50/60 p-4 sm:p-5 dark:border-zinc-800/80 dark:bg-zinc-800/40">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Campus Disbursement Filter</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Filter ready-to-release disbursements by campus.</p>
                </div>
                <form method="GET" action="{{ url()->current() }}" class="flex w-full items-center gap-2 md:w-auto md:min-w-64">
                    <select name="campus_filter" onchange="this.form.submit()" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        <option value="">All Campuses (Global View)</option>
                        @foreach($allCampuses as $campus)
                            <option value="{{ $campus->id }}" {{ ($selectedCampusId ?? '') == $campus->id ? 'selected' : '' }}>{{ $campus->acronym }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="scrollbar-none -mx-1 mt-4 flex gap-2 overflow-x-auto px-1 pb-1">
                @foreach($allCampuses as $campus)
                    <div class="shrink-0 rounded-full border bg-white px-3 py-1 text-xs font-medium dark:bg-zinc-900 {{ ($selectedCampusId ?? '') == $campus->id ? 'border-blue-500 bg-blue-50/10' : 'border-gray-200 dark:border-zinc-800' }}">
                        <span class="text-gray-600 dark:text-gray-400">{{ $campus->acronym }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xs">
        <div class="flex flex-col gap-4 border-b border-gray-100 bg-gray-50/50 px-4 py-4 sm:px-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold tracking-tight text-gray-900">Ready for Disbursement</h2>
                <p class="text-sm text-gray-500 mt-1">Filter disbursements, search a specific record, and open the details in a modal.</p>
            </div>
            <form method="GET" action="{{ route('disbursement.index') }}" class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
                <input type="hidden" name="campus_filter" value="{{ $selectedCampusId ?? '' }}">
                {{-- <div class="relative w-full sm:w-64">
                    <input
                        type="search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Search by ref, member, type..."
                        class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                    <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0a7 7 0 0114 0z" />
                    </svg>
                </div> --}}
                <select name="year" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:w-auto">
                    <option value="">All years</option>
                    {{-- @foreach ($yearOptions as $year)
                        <option value="{{ $year }}" {{ ($filters['year'] ?? '') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach --}}
                    @foreach (range(now()->year, 2010) as $year)
                        <option value="{{ $year }}" {{ (string) ($filters['year'] ?? '') === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
                <select name="claim_type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:w-auto">
                    <option value="">All claim types</option>
                    @foreach ($claimTypeOptions as $claimType)
                        <option value="{{ $claimType['id'] }}" {{ ($filters['claim_type'] ?? '') == $claimType['id'] ? 'selected' : '' }}>{{ $claimType['name'] }}</option>
                    @endforeach
                </select>
                <select name="status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:w-auto">
                    <option value="">All statuses</option>
                    <option value="released" {{ ($filters['status'] ?? '') === 'released' ? 'selected' : '' }}>Released</option>
                    <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-blue-700">Apply</button>
                <a href="{{ route('disbursement.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-xs transition hover:bg-gray-50">Reset</a>
            </form>
        </div>

        <div class="min-h-60 overflow-x-auto p-3 sm:p-4">
            <table class="dt-table w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 text-gray-500 font-semibold tracking-wider text-xs uppercase">
                        <th class="px-6 py-3">Ref#</th>
                        <th class="px-6 py-3">Member</th>
                        @if($isPresident) 
                        <th class="px-6 py-3">Campus</th>
                        @endif
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Amount</th>
                        <th class="px-6 py-3">Payment Method</th>
                        {{-- <th class="px-6 py-3">Account</th> --}}
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($disbursements as $row)
                        @php
                            $member = $row->claim?->member;
                            $campus = $row->claim?->member?->campus?->acronym;
                            $claim = $row->claim;
                            $claimType = $claim?->claimType;
                            $statusText = $row->released_at ? 'Released' : ($claim?->status ?? 'Pending');
                            $accountValue = $member?->gcash_account_name ? trim($member->gcash_account_name . ' • ' . $member->gcash_number) : ($member?->gcash_number ?? 'N/A');
                            $modalData = [
                                'id' => $row->id,
                                'claim_number' => $claim?->claim_number ?? 'N/A',
                                'member_name' => trim(sprintf('%s %s %s', $member?->first_name, $member?->middle_name, $member?->last_name)) ?: 'N/A',
                                'campus' => $campus,
                                'claim_type' => $claimType?->name ?? 'N/A',
                                'amount' => number_format((float) $row->amount, 2),
                                'payment_method' => $row->paymentMethod?->name ?? 'N/A',
                                'account' => $accountValue,
                                'status' => $statusText,
                                'released_on' => optional($row->released_at)->format('M d, Y') ?: 'Pending',
                                'reference_number' => $row->reference_number ?? 'N/A',
                                'remarks' => $row->remarks ?? 'No remarks provided.',
                                'attachments' => $row->receipt_path
                                    ? [['file_path' => $row->receipt_path, 'original_filename' => basename($row->receipt_path)]]
                                    : [],
                            ];
                        @endphp
                        <tr>
                            <td class="px-6 py-4 text-center">{{ $claim?->claim_number ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-center">{{ trim(sprintf('%s %s %s', $member?->first_name, $member?->middle_name, $member?->last_name)) ?: 'N/A' }}</td>
                            @if($isPresident) 
                            <td class="px-6 py-4 text-center">{{ $campus ?? 'N/A' }}</td>
                            @endif
                            <td class="px-6 py-4 text-center">{{ $claimType?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-center">₱{{ number_format((float) $row->amount, 2) }}</td>
                            <td class="px-6 py-4 text-center">{{ $row->paymentMethod?->name ?? 'N/A' }}</td>
                            {{-- <td class="px-6 py-4 text-center">{{ $accountValue }}</td> --}}
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $row->released_at ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-amber-50 text-amber-700 ring-amber-600/20' }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    type="button"
                                    @click="activeDisbursement = @js($modalData); openModal = true;"
                                    class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
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
            <div class="relative w-full max-w-2xl rounded-2xl border border-slate-100 bg-white p-4 shadow-xl sm:p-6" x-show="openModal">
                <div class="mb-5 flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Disbursement Details</h3>
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
                            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Claim Ref#</span>
                            <span class="mt-1 block text-base font-semibold text-slate-900" x-text="activeDisbursement.claim_number || '—'"></span>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Amount</span>
                            <span class="mt-1 block text-base font-semibold text-slate-900" x-text="activeDisbursement.amount ? '₱' + activeDisbursement.amount : '—'"></span>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Member</span>
                            <span class="mt-1 block text-base font-semibold text-slate-900" x-text="activeDisbursement.member_name || '—'"></span>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Claim Type</span>
                            <span class="mt-1 block text-base font-semibold text-slate-900" x-text="activeDisbursement.claim_type || '—'"></span>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-100 p-4">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Payment Method</span>
                                <span class="mt-1 block text-sm font-semibold text-slate-800" x-text="activeDisbursement.payment_method || '—'"></span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Account</span>
                                <span class="mt-1 block text-sm font-semibold text-slate-800" x-text="activeDisbursement.account || '—'"></span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Status</span>
                                <span class="mt-1 block text-sm font-semibold text-slate-800" x-text="activeDisbursement.status || '—'"></span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Released On</span>
                                <span class="mt-1 block text-sm font-semibold text-slate-800" x-text="activeDisbursement.released_on || '—'"></span>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Reference Number</span>
                        <span class="mt-1 block text-sm font-semibold text-slate-800" x-text="activeDisbursement.reference_number || '—'"></span>
                    </div>
                    <div class="rounded-xl border border-slate-100 p-4">
                        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Remarks</span>
                        <p class="mt-1 whitespace-pre-line text-sm text-slate-700" x-text="activeDisbursement.remarks || 'No remarks provided.'"></p>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-100 p-4">
                    <span class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-400">Attachments</span>
                    
                    <div class="divide-y divide-slate-100">
                        <template x-if="activeDisbursement.attachments && activeDisbursement.attachments.length > 0">
                            <div>
                                <template x-for="(doc, index) in activeDisbursement.attachments" :key="index">
                                    <div class="flex items-center justify-between py-2.5 text-xs first:pt-0 last:pb-0">
                                        <div class="flex items-center gap-2.5 min-w-0 pr-4">
                                            <div class="p-2 bg-slate-50 rounded-lg text-slate-500 shrink-0">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                            <div class="min-w-0 truncate">
                                                <span class="font-medium text-slate-800 block truncate" x-text="doc.original_filename"></span>
                                            </div>
                                        </div>
                                        <button type="button" @click="$dispatch('open-file-preview', { url: '/storage/' + doc.file_path, name: doc.original_filename })" class="inline-flex shrink-0 items-center gap-1 rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200 transition hover:bg-blue-100">
                                            View File
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>
                    
                        <template x-if="!activeDisbursement.attachments || activeDisbursement.attachments.length === 0">
                            <div class="text-center py-4 text-slate-400 text-xs">
                                No proof of payment was uploaded for this disbursement.
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('components.file-preview-modal')

    <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xs">
            <div class="border-b border-gray-100 bg-gray-50/50 px-4 py-3.5 sm:px-6">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Disbursement Summary</h3>
            </div>
            <div class="divide-y divide-gray-100">
                <div class="flex items-center justify-between gap-4 px-4 py-3.5 sm:px-6">
                    <span class="text-sm text-gray-600 font-medium">Approved Claims</span>
                    <span class="text-base font-semibold text-gray-900 tabular-nums">{{ $summary['approved_claims'] }}</span>
                </div>
                <div class="flex items-center justify-between gap-4 px-4 py-3.5 sm:px-6">
                    <span class="text-sm text-gray-600 font-medium">Released Claims</span>
                    <span class="text-base font-semibold text-blue-600 tabular-nums">{{ $summary['released_count'] }}</span>
                </div>
                <div class="flex items-center justify-between gap-4 bg-gray-50/30 px-4 py-3.5 sm:px-6">
                    <span class="text-sm text-gray-900 font-bold">Available for Release</span>
                    <span class="text-lg font-bold text-indigo-600 tabular-nums">{{ $summary['approved_claims'] - $summary['released_count'] }}</span>
                </div>
                {{-- <div class="flex items-center justify-between gap-4 px-4 py-3.5 sm:px-6">
                    <span class="text-sm text-gray-600 font-medium">Already Disbursed</span>
                    <span class="text-base font-semibold text-emerald-600 tabular-nums">₱{{ number_format($summary['released_amount'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between gap-4 px-4 py-3.5 sm:px-6">
                    <span class="text-sm text-gray-600 font-medium">Pending Disbursement</span>
                    <span class="text-base font-semibold text-amber-600 tabular-nums">₱{{ number_format($summary['pending_amount'], 2) }}</span>
                </div> --}}
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xs">
            <div class="border-b border-gray-100 bg-gray-50/50 px-4 py-3.5 sm:px-6">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Fund Allocation</h3>
            </div>
            <div class="divide-y divide-gray-100">
                <div class="flex items-center justify-between gap-4 px-4 py-3.5 sm:px-6">
                    <span class="text-sm text-gray-600 font-medium">Approved Amount</span>
                    <span class="text-base font-semibold text-gray-900 tabular-nums">₱{{ number_format($summary['approved_amount'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between gap-4 px-4 py-3.5 sm:px-6">
                    <span class="text-sm text-gray-600 font-medium">Already Disbursed</span>
                    <span class="text-base font-semibold text-emerald-600 tabular-nums">₱{{ number_format($summary['released_amount'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between gap-4 px-4 py-3.5 sm:px-6">
                    <span class="text-sm text-gray-600 font-medium">Pending Disbursement</span>
                    <span class="text-base font-semibold text-amber-600 tabular-nums">₱{{ number_format($summary['pending_amount'], 2) }}</span>
                </div>
                {{-- <div class="flex items-center justify-between gap-4 px-4 py-3.5 sm:px-6">
                    <span class="text-sm text-gray-600 font-medium">Released Entries</span>
                    <span class="text-base font-semibold text-blue-600 tabular-nums">{{ $summary['released_count'] }}</span>
                </div> --}}
                {{-- <div class="flex items-center justify-between gap-4 bg-gray-50/30 px-4 py-3.5 sm:px-6">
                    <span class="text-sm text-gray-900 font-bold">Available for Release</span>
                    <span class="text-lg font-bold text-indigo-600 tabular-nums">₱{{ number_format($summary['pending_amount'], 2) }}</span>
                </div> --}}
            </div>
        </div>
    </div>
</div>
</x-app-layout>