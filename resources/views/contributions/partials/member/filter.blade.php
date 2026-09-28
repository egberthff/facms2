<div class="p-4">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <!-- Filters -->
        {{-- <div class="flex flex-wrap gap-2">
            <template x-for="status in statuses" :key="status">
                <button 
                    type="button"
                    @click="currentStatus = status; filterContributions(status)"
                    :class="currentStatus === status 
                        ? 'bg-blue-600 text-white border-blue-600' 
                        : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                    class="px-4 py-2 text-sm rounded-lg border transition font-medium capitalize"
                    x-text="status">
                </button>
            </template>
        </div> --}}
        <div class="relative inline-block text-left" x-data="{ open: false }" @click.outside="open = false">
            <!-- Dropdown Trigger Button -->
            <button 
                type="button"
                @click="open = !open"
                class="inline-flex w-full justify-between items-center gap-x-2 rounded-lg bg-white px-4 py-2 text-sm font-medium text-slate-700 border border-slate-200 hover:bg-slate-50 transition focus:outline-none focus:ring-2 focus:ring-blue-400 min-w-[160px]">
                
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 font-normal">Status:</span>
                    <!-- Dynamically displays selected status -->
                    <span class="capitalize font-semibold text-slate-800" x-text="currentStatus"></span>
                </div>
        
                <!-- Chevron Icon -->
                <svg class="h-4 w-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
        
            <!-- Dropdown Menu Options Panel -->
            <div 
                x-show="open"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute left-0 z-50 w-56 origin-top-left rounded-lg bg-white shadow-lg border border-slate-100 focus:outline-none py-1 max-h-60 overflow-y-auto"
                style="display: none;">
                
                <!-- Loop through statuses -->
                <template x-for="status in statuses" :key="status">
                    <button 
                        type="button"
                        @click="currentStatus = status; filterContributions(status); open = false"
                        :class="currentStatus === status 
                            ? 'bg-blue-50 text-blue-600 font-semibold' 
                            : 'text-slate-700 hover:bg-slate-50 font-normal'"
                        class="flex w-full items-center px-4 py-2.5 text-sm transition capitalize text-left"
                        x-text="status">
                    </button>
                </template>
            </div>
        </div>
        
        <!-- Search -->
        <form method="GET" action="{{ url()->current() }}">
            <div class="relative w-full lg:w-72">
                <label for="contribution-type" class="block mb-2.5 text-sm font-medium text-heading">Select an option</label>
                <select id="contribution-type" name="contribution_type" onchange="this.form.submit()" class="block w-full px-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body">
                    @foreach(\App\Enums\ClaimTypes::cases() as $case)
                        <option value="{{ $case->value }}" {{ $contribution_type == strtolower(trim($case->value)) ? 'selected' : '' }}>
                            {{ $case->value }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>