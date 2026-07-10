<x-app-layout>
<div class="p-6 bg-gray-50 min-h-screen text-gray-800 font-sans">
    <!-- Top Table Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-lg font-bold text-gray-900 tracking-tight">Ready for Disbursement</h2>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto min-h-[240px]">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 text-gray-500 font-semibold tracking-wider text-xs uppercase">
                        <th class="px-6 py-3">Ref#</th>
                        <th class="px-6 py-3">Member</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Amount</th>
                        <th class="px-6 py-3">Bank</th>
                        <th class="px-6 py-3">Account</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($disbursements as $row)
                        <tr>
                            <td class="px-6 py-4 text-center">{{ $row->claim_id }}</td>
                            <td class="px-6 py-4 text-center">{{ $row->member }}</td>
                            <td class="px-6 py-4 text-center">{{ $row->claim_type }}</td>
                            <td class="px-6 py-4 text-center">{{ $row->requested_amount }}</td>
                            <td class="px-6 py-4 text-center">{{ $row->payment_method }}</td>
                            <td class="px-6 py-4 text-center">{{ $row->account }}</td>
                            <td class="px-6 py-4 text-center">{{ $row->release_by }}</td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('disbursements.show', $row->id) }}" class="text-blue-600 hover:underline">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400 italic">
                                No disbursements ready at this time.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <!-- Bottom Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Disbursement Summary Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Disbursement Summary</h3>
            </div>
            <div class="divide-y divide-gray-100">
                <div class="flex justify-between items-center px-6 py-4">
                    <span class="text-sm text-gray-600 font-medium">Total Claims Approved</span>
                    <span class="text-base font-semibold text-gray-900">$0.00</span>
                </div>
                <div class="flex justify-between items-center px-6 py-4">
                    <span class="text-sm text-gray-600 font-medium">Already Disbursed</span>
                    <span class="text-base font-semibold text-emerald-600">$0.00</span>
                </div>
                <div class="flex justify-between items-center px-6 py-4">
                    <span class="text-sm text-gray-600 font-medium">Pending Disbursement</span>
                    <span class="text-base font-semibold text-amber-600">$0.00</span>
                </div>
            </div>
        </div>

        <!-- Fund Allocation Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Fund Allocation</h3>
            </div>
            <div class="divide-y divide-gray-100">
                <div class="flex justify-between items-center px-6 py-4">
                    <span class="text-sm text-gray-600 font-medium">Current Balance</span>
                    <span class="text-base font-semibold text-gray-900">$0.00</span>
                </div>
                <div class="flex justify-between items-center px-6 py-4">
                    <span class="text-sm text-gray-600 font-medium">Reserved for Pending</span>
                    <span class="text-base font-semibold text-blue-600">$0.00</span>
                </div>
                <div class="flex justify-between items-center px-6 py-4 bg-gray-50/30">
                    <span class="text-sm text-gray-900 font-bold">Available Balance</span>
                    <span class="text-lg font-extrabold text-indigo-600">$0.00</span>
                </div>
            </div>
        </div>
    </div>
</div>

</x-app-layout>