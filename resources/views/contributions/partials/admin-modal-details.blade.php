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