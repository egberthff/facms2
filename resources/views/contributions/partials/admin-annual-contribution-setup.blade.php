<!-- Form Modal Component Backdrop -->
    <div 
        x-show="openAnnualContributionSetupModal" 
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4"
        @click.self="openAnnualContributionSetupModal = false"
        style="display: none;">
        
        <!-- Modal Form Box -->
        <div 
            @click.stop
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-md transform overflow-hidden rounded-xl bg-white p-6 shadow-xl transition-all border border-gray-100">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-lg font-bold text-gray-900">Annual Contribution Setup</h3>
                <button @click="openAnnualContributionSetupModal = false" class="text-gray-400 hover:text-gray-500 rounded-lg p-1 hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form class="mt-4 space-y-4" method="POST" action="{{ route('contributions.create') }}"
                onsubmit="return sendAnnualContributionBatchSetup(event, this)"
                data-user-is-federated="{{ Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']) || Auth::user()->hasPermissionTo('view all campus data') ? 'true' : 'false' }}">
                <div>
                    @if(Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']) || Auth::user()->hasPermissionTo('view all campus data'))
                        {{-- <div class="flex items-center gap-2"> --}}
                            <select name="campus_filter" class="text-sm rounded-lg bg-white dark:bg-zinc-900 border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-300 focus:ring-blue-500 focus:border-blue-500 min-w-[200px]">
                                <option value="">-- Select Campus --</option>
                                @foreach($allCampuses as $campus)
                                    <option value="{{ $campus->id }}" {{ $selectedCampusId == $campus->id ? 'selected' : '' }}>
                                        {{ $campus->acronym }}                                        </option>
                                @endforeach
                            </select>
                        {{-- </div> --}}
                    @endif
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1 mt-1">
                        Setup Year
                    </label>
                    <div class="relative mt-1">
                        <input 
                        type="number" 
                        name="contribution_year"
                        x-model="contributionYear" 
                        min="2000" 
                        max="2099"
                        placeholder="2026"
                        @input="contributionYear = contributionYear.slice(0, 4)"
                        @keydown="if(['e', 'E', '+', '-', '.'].includes($event.key)) $event.preventDefault()"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <!-- Helpful validation hint text -->
                    <p class="mt-1 text-xs text-gray-400">Enter a 4-digit numeric year.</p>
                </div>

                <!-- Modal Actions Footer -->
                <div class="flex items-center justify-end gap-x-2 pt-4 border-t border-gray-100">
                    <button 
                        type="button" 
                        @click="openAnnualContributionSetupModal = false" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition">
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>