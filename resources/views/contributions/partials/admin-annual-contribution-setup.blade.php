<!-- Form Modal Component Backdrop -->
   <!-- Annual Setup modal-->
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
                <button 
                    type="button"
                    @click="openAnnualContributionSetupModal = false" 
                    class="text-gray-400 hover:text-gray-500 rounded-lg p-1 hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form 
                class="mt-4 space-y-4" 
                method="POST" 
                action="{{ route('contributions.create') }}"
                onsubmit="return sendAnnualContributionBatchSetup(event, this)"
                data-user-is-federated="{{ Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']) || Auth::user()->hasPermissionTo('view all campus data') ? 'true' : 'false' }}">

                @csrf

                <!-- Campus Filter Section -->
                @if(Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']) || Auth::user()->hasPermissionTo('view all campus data'))
                    <div>
                        <label for="campus_filter" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
                            Select Campus
                        </label>
                        <select 
                            id="campus_filter"
                            name="campus_filter" 
                            class="w-full text-sm rounded-lg border-gray-300 text-gray-700 bg-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition">
                            <option value="">-- Choose a Campus --</option>
                            @foreach($allCampuses as $campus)
                                <option value="{{ $campus->id }}" {{ $selectedCampusId == $campus->id ? 'selected' : '' }}>
                                    {{ $campus->acronym }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Setup Year Section -->
                <div>
                    <label for="contribution_year" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
                        Setup Year
                    </label>
                    <div class="relative">
                        <input 
                            id="contribution_year"
                            type="number" 
                            name="contribution_year"
                            x-model="contributionYear" 
                            min="2000" 
                            max="2099"
                            placeholder="2026"
                            @input="contributionYear = contributionYear.slice(0, 4)"
                            @keydown="if(['e', 'E', '+', '-', '.'].includes($event.key)) $event.preventDefault()"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition">
                    </div>
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
                        Save Setup
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Deduction Adjustment Modal -->
    <!-- Form Modal Component Backdrop for Contribution Adjustment -->
    <div 
        x-show="openContributionAdjustmentModal" 
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4"
        @click.self="openContributionAdjustmentModal = false"
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
                <h3 class="text-lg font-bold text-gray-900">Contribution Adjustment</h3>
                <button @click="openContributionAdjustmentModal = false" class="text-gray-400 hover:text-gray-500 rounded-lg p-1 hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
    
            <!-- Modal Form Body -->
            <form class="mt-4 space-y-4" method="POST" action="{{ route('contributions.contribution-adjustment') }}"
                onsubmit="return sendContributionAdjustment(event, this)"
                data-user-is-federated="{{ Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']) || Auth::user()->hasPermissionTo('view all campus data') ? 'true' : 'false' }}">
                @csrf
    
                <!-- Campus Filter Section -->
                @if(Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']) || Auth::user()->hasPermissionTo('view all campus data'))
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
                            Campus
                        </label>
                        <select name="campus_id" required class="w-full text-sm rounded-lg bg-white border-gray-300 text-gray-700 focus:ring-blue-500 focus:border-blue-500 py-2 px-3">
                            <option value="">-- Select Campus --</option>
                            @foreach($allCampuses as $campus)
                                <option value="{{ $campus->id }}" {{ $selectedCampusId == $campus->id ? 'selected' : '' }}>
                                    {{ $campus->acronym }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <!-- Pass current user's campus hidden if not authorized to choose -->
                    <input type="hidden" name="campus_id" value="{{ Auth::user()->campus_id }}">
                @endif
    
                <!-- Adjustment Properties Input Group -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Adjustment Applied Month -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
                            Adjustment Month
                        </label>
                        <input 
                            type="month" 
                            name="adjustment_month"
                            required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
    
                    <!-- Reference Month (Optional) -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
                            Reference Month <span class="text-gray-400 font-normal">(Optional)</span>
                        </label>
                        <input 
                            type="month" 
                            name="reference_month"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>
    
                <!-- Type Selector -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
                        Adjustment Type
                    </label>
                    <select name="adjustment_type" required class="w-full text-sm rounded-lg bg-white border-gray-300 text-gray-700 focus:ring-blue-500 focus:border-blue-500 py-2 px-3">
                        <option value="">-- Select Type --</option>
                        @foreach(\App\Enums\ContributionAdjustment::cases() as $type)
                            <option value="{{ $type->value }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
    
                <!-- Signed Numeric Value Amount -->
                <div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">
                            Adjustment Impact
                        </label>
                        <!-- Alpine context tracking the active direction: defaults to 'debit' (+) -->
                        <div class="grid grid-cols-2 gap-2" x-data="{ impactType: 'debit' }">
                            <!-- Hidden input tracking the true signed multiplier -->
                            <input type="hidden" name="amount_modifier" :value="impactType === 'debit' ? '1' : '-1'">
                    
                            <!-- Debit Button (+) -->
                            <button 
                                type="button" 
                                @click="impactType = 'debit'"
                                :class="impactType === 'debit' ? 'bg-blue-50 text-blue-700 border-blue-200 ring-2 ring-blue-500/20' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
                                class="flex items-center justify-center gap-2 px-3 py-2.5 border text-sm font-semibold rounded-lg transition-all">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Debit (+)
                            </button>
                    
                            <!-- Credit Button (-) -->
                            <button 
                                type="button" 
                                @click="impactType = 'credit'"
                                :class="impactType === 'credit' ? 'bg-blue-50 text-blue-700 border-blue-200 ring-2 ring-blue-500/20' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
                                class="flex items-center justify-center gap-2 px-3 py-2.5 border text-sm font-semibold rounded-lg transition-all">
                                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                Credit (-)
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Debit increases the member's collection; Credit decreases it.</p>
                    </div>
                    
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
                        Adjustment Amount
                    </label>
                    <div class="relative mt-1">
                        <input 
                            type="number" 
                            name="amount"
                            step="0.01"
                            required
                            placeholder="0.00"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Use standard values for increases. Insert a negative sign (-) to decrease collections.</p>
                </div>
    
                <!-- Explanation Area -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
                        Description / Explanation
                    </label>
                    <textarea 
                        name="description"
                        rows="3"
                        placeholder="Provide details about the deduction correction..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                </div>
    
                <!-- Modal Actions Footer -->
                <div class="flex items-center justify-end gap-x-2 pt-4 border-t border-gray-100">
                    <button 
                        type="button" 
                        @click="openContributionAdjustmentModal = false" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition">
                        Apply Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CNA Share Setup modal-->
    <div 
        x-show="openCNAShareSetupModal" 
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4"
        @click.self="openCNAShareSetupModal = false"
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
                <h3 class="text-lg font-bold text-gray-900">CNA Share Setup</h3>
                <button 
                    type="button"
                    @click="openCNAShareSetupModal = false" 
                    class="text-gray-400 hover:text-gray-500 rounded-lg p-1 hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form 
                class="mt-4 space-y-4" 
                method="POST" 
                action="{{ route('contributions.cna-share-setup') }}"
                onsubmit="return sendCNAShareSetup(event, this)"
                data-user-is-federated="{{ Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']) || Auth::user()->hasPermissionTo('view all campus data') ? 'true' : 'false' }}">

                @csrf

                <!-- Campus Filter Section -->
                @if(Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']) || Auth::user()->hasPermissionTo('view all campus data'))
                    <div>
                        <label for="campus_filter" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
                            Select Campus
                        </label>
                        <select 
                            id="campus_filter"
                            name="campus_filter" 
                            class="w-full text-sm rounded-lg border-gray-300 text-gray-700 bg-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition">
                            <option value="">-- Choose a Campus --</option>
                            @foreach($allCampuses as $campus)
                                <option value="{{ $campus->id }}" {{ $selectedCampusId == $campus->id ? 'selected' : '' }}>
                                    {{ $campus->acronym }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Setup Year Section -->
                <div>
                    <label for="contribution_year" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
                        Setup Year
                    </label>
                    <div class="relative">
                        <input 
                            id="contribution_year"
                            type="number" 
                            name="contribution_year"
                            x-model="contributionYear" 
                            min="2000" 
                            max="2099"
                            placeholder="2026"
                            @input="contributionYear = contributionYear.slice(0, 4)"
                            @keydown="if(['e', 'E', '+', '-', '.'].includes($event.key)) $event.preventDefault()"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition">
                    </div>
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
                        Save Setup
                    </button>
                </div>
            </form>
        </div>
    </div>

    
