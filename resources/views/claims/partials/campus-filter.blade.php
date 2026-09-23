    @if(Auth::user()->can('view all campus data') && !$isMemberMenu)
        <div class="w-full mb-4">
            <div class="p-6 bg-gray-50 dark:bg-zinc-800/40 rounded-xl border border-gray-200 dark:border-zinc-800/80">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Campus Claim Filter</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Filter claim records by campus for leadership review.</p>
                    </div>
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <select name="campus_filter" onchange="this.form.submit()" class="block w-full px-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body">
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
        </div>
    @endif