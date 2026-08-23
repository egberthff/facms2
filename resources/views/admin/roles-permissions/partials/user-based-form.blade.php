<!-- Live Search UI Component Frame -->
<div class="user-search-autocomplete-wrapper relative mb-6 font-sans">
    <label for="user-autocomplete-input" class="block font-bold text-slate-700 mb-1.5">
        Search User Profile:
    </label>
    <div class="relative">
        <input type="text" 
               id="user-autocomplete-input" 
               class="w-full p-2.5 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" 
               placeholder="Type employee ID, name, email, or campus assignment..." 
               oninput="handleUserRealtimeSearch(this.value)"
               autocomplete="off">
               
        <!-- Floating Recommendation Suggestion Output Dataset Dropdown -->
        <div id="search-suggestion-dataset" 
             class="hidden absolute top-full left-0 right-0 bg-white border border-slate-200 rounded-b-md shadow-lg z-[1050] max-h-[250px] overflow-y-auto">
        </div>
    </div>
</div>

<!-- Main Tabs Card Wrapper (Hidden by default until a user profile is selected) -->
<div id="assignment-card-wrapper" class="hidden w-full mx-auto my-8 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden font-sans">
    <!-- Tab Navigation Buttons -->
    <ul class="flex bg-slate-50 border-b border-slate-200 list-none p-0 m-0">
        <li class="flex-1">
            <button 
                class="tab-btn w-full py-4 px-6 text-sm font-semibold border-b-2 transition-all duration-200 outline-none text-blue-600 border-blue-600 bg-white" 
                data-target="setup-permissions">
                Setup Permissions
            </button>
       </li>
        <li class="flex-1">
            <button 
                class="tab-btn w-full py-4 px-6 text-sm font-semibold border-b-2 transition-all duration-200 outline-none text-slate-500 border-transparent hover:text-slate-700 hover:bg-slate-100" 
                data-target="assign-role">
                Assign Role
            </button>
        </li>
    </ul>
            
    <div class="p-6">
        <!-- Form Panel 1: Setup Permissions Matrix -->
        <div id="setup-permissions" class="tab-panel block">
            <form id="user-permissions-form" action="{{ route('admin.users.sync-access') }}" data-action-type="permission-assignment" class="space-y-4" onsubmit="handleRolePermissionUpdate(event)">
                @csrf
                @method('PUT')

                <input type="hidden" name="user_id" id="target-user-id-field-permission">
                <input type='hidden' name='action_type' value="permission">

                <div class="bg-slate-50 p-4 border-l-4 border-blue-500 mb-5 rounded-r">
                    <h4 class="m-0 text-slate-800 font-medium">Fine-Grained Matrix Control: <span id="display-target-username-permission" class="text-blue-600 font-bold"></span></h4>
                </div>

                <div class="permission-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 my-4">
                    @foreach($permissions as $permission)
                        <label class="flex items-center p-2.5 border border-slate-100 bg-white cursor-pointer rounded hover:bg-slate-50 transition-colors">
                            <input type="checkbox" 
                                   name="permissions[]" 
                                   class="user-permission-checkbox h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                                   value="{{ $permission->name }}">
                            <span class="ml-2 text-sm text-slate-700">{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-600">
                    Save Changes
                </button>
            </form>
        </div>
          
        <!-- Form Panel 2: Assign System Roles -->
        <div id="assign-role" class="tab-panel hidden">
            <form id="user-role-form" method="POST" class="space-y-4" action="{{ route('admin.users.sync-access') }}" data-action-type="role-assignment" onsubmit="handleRolePermissionUpdate(event)">
                @csrf
                @method('PUT')

                <input type="hidden" name="user_id" id="target-user-id-field-role">
                <input type='hidden' name='action_type' value="role">

                <div class="bg-slate-50 p-4 border-l-4 border-blue-500 mb-5 rounded-r">
                    <h4 class="m-0 text-slate-800 font-medium">Role Assignment Control: <span id="display-target-username-role" class="text-blue-600 font-bold"></span></h4>
                </div>

                <div class="role-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 my-4">
                    @foreach($roles as $role)
                        @if(str_contains($role->name, 'Federated') && !$isFederated)
                           @continue
                        @endif
                        <label class="flex items-center p-2.5 border border-slate-100 bg-white cursor-pointer rounded hover:bg-slate-50 transition-colors">
                            <input type="radio" 
                                   name="role" 
                                   class="user-role-checkbox h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                                   value="{{ $role->name }}">
                            <span class="ml-2 text-sm text-slate-700">{{ $role->name }}</span>
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-600">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</div>
