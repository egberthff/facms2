        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm p-4 md:p-6">
            
            <!-- Table Header Toolbar Controls -->
            <div class="pb-4 mb-4 border-b border-gray-200 dark:border-zinc-800 flex flex-col md:flex-row md:items-center md:justify-between gap-3 justify-end">
                {{-- <div class="text-sm text-gray-600 dark:text-gray-400">
                    Showing <span class="font-semibold text-gray-900 dark:text-white" x-text="filteredBatches().length"></span> contribution batch(es)
                </div> --}}
                <input
                    type="search"
                    x-model="search"
                    @input="page = 1" 
                    placeholder="Search by batch, claimant, or claim type..."
                    class="w-full md:w-80 text-sm p-2.5 rounded-lg bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <!-- Responsive Table Element Wrapper -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-zinc-800 dark:text-gray-400">
                        <tr>
                            <!-- Column 1: Primary System Key -->
                            <th scope="col" class="px-4 py-3" x-text="contributionType === 'claims' ? 'Batch ID' : 'Contribution ID'"></th>
                            <th scope="col" class="px-4 py-3" x-text="contributionType === 'claims' ? 'Claim ID' : 'Contribution Year'"></th>
                            <th scope="col" class="px-4 py-3" x-text="contributionType === 'claims' ? 'Claimant' : 'Member'"></th>
                            <!-- Column 4: Administrative Scope Profile -->
                            @if($isPresident)
                                <th scope="col" class="px-4 py-3">Campus</th>
                            @endif
                            <!-- Financial Columns Metrics -->
                            <th scope="col" class="px-4 py-3 text-right">Collectible Amount</th>
                            <th scope="col" class="px-4 py-3 text-right">Collected Amount</th>
                            <th scope="col" class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <!-- Dynamic Alpine Loop Engine -->
                        <template x-for="batch in paginatedBatches" :key="batch.id">
                            <tr class="bg-white hover:bg-gray-50/70 dark:bg-zinc-900 dark:hover:bg-zinc-800/50 transition-colors duration-150">
                                <!-- Column 1: Dynamic Primary Key (Renders in ALL view states) -->
                                <td class="px-4 py-3.5 font-mono text-xs text-gray-600 dark:text-gray-400" x-text="contributionType === 'claims' ? batch.id : batch.claim_number"></td>
                                <td class="px-4 py-3.5 font-mono text-xs text-gray-600 dark:text-gray-400" x-text="contributionType === 'claims' ? batch.claim_number || 'N/A' : batch.claim_id || 'N/A'"></td>
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-gray-900 dark:text-white" x-text="batch.claimant || batch.member_name || 'N/A'"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400" x-text="batch.claim_type || batch.contribution_type_label || 'Standard'"></div>
                                </td>
                                <!-- Column 4: Optional Campus context flag column for Administrative access profiles -->
                                @if($isPresident)
                                    <td class="px-4 py-3.5 font-bold text-xs tracking-wide text-blue-600 dark:text-blue-400" x-text="batch.campus_acronym || 'SYS'"></td>
                                @endif
                                <!-- Financial Values Formatting Cells -->
                                <td class="px-4 py-3.5 text-right font-semibold text-gray-900 dark:text-white" x-text="'₱' + Number(batch.collectible_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></td>
                                <td class="px-4 py-3.5 text-right font-semibold" :class="Number(batch.collected_amount) >= Number(batch.collectible_amount) ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400'" x-text="'₱' + Number(batch.collected_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></td>

                                <!-- Action Interactive Trigger Element Button -->
                                <td class="px-4 py-3.5 text-center">
                                    <button
                                        type="button"
                                        @click="selectBatch(batch)"
                                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition"
                                    >
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        </template>
                    
                        <!-- Fallback empty collection template layout state -->
                        <template x-if="filteredBatches().length === 0">
                            <tr>
                                <!-- Safe dynamic calculation fallback mapping across layout options -->
                                <td :colspan="contributionType === 'claims' ? ({{ $isPresident ? 1 : 0 }} + 7) : ({{ $isPresident ? 1 : 0 }} + 6)" class="text-center py-12 text-gray-400 dark:text-zinc-500 text-xs">
                                    No records match your filters or search terms inside this workspace view.
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Main Table Pagination Toolbar Controls -->
                <div class="flex items-center justify-between border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-zinc-900 px-4 py-3.5 mt-4" x-show="totalBatchesCount > 0">
                    <div class="text-sm text-gray-700 dark:text-gray-400">
                        Showing 
                        <span class="font-semibold text-gray-900 dark:text-white" x-text="((page - 1) * perPage) + 1"></span> to 
                        <span class="font-semibold text-gray-900 dark:text-white" x-text="Math.min(page * perPage, totalBatchesCount)"></span> of 
                        <span class="font-semibold text-gray-900 dark:text-white" x-text="totalBatchesCount"></span> batches
                    </div>
                    
                    <div class="inline-flex items-center space-x-2">
                        <button 
                            type="button"
                            @click="page = Math.max(page - 1, 1)" 
                            :disabled="page === 1"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-700 transition disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            Previous
                        </button>
                        <template x-for="pageNumber in totalBatchesPages" :key="pageNumber">
                            <button
                                type="button"
                                @click="page = pageNumber"
                                x-text="pageNumber"
                                :aria-current="page === pageNumber ? 'page' : null"
                                :class="page === pageNumber
                                    ? 'bg-blue-600 text-white border-blue-600'
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:bg-zinc-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-zinc-700'"
                                class="min-w-8 px-2.5 py-1.5 text-xs font-semibold rounded-lg border transition"
                            ></button>
                        </template>
                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="'Page ' + page + ' of ' + totalBatchesPages"></span>
                        <button 
                            type="button"
                            @click="page = Math.min(page + 1, totalBatchesPages)" 
                            :disabled="page >= totalBatchesPages"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-700 transition disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            Next
                        </button>
                    </div>
                </div>

            </div>
        </div>