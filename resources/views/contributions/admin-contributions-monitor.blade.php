<x-app-layout>
    <div class="p-6 mx-auto space-y-6" x-data="{
        search: '',
        batches: @js($batches),
        selectedBatch: null,
        selectedDetail: null,
        contributionType: @js($contribution_type),
        openModal: false,
        openDetailModal: false,
        openAnnualContributionSetupModal: false,
        openCNAShareSetupModal: false,
        openContributionAdjustmentModal: false,
        contributionYear: '',
        impactType: '',
        formatAmount(value) {
            const amount = Number(value ?? 0);
            return '₱' + amount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },
        batchCollectibleAmount() {
            return Number(this.selectedBatch?.collectible_amount ?? this.selectedBatch?.amount_paid ?? this.selectedBatch.paid_amount ?? 0);
        },
        batchCollectedAmount() {
            return Number(this.selectedBatch?.collected_amount ?? this.selectedBatch?.amount_collected ?? this.selectedBatch?.paid_amount ?? 0);
        },
        batchOutstandingAmount() {
            return Math.max(this.batchCollectibleAmount() - this.batchCollectedAmount(), 0);
        },
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
        markContributionAsPaid(detailId, contributionType) {
            window.markContributionAsPaid(detailId, contributionType);
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
        },
        modalSearch: '',
        get filteredDetails() {
            let details = this.selectedBatch?.details || [];
            let query = this.modalSearch.toLowerCase().trim();
            if (!query) return details;
            return details.filter(item => {
                return (item.member_name || '').toLowerCase().includes(query) ||
                       (item.employee_number || '').toLowerCase().includes(query) ||
                       (item.department || '').toLowerCase().includes(query);
            });
        },
    }">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 dark:border-zinc-800 pb-5" x-data="{ openActions: false }">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Contribution Batch Monitoring</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Review each contribution batch, its collectible value, collected amount, and detailed member contributions.</p>
        </div>
        
        <!-- Action Control Section -->
        <div class="relative mt-4 md:mt-0 w-full md:w-auto">
            <!-- Main Trigger Button -->
            <button 
                @click="openActions = !openActions"
                @click.away="openActions = false"
                class="inline-flex items-center justify-center w-full md:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-lg shadow-sm hover:shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:scale-[0.98]">
                
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://w3.org">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Batch Actions
                <!-- Chevron Down Icon -->
                <svg class="w-4 h-4 ml-2 transition-transform duration-200" :class="{'rotate-180': openActions}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://w3.org">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
    
            <!-- Dropdown Menu Elements -->
            <div 
                x-show="openActions"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-2 w-full md:w-56 rounded-xl bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 shadow-lg ring-1 ring-black ring-opacity-5 z-50 divide-y divide-gray-100 dark:divide-zinc-800"
                style="display: none;">
                
                <div class="py-1">
                    <button @click="openAnnualContributionSetupModal = true; openActions = false" class="flex w-full items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                        <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://w3.org"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Setup Annual Due
                    </button>
                </div>
    
                <div class="py-1">
                    <button @click="openCNAShareSetupModal = true; openActions = false" class="flex w-full items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                        <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://w3.org"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 10.742l4.135-4.135m0 0a3.001 3.001 0 114.243 4.243l-4.135 4.135m-4.135-4.135a3 3 0 11-4.243-4.243l4.135-4.135m0 0L8.684 10.742m4.135 4.135l-4.135 4.135m0 0a3 3 0 11-4.243-4.243l4.135-4.135m4.135 4.135l4.135 4.135m0 0a3 3 0 104.243-4.243l-4.135-4.135"></path></svg>
                        Setup CNA Share
                    </button>
                    
                    <button @click="openContributionAdjustmentModal = true; openActions = false" class="flex w-full items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                        <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://w3.org"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        Contribution Adjustment
                    </button>
                </div>
            </div>
        </div>
    </div>
    
     {{-- <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 dark:border-zinc-800 pb-5">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Contribution Batch Monitoring</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Review each contribution batch, its collectible value, collected amount, and detailed member contributions.</p>
            </div>
            <div>
                <button 
                    @click="openAnnualContributionSetupModal = true"
                    class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-lg shadow-sm hover:shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:scale-[0.98]">

                    <!-- Leading Icon for Visual Cue -->
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://w3.org">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Start Annual Setup
                </button>
            </div>
        </div>   --}}

        {{-- Administratice panel --}}
        @include('contributions.partials.admin-filters')

        {{-- Contributions main table --}}
        @include('contributions.partials.admin-contributions-main-table')

        <!-- Modal for Batch Details -->
        @include('contributions.partials.admin-modal-table')

        {{-- Modal details for each contribution row --}}
        @include('contributions.partials.admin-modal-details')

        {{-- Modal for Annual Contribution Setup --}}
        @include('contributions.partials.admin-annual-contribution-setup')
        
    </div>
</x-app-layout>
