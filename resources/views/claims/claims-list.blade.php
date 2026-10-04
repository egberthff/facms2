<x-app-layout>
@php
    $claimStatuses = array_map(
        fn ($status) => $status->value,
        \App\Enums\ClaimStatus::cases()
    );
    $claimStatusCounts = $claims->countBy('status')->all();
@endphp
<div class="max-w-auto mx-auto p-2" 
    x-data="{ 
            currentStatus: 'all', 
            selectedCycle: 'current',
            cycles: @js($cycles),
            totalClaims: {{ $claims->count() }},
            statusCounts: @js($claimStatusCounts),
            openModal: false,
            activeClaim: {},
            init() {
            // Hook directly into DataTables global filtering engine
            if (window.DataTable) {
                DataTable.ext.search.push((settings, data) => {
                    // Filter Checkpoint A: Status
                    if (this.currentStatus !== 'all') {
                        const statusColumnIndex = 4; // ⚠️ Update to your actual status column index
                        const rowStatus = data[statusColumnIndex]?.toLowerCase().trim();
                        if (rowStatus !== this.currentStatus.toLowerCase().trim()) return false;
                    }

                    // Filter Checkpoint B: Year Range Cycle
                    if (/^\d{4}-\d{4}$/.test(this.selectedCycle ?? '')) { // legacy year-range values only; member cycle options don't map to years
                        const [startYear, endYear] = this.selectedCycle.split('-').map(Number);
                        const submittedAtColumnIndex = 3; // ⚠️ Update to your actual submitted_at column index
                        
                        const rawDateText = data[submittedAtColumnIndex];
                        const yearMatch = rawDateText ? rawDateText.match(/\b\d{4}\b/) : null;
                        if (!yearMatch) return false;

                        const claimYear = parseInt(yearMatch[0], 10);
                        if (claimYear < startYear || claimYear > endYear) return false;
                    }

                    return true; // Keep row if it satisfies both rules
                });
            }
        },
            
             filterClaims(status) {
            this.currentStatus = status;
            this.redrawTable();
        },

        filterClaimsByCycle() {
            this.redrawTable();
        },

redrawTable() {
    const tableEl = document.querySelector('.dt-table');
    if (!tableEl) return;

    const dataTable = tableEl.dataTableInstance;
    
    if (dataTable) {
        let searchQueries = [];

        // 1. Core Status Filtering
        if (this.currentStatus !== 'all') {
            searchQueries.push(this.currentStatus);
        }

        // 2. Mathematical Cycle Range Translation
        if (/^\d{4}-\d{4}$/.test(this.selectedCycle ?? '')) { // legacy year-range values only; member cycle options don't map to years
            const [startYear, endYear] = this.selectedCycle.split('-').map(Number);
            
            let acceptableYears = [];
            for (let year = startYear; year <= endYear; year++) {
                acceptableYears.push(year);
            }

            searchQueries.push(acceptableYears.join(' ')); 
        }

        const finalQuery = searchQueries.join(' ');
        dataTable.search(finalQuery);
    }
}
,
            filteredCount() {
                return this.currentStatus === 'all'
                    ? this.totalClaims
                    : (this.statusCounts[this.currentStatus] || 0);
            }
         }"  @close-modal.window="openModal = false">
    
        <!-- ========================================= -->
        <!-- CAMPUS FILTER -->
        <!-- ========================================= -->
         @include('claims.partials.campus-filter')

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs">
    <!-- ===================================================== -->
    <!-- HEADER -->
    <!-- ===================================================== -->
    <div class="border-b border-slate-200 px-4 py-4 sm:px-6 sm:py-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div>
    <h2 class="text-lg font-semibold text-slate-900 sm:text-xl">
        Claims {{ ($isMemberMenu || Auth::user()->hasRole('Member')) ? 'History' : 'Management' }}
    </h2>
    
    @if(!$isMemberMenu && !Auth::user()->hasRole('Member'))
        <p class="mt-1 text-sm text-slate-500">
            Review, approve, reject, and monitor submitted claims.
        </p>
    @endif
</div>

            <div class="flex items-center gap-3">
                <div class="text-right">
                    <div class="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">
                        <span x-text="filteredCount()"></span>
                    </div>
                    <div class="text-xs uppercase tracking-wide text-slate-500">
                        <span x-text="currentStatus === 'all' ? 'Total Claims' : currentStatus + ' Claims'"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ===================================================== -->
    <!-- FILTER TOOLBAR -->
    <!-- ===================================================== -->
    <!-- 1. Wrapped the entire filters section in a single clean x-data element -->
<div class="border-b border-slate-100 px-4 py-3 sm:px-6">
    
    <div class="flex flex-col xl:flex-row gap-3 xl:items-center">
        {{-- Status Filters --}}
        {{-- <div class="flex flex-wrap gap-2">
            <template x-for="status in ['all', ...@js($claimStatuses)]">
                <button 
                    type="button"
                    @click="filterClaims(status)"
                    :class="currentStatus === status 
                        ? 'bg-blue-400 text-blue-600 border-blue-600' 
                        : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                    class="px-4 py-2 text-sm rounded-lg border transition font-medium capitalize"
                    x-text="status === 'all' ? 'All' : status">
                </button>
            </template>
        </div> --}}
        <div class="relative inline-block text-left" x-data="{ open: false }" @click.outside="open = false">
            <!-- Dropdown Trigger Button -->
            <button 
                type="button"
                @click="open = !open"
                class="inline-flex w-full min-w-40 items-center justify-between gap-x-2 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-xs transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30">
                
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 font-normal">Status:</span>
                    <!-- Dynamically shows current selected status -->
                    <span class="capitalize font-semibold text-slate-800" x-text="currentStatus === 'all' ? 'All' : currentStatus"></span>
                </div>
        
                <!-- Chevron Icon -->
                <svg class="h-4 w-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
        
            <!-- Dropdown Menu Options Panel -->
            <div 
                x-show="open"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute left-0 z-30 mt-2 max-h-60 w-56 origin-top-left overflow-y-auto rounded-xl border border-slate-200 bg-white py-1 shadow-lg ring-1 ring-black/5 focus:outline-none"
                style="display: none;">
                
                <!-- Loop through statuses -->
                <template x-for="status in ['all', ...@js($claimStatuses)]">
                    <button 
                        type="button"
                        @click="filterClaims(status); open = false"
                        :class="currentStatus === status 
                            ? 'bg-blue-50 text-blue-600 font-semibold' 
                            : 'text-slate-700 hover:bg-slate-50 font-normal'"
                        class="flex w-full items-center px-4 py-2.5 text-sm transition capitalize text-left"
                        x-text="status === 'all' ? 'All' : status">
                    </button>
                </template>
            </div>
        </div>
        

        {{-- Dropdown and Utility Controls --}}
        <div class="flex-1 flex gap-2 justify-end">
            {{-- <select 
                x-model="selectedCycle"
                @change="filterClaimsByCycle()"
                class="px-4 py-2 text-sm rounded-lg border border-slate-200 bg-white text-slate-600 font-medium transition focus:border-blue-600 focus:outline-none">                       

                <template x-for="cycle in cycles" :key="cycle.value">
                    <option :value="cycle.value" x-text="cycle.label"></option>
                </template>
            </select> --}}

            <button
                @click="window.location.reload()"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-xs transition hover:bg-slate-50">
                <x-heroicon-o-arrow-path class="size-5 text-slate-500" />
                Refresh
            </button>
        </div> 
    </div>
</div>

    <!-- ===================================================== -->
    <!-- TABLE -->
    <!-- ===================================================== -->
    <!-- Default Display -->
    <div id="claims-form-container" class="overflow-x-auto p-3 sm:p-4">
        <!-- Claims-based view (Visible by default) -->
        <div id="template-claims-table" style="display: block;">
            @include('claims.partials.claims-table')
        </div>
        
        <!-- Member-based view (Hidden by default) -->
        <div id="template-search-member" style="display: none;">
            @include('claims.partials.claim-search-member')
        </div>
    </div>
    

        {{-- <div id="form-templates" style="display: none;">
            <div id="template-claims-table">
                @include('claims.partials.claims-table')
            </div>
            <div id="template-search-member">
                @include('claims.partials.claim-search-member')
            </div>
        </div> --}}

    </div>
    <!--========================================= -->
    <!-- ALPINE DETAILS MODAL OVERLAY -->
    <!-- ========================================= -->
    <div 
        x-show="openModal" 
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
        @keydown.escape.window="openModal = false">

        <!-- Backdrop Overlay -->
        <div 
            x-show="openModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
            @click="openModal = false"></div>

        <!-- Modal Box Wrapper -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div 
                x-show="openModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-3xl transform overflow-hidden rounded-2xl border border-slate-100 bg-white p-4 shadow-xl transition-all sm:p-6">

                <!-- Modal Header -->
                <div class="mb-5 flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Claim Tracking & Overview</h3>
                        <p class="text-xs text-slate-500 mt-0.5" x-text="'Reference Control No: ' + activeClaim.claim_number + ' | Status: ' + activeClaim.status"></p>
                    </div>
                    <button @click="openModal = false" class="text-slate-400 hover:text-slate-600 transition p-1.5 rounded-lg hover:bg-slate-50">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body Content -->
                <div class="space-y-6 text-sm max-h-[calc(100vh-200px)] overflow-y-auto pr-1">

                    <!-- Section 1: Financial Cards Summary Grid -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="bg-blue-50/50 border border-blue-100 p-4 rounded-xl">
                            <span class="block text-xs font-semibold text-blue-500 uppercase tracking-wider mb-1">Requested Amount</span>
                            <span class="text-lg font-bold tracking-tight text-blue-900 tabular-nums sm:text-xl" x-text="'₱' + Number(activeClaim.requested_amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                        </div>
                        <div class="p-4 rounded-xl border border-slate-100" :class="activeClaim.approved_amount && activeClaim.approved_amount !== 'N/A' ? 'bg-emerald-50/50 border-emerald-100' : 'bg-slate-50/50'">
                            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1" :class="activeClaim.approved_amount && activeClaim.approved_amount !== 'N/A' ? 'text-emerald-500' : ''">Approved Amount</span>
                            <span class="text-lg font-bold tracking-tight tabular-nums sm:text-xl" :class="activeClaim.approved_amount && activeClaim.approved_amount !== 'N/A' ? 'text-emerald-900' : 'text-slate-400'" x-text="activeClaim.approved_amount && activeClaim.approved_amount !== 'N/A' ? '₱' + Number(activeClaim.approved_amount).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '—'"></span>
                        </div>
                        {{-- Remaining allowance across all of the member's claims in this claim's cycle --}}
                        <div class="p-4 rounded-xl border border-slate-100" :class="activeClaim.cycle_summary ? 'bg-emerald-50/70 border-emerald-700' : 'bg-slate-50/50'">
                            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1" :class="activeClaim.cycle_summary ? 'text-emerald-700' : ''">Cycle Remaining</span>
                            <span class="text-lg font-bold tracking-tight tabular-nums sm:text-xl" :class="activeClaim.cycle_summary ? 'text-emerald-600' : 'text-slate-300'" x-text="activeClaim.cycle_summary ? '₱' + Number(activeClaim.cycle_summary.remaining).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—'"></span>
                        </div>
                        <div class="bg-slate-50/50 border border-slate-100 p-4 rounded-xl">
                            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Cycle Limit</span>
                            <span class="text-lg font-bold tracking-tight text-slate-700 tabular-nums sm:text-xl" x-text="'₱' + Number(activeClaim.cycle_summary?.limit ?? activeClaim.annual_limit).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                            <span class="mt-1 block text-[11px] text-slate-500" x-text="activeClaim.cycle_summary ? activeClaim.cycle_summary.label + (activeClaim.cycle_summary.is_active ? '' : ' (ended)') : 'No claim cycle'"></span>
                        </div>
                    </div>

                    <!-- Section 2: Core Processing Info Metadata -->
                    <div class="bg-slate-50/70 rounded-xl p-4 border border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <span class="block text-xs font-medium text-slate-400 uppercase">Benefit / Claim Type</span>
                            <span class="font-semibold text-slate-800 text-base" x-text="activeClaim.claim_type?.description"></span>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-slate-400 uppercase">Preferred Disbursement Method</span>
                            <span class="font-semibold text-slate-800 text-base" x-text="activeClaim.payment_method?.name"></span>
                        </div>
                    </div>

                    <!-- Section 3: Detailed Explanations & Descriptions -->
                    <div class="space-y-4">
                        <div class="bg-slate-50/30 p-4 rounded-xl border border-slate-100">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Claim Description</span>
                            <p class="text-slate-700 leading-relaxed whitespace-pre-line text-xs" x-text="activeClaim.description || 'No descriptions were written during submission.'"></p>
                        </div>

                        {{-- <div class="p-4 rounded-xl border" :class="activeClaim.remarks && activeClaim.remarks !== 'No remarks provided.' ? 'bg-amber-50/40 border-amber-100' : 'bg-slate-50/30 border-slate-100'">
                            <span class="block text-xs font-bold uppercase tracking-wider mb-2" :class="activeClaim.remarks && activeClaim.remarks !== 'No remarks provided.' ? 'text-amber-700' : 'text-slate-400'">
                                Processor Evaluation Remarks
                            </span>
                        
                            @hasrole("President")
                                <!-- Editable input area exclusively for the President -->
                                <textarea 
                                    id="claim-remarks-field"
                                    x-model="activeClaim.remarks" 
                                    placeholder="Type review or evaluation remarks here..."
                                    rows="3"
                                    class="w-full text-xs p-2.5 rounded-lg border border-slate-200 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition font-medium text-slate-800 placeholder-slate-400 focus:outline-none">
                                </textarea>
                            @else
                                <!-- View-only text display container for other user roles -->
                                <p class="leading-relaxed whitespace-pre-line text-xs" :class="activeClaim.remarks && activeClaim.remarks !== 'No remarks provided.' ? 'text-slate-800 font-medium' : 'text-slate-400 italic'" x-text="activeClaim.remarks"></p>
                            @endrole
                        </div> --}}

                    </div>

                    <!-- Section 4: Workflow Lifecycle Status Timeline Logs -->
                    <div class="border border-slate-100 rounded-xl p-4 bg-white shadow-sm">
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Processing Timeline Overview</span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 border-l-2 border-slate-100 pl-4 sm:border-l-0 sm:pl-0 sm:divide-x sm:divide-slate-100">
                            <div>
                            <span class="block text-xs text-slate-400 font-medium">1. Filed / Submitted On</span>
                            <span class="block font-semibold text-slate-700 mt-0.5 text-xs" 
                                  x-text="activeClaim.submitted_at ? new Date(activeClaim.submitted_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : 'N/A'">
                            </span>
                            </div>
                            <div class="sm:pl-4">
                                <span class="block text-xs text-slate-400 font-medium">2. Board Evaluated On</span>
                                <span class="block font-semibold text-slate-700 mt-0.5 text-xs" 
                                  x-text="activeClaim.approved_at ? new Date(activeClaim.approved_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : 'N/A'">
                            </div>
                            <div class="sm:pl-4">
                                <span class="block text-xs text-slate-400 font-medium">3. Released / Disbursed On</span>
                                <span class="block font-semibold text-slate-700 mt-0.5 text-xs" 
                                  x-text="activeClaim.disbursed_at ? new Date(activeClaim.disbursed_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : 'N/A'">
                            </div>
                        </div>
                    </div>

                    <!-- Section 5: Attached Supporting Verification Files Checklist -->
                    <div class="border border-slate-100 rounded-xl p-4 bg-white shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Your Attached Documents</span>
                            <span class="text-[11px] px-2 py-0.5 bg-slate-100 text-slate-500 rounded font-medium" x-text="'Required Document: ' + activeClaim.claim_type?.required_document"></span>
                        </div>

                        <!-- Dynamic Document Checklist Listing Loop -->
                        <div class="divide-y divide-slate-50">
                            <template x-if="activeClaim.documents && activeClaim.documents.length > 0">
                                <!-- 1. Changed loop item name to 'doc' for clarity -->
                                <template x-for="(doc, index) in activeClaim.documents" :key="index">
                                    <div class="flex items-center justify-between py-2.5 text-xs">
                                        <div class="flex items-center gap-2.5 min-w-0 pr-4">
                                            <div class="p-2 bg-slate-50 rounded-lg text-slate-500 shrink-0">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </div>
                                            <div class="min-w-0 truncate">
                                                <!-- 2. Target the local 'doc' loop item directly -->
                                                <span class="font-medium text-slate-800 block truncate" x-text="doc.original_filename"></span>
                                                <!-- 3. Format file size to human-readable KB -->
                                                <span class="text-[10px] text-slate-400" x-text="doc.file_size ? (doc.file_size / 1024).toFixed(1) + ' KB' : '0 KB'"></span>
                                            </div>
                                        </div>
                                        <!-- 4. Dynamically generate the storage asset link path -->
                                        <button type="button" @click="$dispatch('open-file-preview', { url: '/storage/' + doc.file_path, name: doc.original_filename })" class="inline-flex shrink-0 items-center gap-1 rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200 transition hover:bg-blue-100">
                                            View File
                                        </button>
                                    </div>
                                </template>
                            </template>
                        
                            <template x-if="!activeClaim.documents || activeClaim.documents.length === 0">
                                <div class="text-center py-6 text-slate-400 italic text-xs bg-slate-50/50 rounded-lg border border-dashed border-slate-200 mt-1">
                                    No uploaded attachment tracking logs linked to this request record.
                                </div>
                            </template>
                        </div>

                    </div>

                </div> <!-- End of Scrollable Body Component Wrapper -->

                <!-- Modal Action Footer Buttons Area -->
                <div class="mt-5 flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
                    @if(!isset($isMemberMenu) || !$isMemberMenu)
                    @if(Auth::user()->hasAnyRole(['President', 'Treasurer', 'Federated President', 'Federated Treasurer']) || Auth::user()->can('update claims'))

                        <!-- PRESIDENT: Approve or reject pending claims -->
                        <button 
                            type="button" 
                            x-show="@js($userRole === 'President') && activeClaim.status && activeClaim.status.toLowerCase().trim() === 'pending'" 
                            @click="updateClaimStatus(activeClaim.id, 'Approved', activeClaim)" 
                            class="inline-flex items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                            Approve
                        </button>

                        <button 
                            type="button" 
                            x-show="@js($userRole === 'President') && activeClaim.status && activeClaim.status.toLowerCase().trim() === 'pending'" 
                            @click="updateClaimStatus(activeClaim.id, 'Rejected', activeClaim)" 
                            class="inline-flex items-center justify-center rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                            Reject
                        </button>

                        <!-- TREASURER: Forward approved claims or disburse ready claims -->
                        <button 
                            type="button" 
                            x-show="@js($userRole === 'Treasurer') && activeClaim.status && activeClaim.status.toLowerCase().trim() === 'approved'" 
                            @click="updateClaimStatus(activeClaim.id, 'For Review FP', activeClaim)" 
                            class="inline-flex items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                            Forward to FP
                        </button>

                        <button 
                            type="button" 
                            x-show="@js($userRole === 'Treasurer') && activeClaim.status && activeClaim.status.toLowerCase().trim() === 'for disbursement'" 
                            @click="updateClaimStatus(activeClaim.id, 'Disbursed', activeClaim)" 
                            class="inline-flex items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                            Mark as Disbursed
                        </button>

                        <!-- FEDERATED PRESIDENT: Start processing reviewed claims -->
                        <button 
                            type="button" 
                            x-show="@js($userRole === 'Federated President') && activeClaim.status && activeClaim.status.toLowerCase().trim() === 'for review fp'" 
                            @click="updateClaimStatus(activeClaim.id, 'Processing', activeClaim)" 
                            class="inline-flex items-center justify-center rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">
                            Processing
                        </button>

                        <!-- FEDERATED TREASURER: Send processed claims for disbursement -->
                        <button 
                            type="button" 
                            x-show="@js($userRole === 'Federated Treasurer') && activeClaim.status && activeClaim.status.toLowerCase().trim() === 'processing'" 
                            @click="updateClaimStatus(activeClaim.id, 'For Disbursement', activeClaim)" 
                            class="inline-flex items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                            For Disbursement
                        </button>

                    @endif
                    @endif

                    <!-- ALWAYS VISIBLE: Close Button (Active in all states: Pending, Processing, Approved, Rejected, Disbursed) -->
                    <button 
                        type="button" 
                        @click="openModal = false" 
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50">
                        Close Sheet Overview
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleForm(value) {
        const claimsTable = document.getElementById('template-claims-table');
        const searchMember = document.getElementById('template-search-member');

        // Clean up input value
        const selectedValue = value.trim().toLowerCase();

        if (selectedValue === 'claims-based') {
            claimsTable.style.display = 'block';
            searchMember.style.display = 'none';
        } else {
            // Defaults to 'member-based'
            claimsTable.style.display = 'none';
            searchMember.style.display = 'block';
        }
    }

</script>
@include('components.file-preview-modal')
</x-app-layout>

