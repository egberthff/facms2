<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto space-y-6" x-data="{
        search: '',
        batches: @js($batches),
        selectedBatch: null,
        selectedDetail: null,
        openModal: false,
        openDetailModal: false,
        selectBatch(batch) {
            this.selectedBatch = batch;
            this.openModal = true;
        },
        selectDetail(detail) {
            this.selectedDetail = detail;
            this.openDetailModal = true;
        },
        closeBatchModal() {
            this.openModal = false;
            this.selectedBatch = null;
        },
        closeDetailModal() {
            this.openDetailModal = false;
            this.selectedDetail = null;
        },
        hasSelectedDetail() {
            return this.selectedDetail !== null && this.selectedDetail !== undefined;
        },
        markContributionAsPaid(detailId) {
            window.markContributionAsPaid(detailId);
        },
        filteredBatches() {
            const query = this.search.toLowerCase();
            return this.batches.filter((batch) => {
                if (!query) return true;
                return [batch.claim_number, batch.claimant, batch.claim_type, batch.id]
                    .join(' ')
                    .toLowerCase()
                    .includes(query);
            });
        }
    }">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b pb-5">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Contribution Batch Monitoring</h1>
                <p class="text-xs text-slate-500 mt-1">Review each contribution batch, its collectible value, collected amount, and detailed member contributions.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="text-sm text-slate-600">
                    Showing <span class="font-semibold text-slate-900">{{ count($batches) }}</span> contribution batch(es)
                </div>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Search by batch, claimant, or claim type..."
                    class="w-full md:w-80 text-sm p-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Batch ID</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Claim ID</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Claimant</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Collectible Amount</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Collected Amount</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="batch in filteredBatches()" :key="batch.id">
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-4 py-3 font-mono text-xs text-slate-700" x-text="batch.id"></td>
                                <td class="px-4 py-3 font-medium text-slate-700" x-text="batch.claim_id"></td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900" x-text="batch.claimant || 'N/A'"></div>
                                    <div class="text-xs text-slate-500" x-text="batch.claim_type"></div>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900" x-text="'₱' + Number(batch.collectible_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></td>
                                <td class="px-4 py-3 text-right font-semibold" :class="batch.collected_amount >= batch.collectible_amount ? 'text-emerald-700' : 'text-amber-700'" x-text="'₱' + Number(batch.collected_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        type="button"
                                        @click="selectBatch(batch)"
                                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition"
                                    >
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeBatchModal()"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-5xl rounded-2xl bg-white shadow-2xl border border-slate-100 overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Contribution Detail Breakdown</h3>
                            <p class="text-xs text-slate-500 mt-1" x-text="selectedBatch ? selectedBatch.claim_number + ' • ' + selectedBatch.claimant : ''"></p>
                        </div>
                        <button @click="closeBatchModal()" class="text-slate-400 hover:text-slate-600 transition p-1.5 rounded-lg hover:bg-slate-50">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6" x-show="selectedBatch">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-400">Batch ID</p>
                                <p class="mt-1 font-semibold text-slate-900" x-text="selectedBatch?.id"></p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-400">Collected</p>
                                <p class="mt-1 font-semibold text-emerald-700" x-text="selectedBatch ? '₱' + Number(selectedBatch.collected_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : ''"></p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-400">Outstanding</p>
                                <p class="mt-1 font-semibold text-amber-700" x-text="selectedBatch ? '₱' + Number(selectedBatch.collectible_amount - selectedBatch.collected_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : ''"></p>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-100">
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Member</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Employee No.</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Department</th>
                                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Amount</th>
                                        <th class="px-4 py-3 text-center font-semibold text-slate-600">Status</th>
                                        <th class="px-4 py-3 text-center font-semibold text-slate-600">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="detail in selectedBatch?.details || []" :key="detail.id">
                                        <tr class="hover:bg-slate-50/60 transition">
                                            <td class="px-4 py-3 font-medium text-slate-800" x-text="detail.member_name"></td>
                                            <td class="px-4 py-3 text-slate-600" x-text="detail.employee_number"></td>
                                            <td class="px-4 py-3 text-slate-600" x-text="detail.department"></td>
                                            <td class="px-4 py-3 text-right font-semibold text-slate-900" x-text="'₱' + Number(detail.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wide"
                                                    :class="((detail.status || '').toString().toLowerCase() === 'paid') ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                                    x-text="detail.status"></span>
                                            </td>
                                            <td class="px-4 py-3 text-center space-y-2">
                                                <button type="button" @click="selectDetail(detail)" class="block w-full text-xs font-semibold text-blue-600 hover:text-blue-700">View Full Details</button>
                                                <button
                                                    type="button"
                                                    @click="markContributionAsPaid(detail.id)"
                                                    :disabled="(detail.status || '').toString().toLowerCase() === 'paid'"
                                                    class="block w-full text-xs font-semibold"
                                                    :class="(detail.status || '').toString().toLowerCase() === 'paid' ? 'text-slate-400 cursor-not-allowed' : 'text-emerald-600 hover:text-emerald-700'"
                                                >
                                                    <span x-text="(detail.status || '').toString().toLowerCase() === 'paid' ? 'Already Paid' : 'Mark as Paid'"></span>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="openDetailModal" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeDetailModal()"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl border border-slate-100 overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Contribution Detail</h3>
                            <p class="text-xs text-slate-500 mt-1">Full details for the selected contribution row.</p>
                        </div>
                        <button @click="closeDetailModal()" class="text-slate-400 hover:text-slate-600 transition p-1.5 rounded-lg hover:bg-slate-50">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-4" x-show="hasSelectedDetail()">
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-400">Member</p>
                            <p class="mt-1 font-semibold text-slate-900" x-text="selectedDetail?.member_name"></p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-400">Employee Number</p>
                                <p class="mt-1 font-semibold text-slate-900" x-text="selectedDetail?.employee_number"></p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-400">Department</p>
                                <p class="mt-1 font-semibold text-slate-900" x-text="selectedDetail?.department"></p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-400">Amount</p>
                                <p class="mt-1 font-semibold text-slate-900" x-text="selectedDetail ? '₱' + Number(selectedDetail.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : ''"></p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-400">Status</p>
                                <p class="mt-1 font-semibold text-slate-900" x-text="selectedDetail?.status"></p>
                            </div>
                        </div>
                        <div class="rounded-xl border border-slate-100 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-400">Paid At</p>
                            <p class="mt-1 font-semibold text-slate-900" x-text="selectedDetail?.paid_at || '—'"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
