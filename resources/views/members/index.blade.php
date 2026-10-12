<x-app-layout>
   
    {{-- @if(($isPresident ?? false) || ($isTreasurer ?? false)) --}}
    @if(Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']) || Auth::user()->can('view all campus data'))
        <div class="mb-6 rounded-xl border border-gray-200 bg-gray-50/60 p-4 sm:p-5 dark:border-zinc-800/80 dark:bg-zinc-800/40">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Campus Member Filter</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Filter members by campus for leadership review.</p>
                </div>
                <form method="GET" action="{{ route('members.index') }}" class="flex items-center gap-2">
                    <select name="campus_filter" onchange="this.form.submit()" class="block w-full min-w-52 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-300">
                        <option value="">All Campuses (Global View)</option>
                        @foreach($allCampuses as $campus)
                            <option value="{{ $campus->id }}" {{ ($selectedCampusId ?? '') == $campus->id ? 'selected' : '' }}>{{ $campus->acronym }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="scrollbar-none -mx-1 mt-4 flex gap-2 overflow-x-auto px-1 pb-1">
                @foreach($allCampuses as $campus)
                    <div class="shrink-0 rounded-full border bg-white px-3 py-1 text-xs font-medium dark:bg-zinc-900 {{ ($selectedCampusId ?? '') == $campus->id ? 'border-blue-500 bg-blue-50/10' : 'border-gray-200 dark:border-zinc-800' }}">
                        <span class="text-gray-600 dark:text-gray-400">{{ $campus->acronym }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs sm:p-5">
        <div class="mb-4 flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <!-- Left End: Filter Dropdown -->
            <div class="flex w-full items-center sm:w-64">
                <label for="member-status-filter" class="sr-only">Filter by Status</label>
                <form action="{{ route('members.index') }}"  method="GET">
                   <select id="member-status-filter" name="status" onchange="this.form.submit()" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        <option value="">All Members</option>                   

                        @foreach(\App\Enums\MembershipStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected($currentStatus === $status->value)>
                                {{ $status->name }} Members
                            </option>
                        @endforeach
                    </select>
                </form>

            </div>  

            <!-- Right End: Add Action --> 
            <div class="flex gap-2">  
                @if($isPresident ?? false)  
                <x-button
                    id="add-new-member-btn-csv"
                    type="button"
                    class="member-btn add-new-member-btn-csv btn-warning bg-amber-500 hover:bg-amber-600 text-white shadow-xs font-medium rounded-lg text-sm px-4 py-2.5 focus:outline-none transition-colors" 
                    data-action="add-new-member-csv"
                    data-modal-target="member-modal-csv" 
                    data-modal-toggle="member-modal-csv">
                        Upload CSV
                </x-button>

                 <x-button
                    id="add-new-member-btn"
                    type="button" 
                    class="member-btn btn btn-info add-new-member-btn shadow-xs font-medium rounded-lg text-sm px-4 py-2.5 focus:outline-none" 
                    data-action="add-new-member"
                    data-modal-target="member-modal" 
                    data-modal-toggle="member-modal">
                        Add New Member
                </x-button>
                @endif
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
                        console.log(data);
                        renderContributionsModal(data);
                    }
                })
                .catch(error => {
                    console.error(`Error retrieving ${type}:`, error);
                    alert(`Could not fetch data. Please try again.`);
                });
            }

            const currencyFormatter = new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP'
            });

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

                const balance = data.balance || [];
                document.getElementById('balance-approved').textContent = currencyFormatter.format(balance?.approvedBalance ?? 0);
                document.getElementById('balance-actual').textContent = currencyFormatter.format(balance?.actualBalance ?? 0);
                document.getElementById('balance-base-limit').textContent = currencyFormatter.format(balance?.baseLimit ?? 0);
                document.getElementById('balance-approved-cycle').textContent = currencyFormatter.format(balance?.totalApprovedCycle ?? 0);
                document.getElementById('balance-disbursed-cycle').textContent = currencyFormatter.format(balance?.totalDisbursedCycle ?? 0);
                document.getElementById('balance-cycle-start').textContent = balance?.cycleStartDate
                    ? `${balance.cycleStartDate} to ${balance.cycleEndDate}`
                    : 'No active cycle';
            
                // 2. Extract records array from response structure
                const claims = data.records || [];
                tbody.innerHTML = '';
            
                // 3. Fallback Handling
                if (claims.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-400 dark:text-zinc-500">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-8 h-8 text-gray-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://w3.org">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                    </svg>
                                    <span>No claims history found for this member.</span>
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    // Clear content before populating
                    tbody.innerHTML = '';
                
                    claims.forEach(claim => {
                        const submittedDate = claim.submitted_at ? claim.submitted_at.split('T')[0] : '—';

                        const requestedAmount = parseFloat(claim.requested_amount || 0).toLocaleString('en-PH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    
                        const status = claim.status || 'Pending';

                        // Match modern styling variants
                        let badgeClasses = 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700';
                        let dotClasses = 'bg-gray-400';

                        if (['Approved', 'Disbursed'].includes(status)) {
                            badgeClasses = 'bg-green-50 text-green-700 border-green-200 dark:bg-green-950/40 dark:text-green-400 dark:border-green-900/50';
                            dotClasses = 'bg-green-500';
                        } else if (['Pending', 'Processing'].includes(status)) {
                            badgeClasses = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900/50';
                            dotClasses = 'bg-amber-500';
                        } else if (['Rejected'].includes(status)) {
                            badgeClasses = 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-400 dark:border-red-900/50';
                            dotClasses = 'bg-red-500';
                        }
                    
                        const rowHTML = `
                            <tr class="bg-white dark:bg-zinc-900/50 hover:bg-gray-50 dark:hover:bg-zinc-800/60 transition-colors duration-150">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    ${claim.claim_number || '—'}
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-zinc-400 whitespace-nowrap">
                                    ${submittedDate}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    ₱${requestedAmount}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-md border ${badgeClasses}">
                                        <span class="w-1.5 h-1.5 me-1.5 rounded-full ${dotClasses}"></span>
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
        
            // 1. Gather all individual collections safely from data.records
            const records = data.records || {};
            const annualDues = records.annual_dues || [];
            const claims = records.claims || []; // standard monthly claims/contributions
            const cna = records.cna || [];

            // 2. Combine and normalize the structure into a single flat array
            const contributions = [
                ...claims.map(item => ({ ...item, type: 'Claims Contribution', displayAmount: item.amount })),
                ...annualDues.map(item => ({ ...item, type: 'Annual Dues', displayAmount: item.amount_paid })),
                ...cna.map(item => ({ ...item, type: 'CNA Share', displayAmount: item.due_amount || item.amount }))
            ];

            tbody.innerHTML = '';

            // 3. Fallback Handling
            if (contributions.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-400 dark:text-zinc-500">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <svg class="w-8 h-8 text-gray-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://w3.org">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                <span>No contribution history records found for this member.</span>
                            </div>
                        </td>
                    </tr>
                `;
            } else {
                // Populate rows
                contributions.forEach(item => {
                    // Uses normalized displayAmount safely 
                    const contributionAmount = parseFloat(item.displayAmount || 0).toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                
                    const paidDate = item.paid_at ? item.paid_at.split('T')[0] : '—';
                    const rawStatus = (item.status || 'Unpaid').toLowerCase();
                
                    // Define modern style variants for status pills
                    let badgeClasses = 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700';
                    let dotClasses = 'bg-gray-400';         
                
                    if (rawStatus === 'paid') {
                        badgeClasses = 'bg-green-50 text-green-700 border-green-200 dark:bg-green-950/40 dark:text-green-400 dark:border-green-900/50';
                        dotClasses = 'bg-green-500';
                    } else if (rawStatus === 'pending') {
                        badgeClasses = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900/50';
                        dotClasses = 'bg-amber-500';
                    } else if (rawStatus === 'unpaid') {
                        badgeClasses = 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-400 dark:border-red-900/50';
                        dotClasses = 'bg-red-500';
                    }
                
                    // Safely extract batch reference if it exists, otherwise fall back to item type
                    const typeOrReference = item.contribution_batch?.batch_reference || item.reference_number;
                
                    const rowHTML = `
                        <tr class="bg-white dark:bg-zinc-900/50 hover:bg-gray-50 dark:hover:bg-zinc-800/60 transition-colors duration-150">
                            <td class="px-6 py-4 font-mono text-xs text-gray-500 dark:text-zinc-400 whitespace-nowrap">
                                ${typeOrReference}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-gray-500 dark:text-zinc-400 whitespace-nowrap">
                                ${item.type}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                ₱${contributionAmount}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-md border ${badgeClasses}">
                                    <span class="w-1.5 h-1.5 me-1.5 rounded-full ${dotClasses}"></span>
                                    ${item.status || 'Unpaid'}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-zinc-400 whitespace-nowrap">
                                ${paidDate}
                            </td>
                        </tr>
                    `;
                    
                    tbody.insertAdjacentHTML('beforeend', rowHTML);
                });
            }

            // 2. Extract records array from response structure
            // const contributions = data.records || [];
            // tbody.innerHTML = '';
        
            // // 3. Fallback Handling
            // if (contributions.length === 0) {
            //     tbody.innerHTML = `
            //         <tr>
            //             <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-400 dark:text-zinc-500">
            //                 <div class="flex flex-col items-center justify-center space-y-2">
            //                     <svg class="w-8 h-8 text-gray-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://w3.org">
            //                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            //                     </svg>
            //                     <span>No contribution history records found for this member.</span>
            //                 </div>
            //             </td>
            //         </tr>
            //     `;
            // } else {
            //     // Clear content before populating
            //     tbody.innerHTML = '';           

            //     contributions.forEach(item => {
            //         const contributionAmount = parseFloat(item.amount || 0).toLocaleString('en-PH', {
            //             minimumFractionDigits: 2,
            //             maximumFractionDigits: 2
            //         });
                
            //         const paidDate = item.paid_at ? item.paid_at.split('T')[0] : '—';
                
            //         const rawStatus = (item.status || 'Unpaid').toLowerCase();

            //         // Define modern style variants for status pills
            //         let badgeClasses = 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700';
            //         let dotClasses = 'bg-gray-400';         

            //         if (rawStatus === 'paid') {
            //             badgeClasses = 'bg-green-50 text-green-700 border-green-200 dark:bg-green-950/40 dark:text-green-400 dark:border-green-900/50';
            //             dotClasses = 'bg-green-500';
            //         } else if (rawStatus === 'pending') {
            //             badgeClasses = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900/50';
            //             dotClasses = 'bg-amber-500';
            //         } else if (rawStatus === 'unpaid') {
            //             badgeClasses = 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-400 dark:border-red-900/50';
            //             dotClasses = 'bg-red-500';
            //         }
                
            //         const rowHTML = `
            //             <tr class="bg-white dark:bg-zinc-900/50 hover:bg-gray-50 dark:hover:bg-zinc-800/60 transition-colors duration-150">
            //                 <td class="px-6 py-4 font-mono text-xs text-gray-500 dark:text-zinc-400 whitespace-nowrap">
            //                     ${item.contribution_batch.batch_reference || '—'}
            //                 </td>
            //                 <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
            //                     ₱${contributionAmount}
            //                 </td>
            //                 <td class="px-6 py-4 whitespace-nowrap">
            //                     <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-md border ${badgeClasses}">
            //                         <span class="w-1.5 h-1.5 me-1.5 rounded-full ${dotClasses}"></span>
            //                         ${item.status || 'Unpaid'}
            //                     </span>
            //                 </td>
            //                 <td class="px-6 py-4 text-gray-600 dark:text-zinc-400 whitespace-nowrap">
            //                     ${paidDate}
            //                 </td>
            //             </tr>
            //         `;
                    
            //         tbody.insertAdjacentHTML('beforeend', rowHTML);
            //     });
            // }


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
                 
                document.getElementById('active_member_on').value =
                    formatToHTMLDate(btn.getAttribute('data-member-active-member-on')) || '';
                
                document.getElementById('inactive_member_on').value = 
                    formatToHTMLDate(btn.getAttribute('data-member-inactive-member-on')) || '';
                
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
