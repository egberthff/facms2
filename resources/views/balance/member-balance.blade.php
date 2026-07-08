<x-app-layout>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 p-6 max-w-4xl">

        <!-- CARD 1: BALANCE THIS YEAR -->
        <div class="relative min-h-[140px] transform rounded-2xl bg-white p-5 shadow-sm border border-slate-200/80 transition-all flex flex-col justify-between">
            <div>
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center sm:text-left">
                    Balance This Year
                </span>
            </div>
            <div class="flex items-baseline gap-2 mt-4">
                <span class="text-3xl font-light text-slate-700 select-none">₱</span>
                <span class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ number_format($balanceThisYear, 2) }}
                </span>
            </div>
        </div>

        <!-- CARD 2: DISBURSED THIS MONTH -->
        <div class="relative min-h-[140px] transform rounded-2xl bg-white p-5 shadow-sm border border-slate-200/80 transition-all flex flex-col justify-between">
            <div>
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center sm:text-left">
                    Disbursed This Month
                </span>
            </div>
            <div class="flex items-baseline gap-2 mt-4">
                <span class="text-3xl font-light text-slate-700 select-none">₱</span>
                <span class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ number_format($disbursedThisMonth, 2) }}
                </span>
            </div>
        </div>

    </div>

</x-app-layout>