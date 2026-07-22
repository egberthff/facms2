<x-app-layout>
    <div class="container mx-auto p-4 md:p-6 space-y-6 bg-white dark:bg-zinc-900 text-gray-900 dark:text-white min-h-screen">
        
        <!-- Top Dashboard Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">

            <!-- Large Main Card: Treasury Fund Balance -->
            <div class="md:col-span-1 flex flex-col justify-between bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm p-6 min-h-[160px]">
                <div>
                    <h3 class="text-xs uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400">
                        Treasury Fund Balance
                    </h3>
                </div>
                <div class="flex items-baseline space-x-1 mt-4">
                    <span class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">₱</span>
                    <!-- Dynamically Bind Value Here -->
                    <span class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white tracking-tight">0.00</span>
                </div>
            </div>

            <!-- Right Side Stacked Cards -->
            <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">

                <!-- Card: Contributions -->
                <div class="flex flex-col justify-between bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm p-6 min-h-[120px]">
                    <div>
                        <h3 class="text-xs uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400">
                            Contributions
                        </h3>
                    </div>
                    <div class="flex items-baseline space-x-1 mt-2">
                        <span class="text-2xl font-bold text-green-600 dark:text-green-400">₱</span>
                        <!-- Dynamically Bind Value Here -->
                        <span class="text-2xl font-bold text-green-600 dark:text-green-400 tracking-tight">0.00</span>
                    </div>
                </div>

                <!-- Card: Disbursements -->
                <div class="flex flex-col justify-between bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm p-6 min-h-[120px]">
                    <div>
                        <h3 class="text-xs uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400">
                            Disbursements
                        </h3>
                    </div>
                    <div class="flex items-baseline space-x-1 mt-2">
                        <span class="text-2xl font-bold text-red-600 dark:text-red-400">₱</span>
                        <!-- Dynamically Bind Value Here -->
                        <span class="text-2xl font-bold text-red-600 dark:text-red-400 tracking-tight">0.00</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Bottom Section: Transaction Ledger Table -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm p-4 md:p-6">
            <!-- Section Header -->
            <div class="border-b border-gray-200 dark:border-zinc-800 pb-4 mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Transaction Ledger
                </h3>
            </div>

            <!-- Responsive Table Element Wrapper -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-zinc-800 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-4 py-3">Date</th>
                            <th scope="col" class="px-4 py-3">Description</th>
                            <th scope="col" class="px-4 py-3">Reference</th>
                            <th scope="col" class="px-4 py-3">Type</th>
                            <th scope="col" class="px-4 py-3">Amount</th>
                            <th scope="col" class="px-4 py-3">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <!-- Dynamic Blade or JavaScript Loop goes here -->
                        <tr class="bg-white hover:bg-gray-50/70 dark:bg-zinc-900 dark:hover:bg-zinc-800/50 transition-colors duration-150">
                            <td class="px-4 py-3.5 whitespace-nowrap text-gray-600 dark:text-gray-400">2026-07-18</td>
                            <td class="px-4 py-3.5 font-medium text-gray-900 dark:text-white">Initial Funding Injection</td>
                            <td class="px-4 py-3.5 font-mono text-xs text-gray-400">REF-0001928</td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    Credit
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-semibold text-gray-900 dark:text-white">₱50,000.00</td>
                            <td class="px-4 py-3.5 text-gray-900 dark:text-white font-medium">₱50,000.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</x-app-layout>