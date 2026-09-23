<div class="p-4 border-b">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <!-- Filters -->
        <div class="flex flex-wrap gap-2">
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
        </div>
        <!-- Search -->
        <form method="GET" action="{{ url()->current() }}">
            <div class="relative w-full lg:w-72">
                <label for="contribution-type" class="block mb-2.5 text-sm font-medium text-heading">Select an option</label>
                <select id="contribution-type" name="contribution_type" onchange="this.form.submit()" class="block w-full px-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body">
                    @foreach(\App\Enums\ClaimTypes::cases() as $case)
                        <option value="{{ $case->value }}" {{ $contribution_type == $case->value ? 'selected' : '' }}>
                            {{ $case->value }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>