<x-app-layout>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 p-6 max-w-4xl">

        <!-- CARD 1: CLAIMABLE BALANCE IN THE CURRENT CYCLE -->
        <div class="relative min-h-[140px] transform rounded-2xl bg-white p-5 shadow-sm border border-slate-200/80 transition-all flex flex-col justify-between">
            <div>
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center sm:text-left">
                    Claimable Balance
                </span>
            </div>
            <div class="flex items-baseline gap-2 mt-4">
                <span class="text-3xl font-light text-slate-700 select-none">₱</span>
                <span class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ number_format($actualBalance, 2) }}
                </span>
            </div>
        </div>

        <!-- CARD 2: DISBURSED THIS MONTH -->
        <div class="relative min-h-[140px] transform rounded-2xl bg-white p-5 shadow-sm border border-slate-200/80 transition-all flex flex-col justify-between">
            <div>
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center sm:text-left">
                    Disbursed This Cycle
                </span>
            </div>
            <div class="flex items-baseline gap-2 mt-4">
                <span class="text-3xl font-light text-slate-700 select-none">₱</span>
                <span class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ number_format($totalDisbursedCycle, 2) }}
                </span>
            </div>
        </div>

        <!-- Member's own claim cycle: starts with their first claim, unused allowance is forfeited at the end -->
        <div class="sm:col-span-2 rounded-2xl bg-white p-5 shadow-sm border border-slate-200/80">
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                Claim Cycle
            </span>
            @if($cycleStartDate)
                <p class="mt-2 text-base font-semibold text-slate-900">
                    {{ \Carbon\Carbon::parse($cycleStartDate)->format('F Y') }} &ndash; {{ \Carbon\Carbon::parse($cycleEndDate)->format('F Y') }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    Limit ₱{{ number_format($baseLimit, 2) }}. Unused allowance is forfeited when this cycle ends; your next claim after that starts a new cycle.
                </p>
            @else
                <p class="mt-2 text-base font-semibold text-slate-900">No active cycle</p>
                <p class="mt-1 text-xs text-slate-500">
                    Your next claim starts a new {{ $cycleYears }}-year cycle with a ₱{{ number_format($baseLimit, 2) }} limit.
                </p>
            @endif
        </div>

    </div>

</x-app-layout>