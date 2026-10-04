    @if(Auth::user()->can('view all campus data') && !$isMemberMenu)
        <div class="w-full mb-4">
            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 sm:p-5 dark:border-zinc-800/80 dark:bg-zinc-800/40">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Campus Claim Filter</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Filter claim records by campus for leadership review.</p>
                    </div>
                    <form method="GET" action="{{ url()->current() }}" class="flex w-full items-center gap-2 md:w-auto md:min-w-64">
                        <select name="campus_filter" onchange="this.form.submit()" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
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
        </div>
    @endif