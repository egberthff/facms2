<div id="member-claims-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden bg-gray-900/50 overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-6xl max-h-full">
        <!-- Modal content -->
         <div class="relative bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-2xl p-4 md:p-6">
            <!-- Modal header -->
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-zinc-800 pb-4 md:pb-5">
                <h3 id="claims-modal-title" class="text-lg font-medium text-gray-900 dark:text-white">
                    Member Claims History
                </h3>
                <button type="button" class="text-gray-500 bg-transparent hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-zinc-800 dark:hover:text-white rounded-lg text-sm w-9 h-9 ms-auto inline-flex justify-center items-center" data-modal-hide="member-claims-modal">
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/></svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>

            <!-- Balance Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 py-4 md:py-5 border-b border-gray-200 dark:border-zinc-800">
                <!-- Card: Approved Balance -->
                <div class="p-4 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm">
                    <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Approved Balance</p>
                    <p id="balance-approved" class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                        ₱0.00
                    </p>
                </div>
            
                <!-- Card: Actual Balance -->
                <div class="p-4 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm">
                    <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Actual Balance</p>
                    <p id="balance-actual" class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                        ₱0.00
                    </p>
                </div>
            
                <!-- Card: Base Limit -->
                <div class="p-4 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm">
                    <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Base Limit</p>
                    <p id="balance-base-limit" class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                        ₱0.00
                    </p>
                </div>
            
                <!-- Card: Total Approved Cycle -->
                <div class="p-4 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm">
                    <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Total Approved Cycle</p>
                    <p id="balance-approved-cycle" class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                        0
                    </p>
                </div>
            
                <!-- Card: Total Disbursed Cycle -->
                <div class="p-4 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm">
                    <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Total Disbursed Cycle</p>
                    <p id="balance-disbursed-cycle" class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                        0
                    </p>
                </div>
            
                <!-- Card: Cycle Start Date -->
                <div class="p-4 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm">
                    <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Cycle Start Date</p>
                    <p id="balance-cycle-start" class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">
                        -
                    </p>
                </div>
            
            </div>

            <!-- Modal body (Claims Table) -->
            <div class="py-4 md:py-6 overflow-x-auto">
                <table id="member-claims-table" class="w-full text-sm text-left text-gray-500 dark:text-zinc-400 border-separate border-spacing-0">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-zinc-800/80 dark:text-zinc-300">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 font-semibold border-b border-gray-200 dark:border-zinc-700">Claim Number</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold border-b border-gray-200 dark:border-zinc-700">Submitted Date</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold border-b border-gray-200 dark:border-zinc-700">Requested</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold border-b border-gray-200 dark:border-zinc-700">Status</th>
                        </tr>
                    </thead>
                    <tbody id="members-claims-table-body" class="divide-y divide-gray-200 dark:divide-zinc-800">
                        {{-- @forelse ($claims as $claim)
                            <tr class="bg-white dark:bg-zinc-900/50 hover:bg-gray-50 dark:hover:bg-zinc-800/60 transition-colors duration-150">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $claim->claim_number }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-zinc-400 whitespace-nowrap">
                                    {{ $claim->submitted_at->format('Y-m-d') }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    ₱{{ number_format($claim->requested_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-md border
                                        @if($claim->status === 'Approved' || $claim->status === 'Disbursed') 
                                            bg-green-50 text-green-700 border-green-200 dark:bg-green-950/40 dark:text-green-400 dark:border-green-900/50
                                        @elseif($claim->status === 'Pending' || $claim->status === 'Processing') 
                                            bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900/50
                                        @else 
                                            bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-400 dark:border-red-900/50 
                                        @endif">
                                        <span class="w-1.5 h-1.5 me-1.5 rounded-full 
                                            @if($claim->status === 'Approved' || $claim->status === 'Disbursed') bg-green-500
                                            @elseif($claim->status === 'Pending' || $claim->status === 'Processing') bg-amber-500
                                            @else bg-red-500 @endif">
                                        </span>
                                        {{ $claim->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-400 dark:text-zinc-500">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="w-8 h-8 text-gray-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://w3.org"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                        <span>No claims history found for this member.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse --}}
                    </tbody>
                </table>
            </div>
            

            <!-- Modal Footer -->
            <div class="flex items-center justify-end border-t border-gray-200 dark:border-zinc-800 pt-4 md:pt-5">
                 <button type="button" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-zinc-800 dark:text-gray-300 dark:border-zinc-700 dark:hover:bg-zinc-700" data-modal-hide="member-claims-modal" data-modal-dismiss="member-claims-modal">Close</button>
            </div>
        </div>
    </div>
</div>
