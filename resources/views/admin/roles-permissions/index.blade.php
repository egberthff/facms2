<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <!-- Header -->
        <header class="mb-10 flex flex-col md:flex-row md:items-center md:justify-between border-b border-gray-200 pb-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">Access Control</h1>
                <p class="mt-2 text-sm text-gray-500">Configure global application roles and fine-tune individual module permissions.</p>
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
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-10">
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6 border border-gray-100">
                <dt class="truncate text-sm font-medium text-gray-500">Total System Roles</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-indigo-600">{{ $roles->count() }}</dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6 border border-gray-100">
                <dt class="truncate text-sm font-medium text-gray-500">System Permissions</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-emerald-600">{{ $permissions->count() }}</dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6 border border-gray-100">
                <dt class="truncate text-sm font-medium text-gray-500">Active Core Guards</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">web / api</dd>
            </div>
        </div>

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
              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
            >
              <option value="role-based" selected>Role Based Access Config</option>
              <option value="user-based">User Based Access Config</option>
            </select>
        </div>

        <!-- Master Matrix Form -->
        <div id="permission-form-container">
            <!-- Load the default form immediately on page render -->
            @include('admin.roles-permissions.partials.role-based-form')
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
        }

        // 3. Request existing configurations via AJAX and cleanly reveal the form view grid
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
                url: `/admin/user-access/${userId}/permissions`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    Swal.close();
                
                    if (response.status === 'success') {
                        // Populate structural IDs and text descriptors
                        document.getElementById('target-user-id-field').value = userId;
                        document.getElementById('display-target-username').textContent = `${userName} [${userCampus}]`;
                    
                        // Wipe checking markers status fields entirely
                        $('.user-permission-checkbox').prop('checked', false);
                    
                        // Set checking ticks matching response context database records array
                        response.direct_permissions.forEach(permissionName => {
                            $(`.user-permission-checkbox[value="${permissionName}"]`).prop('checked', true);
                        });
                    
                        // Clear previous animations and cleanly reveal the form container
                        $('#user-permissions-form').hide().fadeIn(400);
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
    </script>

</x-app-layout>
