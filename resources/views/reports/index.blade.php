{{-- resources/views/reports/index.blade.php --}}

<x-app-layout>

<div
    x-data="reportCenter({
        configuration: @js($reportConfiguration),
        campuses: @js($allCampuses),
        members: @js($members),
        cycles: @js($cycles),
        claimTypes: @js($claimTypes),
        generateUrl: '{{ route('reports.generate') }}'
    })"
    x-init="initialize()"
    class="max-w-7xl mx-auto px-4 py-6 space-y-6">

    {{-- ========================================================= --}}
    {{-- PAGE HEADER --}}
    {{-- ========================================================= --}}

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

        <div>

            <h1 class="text-2xl font-bold text-gray-900">

                Reports

            </h1>

            <p
                class="mt-1 text-sm text-gray-500">

                Generate operational, financial, and allowance reports.

            </p>

        </div>

        <div
            class="flex flex-wrap gap-2">

            <button
                @click="printReport()"
                :disabled="!hasGenerated"
                class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm hover:bg-gray-50 disabled:opacity-50">

                Print

            </button>

            <button
                @click="exportExcel()"
                :disabled="!hasGenerated"
                class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm hover:bg-gray-50 disabled:opacity-50">

                Export Excel

            </button>

            <button
                @click="exportPdf()"
                :disabled="!hasGenerated"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:bg-gray-300">

                Export PDF

            </button>

        </div>

    </div>



    {{-- ========================================================= --}}
    {{-- REPORT CONFIGURATION --}}
    {{-- ========================================================= --}}

    <div
        class="bg-white border border-gray-200 rounded-xl shadow-sm">

        <div
            class="border-b border-gray-200 px-6 py-4">

            <h2
                class="font-semibold text-gray-800">

                Report Configuration

            </h2>

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



    {{-- ========================================================= --}}
    {{-- REPORT CONTEXT --}}
    {{-- ========================================================= --}}

    <template
        x-if="hasGenerated">

        <div
            x-transition
            class="bg-blue-50 border border-blue-200 rounded-xl p-5 print">

            <div
                class="flex flex-wrap gap-x-8 gap-y-3 text-sm">

                <div>

                    <div
                        class="text-xs uppercase tracking-wide text-gray-500">

                        Report

                    </div>

                    <div
                        class="font-semibold text-gray-900"
                        x-text="context.title">

                    </div>

                </div>

                <div>

                    <div
                        class="text-xs uppercase tracking-wide text-gray-500">

                        Generated

                    </div>

                    <div
                        class="font-semibold"
                        x-text="context.generated_at">

                    </div>

                </div>

                <template
                    x-for="item in context.filters"
                    :key="item.label">

                    <div>

                        <div
                            class="text-xs uppercase tracking-wide text-gray-500"
                            x-text="item.label">

                        </div>

                        <div
                            class="font-semibold"
                            x-text="item.value">

                        </div>

                    </div>

                </template>

            </div>

        </div>

    </template>

       {{-- ========================================================= --}}
    {{-- SUMMARY CARDS --}}
    {{-- ========================================================= --}}

    <template x-if="hasGenerated">

        <div
            x-transition
            class="grid grid-cols-2 xl:grid-cols-4 gap-5 print">

            <template
                x-for="card in cards"
                :key="card.key">

                <div
                    class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">

                    <div class="flex items-start justify-between">

                        <div>

                            <p
                                class="text-xs uppercase tracking-wider text-gray-500"
                                x-text="card.label">

                            </p>

                            <h2
                                class="mt-2 text-3xl font-bold text-gray-900"
                                x-text="card.value">

                            </h2>

                            <p
                                class="mt-2 text-sm text-gray-500"
                                x-show="card.subtitle"
                                x-text="card.subtitle">

                            </p>

                        </div>

                        <div
                            class="h-10 w-10 rounded-lg bg-blue-50 flex items-center justify-center">

                            {{-- Heroicons can be injected later --}}
                            <svg
                                class="w-5 h-5 text-blue-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"/>

                            </svg>

                        </div>

                    </div>

                </div>

            </template>

        </div>

    </template>



    {{-- ========================================================= --}}
    {{-- REPORT TABLE --}}
    {{-- ========================================================= --}}

    <template x-if="hasGenerated">

        <div
            x-transition
            class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden print">

            {{-- Header --}}

            <div
                class="px-6 py-4 border-b border-gray-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                <div>

                    <h2
                        class="font-semibold text-gray-900"
                        x-text="context.title">

                    </h2>

                    <p
                        class="mt-1 text-sm text-gray-500"
                        x-text="context.description">

                    </p>

                </div>

                <div
                    class="text-sm text-gray-500">

                    Total Records :

                    <span class="font-semibold text-gray-800" 
                          x-text="cards.find(c => c.key === 'claims')?.value ?? 0">
                    </span> 

                </div>

            </div>



            {{-- Table --}}

            <div
                class="overflow-auto">

                <table
                    class="min-w-full divide-y divide-gray-200 text-sm">

                    <thead
                        class="bg-gray-50">

                        <tr>

                            <template
                                x-for="column in columns"
                                :key="column.key">

                                <th
                                    class="px-6 py-3 text-left uppercase tracking-wider text-xs font-semibold text-gray-600 whitespace-nowrap"
                                    x-text="column.label">

                                </th>

                            </template>

                        </tr>

                    </thead>



                    <tbody
                        class="divide-y divide-gray-100">

                        <template
                            x-for="(row,index) in rows"
                            :key="index">

                            <tr
                                class="hover:bg-gray-50 transition">

                                <template
                                    x-for="column in columns"
                                    :key="column.key">

                                    <td
                                        class="px-6 py-4 whitespace-nowrap">

                                        {{-- Badge --}}

                                        <template
                                            x-if="column.type=='badge'">

                                            <span
                                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                                :class="badgeClass(row[column.key])"
                                                x-text="row[column.key]">

                                            </span>

                                        </template>



                                        {{-- Currency --}}

                                        <template
                                            x-if="column.type=='currency'">

                                            <span
                                                class="font-semibold text-gray-800"
                                                x-text="formatCurrency(row[column.key])">

                                            </span>

                                        </template>



                                        {{-- Number --}}

                                        <template
                                            x-if="column.type=='number'">

                                            <span
                                                class="text-gray-700"
                                                x-text="formatNumber(row[column.key])">

                                            </span>

                                        </template>



                                        {{-- Date --}}

                                        <template
                                            x-if="column.type=='date'">

                                            <span
                                                class="text-gray-700"
                                                x-text="formatDate(row[column.key])">

                                            </span>

                                        </template>



                                        {{-- Default Text --}}

                                        <template
                                            x-if="!column.type || column.type=='text'">

                                            <span
                                                class="text-gray-700"
                                                x-text="row[column.key]">

                                            </span>

                                        </template>

                                    </td>

                                </template>

                            </tr>

                        </template>

                    </tbody>

                </table>

            </div>



            {{-- Footer --}}

            <div
                class="px-6 py-4 border-t border-gray-200 flex justify-between items-center">

                <div
                    class="text-sm text-gray-500">

                    Generated using Faculty Association Claims Monitoring System

                </div>

                <div
                    class="text-sm text-gray-500">

                    <span class="font-semibold text-gray-800" 
                          x-text="cards.find(c => c.key === 'claims')?.value ?? 0">
                    </span> 

                    records

                </div>

            </div>

        </div>

    </template>



    {{-- ========================================================= --}}
    {{-- EMPTY STATE --}}
    {{-- ========================================================= --}}

    <template
        x-if="!hasGenerated && !loading">

        <div
            class="bg-white border border-dashed border-gray-300 rounded-xl p-16 print">

            <div
                class="max-w-lg mx-auto text-center">

                <div
                    class="mx-auto h-16 w-16 rounded-full bg-blue-50 flex items-center justify-center">

                    <svg
                        class="h-8 w-8 text-blue-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">

                        <path
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 17v-6h13M9 5l-7 7 7 7"/>

                    </svg>

                </div>

                <h2
                    class="mt-6 text-lg font-semibold text-gray-900">

                    No Report Generated

                </h2>

                <p
                    class="mt-2 text-sm text-gray-500">

                    Select a report type, configure the filters, then click
                    <strong>Generate Report</strong>.

                </p>

            </div>

        </div>

    </template>

        {{-- ========================================================= --}}
    {{-- LOADING OVERLAY --}}
    {{-- ========================================================= --}}

    <div
        x-show="loading"
        x-transition.opacity
        class="fixed inset-0 z-50 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center">

        <div
            class="bg-white rounded-xl shadow-xl p-8 w-full max-w-sm">

            <div class="flex flex-col items-center">

                <svg
                    class="animate-spin h-10 w-10 text-blue-600"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24">

                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4">

                    </circle>

                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">

                    </path>

                </svg>

                <h3
                    class="mt-6 text-lg font-semibold text-gray-900">

                    Generating Report...

                </h3>

                <p
                    class="mt-2 text-sm text-gray-500 text-center">

                    Please wait while we gather and summarize the report data.

                </p>

            </div>

        </div>

    </div>



    {{-- ========================================================= --}}
    {{-- GLOBAL ALERT --}}
    {{-- ========================================================= --}}

    <div
        x-show="alert.show"
        x-transition
        class="fixed bottom-6 right-6 z-50 max-w-sm">

        <div
            class="rounded-xl shadow-lg border p-4"

            :class="{

                'bg-green-50 border-green-200' : alert.type=='success',

                'bg-red-50 border-red-200' : alert.type=='error',

                'bg-yellow-50 border-yellow-200' : alert.type=='warning',

                'bg-blue-50 border-blue-200' : alert.type=='info'

            }">

            <div class="flex items-start justify-between">

                <div>

                    <h4
                        class="font-semibold"

                        :class="{

                            'text-green-700' : alert.type=='success',

                            'text-red-700' : alert.type=='error',

                            'text-yellow-700' : alert.type=='warning',

                            'text-blue-700' : alert.type=='info'

                        }"

                        x-text="alert.title">

                    </h4>

                    <p
                        class="mt-1 text-sm text-gray-600"
                        x-text="alert.message">

                    </p>

                </div>

                <button
                    @click="alert.show=false"
                    class="ml-4 text-gray-400 hover:text-gray-600">

                    ✕

                </button>

            </div>

        </div>

    </div>



    {{-- ========================================================= --}}
    {{-- HIDDEN EXPORT FORM --}}
    {{-- ========================================================= --}}

    <form

        x-ref="exportForm"

        method="POST"

        class="hidden">

        @csrf

        <input
            type="hidden"
            name="report_type"
            :value="selectedReport">

        <input
            type="hidden"
            name="campus_id"
            :value="filters.campus_id">

        <input
            type="hidden"
            name="member_id"
            :value="filters.member_id">

        <input
            type="hidden"
            name="cycle"
            :value="filters.cycle">

        <input
            type="hidden"
            name="status"
            :value="filters.status">

        <input
            type="hidden"
            name="claim_type"
            :value="filters.claim_type">

        <input
            type="hidden"
            name="date_from"
            :value="filters.date_from">

        <input
            type="hidden"
            name="date_to"
            :value="filters.date_to">

    </form>



    {{-- ========================================================= --}}
    {{-- DEBUG PANEL --}}
    {{-- Remove in production --}}
    {{-- ========================================================= --}}

    <details
        class="hidden">

        <summary>

            Debug

        </summary>

<pre
class="text-xs bg-gray-100 rounded p-4 overflow-auto"
x-text="JSON.stringify({

    selectedReport,

    filters,

    context,

    cards,

    columns,

    rows

},null,2)">
</pre>

    </details>

</div>

</x-app-layout>