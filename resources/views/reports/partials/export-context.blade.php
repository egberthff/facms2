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