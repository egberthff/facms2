        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm p-4 md:p-6">
            
            <!-- Table Header Toolbar Controls -->
           <!-- <div class="pb-4 mb-4 border-b border-gray-200 dark:border-zinc-800 flex flex-col md:flex-row md:items-center md:justify-between gap-3 justify-end">
                {{-- <div class="text-sm text-gray-600 dark:text-gray-400">
                    Showing <span class="font-semibold text-gray-900 dark:text-white" x-text="filteredBatches().length"></span> contribution batch(es)
                </div> --}}
                <input
                    type="search"
                    x-model="search"
                    @input="document.querySelector('.dt-table')?.dataTableInstance?.search(search)"
                    placeholder="Search by batch, claimant, or claim type..."
                    class="w-full md:w-80 text-sm p-2.5 rounded-lg bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div> -->

            <!-- Responsive Table Element Wrapper -->
            <div class="overflow-x-auto">
                @if($contribution_type === 'annual')
                    @php
                        $annualYears = collect($batches)
                            ->pluck('claim_id')
                            ->filter(fn ($year) => filled($year))
                            ->unique()
                            ->sortDesc();
                    @endphp
                    <div class="mb-4 flex items-center gap-3">
                        <label for="annual-year-filter" class="text-sm font-medium text-gray-700 dark:text-gray-300">Contribution Year</label>
                        <select
                            id="annual-year-filter"
                            class="w-40 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-300"
                            @change="document.querySelector('.dt-table')?.dataTableInstance?.search($event.target.value)">
                            <option value="">All Years</option>
                            @foreach($annualYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <table
                    class="dt-table w-full text-sm text-left text-gray-500 dark:text-gray-400"
                    data-datatable-defer
                    x-init="$nextTick(() => window.initializeDataTable($el))">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-zinc-800 dark:text-gray-400">
                        <tr>
                            <!-- Column 1: Primary System Key -->
                            <th scope="col" class="px-4 py-3">{{ $contribution_type === 'claims' ? 'Batch ID' : 'Contribution ID' }}</th>
                            <th scope="col" class="px-4 py-3">{{ $contribution_type === 'claims' ? 'Claim ID' : 'Contribution Year' }}</th>
                            <th scope="col" class="px-4 py-3">{{ $contribution_type === 'claims' ? 'Claimant' : 'Member' }}</th>
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
                        @forelse($batches as $batch)
                            <tr class="bg-white hover:bg-gray-50/70 dark:bg-zinc-900 dark:hover:bg-zinc-800/50 transition-colors duration-150">
                                <!-- Column 1: Dynamic Primary Key (Renders in ALL view states) -->
                                <td class="px-4 py-3.5 font-mono text-xs text-gray-600 dark:text-gray-400">
                                    {{ $contribution_type === 'claims' ? $batch['id'] : $batch['claim_number'] }}
                                </td>
                                <td class="px-4 py-3.5 font-mono text-xs text-gray-600 dark:text-gray-400">
                                    {{ $contribution_type === 'claims' ? ($batch['claim_number'] ?: 'N/A') : ($batch['claim_id'] ?: 'N/A') }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $batch['claimant'] ?: 'N/A' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $batch['claim_type'] ?: 'Standard' }}</div>
                                </td>
                                <!-- Column 4: Optional Campus context flag column for Administrative access profiles -->
                                @if($isPresident)
                                    <td class="px-4 py-3.5 font-bold text-xs tracking-wide text-blue-600 dark:text-blue-400">{{ $batch['campus_acronym'] ?: 'SYS' }}</td>
                                @endif
                                <!-- Financial Values Formatting Cells -->
                                @php
                                    $collectibleAmount = (float) ($batch['collectible_amount'] ?? $batch['amount_paid'] ?? $batch['amount'] ?? 0);
                                    $collectedAmount = (float) ($batch['collected_amount'] ?? $batch['amount_collected'] ?? 0);
                                @endphp
                                <td class="px-4 py-3.5 text-right font-semibold text-gray-900 dark:text-white">₱{{ number_format($collectibleAmount, 2) }}</td>
                                <td class="px-4 py-3.5 text-right font-semibold {{ $collectedAmount >= $collectibleAmount ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">₱{{ number_format($collectedAmount, 2) }}</td>

                                <!-- Action Interactive Trigger Element Button -->
                                <td class="px-4 py-3.5 text-center">
                                    <button
                                        type="button"
                                        @click="selectBatch(@js($batch))"
                                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition"
                                    >
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isPresident ? 7 : 6 }}" class="text-center py-12 text-gray-400 dark:text-zinc-500 text-xs">
                                    No contribution records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>