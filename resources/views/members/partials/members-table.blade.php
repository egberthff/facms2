<x-table id="members-table" class="dt-table dt-responsive">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-gray-700 bg-gray-50 dark:bg-gray-800 dark:text-gray-300"> 
                        <th class="px-6 py-4 font-semibold">Employee ID</th>
                        <th class="px-6 py-4 font-semibold">Member</th>
                        {{-- @if(Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']) || Auth::user()->can('view all campus data')) --}}
                        @if(($isPresident ?? false) || ($isTreasurer ?? false))
                        <th class="px-6 py-4 font-semibold">Campus</th>
                        @endif
                        <th class="px-6 py-4 font-semibold">Work Details</th>
                        {{-- <th class="px-6 py-4 font-semibold">Employment Status</th> --}}
                        <th class="px-6 py-4 font-semibold">Membership Status</th>
                        {{-- <th class="px-6 py-4 font-semibold">Claims</th> --}}
                        {{-- <th class="px-6 py-4 font-semibold">Contributions</th> --}}
                        @if($isPresident ?? false)
                        <th class="px-6 py-4 font-semibold">Action</th>
                        @endif

                     </tr> 
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-center">
                    @if(isset($members) && count($members) > 0)
                        @foreach($members as $member)
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/50 transition-colors duration-150">
                                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white whitespace-nowrap">{{ $member->employee_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ ucwords($member->last_name) }}, {{ ucwords($member->first_name) }} {{ ucwords($member->middle_name) }}, {{ ucwords($member->suffex) }}</td>

                                @if(($isPresident ?? false) || ($isTreasurer ?? false))
                                <td class="px-6 py-4 font-semibold">{{ $member->campus->acronym }}</td>
                                @endif

                                <td class="px-6 py-4 whitespace-nowrap">{{ $member->department }} - {{ $member->position }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $member->membership_status }}</td>

                                <!-- Compacted Actions Column with Dropdown -->
                                {{-- <td class="px-6 py-4 whitespace-nowrap text-right relative" x-data="{ open: false }">
                                    <div class="relative inline-block text-left">
                                        <!-- Dropdown Trigger Button -->
                                        <button @click="open = !open" type="button" class="inline-flex justify-center items-center gap-x-1.5 rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            Actions
                                            <svg class="-mr-1 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                            </svg>
                                        </button>               

                                        <!-- Dropdown Panel -->
                                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none py-1" style="display: none;">

                                            <!-- View Claims Option -->
                                            <button type="button" 
                                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2" 
                                                    data-action="view-claims" 
                                                    data-member-id="{{ $member->id }}"
                                                    data-modal-target="member-claims-modal"
                                                    @click="open = false">
                                                View Claims
                                            </button>               

                                            <!-- View Contributions Option -->
                                            <button type="button" 
                                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2" 
                                                    data-action="view-contributions" 
                                                    data-member-id="{{ $member->id }}"
                                                    data-modal-target="member-contributions-modal"
                                                    @click="open = false">
                                                View Contributions
                                            </button>               

                                            <!-- View/Edit Details Option (President Only) -->
                                            @if($isPresident ?? false)
                                            <button type="button"
                                                    class="w-full text-left px-4 py-2 text-sm text-blue-600 dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 font-semibold flex items-center gap-2 edit-member-btn"
                                                    data-action="edit-member-details"
                                                    data-modal-target="member-modal"
                                                    data-modal-toggle="member-modal"
                                                    data-member-id="{{ $member->id }}"
                                                    data-member-employee-number="{{ $member->employee_number }}"
                                                    data-member-first-name="{{ $member->first_name }}"
                                                    data-member-last-name="{{ $member->last_name }}"
                                                    data-member-middle-name="{{ $member->middle_name }}"
                                                    data-member-suffix="{{ $member->suffix }}"
                                                    data-member-gender="{{ $member->gender }}"
                                                    data-member-birth-date="{{ $member->birth_date }}"
                                                    data-member-department="{{ $member->department }}"
                                                    data-member-position="{{ $member->position }}"
                                                    data-member-employment-status="{{ $member->employment_status }}"
                                                    data-member-membership-status="{{ $member->membership_status }}"
                                                    data-member-active-member-on="{{ $member->active_member_on }}"
                                                    data-member-inactive-member-on="{{ $member->inactive_member_on }}"
                                                    data-contact-number="{{ $member->contact_number }}"
                                                    data-member-address="{{ $member->address }}"
                                                    data-member-gcash-number="{{ $member->gcash_number }}"
                                                    data-member-gcash-account-name="{{ $member->gcash_account_name }}"
                                                    data-member-campus-id="{{ $member->campus_id }}"
                                                    data-member-email="{{ $member->user->email ?? '' }}"
                                                    @click="open = false">
                                                View Details
                                            </button>
                                            @endif              

                                        </div>
                                    </div>
                                </td> --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right relative" x-data="{ open: false }">
                                    <div class="relative inline-block text-left">
                                        <!-- Dropdown Trigger Button -->
                                        <button 
                                            @click="open = !open" 
                                            @keydown.escape.window="open = false"
                                            type="button" 
                                            class="inline-flex justify-center items-center gap-x-2 rounded-lg bg-white dark:bg-gray-800 px-3.5 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/80 transition-all duration-150"
                                            :aria-expanded="open"
                                        >
                                            <span>Actions</span>
                                            <svg 
                                                class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                                :class="{ 'rotate-180': open }"
                                                fill="none" 
                                                stroke="currentColor" 
                                                viewBox="0 0 24 24"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>            
                                
                                        <!-- Dropdown Panel -->
                                        <div 
                                            x-show="open" 
                                            @click.away="open = false" 
                                            x-transition:enter="transition ease-out duration-100" 
                                            x-transition:enter-start="transform opacity-0 scale-95" 
                                            x-transition:enter-end="transform opacity-100 scale-100" 
                                            x-transition:leave="transition ease-in duration-75" 
                                            x-transition:leave-start="transform opacity-100 scale-100" 
                                            x-transition:leave-end="transform opacity-0 scale-95" 
                                            class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-xl bg-white dark:bg-gray-800 shadow-xl ring-1 ring-gray-900/5 dark:ring-gray-700 focus:outline-none py-1.5 overflow-hidden" 
                                            style="display: none;"
                                        >
                                            <!-- View Claims Option -->
                                            <button 
                                                type="button" 
                                                class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 flex items-center gap-3 transition-colors" 
                                                data-action="view-claims" 
                                                data-member-id="{{ $member->id }}"
                                                data-modal-target="member-claims-modal"
                                                @click="open = false">
                                                <x-heroicon-o-document-text class="w-5 h-5 text-blue-500 shrink-0" />
                                                <span class="font-medium">View Claims</span>
                                            </button>            
                                
                                            <!-- View Contributions Option -->
                                            <button 
                                                type="button" 
                                                class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 flex items-center gap-3 transition-colors" 
                                                data-action="view-contributions" 
                                                data-member-id="{{ $member->id }}"
                                                data-modal-target="member-contributions-modal"
                                                @click="open = false">
                                                <x-heroicon-o-currency-dollar class="w-5 h-5 text-emerald-500 shrink-0" />
                                                <span class="font-medium">View Contributions</span>
                                            </button>            
                                
                                            <!-- View/Edit Details Option (President Only) -->
                                            @if($isPresident ?? false)
                                                <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>
                                                <button 
                                                    type="button"
                                                    class="w-full text-left px-4 py-2.5 text-sm text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 font-medium flex items-center gap-3 edit-member-btn transition-colors"
                                                    data-action="edit-member-details"
                                                    data-modal-target="member-modal"
                                                    data-modal-toggle="member-modal"
                                                    data-member-id="{{ $member->id }}"
                                                    data-member-employee-number="{{ $member->employee_number }}"
                                                    data-member-first-name="{{ $member->first_name }}"
                                                    data-member-last-name="{{ $member->last_name }}"
                                                    data-member-middle-name="{{ $member->middle_name }}"
                                                    data-member-suffix="{{ $member->suffix }}"
                                                    data-member-gender="{{ $member->gender }}"
                                                    data-member-birth-date="{{ $member->birth_date }}"
                                                    data-member-department="{{ $member->department }}"
                                                    data-member-position="{{ $member->position }}"
                                                    data-member-employment-status="{{ $member->employment_status }}"
                                                    data-member-membership-status="{{ $member->membership_status }}"
                                                    data-member-active-member-on="{{ $member->active_member_on }}"
                                                    data-member-inactive-member-on="{{ $member->inactive_member_on }}"
                                                    data-contact-number="{{ $member->contact_number }}"
                                                    data-member-address="{{ $member->address }}"
                                                    data-member-gcash-number="{{ $member->gcash_number }}"
                                                    data-member-gcash-account-name="{{ $member->gcash_account_name }}"
                                                    data-member-campus-id="{{ $member->campus_id }}"
                                                    data-member-email="{{ $member->user->email ?? '' }}"
                                                    @click="open = false">
                                                    <x-heroicon-o-pencil-square class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0" />
                                                    <span>View / Edit Details</span>
                                                </button>
                                            @endif            
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center py-4 text-gray-500 dark:text-gray-400">No members found.</td>
                        </tr>
                    @endif
                </tbody>
        </x-table>