<x-app-layout>

    <!-- <x-page-header
        title="Dashboard"
        description="Faculty Association Claims Monitoring System"
    /> -->

    <!-- Fixed Grid: Changed xl:grid-cols-4 to xl:grid-cols-3 to fit 6 cards perfectly -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6">
        
        <x-stats-card title="Members" :value="$members">
            <x-slot:icon>
                <x-heroicon-o-users class="w-10 h-10 text-blue-600"/>
            </x-slot:icon>
        </x-stats-card>

        <x-stats-card title="Total Claims" :value="$totalClaims">
            <x-slot:icon> 
                <x-heroicon-o-currency-dollar class="w-10 h-10 text-blue-600"/>
            </x-slot:icon>
        </x-stats-card>

       <!-- <x-stats-card title="Processing Claims" :value="$approvedClaims">
            <x-slot:icon>
                <x-heroicon-o-clock class="w-10 h-10 text-yellow-500"/>
            </x-slot:icon>
        </x-stats-card> -->

        <x-stats-card title="Pending Claims" :value="$pendingClaims">
            <x-slot:icon>
                <x-heroicon-o-clock class="w-10 h-10 text-yellow-500"/>
            </x-slot:icon>
        </x-stats-card>

        <x-stats-card title="Approved" :value="$approvedClaims">
            <x-slot:icon>
                <x-heroicon-o-check-circle class="w-10 h-10 text-green-600"/>
            </x-slot:icon>
        </x-stats-card>

        <x-stats-card title="Disbursed Claims" :value="$disbursedClaims">
            <x-slot:icon>
                <!-- Fixed Icon: Changed yellow clock to green check-box/arrow if preferred, kept yellow clock as per original -->
                <x-heroicon-o-clock class="w-10 h-10 text-yellow-500"/>
            </x-slot:icon>
        </x-stats-card>

        <x-stats-card title="Rejected" :value="$rejectedClaims">
            <x-slot:icon>
                <x-heroicon-o-x-circle class="w-10 h-10 text-red-600"/>
            </x-slot:icon>
        </x-stats-card>

    </div>
        
    <!-- Centered Treasury Balance Section -->
    <div class="flex justify-center mt-6">
        <div class="w-full max-w-md bg-navy-900 text-white rounded-2xl p-5 sm:p-6 shadow-sm">
            <!-- Fixed Alignment: Added text-center to match the balance placement -->
            <div class="text-xs font-medium uppercase tracking-wider text-white/70 mb-2 text-center">
                TREASURY BALANCE 
            </div>

            <!-- Fixed Layout: Removed conflicting 'flex items-center' to allow clean centering -->
            <div class="text-3xl sm:text-4xl text-center font-semibold tracking-tight tabular-nums mb-2">
                <span>₱ {{ $balance }}</span>
            </div>
        </div>
    </div>
        
</x-app-layout>
