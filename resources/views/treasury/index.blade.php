<x-app-layout>
    <div class="container mx-auto p-4 md:p-6 space-y-6 bg-white dark:bg-zinc-900 text-gray-900 dark:text-white min-h-screen">
        
        <!-- Presidential Administrative Campus Filter Panel -->
        @if($isPresident)
            <div class="p-6 bg-gray-50 dark:bg-zinc-800/40 rounded-xl border border-gray-200 dark:border-zinc-800/80">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Campus Funds Breakdown</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Ledger audit breakdown per physical location.</p>
                    </div>
                    
                    <!-- Filtering Form Context Trigger -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <select name="campus_filter" onchange="this.form.submit()" class="text-sm rounded-lg bg-white dark:bg-zinc-900 border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-300 focus:ring-blue-500 focus:border-blue-500 min-w-[200px]">
                            <option value="">All Campuses (Global View)</option>
                            @foreach($allCampuses as $campus)
                                <option value="{{ $campus['id'] }}" {{ $selectedCampusId == $campus['id'] ? 'selected' : '' }}>
                                    {{ $campus['acronym'] }} — (₱{{ number_format($campus['latest_balance'], 2) }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <!-- Horizontal Quick Scroll Badges for President -->
                <div class="flex gap-3 overflow-x-auto mt-4 pb-2 scrollbar-none">
                    {{-- @foreach($campusBalances as $campus)
                        <div class="shrink-0 bg-white dark:bg-zinc-900 px-3 py-1.5 rounded-lg border {{ $selectedCampusId == $campus['id'] ? 'border-blue-500 bg-blue-50/10' : 'border-gray-200 dark:border-zinc-800' }} text-xs">
                            <span class="font-bold text-gray-500 dark:text-gray-400">{{ $campus['acronym'] }}:</span>
                            <span class="font-mono font-semibold ml-1 text-gray-900 dark:text-white">₱{{ number_format($campus['latest_balance'], 2) }}</span>
                        </div>
                    @endforeach --}}
                     @foreach($filteredCampuses as $campus)
                        <div class="shrink-0 bg-white dark:bg-zinc-900 px-3 py-1.5 rounded-lg border {{ $selectedCampusId == $campus->id ? 'border-blue-500 bg-blue-50/10' : 'border-gray-200 dark:border-zinc-800' }} text-xs">
                            <span class="font-bold text-gray-500 dark:text-gray-400">{{ $campus->acronym }}:</span>
                            <span class="font-mono font-semibold ml-1 text-gray-900 dark:text-white">₱{{ number_format($campus->latest_balance, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Top Dashboard Metrics Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">

            <!-- Card: Treasury Fund Balance -->
            <div class="md:col-span-1 flex flex-col justify-between bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm p-6 min-h-[160px]">
                <div>
                    <h3 class="text-xs uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400">
                        {{ $isPresident && !$selectedCampusId ? 'Global Cash Balance Pool' : 'Treasury Fund Balance' }}
                    </h3>
                </div>
                <div class="flex items-baseline space-x-1 mt-4">
                    <span class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">₱</span>
                    <span class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white tracking-tight">
                        {{ number_format($isPresident && !$selectedCampusId ? $totalGlobalBalance : $currentBalance, 2) }}
                    </span>
                </div>
            </div>

            <!-- Right Side Stacked Cards (Contributions & Disbursements) -->
            <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">

                <!-- Card: Contributions -->
                <div class="flex flex-col justify-between bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm p-6 min-h-[120px]">
                    <div>
                        <h3 class="text-xs uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400">
                            Contributions
                        </h3>
                    </div>
                    <div class="flex items-baseline space-x-1 mt-2">
                        <span class="text-2xl font-bold text-green-600 dark:text-green-400">₱</span>
                        <span class="text-2xl font-bold text-green-600 dark:text-green-400 tracking-tight">
                            {{ number_format($totalContributions, 2) }}
                        </span>
                    </div>
                </div>

                <!-- Card: Disbursements -->
                <div class="flex flex-col justify-between bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm p-6 min-h-[120px]">
                    <div>
                        <h3 class="text-xs uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400">
                            Disbursements
                        </h3>
                    </div>
                    <div class="flex items-baseline space-x-1 mt-2">
                        <span class="text-2xl font-bold text-red-600 dark:text-red-400">₱</span>
                        <span class="text-2xl font-bold text-red-600 dark:text-red-400 tracking-tight">
                            {{ number_format($totalDisbursements, 2) }}
                        </span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Bottom Section: Transaction Ledger Table -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm p-4 md:p-6">
            <div class="border-b border-gray-200 dark:border-zinc-800 pb-4 mb-4 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Transaction Ledger
                </h3>
                @if($isPresident && $selectedCampusId)
                    <span class="px-2.5 py-1 text-xs font-semibold rounded bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 uppercase">
                        Filtered View
                    </span>
                @endif
            </div>

            <!-- Responsive Table Element Wrapper -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-zinc-800 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-4 py-3">Date</th>
                            @if($isPresident)
                                <th scope="col" class="px-4 py-3">Campus</th>
                            @endif
                            <th scope="col" class="px-4 py-3">Description</th>
                            <th scope="col" class="px-4 py-3">Reference</th>
                            <th scope="col" class="px-4 py-3">Type</th>
                            <th scope="col" class="px-4 py-3">Amount</th>
                            <th scope="col" class="px-4 py-3">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($entries as $entry)
                            <tr class="bg-white hover:bg-gray-50/70 dark:bg-zinc-900 dark:hover:bg-zinc-800/50 transition-colors duration-150">
                                <!-- Formatted Date String -->
                                <td class="px-4 py-3.5 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                    {{ $entry->created_at->format('Y-m-d') }}
                                </td>
                                
                                <!-- Render Campus context if President account type -->
                                @if($isPresident)
                                    <td class="px-4 py-3.5 font-bold text-xs tracking-wide text-blue-600 dark:text-blue-400">
                                        {{ $entry->campus?->acronym ?? 'SYS' }}
                                    </td>
                                @endif
                                
                                <td class="px-4 py-3.5 font-medium text-gray-900 dark:text-white">
                                    {{ $entry->description ?? 'No description provided' }}
                                </td>
                                <td class="px-4 py-3.5 font-mono text-xs text-gray-400">
                                    {{ $entry->reference_number ?? '—' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    @if(strtolower($entry->type ?? '') === 'credit' || ($entry->credit ?? 0) > 0)
                                        <span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            Credit
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                            Debit
                                        </span>
                                    @endif
                                </td>
                                                                <td class="px-4 py-3.5 font-semibold {{ (strtolower($entry->type ?? '') === 'credit' || ($entry->credit ?? 0) > 0) ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white' }}">
                                    ₱{{ number_format($entry->amount, 2) }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-900 dark:text-white font-medium">
                                    ₱{{ number_format($entry->running_balance, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isPresident ? '7' : '6' }}" class="text-center py-12 text-gray-400 dark:text-zinc-500 text-xs">
                                    No transaction logs recorded inside this ledger context matching selection criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Section -->
            <div class="mt-4">
                {{ $entries->appends(request()->query())->links() }}
            </div>
        </div>

    </div>
</x-app-layout>

