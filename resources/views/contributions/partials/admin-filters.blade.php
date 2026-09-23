        {{-- @if(Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']) || Auth::user()->hasPermissionTo('view all campus data')) --}}
            <div class="p-6 bg-gray-50 dark:bg-zinc-800/40 rounded-xl border border-gray-200 dark:border-zinc-800/80">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Campus Batch Filter</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Filter batch collections and member contributions per campus.</p>
                    </div>

                    <form method="GET" action="{{ url()->current() }}" class="flex flex-col md:flex-row md:items-center justify-end gap-3 w-full">
    
                    <!-- Contribution Type Filter -->
                    <div class="flex items-center gap-2">
                        <select id="contribution-type" name="contribution_type" onchange="this.form.submit()" class="block w-full px-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body">
                            @foreach(\App\Enums\ClaimTypes::cases() as $case)
                                <option value="{{ $case->value }}" {{ $contribution_type == $case->value ? 'selected' : '' }}>
                                    {{ $case->value }}
                                </option>
                            @endforeach
                        </select>
                        {{-- <select name="contribution_type" onchange="this.form.submit()" class="text-sm rounded-lg bg-white dark:bg-zinc-900 border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-300 focus:ring-blue-500 focus:border-blue-500 min-w-[200px]">
                            <option value="claims" {{ request('contribution_type') == 'claims' ? 'selected' : '' }}>Claims</option>
                            <option value="annual" {{ request('contribution_type') == 'annual' ? 'selected' : '' }}>Annual</option>
                            <option value="cna_share" {{ request('contribution_type') == 'cna_share' ? 'selected' : '' }}>CNA Share</option>
                        </select> --}}
                    </div>
                
                    <!-- Campus Filter (Note: Fixed array syntax to object syntax) -->
                     @if(Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']) || Auth::user()->hasPermissionTo('view all campus data'))
                        <div class="flex items-center gap-2">
                            <select name="campus_filter" onchange="this.form.submit()" class="block w-full px-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body">
                                <option value="">All Campuses (Global View)</option>
                                @foreach($allCampuses as $campus)
                                    <option value="{{ $campus->id }}" {{ $selectedCampusId == $campus->id ? 'selected' : '' }}>
                                        {{ $campus->acronym }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                
                </form>

                </div>
            </div>
        {{-- @endif --}}