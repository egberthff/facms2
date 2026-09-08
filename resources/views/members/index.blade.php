<x-app-layout>
   
    {{-- @if(isset($isPresident) && $isPresident) --}}
    @if(Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']) || Auth::user()->can('view all campus data'))
        <div class="p-6 bg-gray-50 dark:bg-zinc-800/40 rounded-xl border border-gray-200 dark:border-zinc-800/80 mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Campus Member Filter</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Filter members by campus for leadership review.</p>
                </div>
                <form method="GET" action="{{ route('members.index') }}" class="flex items-center gap-2">
                    <select name="campus_filter" onchange="this.form.submit()" class="text-sm rounded-lg bg-white dark:bg-zinc-900 border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-300 focus:ring-blue-500 focus:border-blue-500 min-w-52">
                        <option value="">All Campuses (Global View)</option>
                        @foreach($allCampuses as $campus)
                            <option value="{{ $campus->id }}" {{ ($selectedCampusId ?? '') == $campus->id ? 'selected' : '' }}>{{ $campus->acronym }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="flex gap-3 overflow-x-auto mt-4 pb-2 scrollbar-none">
                @foreach($allCampuses as $campus)
                    <div class="shrink-0 bg-white dark:bg-zinc-900 px-3 py-1.5 rounded-lg border {{ ($selectedCampusId ?? '') == $campus->id ? 'border-blue-500 bg-blue-50/10' : 'border-gray-200 dark:border-zinc-800' }} text-xs">
                        <span class="font-bold text-gray-500 dark:text-gray-400">{{ $campus->acronym }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="p-4 bg-white border border-blue-200 rounded-xl shadow-sm ">
        <div class="flex justify-between items-center mb-4 w-full p-2">
            <!-- Left End: Filter Dropdown -->
            <div class="flex items-center bg-blue-100 border border-blue-300 rounded-xl">
                <label for="member-status-filter" class="sr-only">Filter by Status</label>
                <select id="member-status-filter" class=" text-black text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="">All Members</option>
                    <option value="Active" selected>Active Members</option>
                    <option value="Inactive">Inactive Members</option>
                </select>
            </div>  

            <!-- Right End: Add Action --> 
            <div class="flex gap-2">  
                {{-- @if(Auth::user()->hasRole('President'))   --}}
                <x-button
                    id="add-new-member-btn-csv"
                    type="button"
                    class="member-btn add-new-member-btn-csv btn-warning bg-amber-500 hover:bg-brand-strong text-white focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none transition-colors" 
                    data-action="add-new-member-csv"
                    data-modal-target="member-modal-csv" 
                    data-modal-toggle="member-modal-csv">
                        Upload CSV
                </x-button>

                 <x-button
                    id="add-new-member-btn"
                    type="button" 
                    class="member-btn btn btn-info add-new-member-btn hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none" 
                    data-action="add-new-member"
                    data-modal-target="member-modal" 
                    data-modal-toggle="member-modal">
                        Add New Member
                </x-button>
                {{-- @endif --}}
            </div>
        </div>
         

        <div class="w-full">
            @include('members.partials.members-table')
        </div>
    </div>

    <!-- Adding/updating member modal -->
    @include('members.partials.member-form')

    @include('members.partials.member-claims')

    @include('members.partials.member-contributions')



    <!-- Corrected & Working Script Execution Block -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            //Members Table Initialization
            const tableElement = document.getElementById("members-table");
            if (tableElement && typeof simpleDatatables.DataTable !== 'undefined') {
                document.getElementById('member-status-filter').addEventListener('change', function(e) {
                    const selectedValue = e.target.value;
    
                        // If 'All Members' is selected, value is "". It resets the visible dataset rows.
                        // const dataTable = event.target.closest('[class="dt-table"]')
                        tableElement.dataTableInstance.search(selectedValue);
                        });

                // Attach a single listener to the entire table wrapper
                tableElement.addEventListener('click', function (event) {
                    // Find closest element with data-action to catch clicks inside custom components correctly
                    const button = event.target.closest('[data-action]');
                    if (!button) return;
                
                    const action = button.getAttribute('data-action');
                    const memberId = button.getAttribute('data-member-id');
                
                    // Route actions accordingly
                    if (action === 'view-claims') {
                        fetchMemberDetails(memberId, 'claims');
                    } else if (action === 'view-contributions') {
                        fetchMemberDetails(memberId, 'contributions');
                    }
                });
            }

            /**
             * Universal functional wrapper handling Ajax requests
             */
            function fetchMemberDetails(memberId, type) {
                // Generate clean RESTful route mapping endpoints matching your back-end structure
                const url = `/members/${memberId}/${type}`;
            
                fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response failed.');
                    return response.json();
                })
                .then(data => {
                    if (type === 'claims') {
                        renderClaimsModal(data);
                    } else {
                        renderContributionsModal(data);
                    }
                })
                .catch(error => {
                    console.error(`Error retrieving ${type}:`, error);
                    alert(`Could not fetch data. Please try again.`);
                });
            }

            /**
             * Dynamic modal injection script for Member Claims
             * Supports both an array of claims or a single claim object.
             */
            function renderClaimsModal(data) {
                const tbody = document.getElementById('members-claims-table-body');
                const modal = document.getElementById('member-claims-modal');
                const modalTitle = document.getElementById('claims-modal-title');
                        
                if (!tbody || !modal) return;
                        
                // 1. Dynamic Header Update with Member's Name
                if (data.member) {
                    modalTitle.textContent = `Claims History: ${data.member.first_name} ${data.member.last_name}`;
                } else {
                    modalTitle.textContent = 'Member Claims History';
                }
            
                // 2. Extract records array from response structure
                const claims = data.records || [];
                tbody.innerHTML = '';
            
                // 3. Fallback Handling
                if (claims.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No claims history found for this member.
                            </td>
                        </tr>
                    `;
                } else {
                    // 4. Populate table using payload arrays
                    claims.forEach(claim => {
                        // ISO Split cleaner converts '2026-07-17T13:31:05.000000Z' to '2026-07-17'
                        const submittedDate = claim.submitted_at ? claim.submitted_at.split('T')[0] : '—';
                        
                        // Format currency fields
                        const requestedAmount = parseFloat(claim.requested_amount || 0).toLocaleString('en-PH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    
                        // Map responsive status colors 
                        let statusClasses = 'bg-gray-100 text-gray-800 dark:bg-zinc-800 dark:text-gray-300';
                        const status = claim.status || 'Pending';
                        
                        if (['Approved', 'Disbursed'].includes(status)) {
                            statusClasses = 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
                        } else if (['Pending', 'Processing'].includes(status)) {
                            statusClasses = 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400';
                        } else if (['Rejected'].includes(status)) {
                            statusClasses = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
                        }
                    
                        const rowHTML = `
                            <tr class="bg-white border-b dark:bg-zinc-900 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    ${claim.claim_number || '—'}
                                </td>
                                <td class="px-4 py-3">
                                    ${submittedDate}
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                    ₱${requestedAmount}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-0.5 text-xs font-medium rounded-full ${statusClasses}">
                                        ${status}
                                    </span>
                                </td>
                            </tr>
                        `;
                        
                        tbody.insertAdjacentHTML('beforeend', rowHTML);
                    });
                }
            
                // 5. Unhide Modal Layout container on screen
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            /**
         * Dynamic modal injection script for Member Contributions
         */
        function renderContributionsModal(data) {
            const tbody = document.getElementById('contributions-table-body');
            const modalElement = document.getElementById("member-contributions-modal");
            const contributionModalTitle = document.getElementById('contributions-modal-title');

            if (!tbody || !modalElement) return;
        
            // 1. Dynamic Title Injection
            if (data.member) {
                contributionModalTitle.textContent = `Contributions History: ${data.member.first_name} ${data.member.last_name}`;
            } else {
                contributionModalTitle.textContent = 'Member Contributions History';
            }
        
            // 2. Extract records array from response structure
            const contributions = data.records || [];
            tbody.innerHTML = '';
        
            // 3. Fallback Handling
            if (contributions.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            No contribution history records found for this member.
                        </td>
                    </tr>
                `;
            } else {
                // 4. Populate table using payload arrays matching your 4 explicit headers
                contributions.forEach(item => {
                    // Formats currency string to standard PHP Peso locale 
                    const contributionAmount = parseFloat(item.amount || 0).toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                
                    // Isolate clean date formatting for paid_at records
                    const paidDate = item.paid_at ? item.paid_at.split('T')[0] : '—';
                
                    // Clean string checks for active status badge mapping
                    const rawStatus = (item.status || 'Unpaid').toLowerCase();
                    let statusClasses = 'bg-gray-100 text-gray-800 dark:bg-zinc-800 dark:text-gray-300';

                    if (rawStatus === 'paid') {
                        statusClasses = 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
                    } else if (rawStatus === 'pending') {
                        statusClasses = 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400';
                    } else if (rawStatus === 'unpaid') {
                        statusClasses = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
                    }
                
                    const rowHTML = `
                        <tr class="bg-white border-b dark:bg-zinc-900 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                            <td class="px-4 py-3 font-medium text-xs text-gray-400 font-mono whitespace-nowrap">
                                ${item.contribution_batch_id || '—'}
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                ₱${contributionAmount}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-0.5 text-xs font-medium rounded-full ${statusClasses}">
                                    ${item.status || 'Unpaid'}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                ${paidDate}
                            </td>
                        </tr>
                    `;
                    
                    tbody.insertAdjacentHTML('beforeend', rowHTML);
                });
            }

            modalElement.classList.remove('hidden');
            modalElement.classList.add('flex');
        }   
        });

        function handleFormSubmitCSV(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            $.ajax({
                url: "members/add-new-member-csv",
                method: form.method,
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if( response.status === 200 ) {
                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: response.message,
                            showConfirmButton: true,
                        }).then(() => {
                            form.reset();
                            $("#member-modal-csv").prop(false);
                            window.location.reload();
                        })
                    }else if( response.status === 500 ) {
                        Swal.fire({ 
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                        })
                    }else {
                        Swal.fire({ 
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        })
                    }
                },
               error: function(xhr, status, error) {
                    console.error('Form submission failed:', error);

                    // Safely get the message from the server response
                    let errorMsg = 'An unexpected error occurred.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg,
                    });
                }

            });

        }

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
                    }else{
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Form submission failed:', error);
                            
                    // Safely get the message from the server response
                    let errorMsg = 'An unexpected error occurred.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg,
                    });
                }
            });   
        }

        //Date format helper
        const formatToHTMLDate = (dateString) => {
            if (!dateString) return '';
                const date = new Date(dateString);
                // Check if the date is valid
                return isNaN(date.getTime()) ? '' : date.toISOString().split('T')[0];
            };

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-modal-target="member-modal"]');
        
            if (!btn) {
                return;
            }
        
            const action = btn.getAttribute('data-action');
        
            const modalTitle = document.getElementById('modal-title');
            const submitBtn = document.getElementById('submit-btn');
            const memberForm = document.getElementById('member-form');
        
            if (!modalTitle || !submitBtn || !memberForm) {
                return;
            }
        
            if (action === 'edit-member-details') {
            
                // Change modal labels
                modalTitle.textContent = 'Edit Member Details';
                submitBtn.textContent = 'Update Details';
            
                // Get member ID
                const memberId = btn.getAttribute('data-member-id');
            
                // Set update route
                memberForm.action = `/members/${memberId}`;
            
                // Add/update PUT method
                let methodInput = memberForm.querySelector(
                    'input[name="_method"]'
                );
            
                if (!methodInput) {
                    methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    memberForm.appendChild(methodInput);
                }
            
                methodInput.value = 'PUT';
            
                // Fill form fields
                document.getElementById('employee_number').value =
                    btn.getAttribute('data-member-employee-number') || '';
            
                document.getElementById('first_name').value =
                    btn.getAttribute('data-member-first-name') || '';
            
                document.getElementById('middle_name').value =
                    btn.getAttribute('data-member-middle-name') || '';
            
                document.getElementById('last_name').value =
                    btn.getAttribute('data-member-last-name') || '';
            
                document.getElementById('suffix').value =
                    btn.getAttribute('data-member-suffix') || '';
            
                document.getElementById('birth_date').value = 
                    formatToHTMLDate(btn.getAttribute('data-member-birth-date')) || '';
            
                document.getElementById('gender').value =
                    btn.getAttribute('data-member-gender') || '';
            
                document.getElementById('campus_id').value =
                    btn.getAttribute('data-member-campus-id') || '';

                document.getElementById('department').value =
                    btn.getAttribute('data-member-department') || '';

                document.getElementById('position').value = 
                    btn.getAttribute('data-member-position') || ''; 

                document.getElementById('employment_status').value =
                    btn.getAttribute('data-member-employment-status') || '';

                document.getElementById('membership_status').value =
                    btn.getAttribute('data-member-membership-status') || '';
                
                document.getElementById('contact_number').value =
                    btn.getAttribute('data-contact-number') || '';

                document.getElementById('address').value =
                    btn.getAttribute('data-member-address') || '';

                document.getElementById('gcash_number').value =
                    btn.getAttribute('data-member-gcash-number') || '';

                document.getElementById('gcash_account_name').value =
                    btn.getAttribute('data-member-gcash-account-name') || '';

                document.getElementById('email').value = 
                    btn.getAttribute('data-member-email') || '';

                const memberModal = document.getElementById('member-modal');
                memberModal.classList.remove('hidden');
                memberModal.classList.add('flex');
                memberModal.setAttribute('aria-hidden', 'false');

            
            } else if (action === 'add-new-member') {
            
                // Reset form first
                memberForm.reset();
            
                // Change modal labels
                modalTitle.textContent = 'Add New Member';
                submitBtn.textContent = 'Save Details';
            
                // Set store route
                memberForm.action = '/members';
            
                // Remove PUT method
                const methodInput = memberForm.querySelector(
                    'input[name="_method"]'
                );
            
                if (methodInput) {
                    methodInput.remove();
                }
            }
        });

    </script>
</x-app-layout>
