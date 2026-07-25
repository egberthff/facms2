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