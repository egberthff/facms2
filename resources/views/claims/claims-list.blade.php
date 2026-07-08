<x-app-layout>
<div class="max-w-7xl mx-auto p-4" 
     x-data="{ 
        currentStatus: 'all', 
        searchText: '',
        matches(status, number, desc, type) {
            const matchesStatus = this.currentStatus === 'all' || status.toLowerCase() === this.currentStatus.toLowerCase();
            const searchLower = this.searchText.toLowerCase();
            const matchesSearch = !this.searchText || 
                                 number.toLowerCase().includes(searchLower) || 
                                 desc.toLowerCase().includes(searchLower) ||
                                 type.toLowerCase().includes(searchLower);
            return matchesStatus && matchesSearch;
        }
     }">
    
    <!-- ========================================= -->
    <!-- CLAIMS LIST CARD -->
    <!-- ========================================= -->
    <div class="bg-white border border-blue-700 rounded-2xl shadow-sm">
        <!-- Header -->
        <div class="p-4 border-b">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <!-- Filters -->
                <div class="flex flex-wrap gap-2">
                    <template x-for="status in ['all', 'Pending', 'Processing', 'Approved', 'Disbursed', 'Rejected']">
                        <button 
                            type="button"
                            @click="currentStatus = status"
                            :class="currentStatus === status 
                                ? 'bg-blue-600 text-white border-blue-600' 
                                : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                            class="px-4 py-2 text-sm rounded-lg border transition font-medium capitalize"
                            x-text="status">
                        </button>
                    </template>
                </div>
                <!-- Search -->
                <div class="relative w-full lg:w-72">
                    <input
                        type="text"
                        x-model="searchText"
                        placeholder="Search claim..."
                        class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-orange-400 focus:border-orange-400">
                    <svg
                        xmlns="http://w3.org"
                        class="absolute left-3 top-2.5 w-4 h-4 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Ref #</th>
                        <th class="px-4 py-3 text-left font-semibold">Claim Type</th>
                        <th class="px-4 py-3 text-left font-semibold">Description</th>
                        <th class="px-4 py-3 text-right font-semibold">Amount</th>
                        <th class="px-4 py-3 text-center font-semibold">Date Filed</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($claims as $claim)
                    <tr x-show="matches(
                            '{{ $claim->status }}', 
                            '{{ $claim->claim_number }}', 
                            '{{ addslashes($claim->remarks ?? $claim->description) }}', 
                            '{{ $claim->claimType->name }}'
                        )"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100">
                        <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-900">{{ $claim->claim_number }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $claim->claimType->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ Str::limit($claim->remarks ?? $claim->description, 40) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format($claim->requested_amount, 2) }}</td>
                        <td class="px-4 py-3 text-center text-slate-500">{{ $claim->submitted_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $claim->status === 'Pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                {{ $claim->status === 'Approved' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                {{ $claim->status === 'Rejected' ? 'bg-rose-100 text-rose-800' : '' }}
                                {{ $claim->status === 'Processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $claim->status === 'Disbursed' ? 'bg-purple-100 text-purple-800' : '' }}
                            ">
                                {{ $claim->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <!-- Action button markup -->
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-app-layout>
