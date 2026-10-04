<x-app-layout>

    <x-page-header
    title="My Dashboard"
    description="Welcome back."/>

    <div class="flex flex-col space-y-6">

        <!-- Top Row: 4-Column Claims Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- ============ Total Claims ========== -->
            <x-stats-card
            title="Total Claims"
            value="{{ $totalClaims }}">
            <x-slot:icon>
            <x-heroicon-o-clock class="w-10 h-10 text-yellow-500"/>
            </x-slot:icon>
            </x-stats-card>

            <!-- ============ Processing ============ -->
            <x-stats-card
            title="Processing"
            value="{{ $processing }}">
            <x-slot:icon>
            <x-heroicon-o-clock class="w-10 h-10 text-yellow-500"/>
            </x-slot:icon>
            </x-stats-card>

            <!-- ============ Disbursed ============= -->
            <x-stats-card
            title="Disbursed"
            value="{{ $disbursed }}">
            <x-slot:icon>
            <x-heroicon-o-clock class="w-10 h-10 text-yellow-500"/>
            </x-slot:icon>
            </x-stats-card>

            <!-- ============ Pending =============== -->
            <x-stats-card
            title="Pending"
            value="{{ $pending }}">
            <x-slot:icon>
            <x-heroicon-o-clock class="w-10 h-10 text-yellow-500"/>
            </x-slot:icon>
            </x-stats-card>
        </div>

        <!-- Middle Row: Balance Card with Action Buttons -->
        <div class="flex justify-center">
            <div class="w-full max-w-md bg-navy-900 text-white rounded-2xl p-5 sm:p-6 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wider text-white/70 mb-2">
                    Balance
                </div>
                
                <!-- Large Peso Currency Display -->
                <div class="text-3xl sm:text-4xl font-semibold tracking-tight tabular-nums mb-6 flex items-center">
                    <span>₱{{ $balance }}</span>
                </div>

                <!-- Action Button Actions -->
                <div class="flex gap-3">
                    <a href="{{ route('claim.my', Auth::user()) }}" class="flex-1 text-center border border-white/40 hover:bg-white/10 text-white font-medium py-2.5 px-4 rounded-full transition-colors text-sm">
                        View Details
                    </a>
                    <a href="{{ route('claim.create') }}" class="flex-1 text-center bg-[#F28C4F] hover:bg-[#e07b3e] text-white font-medium py-2.5 px-4 rounded-full transition-colors text-sm">
                        New Claim
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom Row: Recent Transactions Header Line -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs sm:p-6">
            <h3 class="mb-4 text-base font-semibold text-gray-900">
                Recent Transactions
            </h3>
            <div class="w-full overflow-x-auto">
            <table class="dt-responsive w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Ref #</th>
                        <th class="px-4 py-3 text-left font-semibold">Claim Type</th>
                        <th class="px-4 py-3 text-right font-semibold">Amount</th>
                        <th class="px-4 py-3 text-center font-semibold">Date Filed</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody>
                      @foreach($recentTransactions as $claim)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-900">{{ $claim->claim_number }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $claim->claimType->name }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-900 tabular-nums">{{ number_format($claim->requested_amount, 2) }}</td>
                        <td class="px-4 py-3 text-center text-slate-500">{{ $claim->submitted_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset ring-current/20
                                {{ $claim->status === 'Pending' ? 'bg-amber-50 text-amber-700' : '' }}
                                {{ $claim->status === 'Approved' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                {{ $claim->status === 'Rejected' ? 'bg-rose-50 text-rose-700' : '' }}
                                {{ $claim->status === 'Processing' ? 'bg-blue-50 text-blue-700' : '' }}
                                {{ $claim->status === 'Disbursed' ? 'bg-purple-50 text-purple-700' : '' }}
                            ">
                                {{ $claim->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

    </div>

</x-app-layout>
