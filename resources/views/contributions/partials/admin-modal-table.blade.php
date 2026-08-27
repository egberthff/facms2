<div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto mx-lg-auto" style="display: none;">
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
                                <p class="text-xs uppercase tracking-wide text-slate-400"
                                x-text="contributionType === 'claims' ? 'Claim ID' : 'Contribution ID'"></p>
                                <p class="mt-1 font-semibold text-slate-900" x-text="selectedBatch?.claim_number"></p>
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

                        <!-- 1. Wrap your table in a scrollable div container to manage long lists cleanly -->
                        <div class="mb-4">
                            <div class="relative rounded-md shadow-sm max-w-md">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    x-model="modalSearch" 
                                    placeholder="Search member, employee number, or department..." 
                                    class="block w-full rounded-lg border-0 py-2 pl-10 text-sm text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 justify-self-end"
                                >
                            </div>
                        </div>
                        <div class="overflow-x-auto overflow-y-auto max-h-[450px] border border-slate-100 rounded-xl shadow-sm">
                            <!-- 2. Keep the table completely natural (NO dt-table, NO custom JS directives) -->
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-50 sticky top-0 z-10 shadow-[inset_0_-1px_0_rgba(15,23,42,0.05)]">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Member</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Employee No.</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Department</th>
                                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Amount</th>
                                        <th class="px-4 py-3 text-center font-semibold text-slate-600">Status</th>
                                        <th class="px-4 py-3 text-center font-semibold text-slate-600">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <!-- 3. Your Alpine template loops and clicks will now run perfectly without crashing -->
                                    <template x-for="detail in filteredDetails" :key="detail.id">
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
                                            <td class="px-4 py-3 text-center space-y-1">
                                                <button type="button" @click="selectDetail(detail)" class="block w-full text-xs font-semibold text-blue-600 hover:text-blue-700">View Full Details</button>
                                                <button
                                                    type="button"
                                                    @click="markContributionAsPaid(detail.id, contributionType)"
                                                    :disabled="(detail.status || '').toString().toLowerCase() === 'paid'"
                                                    class="block w-full text-xs font-semibold"
                                                    :class="(detail.status || '').toString().toLowerCase() === 'paid' ? 'text-slate-400 cursor-not-allowed' : 'text-emerald-600 hover:text-emerald-700'"
                                                >
                                                    <span x-text="(detail.status || '').toString().toLowerCase() === 'paid' ? 'Already Paid' : 'Mark as Paid'"></span>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="filteredDetails.length === 0">
                                        <tr>
                                            <td colspan="{{ $isPresident ? '7' : '6' }}" class="text-center py-12 text-gray-400 dark:text-zinc-500 text-xs">
                                                No records match your filters or search terms inside this workspace view.
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