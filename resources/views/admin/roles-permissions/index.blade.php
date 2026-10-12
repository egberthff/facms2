<x-app-layout>
    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <!-- Header -->
        <header class="mb-10 flex flex-col md:flex-row md:items-center md:justify-between border-b border-gray-200 pb-6">
            {{-- <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">Access Control</h1>
                <p class="mt-2 text-sm text-gray-500">Configure global application roles and fine-tune individual module permissions.</p>
            </div> --}}
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">Access Control</h1>
                <p class="mt-2 text-sm text-gray-500">Configure global application roles.</p>
            </div>
            <!-- Action Buttons -->
            {{-- <div class="mt-4 md:mt-0 flex space-x-3">
                <button class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">+ New Permission</button>
                <button class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">+ New Role</button>
            </div> --}}
        </header>

        <!-- Status Alerts -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-md bg-green-50 border border-green-200 flex items-center text-sm text-green-700">
                <svg class="h-5 w-5 text-green-400 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Quick Stats Widgets -->
        <!-- Stats Dashboard Section -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 mb-10">
            
            <!-- Total System Roles -->
            <div class="relative overflow-hidden rounded-xl bg-white p-4 sm:p-5 shadow-sm border border-gray-100 transition-all hover:shadow-md">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider truncate">Total System Roles</dt>
                        <dd class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-indigo-600">{{ $roles->count() }}</dd>
                    </div>
                    <div class="p-2.5 sm:p-3 bg-indigo-50 text-indigo-600 rounded-lg shrink-0">
                        <svg class="w-5 h-5 sm:w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                </div>
            </div>
        
            <!-- Federated Presidents -->
            <div class="relative overflow-hidden rounded-xl bg-white p-4 sm:p-5 shadow-sm border border-gray-100 transition-all hover:shadow-md flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider truncate">Federated Presidents</dt>
                        <dd class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-indigo-600">{{ count($userRoles['federatedPresident'] ?? []) }}</dd>
                    </div>
                    <div class="p-2.5 sm:p-3 bg-indigo-50 text-indigo-600 rounded-lg shrink-0">
                        <svg class="w-5 h-5 sm:w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                </div>
                @if(!empty($userRoles['federatedPresident']))
                    <div class="mt-3 pt-3 border-t border-gray-50 max-h-24 overflow-y-auto custom-scrollbar space-y-1.5">
                        @foreach($userRoles['federatedPresident'] as $user)
                            <div class="flex justify-between items-center text-xs gap-2">
                                <span class="font-medium text-gray-700 truncate" title="{{ $user['name'] }}">{{ $user['name'] }}</span>
                                <span class="inline-flex items-center rounded-md bg-indigo-50 px-1.5 py-0.5 text-2xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10 shrink-0">{{ $user['campus'] ?: 'N/A' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        
            <!-- Presidents -->
            <div class="relative overflow-hidden rounded-xl bg-white p-4 sm:p-5 shadow-sm border border-gray-100 transition-all hover:shadow-md flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider truncate">Presidents</dt>
                        <dd class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-indigo-600">{{ count($userRoles['president'] ?? []) }}</dd>
                    </div>
                    <div class="p-2.5 sm:p-3 bg-indigo-50 text-indigo-600 rounded-lg shrink-0">
                        <svg class="w-5 h-5 sm:w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                </div>
                @if(!empty($userRoles['president']))
                    <div class="mt-3 pt-3 border-t border-gray-50 max-h-24 overflow-y-auto custom-scrollbar space-y-1.5">
                        @foreach($userRoles['president'] as $user)
                            <div class="flex justify-between items-center text-xs gap-2">
                                <span class="font-medium text-gray-700 truncate" title="{{ $user['name'] }}">{{ $user['name'] }}</span>
                                <span class="inline-flex items-center rounded-md bg-indigo-50 px-1.5 py-0.5 text-2xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10 shrink-0">{{ $user['campus'] ?: 'N/A' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        
            <!-- Federated Treasurers -->
            <div class="relative overflow-hidden rounded-xl bg-white p-4 sm:p-5 shadow-sm border border-gray-100 transition-all hover:shadow-md flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider truncate">Federated Treasurers</dt>
                        <dd class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-indigo-600">{{ count($userRoles['federatedTreasurer'] ?? []) }}</dd>
                    </div>
                    <div class="p-2.5 sm:p-3 bg-indigo-50 text-indigo-600 rounded-lg shrink-0">
                        <svg class="w-5 h-5 sm:w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                @if(!empty($userRoles['federatedTreasurer']))
                    <div class="mt-3 pt-3 border-t border-gray-50 max-h-24 overflow-y-auto custom-scrollbar space-y-1.5">
                        @foreach($userRoles['federatedTreasurer'] as $user)
                            <div class="flex justify-between items-center text-xs gap-2">
                                <span class="font-medium text-gray-700 truncate" title="{{ $user['name'] }}">{{ $user['name'] }}</span>
                                <span class="inline-flex items-center rounded-md bg-indigo-50 px-1.5 py-0.5 text-2xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10 shrink-0">{{ $user['campus'] ?: 'N/A' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        
            <!-- Treasurers -->
            <div class="relative overflow-hidden rounded-xl bg-white p-4 sm:p-5 shadow-sm border border-gray-100 transition-all hover:shadow-md flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider truncate">Treasurers</dt>
                        <dd class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-indigo-600">{{ count($userRoles['treasurer'] ?? []) }}</dd>
                    </div>
                    <div class="p-2.5 sm:p-3 bg-indigo-50 text-indigo-600 rounded-lg shrink-0">
                        <svg class="w-5 h-5 sm:w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                </div>
                @if(!empty($userRoles['treasurer']))
                    <div class="mt-3 pt-3 border-t border-gray-50 max-h-24 overflow-y-auto custom-scrollbar space-y-1.5">
                        @foreach($userRoles['treasurer'] as $user)
                            <div class="flex justify-between items-center text-xs gap-2">
                                <span class="font-medium text-gray-700 truncate" title="{{ $user['name'] }}">{{ $user['name'] }}</span>
                                <span class="inline-flex items-center rounded-md bg-indigo-50 px-1.5 py-0.5 text-2xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10 shrink-0">{{ $user['campus'] ?: 'N/A' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        
            <!-- Members (Total Users Example) -->
            <div class="relative overflow-hidden rounded-xl bg-white p-4 sm:p-5 shadow-sm border border-gray-100 transition-all hover:shadow-md">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider truncate">Total Members</dt>
                        <dd class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-indigo-600">{{ $allUsers->count() }}</dd>
                    </div>
                    <div class="p-2.5 sm:p-3 bg-indigo-50 text-indigo-600 rounded-lg shrink-0">
                        <svg class="w-5 h-5 sm:w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                </div>
            </div>
        
        </div>
        
        <!-- Responsive Layout Styling -->
        <style>
            .custom-scrollbar::-webkit-scrollbar { width: 3px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
            .text-2xs { font-size: 0.65rem; line-height: 1rem; }
        </style>
        
        {{-- <x-select onchange="toggleForm(this.value)">
            <option value="role-based" selected>Role Based Access Config</option>
            <option value="user-based">User Based Access Config</option>
        </x-select> --}}
        <div class="mb-4 max-w-sm">
            <label for="config-option" class="block mb-2 text-sm font-medium text-gray-900">
              Select Assignment Option
            </label>
            <select 
              name="config-option" 
              id="config-option" 
              onchange="toggleForm(this.value)" 
              class="block w-full px-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"
            >
              {{-- <option value="role-based">Role Based Access Config</option> --}}
              <option value="user-based" selected>User Based Access Config</option>
            </select>
        </div>

        <!-- Master Matrix Form -->
        <div id="permission-form-container">
            <!-- Load the default form immediately on page render -->
            {{-- @include('admin.roles-permissions.partials.role-based-form') --}}
            @include('admin.roles-permissions.partials.user-based-form')

        </div>

        <div id="form-templates" style="display: none;">
            <div id="template-role-based">
                @include('admin.roles-permissions.partials.role-based-form')
            </div>
            <div id="template-user-based">
                @include('admin.roles-permissions.partials.user-based-form')
            </div>
        </div>
        
    </div>

    <script>

        // Compile Laravel data structures directly into optimized client-side arrays
        const SYSTEM_USER_DATASET = {!! json_encode($allUsers->map(function($u) {
            return [
                'id'     => $u->user_id,
                'empId'  => $u->member?->employee_number,
                'name'   => $u->name,
                'email'  => $u->email,
                'campus' => $u->member?->campus?->acronym ?? 'No Campus Assignment',
            ];
        })) !!};

        // 1. Process matching queries as the user types
        function handleUserRealtimeSearch(query) {
            const suggestionBox = document.getElementById('search-suggestion-dataset');
            const cleanQuery = query.trim().toLowerCase();
        
            // Clear and hide immediately if search text is empty
            if (!cleanQuery) {
                suggestionBox.innerHTML = '';
                suggestionBox.style.display = 'none';
                return;
            }
        
            // Filter local array data by name, email, or campus
            const matches = SYSTEM_USER_DATASET.filter(user => {
                return user.name.toLowerCase().includes(cleanQuery) || 
                       user.email.toLowerCase().includes(cleanQuery) || 
                       user.campus.toLowerCase().includes(cleanQuery);
            });
        
            if (matches.length === 0) {
                suggestionBox.innerHTML = '<div style="padding: 10px; color: #888; text-align: center;">No matches found</div>';
                suggestionBox.style.display = 'block';
                return;
            }
        
            // Compile match rows dynamically
            let htmlOutput = '';
            matches.forEach(user => {
                // CRITICAL: Wrap user.id in quotes because it is a UUID string, not a number!
                const safeId = user.id ? `"${user.id}"` : '""'; 

                htmlOutput += `
                    <div class="suggestion-item" 
                         onclick='selectUserFromDataset(${safeId}, "${user.name.replace(/"/g, '\\"')}", "${user.campus.replace(/"/g, '\\"')}")'
                         style="padding: 10px 15px; border-bottom: 1px solid #f0f0f0; cursor: pointer;">
                        <strong style="display:block; color:#333;">${user.name}</strong>
                        <small style="color:#666;">${user.email} &bull; <span style="color:#3085d6;">${user.campus}</span></small>
                    </div>
                `;
            });
        
            suggestionBox.innerHTML = htmlOutput;
            suggestionBox.style.display = 'block';
        
            // Simple CSS hover styling injector trick for dynamic templates
            $('.suggestion-item').hover(
                function() { $(this).css('background-color', '#f1f5f9'); },
                function() { $(this).css('background-color', '#ffffff'); }
            );
        }

        // 2. Triggers when an administrator clicks a list item selection row
        function selectUserFromDataset(userId, userName, userCampus) {
            // Synchronize Input box field text content visually
            document.getElementById('user-autocomplete-input').value = `${userName} (${userCampus})`;

            // Shut down dataset visualization panel frame layer
            document.getElementById('search-suggestion-dataset').style.display = 'none';
        
            // Fire data acquisition system processing pipeline
            loadUserPermissionMatrix(userId, userName, userCampus);
            // loadUserRolesMetrix(userId, userName, userCampus);
        }

        // 3. Request existing configurations via AJAX and cleanly reveal the form view grid - request permission
        function loadUserPermissionMatrix(userId, userName, userCampus) {

            if (!userId || userId === 'null' || userId === 0) {
                console.error('Aborted operation: Invalid User ID provided.');
                Swal.fire({
                    title: 'Selection Error',
                    text: 'Please select a valid user profile from the search list suggestions.',
                    icon: 'warning'
                });
                return;
            }

            Swal.fire({
                title: 'Fetching User Matrix...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        
            $.ajax({
                url: `/admin/user-access/${userId}/access`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    Swal.close();
                
                    if (response.status === 'success') {
                        // Populate structural IDs and text descriptors
                        document.getElementById('target-user-id-field-permission').value = userId;
                        document.getElementById('display-target-username-permission').textContent = `${userName} [${userCampus}]`;

                        document.getElementById('target-user-id-field-role').value = userId;
                        document.getElementById('display-target-username-role').textContent = `${userName} [${userCampus}]`;

                        document.getElementById('assignment-card-wrapper').style.display = 'block';

                        // Wipe checking markers status fields entirely
                        $('.user-permission-checkbox').prop('checked', false);
                    
                        // Set checking ticks matching response context database records array
                        response.direct_permissions.forEach(permissionName => {
                            $(`.user-permission-checkbox[value="${permissionName}"]`).prop('checked', true);
                        });

                        $('.user-role-checkbox').prop('checked', false);
                        response.roles.forEach(roleName => {
                            $(`.user-role-checkbox[value="${roleName}"]`).prop('checked', true);
                        });

                        // Clear previous animations and cleanly reveal the form container
                        $('#setup-permissions').hide().fadeIn(400);
                    }
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    console.error('Failed to sync data components state stream:', error);
                    Swal.fire({
                        title: 'Data Load Error',
                        text: 'Could not fetch permissions for this user account.',
                        icon: 'error'
                    });
                }
            });
        }

        // Close suggestion box automatically if admin clicks anywhere else on the document frame page
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-search-autocomplete-wrapper')) {
                const box = document.getElementById('search-suggestion-dataset');
                if (box) box.style.display = 'none';
            }
             initTabsEngine();
        });

       function toggleForm(value) {
            const targetContainer = document.getElementById('permission-form-container');
            let templateSourceId = '';

            switch(value.trim().toLowerCase()) {
                case 'user-based':
                    templateSourceId = 'template-user-based';
                    break;
                case 'role-based':
                default:
                    templateSourceId = 'template-role-based';
                    break;
            }
        
            // Grab the pre-rendered HTML from the hidden storage container
            const templateElement = document.getElementById(templateSourceId);

            if (templateElement) {
                // Clone the content and inject it cleanly into the active layout frame
                targetContainer.innerHTML = templateElement.innerHTML;
            }
        }


        function handleRolePermissionUpdate(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            Swal.fire({
              icon: 'warning',
              title: 'Warning',
              html: 'Are you sure you want to update these settings?<br>Changes will take effect immediately on success',
              showCancelButton: true,
              confirmButtonColor: "#3085d6",
              cancelButtonColor: "#d33",
              confirmButtonText: "Yes, update it!"
            }).then((result) => {
              if (result.isConfirmed) {
                Swal.showLoading();
                
                $.ajax({
                  url: form.action,  
                  type: 'POST', // Fallback to POST if method isn't explicit in HTML                
                  data: formData,
                  processData: false,          // Tell jQuery NOT to convert the FormData object into a string
                  contentType: false,          // Tell jQuery NOT to set a default Content-Type header
                  success: function(response) {
                    Swal.fire({
                      title: 'SUCCESS!',
                      text: response.message || 'Settings updated successfully',
                      icon: response.status ?? 'success'
                    });
                  },
                  error: function(xhr, status, error) {
                    console.error('Form submission failed:', error);
                    let errorMessage = xhr.responseJSON?.message || 'Something went wrong. Please try again.';
          
                    Swal.fire({
                      title: 'ERROR!',
                      text: errorMessage,
                      icon: 'error'
                    });
                  }
                });
              } else {
                Swal.fire({
                  title: 'CANCELLED!',
                  text: 'No settings config was changed',
                  icon: 'info'
                });
              }
            });
        }

        function initTabsEngine(user_id = null, userName = null, userCampus = null) {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabPanels = document.querySelectorAll('.tab-panel');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    tabButtons.forEach(btn => {
                        
                        btn.classList.remove('text-blue-600', 'border-blue-600', 'bg-white');
                        btn.classList.add('text-slate-500', 'border-transparent', 'hover:text-slate-700', 'hover:bg-slate-100');
                    });
                    tabPanels.forEach(panel => {
                        panel.classList.remove('block');
                        panel.classList.add('hidden');
                    });
                
                    button.classList.remove('text-slate-500', 'border-transparent', 'hover:text-slate-700', 'hover:bg-slate-100');
                    button.classList.add('text-blue-600', 'border-blue-600', 'bg-white');
                
                    const targetId = button.getAttribute('data-target');
                    document.getElementById(targetId).classList.remove('hidden');
                    document.getElementById(targetId).classList.add('block');
                });
            });
        }

    </script>

</x-app-layout>
