<x-table id="members-table" class="dt-table dt-responsive">
                <thead>
                    {{-- <tr class="text-xs uppercase tracking-wider text-gray-700 bg-gray-50 dark:bg-gray-800 dark:text-gray-300"> --}}
                        <th class="px-6 py-4 font-semibold">Employee ID</th>
                        <th class="px-6 py-4 font-semibold">Member</th>
                        <th class="px-6 py-4 font-semibold">Work Details</th>
                        {{-- <th class="px-6 py-4 font-semibold">Employment Status</th> --}}
                        <th class="px-6 py-4 font-semibold">Membership Status</th>
                        <th class="px-6 py-4 font-semibold">Claims</th>
                        <th class="px-6 py-4 font-semibold">Contributions</th>
                        <th class="px-6 py-4 font-semibold">Action</th>

                    {{-- </tr> --}}
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @if(isset($members) && count($members) > 0)
                        @foreach($members as $member)
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/50 transition-colors duration-150">
                                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white whitespace-nowrap">{{ $member->employee_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ ucwords($member->last_name) }}, {{ ucwords($member->first_name) }} {{ ucwords($member->middle_name) }}, {{ ucwords($member->suffex) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $member->department }} - {{ $member->position }}</td>
                                {{-- <td class="px-6 py-4 whitespace-nowrap">{{ $member->employment_status }}</td> --}}
                                <td class="px-6 py-4 whitespace-nowrap">{{ $member->membership_status }}</td>
                                {{-- <td class="px-6 py-4 whitespace-nowrap">{{ $member->claims }}</td> --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-button type='button' 
                                              class="btn btn-md btn-info" 
                                              data-action="view-claims" 
                                              data-member-id="{{ $member->id }}"
                                              data-modal-target="member-claims-modal">
                                         View Claims 
                                    </x-button>
                                </td>
                                {{-- <td class="px-6 py-4 whitespace-nowrap">{{ $member->contributions }}</td> --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-button type='button' 
                                              class="btn btn-md btn-info" 
                                              data-action="view-contributions" 
                                              data-member-id="{{ $member->id }}"
                                              data-modal-target="member-contributions-modal">
                                         View Contributions 
                                    </x-button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-button type="button" 
                                        id="edit-member-btn"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                        data-action="edit-member-details"
                                        data-modal-target="member-modal"
                                        data-modal-toggle="member-modal"
                                        data-member-id="{{ $member->id }}"
                                        data-member-employee-number="{{ $member->employee_number }}"
                                        data-member-first-name="{{ $member->first_name }}"
                                        data-member-last-name="{{ $member->lastname }}"
                                        data-member-middle-name="{{ $member->middle_name }}"
                                        data-member-suffix="{{ $member->suffix }}"
                                        data-member-gender="{{ $member->gender }}"
                                        data-member-birtdate="{{ $member->birth_date }}"
                                        data-member-department="{{ $member->department }}"
                                        data-member-position="{{ $member->position }}"
                                        data-member-employment-status="{{ $member->employment_status }}"
                                        data-member-membership-status="{{ $member->membership_status }}"
                                        data-contact-number="{{ $member->contact_number }}"
                                        data-member-address="{{ $member->address }}"
                                        data-member-gcash-number="{{ $member->gcash_number }}"
                                        data-member-gcash-account-name="{{ $member->gcash_account_name }}"
                                        data-member-campus_id="{{ $member->campus_id }}"
                                        >
                                        Edit
                                    </x-button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="text-center py-4">No members found.</td>
                        </tr>
                    @endif
    
                </tbody>
            </x-table>