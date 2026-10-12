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
                <div>
                    <label for="contribution_amount" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
                        CNA Share Amount
                    </label>
                    <div class="relative">
                        <input 
                            id="contribution_amount"
                            type="number" 
                            name="contribution_amount"
                            step="0.01"
                            placeholder="0.00"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition">        
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
</div>

    <!-- Membership Fee Entry Modal -->
<div
    x-show="openMembershipFeeModal"
    x-cloak
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4 overflow-auto"
    @click.self="openMembershipFeeModal()"
    style="display: none;"
    {{-- @keydown.escape.window="closeMembershipFeeModal()" --}}
>
    <div
        @click.stop
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="w-full max-w-2xl transform overflow-hidden rounded-xl bg-white shadow-xl transition-all border border-gray-100"
    >

        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">
                    Membership Fee Entry
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Search for a member and record their membership registration fee.
                </p>
            </div>

            <button
                type="button"
                @click="closeMembershipFeeModal()"
                class="text-gray-400 hover:text-gray-500 rounded-lg p-1 hover:bg-gray-100 transition"
            >
                <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>

        <form
            method="POST"
            action="{{ route('members.membership-fees-store') }}"
            @submit="window.submitMembershipFee($event)"
        >
            @csrf

            <div class="px-6 py-5 space-y-5">

                <!-- ===================================================== -->
                <!-- MEMBER SEARCH                                         -->
                <!-- ===================================================== -->

                <div>
                    <label
                        for="membership_member_search"
                        class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1"
                    >
                        Search Member
                    </label>

                    <div class="relative">
                        <input
                            id="membership_member_search"
                            type="text"
                            x-model="memberSearch"
                            @input.debounce.400ms="searchMembers()"
                            @keydown.escape="memberResults = []"
                            autocomplete="off"
                            placeholder="Search employee number or member name..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 text-sm
                                   focus:border-blue-500 focus:outline-none focus:ring-1
                                   focus:ring-blue-500 transition"
                        >

                        <!-- Loading -->
                        <div
                            x-show="searchingMember"
                            class="absolute inset-y-0 right-3 flex items-center"
                        >
                            <svg
                                class="h-4 w-4 animate-spin text-blue-500"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                />

                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                />
                            </svg>
                        </div>

                        <!-- Search Results -->
                        <div
                            x-show="memberResults.length > 0"
                            x-transition
                            class="absolute z-20 mt-1 max-h-64 w-full overflow-y-auto
                                   rounded-lg border border-gray-200 bg-white shadow-lg"
                        >
                            <template
                                x-for="member in memberResults"
                                :key="member.id"
                            >
                                <button
                                    type="button"
                                    @click="selectMember(member)"
                                    class="flex w-full items-center justify-between
                                           border-b border-gray-100 px-4 py-3 text-left
                                           last:border-b-0 hover:bg-blue-50 transition"
                                >
                                    <div>
                                        <p
                                            class="text-sm font-semibold text-gray-900"
                                            x-text="member.name"
                                        ></p>

                                        <p class="mt-0.5 text-xs text-gray-500">
                                            <span x-text="member.employee_number"></span>

                                            <template x-if="member.department">
                                                <span>
                                                    ·
                                                    <span x-text="member.department"></span>
                                                </span>
                                            </template>
                                        </p>
                                    </div>

                                    <span
                                        class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600"
                                        x-text="member.membership_status"
                                    ></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- No results -->
                    <p
                        x-show="
                            memberSearch.length >= 2 &&
                            !searchingMember &&
                            memberResults.length === 0 &&
                            !selectedMember
                        "
                        class="mt-2 text-xs text-gray-500"
                    >
                        No matching members found.
                    </p>
                </div>


                <!-- ===================================================== -->
                <!-- SELECTED MEMBER                                       -->
                <!-- ===================================================== -->

                <div
                    x-show="selectedMember"
                    x-transition
                    class="rounded-xl border border-blue-100 bg-blue-50/50 p-4"
                >
                    <input
                        type="hidden"
                        name="member_id"
                        :value="selectedMember?.id ?? ''"
                    >

                    <div class="mb-4 flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">
                                Selected Member
                            </p>

                            <p
                                class="mt-1 text-base font-bold text-gray-900"
                                x-text="selectedMember?.name"
                            ></p>
                        </div>

                        <button
                            type="button"
                            @click="clearSelectedMember()"
                            class="text-xs font-medium text-blue-600 hover:text-blue-700"
                        >
                            Change Member
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <!-- Employee Number -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500">
                                Employee Number
                            </label>

                            <input
                                type="text"
                                :value="selectedMember?.employee_number ?? ''"
                                readonly
                                class="mt-1 w-full rounded-lg border border-gray-200
                                       bg-gray-50 px-3 py-2 text-sm text-gray-700"
                            >
                        </div>

                        <!-- Campus -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500">
                                Campus
                            </label>

                            <input
                                type="text"
                                :value="selectedMember?.campus ?? ''"
                                readonly
                                class="mt-1 w-full rounded-lg border border-gray-200
                                       bg-gray-50 px-3 py-2 text-sm text-gray-700"
                            >
                        </div>

                        <!-- Department -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500">
                                Department
                            </label>

                            <input
                                type="text"
                                :value="selectedMember?.department ?? ''"
                                readonly
                                class="mt-1 w-full rounded-lg border border-gray-200
                                       bg-gray-50 px-3 py-2 text-sm text-gray-700"
                            >
                        </div>

                        <!-- Position -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500">
                                Position
                            </label>

                            <input
                                type="text"
                                :value="selectedMember?.position ?? ''"
                                readonly
                                class="mt-1 w-full rounded-lg border border-gray-200
                                       bg-gray-50 px-3 py-2 text-sm text-gray-700"
                            >
                        </div>

                    </div>
                </div>


                <!-- ===================================================== -->
                <!-- PAYMENT DETAILS                                       -->
                <!-- ===================================================== -->

                <div
                    x-show="selectedMember"
                    x-transition
                    class="space-y-4"
                >
                    <div class="border-b border-gray-100 pb-2">
                        <h4 class="text-sm font-semibold text-gray-900">
                            Payment Details
                        </h4>

                        <p class="mt-1 text-xs text-gray-500">
                            Enter the membership registration payment information.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <!-- Amount -->
                        <div>
                            <label
                                for="membership_fee_amount"
                                class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1"
                            >
                                Payment Amount <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3
                                           text-sm text-gray-500"
                                >
                                    ₱
                                </span>

                                <input
                                    id="membership_fee_amount"
                                    type="text"
                                    name="amount"
                                    x-model="membershipFee.amount"
                                    min="0"
                                    inputMode='decimal'
                                    step="0.01"
                                    x-on:input="
                                        let value = $event.target.value.replace(/[^0-9]/g, '');

                                        if (value === '') {
                                            amount = '';
                                            $event.target.value = '';
                                            return;
                                        }

                                        amount = value;
                                        $event.target.value = Number(value).toLocaleString('en-US');
                                    "

                                    x-on:paste="
                                        setTimeout(() => {
                                            let value = $event.target.value.replace(/[^0-9,]/g, '');

                                            if (value === '') {
                                                amount = '';
                                                $event.target.value = '';
                                                return;
                                            }

                                            amount = value;
                                            $event.target.value = Number(value).toLocaleString('en-US');
                                        }, 0);
                                    "
                                    placeholder="0.00"
                                    required
                                    class="w-full rounded-lg border border-gray-300
                                           py-2 pl-8 pr-3 text-sm
                                           focus:border-blue-500 focus:outline-none
                                           focus:ring-1 focus:ring-blue-500 transition"
                                >
                            </div>
                        </div>

                        <!-- Payment Status -->
                        <div>
                            <label
                                for="membership_fee_status"
                                class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1"
                            >
                                Payment Status <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="membership_fee_status"
                                name="status"
                                x-model="membershipFee.status"
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white
                                       px-3 py-2 text-sm text-gray-700
                                       focus:border-blue-500 focus:outline-none
                                       focus:ring-1 focus:ring-blue-500 transition"
                            >
                                <option value="">-- Select Status --</option>

                                @foreach(\App\Enums\MembershipFeeStatus::cases() as $status)
                                    <option value="{{ $status->value }}">
                                        {{ $status->value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label
                                for="membership_payment_method"
                                class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1"
                            >
                                Payment Method <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="membership_payment_method"
                                name="payment_method_id"
                                x-model="membershipFee.payment_method_id"
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white
                                       px-3 py-2 text-sm text-gray-700
                                       focus:border-blue-500 focus:outline-none
                                       focus:ring-1 focus:ring-blue-500 transition"
                            >
                                <option value="">-- Select Payment Method --</option>
                                <?php $paymentMethods = App\Models\PaymentMethod::where('is_active', true)->get(); ?>
                                @foreach($paymentMethods as $paymentMethod)
                                    <option value="{{ $paymentMethod->id }}">
                                        {{ $paymentMethod->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Payment Date -->
                        <div>
                            <label
                                for="membership_paid_at"
                                class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1"
                            >
                                Payment Date
                            </label>

                            <input
                                id="membership_paid_at"
                                type="date"
                                name="paid_at"
                                x-model="membershipFee.paid_at"
                                :required="membershipFee.status === 'Paid'"
                                class="w-full rounded-lg border border-gray-300
                                       px-3 py-2 text-sm
                                       focus:border-blue-500 focus:outline-none
                                       focus:ring-1 focus:ring-blue-500 transition"
                            >
                        </div>

                    </div>

                    <!-- Reference Number -->
                    <div>
                        <label
                            for="membership_reference_number"
                            class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1"
                        >
                            Reference Number
                        </label>

                        <input
                            id="membership_reference_number"
                            type="text"
                            name="reference_number"
                            x-model="membershipFee.reference_number"
                            placeholder="Receipt / transaction reference number"
                            class="w-full rounded-lg border border-gray-300
                                   px-3 py-2 text-sm
                                   focus:border-blue-500 focus:outline-none
                                   focus:ring-1 focus:ring-blue-500 transition"
                        >
                    </div>

                    <!-- Remarks -->
                    <div>
                        <label
                            for="membership_remarks"
                            class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1"
                        >
                            Remarks
                        </label>

                        <textarea
                            id="membership_remarks"
                            name="remarks"
                            x-model="membershipFee.remarks"
                            rows="2"
                            placeholder="Optional remarks..."
                            class="w-full resize-none rounded-lg border border-gray-300
                                   px-3 py-2 text-sm
                                   focus:border-blue-500 focus:outline-none
                                   focus:ring-1 focus:ring-blue-500 transition"
                        ></textarea>
                    </div>
                </div>

            </div>


            <!-- Modal Footer -->
            <div
                class="flex items-center justify-end gap-x-2 border-t
                       border-gray-100 bg-gray-50/50 px-6 py-4"
            >
                <button
                    type="button"
                    @click="closeMembershipFeeModal()"
                    class="px-4 py-2 text-sm font-medium text-gray-700
                           bg-white border border-gray-300 rounded-lg
                           hover:bg-gray-50 transition"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    :disabled="!selectedMember || submittingMembershipFee"
                    class="inline-flex items-center justify-center px-4 py-2
                           text-sm font-medium text-white bg-blue-600 rounded-lg
                           hover:bg-blue-700 shadow-sm transition
                           disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <svg
                        x-show="submittingMembershipFee"
                        class="mr-2 h-4 w-4 animate-spin"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        />

                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                        />
                    </svg>

                    <span
                        x-text="submittingMembershipFee
                            ? 'Saving...'
                            : 'Save Membership Fee'"
                    ></span>
                </button>
            </div>

        </form>
    </div>
</div>

    
