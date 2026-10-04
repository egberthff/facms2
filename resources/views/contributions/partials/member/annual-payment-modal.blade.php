<!-- ========================================================== -->
<!-- 2. ANNUAL MEMBERSHIP / CONTRIBUTION MODAL                  -->
<!-- ========================================================== -->
<div 
    x-show="openAnnualModal" 
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
    @keydown.escape.window="openAnnualModal = false">

    <!-- Backdrop Overlay -->
    <div 
        x-show="openAnnualModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
        @click="openAnnualModal = false"></div>

    <!-- Modal Box Wrapper -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div 
            x-show="openAnnualModal"
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
                    <!-- Dynamic Title Based on Payment Status -->
                    <h3 class="text-lg font-semibold text-slate-900"> 
                        Annual Contribution Details
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="'Coverage Period: Calendar Year ' + (activeAnnual.contribution_year || new Date().getFullYear())"></p>
                </div>
                <button @click="openAnnualModal = false" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body Content -->
            <div class="space-y-5 text-sm max-h-[calc(100vh-220px)] overflow-y-auto pr-1">

                <!-- Notice Banner: Compliance & Standing Rules -->
                {{-- <div class="bg-blue-50/60 border border-blue-100 text-blue-800 p-3.5 rounded-xl text-xs leading-relaxed flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Annual contributions secure your baseline validation window in the community fund. Keeping this up to date protects your account from sudden premium allocation penalties or benefit freezing.</span>
                </div> --}}

                <!-- Section 1: Money Grid Summary (Uniform sizing with Claims) -->
                <div class="grid grid-cols-1 gap-4">
                    <div class="p-4 rounded-xl border text-center" 
                         :class="activeAnnual.status === 'Paid' ? 'bg-emerald-50/50 border-emerald-100' : 'bg-slate-50/50 border-slate-100'">
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1"
                              x-text="activeAnnual.status === 'Paid' || activeAnnual.status === 'Verified' ? 'Contribution Settled' : 'Total Amount Due'"></span>
                               <p class="text-lg font-bold tracking-tight tabular-nums sm:text-xl" 
                               :class="activeAnnual.status === 'Paid' || activeAnnual.status === 'Verified' ? 'text-emerald-700' : 'text-slate-900'"
                               x-text="'₱' + Number(activeAnnual.amount_paid || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })">
                            </p>
                    </div>
                    {{-- <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100 text-center flex flex-col justify-center">
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Membership Plan Type</span>
                        <p class="text-base font-bold text-slate-700 capitalize" x-text="activeAnnual.membership_tier || 'Standard Baseline'"></p>
                    </div> --}}
                </div>

                <!-- Section 2: Detailed Metadata Row (Fills out the "View Details" expectations) -->
                <div class="border border-slate-100 rounded-xl overflow-hidden text-xs">
                    <div class="bg-slate-50/70 px-4 py-2.5 font-bold text-slate-700 border-b border-slate-100 uppercase tracking-wider">
                        Account Verification Details
                    </div>
                    <div class="divide-y divide-slate-100 bg-white px-4">
                        <div class="py-2.5 flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Transaction Reference Token</span>
                            <span class="font-mono font-semibold text-slate-800" x-text="activeAnnual.reference_nunmber || 'GEN-PENDING-FEE'"></span>
                        </div>
                        {{-- <div class="py-2.5 flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Payment Cut-off Deadline</span>
                            <span class="font-semibold text-slate-800" x-text="activeAnnual.due_date || 'N/A'"></span>
                        </div> --}}
                        <div class="py-2.5 flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Current Settlement Standing</span>
                            <span class="px-2.5 py-0.5 rounded-full font-bold uppercase text-[10px] tracking-wide"
                                  :class="{
                                      'bg-emerald-100 text-emerald-800': activeAnnual.status === 'Paid' || activeAnnual.status === 'Verified',
                                      'bg-amber-100 text-amber-800': activeAnnual.status === 'Pending' || activeAnnual.status === 'Unpaid',
                                      'bg-rose-100 text-rose-800': activeAnnual.status === 'Failed'
                                  }"
                                  x-text="activeAnnual.status">
                            </span>
                        </div>
                        <!-- Conditional Row: Only displays if payment history exists -->
                        <template x-if="activeAnnual.status === 'Paid'">
                            <div class="py-2.5 flex justify-between items-center bg-emerald-50/-5 px-2 -mx-2">
                                <span class="text-emerald-700 font-semibold">Payment Confirmed Timestamp</span>
                                <span class="font-semibold text-emerald-800" x-text="activeAnnual.paid_at || 'Processed'"></span>
                            </div>
                        </template>
                    </div>
                </div>

            </div> <!-- End of Scrollable Body Component Wrapper -->

            <!-- Modal Action Footer Buttons Area -->
            <div class="mt-5 flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
                <button 
                    type="button" 
                    @click="openAnnualModal = false" 
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50">
                    Close Sheet
                </button>

                <!-- Primary Action: Process the outstanding baseline fee -->
                <template x-if="activeAnnual.status !== 'Paid' && activeAnnual.status !== 'Failed'">
                    <form :action="'/annual-contributions/' + activeAnnual.id + '/pay'" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button 
                            type="submit" 
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-blue-700">
                            Settle Contribution ₱<span x-text="Number(activeAnnual.amount_paid).toLocaleString('en-US', { minimumFractionDigits: 2 })"></span>
                        </button>
                    </form>
                </template>

                <!-- Secondary Action: Print Receipt for Verified Records -->
                <template x-if="activeAnnual.status === 'Paid'">
                    <button 
                        type="button" 
                        @click="window.print()" 
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 012-2h2a2 2 0 012 2v4M5 10h14" />
                        </svg>
                        Print Payment Token
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>
