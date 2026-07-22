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
            
            <!-- Modal body (Claims Table) -->
            <div class="py-4 md:py-6 overflow-x-auto">
                <table id="member-claims-table" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-zinc-800 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-4 py-3">Claim Number</th>
                            <th scope="col" class="px-4 py-3">Submitted Date</th>
                            <th scope="col" class="px-4 py-3">Requested</th>
                            {{-- <th scope="col" class="px-4 py-3">Approved</th> --}}
                            <th scope="col" class="px-4 py-3">Status</th>
                            {{-- <th scope="col" class="px-4 py-3">Description</th> --}}
                        </tr>
                    </thead>
                    <tbody id="members-claims-table-body">
                
                        {{-- @forelse ($claims as $claim)
                            <tr class="bg-white border-b dark:bg-zinc-900 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $claim->claim_number }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $claim->submitted_at->format('Y-m-d') }}
                                </td>
                                <td class="px-4 py-3">
                                    ₱{{ number_format($claim->requested_amount, 2) }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $claim->approved_amount ? '₱'.number_format($claim->approved_amount, 2) : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-0.5 text-xs font-medium rounded-full 
                                        @if($claim->status === 'Approved' || $claim->status === 'Disbursed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                        @elseif($claim->status === 'Pending' || $claim->status === 'Processing') bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400
                                        @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 @endif">
                                        {{ $claim->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 max-w-xs truncate" title="{{ $claim->description }}">
                                    {{ $claim->description ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No claims history found for this member.
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
