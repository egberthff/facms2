<x-app-layout>
<div class="mx-auto space-y-4" 
    x-data="{ 
            currentStatus: 'all', 
            statuses: ['all', ...{{ json_encode(array_column(App\Enums\ContributionStatus::cases(), 'value')) }}], 
            searchText: '',
            openModal: false,
            openPaymentModal: false,
            openAnnualModal : false,
            activeAnnual: {},
            activeContribution: {},
            filterContributions(status) {
                const table = document.querySelector('.dt-table');
                const dataTable = table?.dataTableInstance;

                if (dataTable) {
                    dataTable.search(status === 'all' ? '' : status);
                }
            },
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
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xs">
        <!-- Header filter -->
        @include('contributions.partials.member.filter') 
        
        <!-- Table -->
        <div class="overflow-x-auto">
            @if($contribution_type === 'annual due')
                <!-- ANNUAL CONTRIBUTION TABLE -->
                @include('contributions.partials.member.annual-table')
            @elseif($contribution_type === 'claims')
                <!-- CLAIMS TABLE (Original Layout) -->
                @include('contributions.partials.member.claims-table')
            @elseif($contribution_type === 'cna share')
                <!-- CLAIMS TABLE (Original Layout) -->
                {{-- @include('contributions.partials.member.cna-share-table') --}}
                @include('contributions.partials.member.annual-table')
            @elseif($contribution_type === 'membership fee')
                <!-- CLAIMS TABLE (Original Layout) -->
                {{-- @include('contributions.partials.member.cna-share-table') --}}
                @include('contributions.partials.member.annual-table')    
            @endif
        </div>
    </div>
    
    <!--========================================= -->
    <!-- ALPINE DETAILS MODAL OVERLAY -->
    <!-- ========================================= -->
    <!-- Claims contribution payment modal -->
    @include('contributions.partials.member.claims-payment-modal')
    
    <!-- Annual contribution payment modal -->
    @include('contributions.partials.member.annual-payment-modal')

</div>
</x-app-layout>
