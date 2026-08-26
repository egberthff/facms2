<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto space-y-6" x-data="{
        search: '',
        batches: @js($batches),
        selectedBatch: null,
        selectedDetail: null,
        openModal: false,
        openDetailModal: false,
        openAnnualContributionSetupModal: false,
        contributionYear: '',
        page: 1,
        perPage: 5, 
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
        },
         // 2. Add Dynamic Pagination Getters
        get paginatedBatches() {
            const start = (this.page - 1) * this.perPage;
            const end = start + this.perPage;
            return this.filteredBatches().slice(start, end);
        },
        get totalBatchesPages() {
            return Math.ceil(this.filteredBatches().length / this.perPage) || 1;
        },
        get totalBatchesCount() {
            return this.filteredBatches().length;
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

     <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 dark:border-zinc-800 pb-5">
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
        </div>  

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
