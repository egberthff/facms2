<!-- Live Search UI Component Frame -->
<div class="user-search-autocomplete-wrapper" style="position: relative; margin-bottom: 25px;">
    <label for="user-autocomplete-input" style="font-weight: bold; display: block; margin-bottom: 6px;">
        Search User Profile:
    </label>
    <div style="position: relative;">
        <input type="text" 
               id="user-autocomplete-input" 
               class="form-control" 
               placeholder="Type employee ID, name, email, or campus assignment..." 
               oninput="handleUserRealtimeSearch(this.value)"
               autocomplete="off"
               style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
               
        <!-- Floating Recommendation Suggestion Output Dataset Dropdown -->
        <div id="search-suggestion-dataset" 
             style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #ddd; border-radius: 0 0 4px 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 1050; max-height: 250px; overflow-y: auto;">
        </div>
    </div>
</div>

<!-- Core Matrix form container layout - remains hidden until item selected -->
<form id="user-permissions-form" action="{{ route('admin.users.sync-permissions') }}" method="POST" onsubmit="handleRolePermissionUpdate(event)" style="display: none;">
    @csrf
    @method('PUT')
    
    <input type="hidden" name="user_id" id="target-user-id-field">

    <div style="background: #f8f9fa; padding: 15px; border-left: 4px solid #3085d6; margin-bottom: 20px;">
        <h4 style="margin: 0;">Fine-Grained Matrix Control: <span id="display-target-username" style="color: #3085d6; font-weight: bold;"></span></h4>
    </div>
    
    <div class="permission-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 15px 0;">
        {{-- @if(isset($permissions)) --}}
            @foreach($permissions as $permission)
                <label style="display: block; padding: 8px; border: 1px solid #eee; background: #fff; cursor: pointer; border-radius: 4px; transition: background 0.2s;">
                    <input type="checkbox" 
                           name="permissions[]" 
                           class="user-permission-checkbox"
                           value="{{ $permission->name }}">
                    <span style="margin-left: 5px;">{{ $permission->name }}</span>
                </label>
            @endforeach
        {{-- @endif --}}
    </div>
    
    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-500 focus-visible:outline focus-visible:outline-2">
        Save Changes
    </button>
</form>

<!-- Add a quick CSS style block for smoother cross-browser hover rendering -->
<style>
    .suggestion-item:hover {
        background-color: #f1f5f9 !important;
    }
</style>
