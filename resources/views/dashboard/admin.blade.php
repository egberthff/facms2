<x-app-layout>

    @if(isset($allCampuses) && Auth::user()->can('view all campus data'))
        <div class="mb-6 rounded-xl border border-gray-200 bg-gray-50/60 p-4 sm:p-5 dark:border-zinc-800/80 dark:bg-zinc-800/40">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Campus Dashboard Filter</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Filter dashboard metrics by campus for leadership review.</p>
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
    @endif

    <!-- KPI row: 6 stat cards + the treasury balance tile fill two rows of four on xl -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 xl:grid-cols-4">

        <div class="flex flex-col justify-between rounded-xl bg-navy-900 p-5 text-white shadow-sm sm:col-span-2 sm:p-6">
            <div class="text-xs font-medium uppercase tracking-wider text-white/70">Treasury Balance</div>
            <div class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">₱{{ number_format($balance, 2) }}</div>
            <div class="mt-2 text-xs text-white/60">
                @if(!empty($selectedCampusId) && isset($allCampuses))
                    Latest running balance for {{ $allCampuses->firstWhere('id', $selectedCampusId)?->acronym }}
                @else
                    Latest running balance across the campus funds you can view
                @endif
            </div>
        </div>

        <x-stats-card title="Members" :value="number_format($members)">
            <x-slot:icon>
                <x-heroicon-o-users class="w-10 h-10 text-blue-600"/>
            </x-slot:icon>
        </x-stats-card>

        <x-stats-card title="Total Claims" :value="number_format($totalClaims)">
            <x-slot:icon>
                <x-heroicon-o-document-text class="w-10 h-10 text-blue-600"/>
            </x-slot:icon>
        </x-stats-card>

        {{-- <x-stats-card title="Pending Claims" :value="number_format($pendingClaims)">
            <x-slot:icon>
                <x-heroicon-o-clock class="w-10 h-10 text-yellow-500"/>
            </x-slot:icon>
        </x-stats-card> --}}

        {{-- <x-stats-card title="Approved" :value="number_format($approvedClaims)">
            <x-slot:icon>
                <x-heroicon-o-check-circle class="w-10 h-10 text-green-600"/>
            </x-slot:icon>
        </x-stats-card> --}}

        {{-- <x-stats-card title="Disbursed Claims" :value="number_format($disbursedClaims)">
            <x-slot:icon>
                <x-heroicon-o-banknotes class="w-10 h-10 text-blue-600"/>
            </x-slot:icon>
        </x-stats-card> --}}

        {{-- <x-stats-card title="Rejected" :value="number_format($rejectedClaims)">
            <x-slot:icon>
                <x-heroicon-o-x-circle class="w-10 h-10 text-red-600"/>
            </x-slot:icon>
        </x-stats-card> --}}
    </div>

    <!-- Charts -->
    <div class="mt-6 grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-3">
        <x-chart-card
            class="lg:col-span-2"
            title="Monthly Disbursements"
            subtitle="Amount released per month, last 12 months"
            chart="monthlyDisbursements"
            :series="$chartData['monthlyDisbursements']"
            format="currency" />

        <x-chart-card
            title="Claims by Status"
            subtitle="Every claim, in workflow order"
            chart="claimsByStatus"
            :series="$chartData['claimsByStatus']"
            type="bar"
            critical="Rejected" />

        <x-chart-card
            :class="$chartData['balanceByCampus'] ? '' : 'lg:col-span-3'"
            title="Disbursed by Claim Type"
            subtitle="Total amount released per claim type"
            chart="amountByClaimType"
            :series="$chartData['amountByClaimType']"
            type="bar"
            format="currency" />

        @if($chartData['balanceByCampus'])
            <x-chart-card
                class="lg:col-span-2"
                title="Treasury Balance by Campus"
                subtitle="Latest running balance per campus fund"
                chart="balanceByCampus"
                :series="$chartData['balanceByCampus']"
                type="bar"
                format="currency" />
        @endif
    </div>

    <script type="application/json" id="dashboard-chart-data">@json($chartData)</script>

</x-app-layout>
