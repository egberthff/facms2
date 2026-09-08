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
                        <input type="text" name="employee_number" value="" id="employee_number" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs placeholder:text-gray-400" placeholder="EMP-2026-0001" required>
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <label for="campus_id" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Assigned Campus</label>
                        <select name="campus_id" id="campus_id" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs">
                            <option value="">-- SELECT CAMPUS --</option>
                            @foreach($campuses as $campus)
                                <option value="{{ $campus->id }}" 
                                    {{ Auth::user()->hasRole('President') && Auth::user()->member?->campus_id === $campus->id ? 'selected' : '' }}>
                                    {{ $campus->acronym }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Name Breakdown Section -->
                    <div class="col-span-1">
                        <label for="first_name" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">First Name</label>
                        <input type="text" name="first_name" value="" id="first_name" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs placeholder:text-gray-400" placeholder="John" required>
                    </div>
                    <div class="col-span-1">
                        <label for="middle_name" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Middle Name</label>
                        <input type="text" name="middle_name" value="" id="middle_name" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs placeholder:text-gray-400" placeholder="Robert">
                    </div>
                    <div class="col-span-1">
                        <label for="last_name" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Last Name</label>
                        <input type="text" name="last_name" value="" id="last_name" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs placeholder:text-gray-400" placeholder="Doe" required>
                    </div>
                    <div class="col-span-1">
                        <label for="suffix" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Suffix</label>
                        <input type="text" name="suffix" id="suffix" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs placeholder:text-gray-400" placeholder="Jr. / III">
                    </div>

                    <!-- Demographics Section -->
                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="birth_date" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Birth Date</label>
                        <input type="date" name="birth_date" value="" id="birth_date" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs">
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
                        <input type="text" name="address" value="" id="address" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs placeholder:text-gray-400" placeholder="Enter address" required>
                    </div>

                    <!-- Employment Section -->
                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="department" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Department</label>
                        <input type="text" name="department" value="" id="department" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs placeholder:text-gray-400" placeholder="Human Resources" required>
                    </div>
                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="position" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Position</label>
                        <input type="text" name="position" value="" id="position" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs placeholder:text-gray-400" placeholder="HR Generalist" required>
                    </div>

                    <!-- Statuses Section -->
                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="employment_status" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Employment Status</label>
                        <select name="employment_status" id="employment_status" class="block w-full px-3 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-xs" required>
                            <option value="" disabled selected>Select Status</option>
                            <option value="Permanent">Permanent</option>
                            <option value="Probationary">Probationary</option>
                            <option value="Contractual">Contractual</option>
                            <option value="Part-time">Part-time</option>
                        </select>
                    </div>
                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="membership_status" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Membership Status</label>
                        <select name="membership_status" id="membership_status" class="block w-full px-3 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-xs" required>
                            <option value="" disabled selected>Select Status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="contact_number" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Contact Number</label>
                        <input type="text" name="contact_number" value='' id="contact_number" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs" placeholder="Enter contact number" required>
                    </div>
                    <div class="col-span-1 sm:col-span-2 md:col-span-2">
                        <label for="email" class="block mb-2.5 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                        <input type="email" name="email" value="" id="email" class="bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5 shadow-xs" placeholder="Enter email" required>
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


<!-- Main modal for CSV uploading -->
<div id="member-modal-csv" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 justify-center items-center w-full h-full">
    
    <!-- Backdrop Overlay: This darkens the screen and separates the modal from the background -->
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" data-modal-hide="member-modal-csv"></div>

    <!-- Modal Content Wrapper (Elevated relative to the backdrop) -->
    <div class="relative p-4 w-full max-w-md max-h-full z-10">
        <!-- Modal card structure -->
        <!-- NOTE: If bg-neutral-primary-soft is transparent in your custom config, change it to bg-white -->
        <div class="relative bg-neutral-primary-soft border bg-white border-default rounded-base shadow-lg p-4 md:p-6">
            <!-- Modal header -->
            <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                <h3 class="text-lg font-medium text-heading">
                    Upload CSV File
                </h3>
                <button type="button" class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center" data-modal-hide="member-modal-csv">
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/></svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form action="#" class="pt-4 md:pt-6" method="POST" enctype="multipart/form-data" onsubmit="handleFormSubmitCSV(event)">
                @csrf
                <div class="mb-4">
                    <label for="csv_file" class="block mb-2.5 text-sm font-medium text-heading">Select CSV File</label>
                    <input type="file" name="member_csv_file" id="csv_file" class="bg-neutral-tertiary border border-default text-body text-sm rounded-base focus:ring-primary focus:border-primary block w-full p-2.5">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file_input_help">CSV (10MB).</p>
                </div>
                <button type="submit" class="text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none w-full mb-3">Upload File</button>
            </form>
        </div>
    </div>
</div>

