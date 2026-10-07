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

        <!-- Middle Row: Claim cycle allowance + claims by status -->
        <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-3">
            <div class="flex flex-col rounded-xl bg-navy-900 p-5 text-white shadow-sm sm:p-6 lg:col-span-2">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wider text-white/70">Available Balance</div>
                        <div class="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">₱{{ number_format($balance, 2) }}</div>
                        @if($allowance)
                            <div class="mt-1 text-xs text-white/60">of ₱{{ number_format($allowance['limit'], 2) }} claim cycle limit</div>
                        @endif
                    </div>
                    <div class="flex w-full gap-3 sm:w-auto">
                        <a href="{{ route('claim.my', Auth::user()) }}" class="flex-1 rounded-full border border-white/40 px-4 py-2.5 text-center text-sm font-medium text-white transition-colors hover:bg-white/10">
                            View Details
                        </a>
                        <a href="{{ route('claim.create') }}" class="flex-1 rounded-full bg-[#F28C4F] px-4 py-2.5 text-center text-sm font-medium text-white transition-colors hover:bg-[#e07b3e]">
                            New Claim
                        </a>
                    </div>
                </div>

                @if($allowance && $allowance['limit'] > 0)
                    @php
                        $segments = [
                            ['label' => 'Disbursed', 'value' => $allowance['disbursed'], 'color' => 'bg-chart-1'],
                            ['label' => 'Approved, not yet released', 'value' => $allowance['approved'], 'color' => 'bg-chart-2'],
                            ['label' => 'Reserved by claims in review', 'value' => $allowance['reserved'], 'color' => 'bg-chart-3'],
                        ];
                    @endphp

                    <!-- Cycle limit meter: segments add up to the limit; the white track is what remains -->
                    <div class="mt-6">
                        <div class="flex h-3 w-full gap-0.5 overflow-hidden rounded-full bg-white/15"
                             role="img"
                             aria-label="Claim cycle usage: ₱{{ number_format($allowance['limit'] - $allowance['remaining'], 2) }} used of ₱{{ number_format($allowance['limit'], 2) }}">
                            @foreach($segments as $segment)
                                @if($segment['value'] > 0)
                                    <div class="{{ $segment['color'] }} h-full" style="width: {{ round($segment['value'] / $allowance['limit'] * 100, 2) }}%"></div>
                                @endif
                            @endforeach
                        </div>

                        <dl class="mt-4 grid grid-cols-1 gap-3 text-xs sm:grid-cols-2 xl:grid-cols-4">
                            @foreach($segments as $segment)
                                <div class="flex items-start gap-2">
                                    <span class="{{ $segment['color'] }} mt-0.5 h-2.5 w-2.5 shrink-0 rounded-sm"></span>
                                    <div>
                                        <dt class="text-white/70">{{ $segment['label'] }}</dt>
                                        <dd class="font-semibold tabular-nums">₱{{ number_format($segment['value'], 2) }}</dd>
                                    </div>
                                </div>
                            @endforeach
                            <div class="flex items-start gap-2">
                                <span class="mt-0.5 h-2.5 w-2.5 shrink-0 rounded-sm bg-white/15 ring-1 ring-white/30"></span>
                                <div>
                                    <dt class="text-white/70">Remaining</dt>
                                    <dd class="font-semibold tabular-nums">₱{{ number_format($allowance['remaining'], 2) }}</dd>
                                </div>
                            </div>
                        </dl>
                    </div>
                @endif
            </div>

            <x-chart-card
                title="My Claims by Status"
                subtitle="All your claims, in workflow order"
                chart="claimsByStatus"
                :series="$chartData['claimsByStatus']"
                type="bar"
                critical="Rejected"
                height="h-64" />
        </div>

        <script type="application/json" id="dashboard-chart-data">@json($chartData)</script>

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
