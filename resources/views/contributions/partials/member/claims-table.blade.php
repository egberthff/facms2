<table class="dt-table min-w-full text-sm">
    <thead class="bg-gray-100">
        <tr>
            <th class="px-4 py-3 text-left font-semibold">Ref #</th>
            <th class="px-4 py-3 text-left font-semibold">Claimant (Benefit For)</th>
            <th class="px-4 py-3 text-left font-semibold">Claim Type</th>
            <th class="px-4 py-3 text-right font-semibold">My Contribution</th>
            <th class="px-4 py-3 text-center font-semibold">Issued Date</th>
            <th class="px-4 py-3 text-center font-semibold">Contribution Status</th>
            <th class="px-4 py-3 text-center font-semibold">Action</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
        @foreach($contributions as $item)
            <tr x-show="matches(
                    '{{ $item['status'] }}', 
                    '{{ $item['claim_number'] ?? '' }}', 
                    '{{ addslashes($item['claimant_name']) }}', 
                    '{{ addslashes($item['claim_type']) }}'
                )"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100">

                <!-- 1. Claim Reference Number -->
                <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-900">
                    {{ $item['claim_number'] ?? 'N/A' }}
                </td>

                <!-- 2. Claimant Metadata Summary -->
                <td class="px-4 py-3 text-slate-600">
                    <div class="font-medium text-slate-900">{{ $item['claimant_name'] }}</div>
                    <div class="text-xs text-slate-400">{{ $item['claimant_dept'] ?? 'No Dept' }}</div>
                </td>

                <!-- 3. Type of Claim -->
                <td class="px-4 py-3 text-slate-500 font-medium">
                    {{ $item['claim_type'] }}
                </td>

                <!-- 4. Individual Split Amount Owed -->
                <td class="px-4 py-3 text-right font-bold text-slate-900">
                    ₱{{ number_format($item['amount_to_pay'], 2) }}
                </td>

                <!-- 5. Date the requirement batch was issued -->
                <td class="px-4 py-3 text-center text-slate-500">
                    {{ $item['generated_at'] ?? 'N/A' }}
                </td>

                <!-- 6. Styled Context Payment Badges -->
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize
                        {{ $item['status'] === 'Unpaid' ? 'bg-amber-100 text-amber-800' : '' }}
                        {{ $item['status'] === 'Paid' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ !in_array($item['status'], ['Unpaid', 'Paid']) ? 'bg-slate-100 text-slate-800' : '' }}
                    ">
                        {{ $item['status'] ?? 'Unknown' }}
                    </span>
                </td>

                <!-- 7. Dynamic Action Dispatchers -->
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    @if($item['status'] === 'Unpaid')
                        <button
                            type="button"
                            @click="activeContribution = @js($item); openPaymentModal = true;"
                            class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent text-white rounded-lg text-xs font-semibold hover:bg-blue-700 shadow-sm transition">
                            View Details
                        </button>
                    @else
                        <button
                            type="button"
                            @click="activeContribution = @js($item); openPaymentModal = true;"
                            class="inline-flex items-center px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-100 transition">
                            View Details
                        </button>
                    @endif
                </td>

            </tr>
        @endforeach
    </tbody>
</table>