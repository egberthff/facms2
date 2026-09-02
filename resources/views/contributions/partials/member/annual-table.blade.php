<table class="dt-table min-w-full text-sm">
    <thead class="bg-gray-100">
        <tr>
            <th class="px-4 py-3 text-left font-semibold">Ref #</th>
            <th class="px-4 py-3 text-left font-semibold">Year</th>
            <th class="px-4 py-3 text-left font-semibold">Payment Details</th>
            <th class="px-4 py-3 text-right font-semibold">Amount Paid</th>
            <th class="px-4 py-3 text-center font-semibold">Paid Date</th>
            <th class="px-4 py-3 text-center font-semibold">Status</th>
            <th class="px-4 py-3 text-center font-semibold">Action</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
        @foreach($contributions as $item)
            {{-- 
                Assuming your Alpine.js 'matches' function filters values, 
                we match the annual data fields below.
            --}}
            <tr x-show="matches(
                    '{{ $item['status'] }}', 
                    '{{ $item['reference_number'] ?? '' }}', 
                    '{{ $item['contribution_year'] ?? '' }}', 
                    '{{ $item['payment_reference_number'] ?? '' }}'
                )"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100">

                <!-- 1. Human Readable Reference ID -->
                <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-900">
                    {{ $item['reference_number'] ?? 'N/A' }}
                </td>

                <!-- 2. Contribution Year -->
                <td class="px-4 py-3 text-slate-600 font-medium">
                    {{ $item['contribution_year'] }}
                </td>

                <!-- 3. Payment Method & External Gateway Reference -->
                <td class="px-4 py-3 text-slate-600">
                    <div class="font-medium text-slate-900">
                        {{ $item['payment_method']['name'] ?? 'Direct/System' }}
                    </div>
                    <div class="text-xs text-slate-400">
                        Ref: {{ $item['payment_reference_number'] ?? 'None' }}
                    </div>
                </td>

                <!-- 4. Amount Paid / Due -->
                <td class="px-4 py-3 text-right font-bold text-slate-900">
                    ₱{{ number_format($item['amount_paid'], 2) }}
                </td>

                <!-- 5. Paid Timestamp / Notes -->
                <td class="px-4 py-3 text-center text-slate-500">
                    <div class="text-sm">{{ $item['paid_at'] ? date('M d, Y', strtotime($item['paid_at'])) : 'N/A' }}</div>
                    @if(!empty($item['remarks']))
                        <div class="text-xs text-slate-400 italic mt-0.5" title="{{ $item['remarks'] }}">
                            {{ Str::limit($item['remarks'], 20) }}
                        </div>
                    @endif
                </td>

                <!-- 6. Payment Status Badge -->
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize
                        {{ $item['status'] === 'Unpaid' ? 'bg-amber-100 text-amber-800' : '' }}
                        {{ $item['status'] === 'Paid' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ !in_array($item['status'], ['Unpaid', 'Paid']) ? 'bg-slate-100 text-slate-800' : '' }}
                    ">
                        {{ $item['status'] ?? 'Unknown' }}
                    </span>
                </td>

                <!-- 7. Action -->
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    @if($item['status'] === 'Unpaid')
                        <button
                            type="button"
                            @click="activeAnnual = @js($item); openAnnualModal = true;"
                            class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent text-white rounded-lg text-xs font-semibold hover:bg-blue-700 shadow-sm transition">
                            View Details
                        </button>
                    @else
                        <button
                            type="button"
                            @click="activeAnnual = @js($item); openAnnualModal = true;"
                            class="inline-flex items-center px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-100 transition">
                            View Details
                        </button>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>