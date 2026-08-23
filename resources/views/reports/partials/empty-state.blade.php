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