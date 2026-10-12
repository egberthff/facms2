<div 
        x-show="openPaymentModal" 
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
        @keydown.escape.window="openPaymentModal = false">

        <!-- Backdrop Overlay -->
        <div 
            x-show="openPaymentModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
            @click="openPaymentModal = false"></div>

        <!-- Modal Box Wrapper -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div 
                x-show="openPaymentModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-2xl transform overflow-hidden rounded-2xl border border-slate-100 bg-white p-4 shadow-xl transition-all sm:p-6">

                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                    <div>
                        <!-- Dynamic Title based on status -->
                        <h3 class="text-lg font-semibold text-slate-900" 
                            x-text="activeContribution.status === 'Paid' ? 'Contribution Receipt Summary' : 'Contribution Pool Details'">
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5" x-text="'Claim Reference No: ' + (activeContribution.claim_number || 'N/A')"></p>
                    </div>
                    <button @click="openPaymentModal = false" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body Content -->
                <div class="space-y-5 text-sm max-h-[calc(100vh-220px)] overflow-y-auto pr-1">

                    <!-- Notice Banner: Purpose of Information Disclosure -->
                    <div class="bg-blue-50/60 border border-blue-100 text-blue-800 p-3.5 rounded-xl text-xs leading-relaxed flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>This information is shared with member contributors strictly to verify the transparency of pool distributions. Confidential diagnostic medical details have been completely hidden.</span>
                    </div>

                    <!-- Section 1: Split Summary Info Row -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl border text-center" 
                             :class="activeContribution.status === 'Paid' ? 'bg-emerald-50/50 border-emerald-100' : 'bg-slate-50/50 border-slate-100'">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1"
                                  x-text="activeContribution.status === 'Paid' ? 'Amount Contributed' : 'Your Assessed Split'"></span>
                            <p class="text-lg font-bold tracking-tight tabular-nums sm:text-xl" 
                               :class="activeContribution.status === 'Paid' ? 'text-emerald-700' : 'text-slate-900'"
                               x-text="'₱' + Number(activeContribution.amount_to_pay).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></p>
                        </div>
                        <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100 text-center">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Batch Pool Base Share</span>
                            <p class="text-xl font-bold text-slate-700" x-text="'₱' + Number(activeContribution.amount_per_member).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></p>
                        </div>
                    </div>

                    <!-- Section 2: Core Beneficiary & Purpose Context Matrix -->
                    <div class="bg-white rounded-xl border border-slate-100 divide-y divide-slate-100">
                        <div class="p-3.5 flex justify-between items-center">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Benefit For (Claimant)</span>
                            <span class="font-bold text-slate-800 text-sm" x-text="activeContribution.claimant_name"></span>
                        </div>
                        <div class="p-3.5 flex justify-between items-center">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Department Organization</span>
                            <span class="font-medium text-slate-600 text-sm" x-text="activeContribution.claimant_dept || 'N/A'"></span>
                        </div>
                        <div class="p-3.5 flex justify-between items-center">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Assigned Benefit Type</span>
                            <span class="font-bold text-blue-700 text-sm" x-text="activeContribution.claim_type"></span>
                        </div>
                        <div class="p-3.5 flex justify-between items-center">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Batch Generation Date</span>
                            <span class="font-medium text-slate-500 text-sm" x-text="activeContribution.generated_at || 'N/A'"></span>
                        </div>

                        <!-- Dynamic row visible only when PAID -->
                        <template x-if="activeContribution.status === 'Paid'">
                            <div class="p-3.5 flex justify-between items-center bg-emerald-50/20">
                                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Payment Cleared Date</span>
                                <span class="font-bold text-emerald-800 text-sm" x-text="activeContribution.paid_at || 'N/A'"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Section 3: General Non-Confidential Support Descriptions -->
                    <div class="space-y-3">
                        <div class="bg-slate-50/30 p-4 rounded-xl border border-slate-100">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">General Claim Notes</span>
                            <p class="text-slate-600 leading-relaxed text-xs whitespace-pre-line" 
                               x-text="activeContribution.claim_description || 'No additional remarks or generic contexts were summarized during generation.'">
                            </p>
                        </div>
                    </div>

                    <!-- Section 4: Audit Status Info Badge Block -->
                    {{-- <div class="p-4 rounded-xl border bg-slate-50/10 flex items-center justify-between"
                         :class="activeContribution.status === 'Paid' ? 'border-emerald-100 bg-emerald-50/5' : 'border-slate-100'">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" :class="activeContribution.status === 'Paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-emerald-50 text-emerald-600'">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-800" x-text="activeContribution.status === 'Paid' ? 'Transaction Settled & Closed' : 'Verification Integrity Secure'"></h4>
                                <p class="text-slate-400 text-[11px] mt-0.5" x-text="activeContribution.status === 'Paid' ? 'This contribution split package has been paid.' : 'Required claims audit criteria have been checked and approved.'"></p>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-md"
                              :class="activeContribution.status === 'Paid' ? 'text-white bg-emerald-600' : 'text-emerald-700 bg-emerald-50'"
                              x-text="activeContribution.status === 'Paid' ? 'Settled' : 'Audited'">
                        </span>
                    </div> --}}

                </div> <!-- End of Scrollable Body Component Wrapper -->

                           <!-- Modal Action Footer Buttons Area -->
                <div class="mt-5 flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
                    <button 
                        type="button" 
                        @click="openPaymentModal = false" 
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50">
                        Close Sheet
                    </button>

                    <!-- Primary Action: Form submission for Pending payments -->
                    {{-- <template x-if="activeContribution.status === 'Unpaid'">
                        <form :action="'/contribution-details/' + activeContribution.contribution_id + '/pay'" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button 
                                type="submit" 
                                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-blue-700">
                                Settle Contribution ₱<span x-text="Number(activeContribution.amount_to_pay).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                            </button>
                        </form>
                    </template> --}}

                    <!-- Secondary Action: Print option for Paid contributions -->
                    {{-- <template x-if="activeContribution.status === 'Paid'">
                        <button 
                            type="button" 
                            @click="window.print()" 
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 012-2h2a2 2 0 012 2v4M5 10h14" />
                            </svg>
                            Print Receipt Proof
                        </button>  
                    </template> --}}
                </div>
            </div>
        </div>
    </div>