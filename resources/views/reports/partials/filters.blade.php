    <div
        class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-800">
                Report Configuration
            </h2>
            <a href="{{ route('reports.collection-summary') }}" 
               class="rounded-md bg-blue-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition duration-150 ease-in-out">
                Collection Summary Entry
            </a>
        </div>

        <div
            class="p-6 grid grid-cols-1 lg:grid-cols-4 gap-6">
            {{-- Report Type --}}
            <div class="lg:col-span-2">
                <label
                    class="block text-sm font-medium text-gray-700">
                    Report Type
                </label>
                <select
                    x-model="selectedReport"
                    @change="changeReport()"
                    class="mt-1 w-full rounded-lg border-gray-300">
                    <template
                        x-for="(category, categoryKey) in configuration.categories"
                        :key="categoryKey">
                        <optgroup
                            :label="category.label">
                            <template
                                x-for="(report, reportKey) in reportsByCategory(categoryKey)"
                                :key="reportKey">
                                <option
                                    :value="reportKey"
                                    x-text="report.title">
                                </option>
                            </template>
                        </optgroup>
                    </template>
                </select>
            </div>

            {{-- Campus --}}

            @if(Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']) || Auth::user()->hasPermissionTo('view all campus data'))
            <template
                x-if="showFilter('campus')">
                <div>
                    <label
                        class="block text-sm font-medium text-gray-700">
                        Campus
                    </label>
                    <select
                        x-model="filters.campus_id"
                        class="mt-1 w-full rounded-lg border-gray-300">
                        <option value="">
                            All Campuses
                        </option>
                        <template
                            x-for="campus in campuses"
                            :key="campus.id">
                            <option
                                :value="campus.id"
                                x-text="campus.acronym">
                            </option>
                        </template>
                    </select>
                </div>
            </template>
            @endif

            {{-- Contribution Type --}}
            {{-- Member --}}

            <template
                x-if="showFilter('member')">
                <div>
                    <label
                        class="block text-sm font-medium text-gray-700">
                        Member
                    </label>
                    <select
                        x-model="filters.member_id"
                        class="mt-1 w-full rounded-lg border-gray-300">
                        <option value="">
                            All Members
                        </option>
                        <template
                            x-for="member in members"
                            :key="member.id">
                            <option
                                :value="member.id"
                                x-text="member.name">
                            </option>
                        </template>
                    </select>
                </div>
            </template>
        </div>

        {{-- ========================================================= --}}
        {{-- SECOND ROW OF FILTERS --}}
        {{-- ========================================================= --}}

        <div
            class="px-6 pb-6">
            <div
                class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                {{-- Date From --}}
                <template
                    x-if="showFilter('date_range')">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700">
                            Date From
                        </label>
                        <input
                            x-model="filters.date_from"
                            type="date"
                            class="mt-1 w-full rounded-lg border-gray-300">
                    </div>
                </template>

                {{-- Claim Submission Month --}}
                <template
                    x-if="showFilter('claim_month')">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700">
                            Claim Submission Month
                        </label>
                        <input
                            x-model="filters.claim_month"
                            type="month"
                            class="mt-1 w-full rounded-lg border-gray-300">
                    </div>
                </template>

                {{-- Payroll Month --}}
                <template
                    x-if="showFilter('payroll_month')">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700">
                            Payroll Month
                        </label>
                        <input
                            x-model="filters.payroll_month"
                            type="month"
                            class="mt-1 w-full rounded-lg border-gray-300">
                    </div>
                </template>

                {{-- Date To --}}

                <template
                    x-if="showFilter('date_range')">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700">
                            Date To
                        </label>
                        <input
                            x-model="filters.date_to"
                            type="date"
                            class="mt-1 w-full rounded-lg border-gray-300">
                    </div>
                </template>

                {{-- Reporting Year --}}
                <template 
                    x-if="showFilter('year_range')">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Reporting year:
                        </label>

                        <!-- Corrected select without an internal x-if -->
                        <select 
                            x-model="filters.reporting_year"
                            id="year-select">
                            <option value="">--Select Year--</option>

                            <!-- Pure Alpine way to loop from 2026 down to 1900 -->
                            <template x-for="year in Array.from({length: new Date().getFullYear() - 2010 + 1}, (_, i) => new Date().getFullYear() - i)">
                                <option :value="year" x-text="year"></option>
                            </template>
                        </select>
                    </div>
                </template>


                {{-- Allowance Cycle --}}

                <template
                    x-if="showFilter('cycle')">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700">
                            Allowance Cycle
                        </label>
                        <select
                            x-model="filters.cycle"
                            class="mt-1 w-full rounded-lg border-gray-300">
                            <template
                                x-for="cycle in cycles"
                                :key="cycle.value">
                                <option
                                    :value="cycle.value"
                                    x-text="cycle.label">
                                </option>
                            </template>
                        </select>
                    </div>
                </template>

                {{-- Claim Status --}}

                <template
                    x-if="showFilter('status')">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700">
                            Status
                        </label>
                        <select
                            x-model="filters.status"
                            class="mt-1 w-full rounded-lg border-gray-300">
                            <option value="">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Disbursed">Disbursed</option>
                        </select>
                    </div>
                </template>

                {{-- Claim Type --}}

                <template
                    x-if="showFilter('claim_type')">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700">
                            Claim Type
                        </label>
                        <select
                            x-model="filters.claim_type"
                            class="mt-1 w-full rounded-lg border-gray-300">
                            <option value="">All Claim Types</option>
                             <template
                                x-for="claimType in claimTypes"
                                :key="claimType.id">
                                <option
                                    :value="claimType.id"
                                    x-text="claimType.name">
                                </option>
                            </template>
                        </select>
                    </div>
                </template>

                {{-- Generate Button --}}

                <div
                    class="flex items-end">
                    <button
                        @click="generateReport()"
                        class="w-full rounded-lg bg-blue-600 text-white py-2.5 hover:bg-blue-700">
                        Generate Report
                    </button>
                </div>
            </div>
        </div>
    </div>