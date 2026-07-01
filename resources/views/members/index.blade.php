<x-app-layout>
   
    <div class="p-4 bg-white border border-blue-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800 overflow-hidden">
        <div class="flex justify-end mb-4">
        <x-button
        id="add-new-member-btn"
        type="button" 
        class="btn btn-info align-self-end add-new-member-btn hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none" 
        data-action="add-new-member"
        data-modal-target="member-modal" 
        data-modal-toggle="member-modal">
            Add New Member
        </x-button>
        </div>
        <div class="overflow-x-auto">
            {{-- <x-table id="members-table" class="w-full text-sm text-left text-gray-500 dark:text-gray-400 border-collapse"> --}}
            <x-table id="members-table" >
                <thead>
                    {{-- <tr class="text-xs uppercase tracking-wider text-gray-700 bg-gray-50 dark:bg-gray-800 dark:text-gray-300"> --}}
                        <tr></tr>
                        <th class="px-6 py-4 font-semibold">Employee ID</th>
                        <th class="px-6 py-4 font-semibold">Member</th>
                        <th class="px-6 py-4 font-semibold">Work Details</th>
                        <th class="px-6 py-4 font-semibold">Employment Status</th>
                        <th class="px-6 py-4 font-semibold">Membership Status</th>
                        <th class="px-6 py-4 font-semibold">Action</th>

                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @if(isset($members) && count($members) > 0)
                        @foreach($members as $member)
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/50 transition-colors duration-150">
                                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white whitespace-nowrap">{{ $member->employee_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ ucwords($member->last_name) }}, {{ ucwords($member->first_name) }} {{ ucwords($member->middle_name) }}, {{ ucwords($member->suffex) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $member->department }} - {{ $member->position }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $member->employment_status }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $member->membership_status }}</td>
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
        </div>
    </div>

<!-- Adding new member modal -->
<div id="member-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden bg-gray-900/50 overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-4xl max-h-full">
        <!-- Modal content -->
         <div class="relative bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-2xl p-4 md:p-6">
            <!-- Modal header -->
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-zinc-800 pb-4 md:pb-5">
                <h3 id="modal-title" class="text-lg font-medium text-gray-900 dark:text-white">
                    Add New Member
                </h3>
                <button type="button" class="text-gray-500 bg-transparent hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-zinc-800 dark:hover:text-white rounded-lg text-sm w-9 h-9 ms-auto inline-flex justify-center items-center" data-modal-hide="member-modal">
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/></svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            
            <!-- Modal body -->
            <form action="" id="member-form" method="POST" onsubmit="handleFormSubmit(event)">
                @csrf
                <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 md:grid-cols-4 py-4 md:py-6">
                    
                    <!-- Identification Section -->
                    <div class="col-span-1 sm:col-span-2">
                        <label for="employee_number" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Employee Number</label>
                        <input type="text" name="employee_number" value="123456" id="employee_number" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs placeholder:text-gray-400" placeholder="EMP-2026-0001" required>
                    </div>
                    {{-- <div class="col-span-1 sm:col-span-2">
                        <label for="user_id" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Linked User Account ID (Optional)</label>
                        <input type="text" name="user_id" id="user_id" value="098765" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs placeholder:text-gray-400" placeholder="System User UUID">
                    </div> --}}

                    <!-- Name Breakdown Section -->
                    <div class="col-span-1">
                        <label for="first_name" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">First Name</label>
                        <input type="text" name="first_name" value="Jhon" id="first_name" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs placeholder:text-gray-400" placeholder="John" required>
                    </div>
                    <div class="col-span-1">
                        <label for="middle_name" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Middle Name</label>
                        <input type="text" name="middle_name" value="Eric" id="middle_name" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs placeholder:text-gray-400" placeholder="Robert">
                    </div>
                    <div class="col-span-1">
                        <label for="last_name" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Last Name</label>
                        <input type="text" name="last_name" value="Sage" id="last_name" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs placeholder:text-gray-400" placeholder="Doe" required>
                    </div>
                    <div class="col-span-1">
                        <label for="suffix" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Suffix</label>
                        <input type="text" name="suffix" id="suffix" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs placeholder:text-gray-400" placeholder="Jr. / III">
                    </div>

                    <!-- Demographics Section -->
                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="birth_date" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Birth Date</label>
                        <input type="date" name="birth_date" value="2003-03-28" id="birth_date" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs">
                    </div>
                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="gender" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Gender</label>
                        <select name="gender" id="gender" class="block w-full px-3 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-xs" required>
                            <option value="" disabled selected>Select Gender</option>
                            <option value="Male" selected>Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="address" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Address</label>
                        <input type="text" name="address" value="123 Main St" id="address" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs placeholder:text-gray-400" placeholder="Enter address" required>
                    </div>

                    <!-- Employment Section -->
                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="department" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Department</label>
                        <input type="text" name="department" value="CCJ" id="department" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs placeholder:text-gray-400" placeholder="Human Resources" required>
                    </div>
                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="position" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Position</label>
                        <input type="text" name="position" value="Instructor" id="position" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs placeholder:text-gray-400" placeholder="HR Generalist" required>
                    </div>

                    <!-- Statuses Section -->
                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="employment_status" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Employment Status</label>
                        <select name="employment_status" id="employment_status" class="block w-full px-3 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-xs" required>
                            <option value="" disabled selected>Select Status</option>
                            <option value="Permanent" selected>Permanent</option>
                            <option value="Probationary">Probationary</option>
                            <option value="Contractual">Contractual</option>
                            <option value="Part-time">Part-time</option>
                        </select>
                    </div>
                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="membership_status" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Membership Status</label>
                        <select name="membership_status" id="membership_status" class="block w-full px-3 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-xs" required>
                            <option value="" disabled selected>Select Status</option>
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="contact_number" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Contact Number</label>
                        <input type="text" name="contact_number" value='09876543212' id="contact_number" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs" placeholder="Enter contact number" required>
                    </div>
                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="email" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                        <input type="email" name="email" value="sample@example.com" id="email" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs" placeholder="Enter email" required>
                    </div>
                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="gcash_number" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">GCash Number</label>
                        <input type="text" name="gcash_number" id="gcash_number" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs" placeholder="Enter GCash number">
                    </div>
                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="gcash_account_name" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">GCash Account Name</label>
                        <input type="text" name="gcash_account_name" id="gcash_account_name" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs" placeholder="Enter GCash account name">
                    </div>
                </div>
                     
                
                <!-- Modal Footer -->
                <div class="flex items-center justify-end space-x-2 border-t border-gray-200 dark:border-zinc-800 pt-4 md:pt-5">
                     <button type="button" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300" data-modal-hide="member-modal">Cancel</button>
                     <button type="submit" id="submit-btn" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Save Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Corrected & Working Script Execution Block -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            //Members Table Initialization
            const tableElement = document.getElementById("members-table");
            if (tableElement && typeof simpleDatatables.DataTable !== 'undefined') {
                const dataTable = new simpleDatatables.DataTable("#members-table", {
                    searchable: true,
                    paging: true,
                    perPage: 5,
                    perPageSelect: [5, 10, 15, 20, -1], 
                    sortable: true, 
                    labels: { 
                        placeholder: "Search components...",
                        perPage: "Entries per page",
                        noRows: "No entries found",
                        info: "Showing {start} to {end} of {rows} entries"
                    }
                });
            }

            //Listener for Dynamic Edit/Add Member button click
            // Select all buttons that toggle the member modal
            const toggleButtons = document.querySelectorAll('[data-modal-target="member-modal"]');
             // Select the modal elements we need to change dynamically
            const modalTitle = document.getElementById('modal-title');
            const submitBtn = document.getElementById('submit-btn');
            const memberForm = document.getElementById('member-form');

             toggleButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    // Find the actual button element (handles nested icon clicks)
                    const btn = e.target.closest('[data-modal-target="member-modal"]');
                    if (!btn) return;

                    const action = btn.getAttribute('data-action');

                    if (action === 'edit-member-details') {
                        // 1. Change text labels for EDIT mode
                        modalTitle.textContent = 'Edit Member Details';
                        submitBtn.textContent = 'Update Details';

                        // 2. Dynamically set Laravel update route (example using member UUID)
                        const memberId = btn.getAttribute('data-member-id');
                        memberForm.action = `/members/${memberId}`; 

                        // 3. Inject Laravel's PUT method spoofing if it doesn't exist
                        let methodInput = memberForm.querySelector('input[name="_method"]');
                        if (!methodInput) {
                            methodInput = document.createElement('input');
                            methodInput.type = 'hidden';
                            methodInput.name = '_method';
                            methodInput.value = 'PUT';
                            memberForm.appendChild(methodInput);
                        }

                        // 4. Fill form inputs with the clicked member's data
                        document.getElementById('employee_number').value = btn.getAttribute('data-employee-number') || '';
                        document.getElementById('first_name').value = btn.getAttribute('data-first-name') || '';
                        document.getElementById('middle_name').value = btn.getAttribute('data-middle-name') || '';
                        document.getElementById('last_name').value = btn.getAttribute('data-last-name') || '';
                        document.getElementById('suffix').value = btn.getAttribute('data-suffix') || '';
                        document.getElementById('birth_date').value = btn.getAttribute('data-birth-date') || '';
                        document.getElementById('gender').value = btn.getAttribute('data-gender') || '';

                    } else if (action === 'add-new-member') {
                        // 1. Reset text labels for ADD mode
                        modalTitle.textContent = 'Add New Member';
                        submitBtn.textContent = 'Save Details';

                        // 2. Set backend store route
                        memberForm.action = '/members.store'; 

                        // 3. Remove Laravel's PUT method spoofing so it stays a clean POST request
                        const methodInput = memberForm.querySelector('input[name="_method"]');
                        if (methodInput) {
                            methodInput.remove();
                        }

                        // 4. Clear form fields completely for fresh input
                        memberForm.reset();
                    }
                });
            });

        });

        //Function to handle form submission via AJAX
        function handleFormSubmit(event) {
            event.preventDefault(); // Prevent the default form submission
            const form = event.target; // Get the form element
            const formData = new FormData(form); // Create a FormData object from the form

            $.ajax({
                url: form.action, // The URL to send the request to
                method: form.method, // The HTTP method (POST)
                data: formData, // The form data
                processData: false, // Prevent jQuery from processing the data
                contentType: false, // Prevent jQuery from setting the content type
                success: function(response) {
                    console.log('Form submitted successfully:', response);
                    if(response.status === 200){
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            showConfirmButton: true,
                            // timer: 1500,
                        }).then(() =>{
                             form.reset(); // Reset the form fields
                             document.querySelector('[data-modal-hide="member-modal"]').click(); // Close the modal
                             window.location.reload();
                        })
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Form submission failed:', error);
                    // Optionally, display an error message to the user
                }
            });
            // Here you can add your AJAX request or any other logic to handle the form submission
        }
        
    </script>
</x-app-layout>
