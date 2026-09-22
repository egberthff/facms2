<x-app-layout>

<div
    x-data="reportCenter({
        configuration: @js($reportConfiguration),
        campuses: @js($allCampuses),
        members: @js($members),
        cycles: @js($cycles),
        claimTypes: @js($claimTypes),
        generateUrl: '{{ route('reports.generate') }}',
        payrollPrintUrl: '{{ route('reports.payroll-deduction-request') }}',
        monthlyClaimsSummary: '{{ route('reports.monthly-claims-summary') }}',
        collectionSummaryPrintUrl: '{{ route('reports.collection-summary.print') }}',
        facultyDeductionSummaryPrintUrl: '{{ route('reports.faculty-deduction-summary') }}',
        medicalAssistanceReportPrintUrl: '{{ route('reports.medical-assistance-report') }}',
        unifiedReportUrl: '{{ route('reports.unified-report-url') }}',
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
                 Preview
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

    @include('reports.partials.filters')

    {{-- ========================================================= --}}
    {{-- REPORT CONTEXT --}}
    {{-- ========================================================= --}}

    @include('reports.partials.export-context')

    {{-- ========================================================= --}}
    {{-- SUMMARY CARDS --}}
    {{-- ========================================================= --}}

    @include('reports.partials.summary-cards')

    {{-- ========================================================= --}}
    {{-- REPORT TABLE --}}
    {{-- ========================================================= --}}

    @include('reports.partials.report-table')

    {{-- ========================================================= --}}
    {{-- EMPTY STATE --}}
    {{-- ========================================================= --}}

    @include('reports.partials.empty-state')

    {{-- ========================================================= --}}
    {{-- LOADING OVERLAY --}}
    {{-- ========================================================= --}}

    @include('reports.partials.loading-overlay')

    {{-- ========================================================= --}}
    {{-- GLOBAL ALERT --}}
    {{-- ========================================================= --}}

    @include('reports.partials.global-alert')

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
        <input
            type="hidden"
            name="reporting_year"
            :value="filters.reporting_year">    

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