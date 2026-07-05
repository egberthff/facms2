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
            value="₱23,000">
            <x-slot:icon>
            <x-heroicon-o-clock class="w-10 h-10 text-yellow-500"/>
            </x-slot:icon>
            </x-stats-card>

            <!-- ============ Processing ============ -->
            <x-stats-card
            title="Processing"
            value="5">
            <x-slot:icon>
            <x-heroicon-o-clock class="w-10 h-10 text-yellow-500"/>
            </x-slot:icon>
            </x-stats-card>

            <!-- ============ Disbursed ============= -->
            <x-stats-card
            title="Disbursed"
            value="5">
            <x-slot:icon>
            <x-heroicon-o-clock class="w-10 h-10 text-yellow-500"/>
            </x-slot:icon>
            </x-stats-card>

            <!-- ============ Pending =============== -->
            <x-stats-card
            title="Pending"
            value="5">
            <x-slot:icon>
            <x-heroicon-o-clock class="w-10 h-10 text-yellow-500"/>
            </x-slot:icon>
            </x-stats-card>
        </div>

        <!-- Middle Row: Balance Card with Action Buttons -->
        <div class="flex justify-center">
            <div class="w-full max-w-md bg-[#11224D] text-white rounded-2xl p-6 shadow-sm">
                <div class="text-sm font-medium tracking-wide opacity-80 mb-2">
                    Balance
                </div>
                
                <!-- Large Peso Currency Display -->
                <div class="text-4xl font-bold mb-6 flex items-center">
                    <span>₱0.00</span>
                </div>

                <!-- Action Button Actions -->
                <div class="flex gap-4">
                    <a href="#" class="flex-1 text-center border border-white/40 hover:bg-white/10 text-white font-medium py-2.5 px-4 rounded-full transition-colors text-sm">
                        View Details
                    </a>
                    <a href="{{ route('claim.create') }}" class="flex-1 text-center bg-[#F28C4F] hover:bg-[#e07b3e] text-white font-medium py-2.5 px-4 rounded-full transition-colors text-sm">
                        New Claim
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom Row: Recent Transactions Header Line -->
        <div class="border-t border-b border-gray-200 py-3 mt-4">
            <h3 class="text-base font-bold text-gray-900">
                Recent Transactions
            </h3>
        </div>

    </div>

</x-app-layout>
