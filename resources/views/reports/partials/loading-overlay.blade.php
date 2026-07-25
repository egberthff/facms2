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