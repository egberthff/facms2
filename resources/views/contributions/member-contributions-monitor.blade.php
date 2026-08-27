<x-app-layout>
<div class="max-w-7xl mx-auto p-4" 
    x-data="{ 
            currentStatus: 'all', 
            statuses: ['all', ...{{ json_encode(array_column(App\Enums\ContributionStatus::cases(), 'value')) }}], 
            searchText: '',
            openModal: false,
            openPaymentModal: false,
            openAnnualModal : false,
            activeAnnual: {},
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
        <!-- Header filter -->
        @include('contributions.partials.member.filter') 
        
        <!-- Table -->
        <div class="overflow-x-auto">
            @if($contribution_type === 'annual')
                <!-- ANNUAL CONTRIBUTION TABLE -->
                @include('contributions.partials.member.annual-table')
            @else
                <!-- CLAIMS TABLE (Original Layout) -->
                @include('contributions.partials.member.claims-table')
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
