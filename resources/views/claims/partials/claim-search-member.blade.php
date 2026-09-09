<!-- Live Search UI Component Frame -->
<div class="user-search-autocomplete-wrapper relative mb-6 font-sans">
    <label for="user-autocomplete-input" class="block font-bold text-slate-700 mb-1.5">
        Search Member:
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
                class="tab-btn w-full py-4 px-6 text-sm font-semibold border-b-2 transition-all duration-200 outline-none text-slate-500 border-transparent hover:text-slate-700 hover:bg-slate-100" 
                data-target="assign-role">
                 Member
            </button>
        </li>
    </ul>
            
    <div class="p-6">
        <!-- Form Panel 2: Assign System Roles -->
        
    </div>
</div>
