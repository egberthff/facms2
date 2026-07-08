<x-app-layout>
<div class="max-w-7xl mx-auto p-4" 
    x-data="{ 
            currentStatus: 'all', 
            searchText: '',
            openModal: false,
            activeClaim: {},
            matches(status, number, desc, type) {
                const matchesStatus = this.currentStatus === 'all' || status.toLowerCase() === this.currentStatus.toLowerCase();
                const searchLower = this.searchText.toLowerCase();
                const matchesSearch = !this.searchText || 
                                     number.toLowerCase().includes(searchLower) || 
                                     desc.toLowerCase().includes(searchLower) ||
                                     type.toLowerCase().includes(searchLower);
                return matchesStatus && matchesSearch;
            }
         }">
    
    <!-- ========================================= -->
    <!-- CLAIMS LIST CARD -->
    <!-- ========================================= -->
    <div class="bg-white border border-blue-700 rounded-2xl shadow-sm">
        <!-- Header -->
        <div class="p-4 border-b">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <!-- Filters -->
                <div class="flex flex-wrap gap-2">
                    <template x-for="status in ['all', 'Pending', 'Processing', 'Approved', 'Disbursed', 'Rejected']">
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
                        <th class="px-4 py-3 text-left font-semibold">Claim Type</th>
                        <th class="px-4 py-3 text-left font-semibold">Description</th>
                        <th class="px-4 py-3 text-right font-semibold">Amount</th>
                        <th class="px-4 py-3 text-center font-semibold">Date Filed</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($claims as $claim)
                    <tr x-show="matches(
                            '{{ $claim->status }}', 
                            '{{ $claim->claim_number }}', 
                            '{{ addslashes($claim->remarks ?? $claim->description) }}', 
                            '{{ $claim->claimType->name }}'
                        )"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100">
                        <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-900">{{ $claim->claim_number }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $claim->claimType->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ Str::limit($claim->description ?? $claim->remarks, 40) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format($claim->requested_amount, 2) }}</td>
                        <td class="px-4 py-3 text-center text-slate-500">{{ $claim->submitted_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $claim->status === 'Pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                {{ $claim->status === 'Approved' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                {{ $claim->status === 'Rejected' ? 'bg-rose-100 text-rose-800' : '' }}
                                {{ $claim->status === 'Processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $claim->status === 'Disbursed' ? 'bg-purple-100 text-purple-800' : '' }}
                            ">
                                {{ $claim->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <button
                                type="button"
                                @click="activeClaim = @js($claim); console.error(activeClaim); openModal = true;"
                                class="inline-flex items-center px-3 py-1.5 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg text-xs font-semibold hover:bg-blue-100 transition">
                                View Details
                            </button>
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
                class="relative w-full max-w-3xl transform overflow-hidden rounded-2xl bg-white p-6 shadow-2xl transition-all border border-slate-100">

                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
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
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="bg-blue-50/50 border border-blue-100 p-4 rounded-xl">
                            <span class="block text-xs font-semibold text-blue-500 uppercase tracking-wider mb-1">Requested Amount</span>
                            <span class="text-xl font-black text-blue-900" x-text="'₱' + activeClaim.requested_amount"></span>
                        </div>
                        <div class="p-4 rounded-xl border border-slate-100" :class="activeClaim.approved_amount && activeClaim.approved_amount !== 'N/A' ? 'bg-emerald-50/50 border-emerald-100' : 'bg-slate-50/50'">
                            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1" :class="activeClaim.approved_amount && activeClaim.approved_amount !== 'N/A' ? 'text-emerald-500' : ''">Approved Amount</span>
                            <span class="text-xl font-black" :class="activeClaim.approved_amount && activeClaim.approved_amount !== 'N/A' ? 'text-emerald-900' : 'text-slate-400'" x-text="activeClaim.approved_amount && activeClaim.approved_amount !== 'N/A' ? '₱' + activeClaim.approved_amount : '—'"></span>
                        </div>
                        <div class="bg-slate-50/50 border border-slate-100 p-4 rounded-xl">
                            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Annual Available Limit</span>
                            <span class="text-xl font-bold text-slate-700" x-text="'₱' + activeClaim.annual_limit"></span>
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

                        <div class="p-4 rounded-xl border" :class="activeClaim.remarks && activeClaim.remarks !== 'No remarks provided.' ? 'bg-amber-50/40 border-amber-100' : 'bg-slate-50/30 border-slate-100'">
                            <span class="block text-xs font-bold uppercase tracking-wider mb-1" :class="activeClaim.remarks && activeClaim.remarks !== 'No remarks provided.' ? 'text-amber-700' : 'text-slate-400'">Processor Evaluation Remarks</span>
                            <p class="leading-relaxed whitespace-pre-line text-xs" :class="activeClaim.remarks && activeClaim.remarks !== 'No remarks provided.' ? 'text-slate-800 font-medium' : 'text-slate-400 italic'" x-text="activeClaim.remarks"></p>
                        </div>
                    </div>

                    <!-- Section 4: Workflow Lifecycle Status Timeline Logs -->
                    <div class="border border-slate-100 rounded-xl p-4 bg-white shadow-sm">
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Processing Timeline Logs</span>
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
                                        <a :href="'/storage/' + doc.file_path" target="_blank" class="inline-flex items-center gap-1 font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100/70 px-2.5 py-1 rounded transition shrink-0">
                                            Download File
                                        </a>
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
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4 mt-5">
                    <button 
                        type="button" 
                        @click="openModal = false" 
                        class="px-5 py-2 text-sm font-semibold text-slate-600 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl transition">
                        Close Sheet Overview
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
