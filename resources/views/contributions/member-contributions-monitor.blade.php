<x-app-layout>
<div class="max-w-7xl mx-auto p-4" 
    x-data="{ 
            currentStatus: 'all', 
            searchText: '',
            openModal: false,
            openPaymentModal: false,
            activeContribution: {},
            matches(status, number, desc, type) {
                const matchesStatus = this.currentStatus === 'all' || status.toLowerCase() === this.currentStatus.toLowerCase();
                const searchLower = this.searchText.toLowerCase();
                const matchesSearch = !this.searchText || 
                                     number.toLowerCase().includes(searchLower) || 
                                     desc.toLowerCase().includes(searchLower) ||
                                     type.toLowerCase().includes(searchLower);
                return matchesStatus && matchesSearch;
            }
         }"  @close-modal.window="openModal = false">
    
    <!-- ========================================= -->
    <!-- CLAIMS LIST CARD -->
    <!-- ========================================= -->
    <div class="bg-white border border-blue-700 rounded-2xl shadow-sm">
        <!-- Header -->
        <div class="p-4 border-b">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <!-- Filters -->
                <div class="flex flex-wrap gap-2">
                    <template x-for="status in ['all', 'Paid', 'Unpaid']">
                        <button 
                            type="button"
                            @click="currentStatus = status"
                            :class="currentStatus === status 
                                ? 'bg-blue-600 text-white border-blue-600' 
                                : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                            class="px-4 py-2 text-sm rounded-lg border transition font-medium capitalize"
                            x-text="status">
                        </button>
                    </template>
                </div>
                <!-- Search -->
                <div class="relative w-full lg:w-72">
                    <input
                        type="search"
                        x-model="searchText"
                        placeholder="Search claim..."
                        class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-orange-400 focus:border-orange-400">
                    <svg
                        xmlns="http://w3.org"
                        class="absolute left-3 top-2.5 w-4 h-4 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Table -->
        <div class="overflow-x-auto">
           <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Ref #</th>
                        <th class="px-4 py-3 text-left font-semibold">Claimant (Benefit For)</th>
                        <th class="px-4 py-3 text-left font-semibold">Claim Type</th>
                        <th class="px-4 py-3 text-right font-semibold">My Contribution</th>
                        <th class="px-4 py-3 text-center font-semibold">Issued Date</th>
                        <th class="px-4 py-3 text-center font-semibold">Contribution Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($contributions as $item)
                        <tr x-show="matches(
                                '{{ $item['status'] }}', 
                                '{{ $item['claim_number'] ?? '' }}', 
                                '{{ addslashes($item['claimant_name']) }}', 
                                '{{ addslashes($item['claim_type']) }}'
                            )"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform scale-95"
                            x-transition:enter-end="opacity-100 transform scale-100">

                            <!-- 1. Claim Reference Number -->
                            <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-900">
                                {{ $item['claim_number'] ?? 'N/A' }}
                            </td>

                            <!-- 2. Claimant Metadata Summary -->
                            <td class="px-4 py-3 text-slate-600">
                                <div class="font-medium text-slate-900">{{ $item['claimant_name'] }}</div>
                                <div class="text-xs text-slate-400">{{ $item['claimant_dept'] ?? 'No Dept' }}</div>
                            </td>

                            <!-- 3. Type of Claim -->
                            <td class="px-4 py-3 text-slate-500 font-medium">
                                {{ $item['claim_type'] }}
                            </td>

                            <!-- 4. Individual Split Amount Owed -->
                            <td class="px-4 py-3 text-right font-bold text-slate-900">
                                ₱{{ number_format($item['amount_to_pay'], 2) }}
                            </td>

                            <!-- 5. Date the requirement batch was issued -->
                            <td class="px-4 py-3 text-center text-slate-500">
                                {{ $item['generated_at'] ?? 'N/A' }}
                            </td>

                            <!-- 6. Styled Context Payment Badges -->
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize
                                    {{ $item['status'] === 'Unpaid' ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ $item['status'] === 'Paid' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ !in_array($item['status'], ['Unpaid', 'Paid']) ? 'bg-slate-100 text-slate-800' : '' }}
                                ">
                                    {{ $item['status'] ?? 'Unknown' }}
                                </span>
                            </td>

                            <!-- 7. Dynamic Action Dispatchers -->
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($item['status'] === 'Unpaid')
                                    <button
                                        type="button"
                                        @click="activeContribution = @js($item); openPaymentModal = true;"
                                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent text-white rounded-lg text-xs font-semibold hover:bg-blue-700 shadow-sm transition">
                                        {{-- Pay Now --}}
                                        View Details
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        @click="activeContribution = @js($item); openPaymentModal = true;"
                                        class="inline-flex items-center px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-100 transition">
                                        {{-- View Receipt --}}
                                        View Details
                                    </button>
                                @endif
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>


        </div>
    </div>
    
    <!--========================================= -->
    <!-- ALPINE DETAILS MODAL OVERLAY -->
    <!-- ========================================= -->
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
                class="relative w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-6 shadow-2xl transition-all border border-slate-100">

                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                    <div>
                        <!-- Dynamic Title based on status -->
                        <h3 class="text-lg font-bold text-slate-900" 
                            x-text="activeContribution.status === 'Paid' ? 'Contribution Receipt Summary' : 'Contribution Pool Details'">
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5" x-text="'Claim Reference No: ' + (activeContribution.claim_number || 'N/A')"></p>
                    </div>
                    <button @click="openPaymentModal = false" class="text-slate-400 hover:text-slate-600 transition p-1.5 rounded-lg hover:bg-slate-50">
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
                            <p class="text-xl font-black" 
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
                    <div class="p-4 rounded-xl border bg-slate-50/10 flex items-center justify-between"
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
                    </div>

                </div> <!-- End of Scrollable Body Component Wrapper -->

                           <!-- Modal Action Footer Buttons Area -->
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4 mt-5">
                    <button 
                        type="button" 
                        @click="openPaymentModal = false" 
                        class="px-5 py-2 text-sm font-semibold text-slate-600 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl transition">
                        Close Sheet
                    </button>

                    <!-- Primary Action: Form submission for Pending payments -->
                    <template x-if="activeContribution.status === 'Unpaid'">
                        <form :action="'/contribution-details/' + activeContribution.contribution_id + '/pay'" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button 
                                type="submit" 
                                class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition">
                                Proceed to Settle ₱<span x-text="Number(activeContribution.amount_to_pay).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                            </button>
                        </form>
                    </template>

                    <!-- Secondary Action: Print option for Paid contributions -->
                    <template x-if="activeContribution.status === 'Paid'">
                        {{-- <button 
                            type="button" 
                            @click="window.print()" 
                            class="px-5 py-2 text-sm font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 012-2h2a2 2 0 012 2v4M5 10h14" />
                            </svg>
                            Print Receipt Proof
                        </button> --}}
                    </template>
                </div>
            </div>
        </div>
    </div>

</div>
</x-app-layout>
