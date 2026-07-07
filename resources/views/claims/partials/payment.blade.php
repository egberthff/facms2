<section
    x-show="step == 3"
    x-transition.opacity.duration.250ms
    x-cloak>
    <div class="bg-white rounded-2xl border border-blue-700 shadow-sm p-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold">
                Payment Information
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Select how you would like to receive your approved claim.
            </p>
        </div>
        <!-- =========================================== -->
        <!-- PAYMENT OPTIONS -->
        <!-- =========================================== -->
        <div class="grid md:grid-cols-3 gap-5">
            <!-- ================================= -->
            <!-- BANK -->
            <!-- ================================= -->
            <button
                type="button"
                @click="paymentMethod='bank'"
                :class="paymentMethod=='bank'
                    ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-200 scale-105'
                    : 'border-gray-300 bg-white'"
                class="relative border rounded-2xl p-6 transition duration-200">
                <template x-if="paymentMethod=='bank'">
                    <div
                        class="absolute top-3 right-3 w-6 h-6 rounded-full bg-orange-500 text-white flex items-center justify-center text-xs">
                        ✓
                    </div>
                </template>
                <div class="flex justify-center">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-14 h-14 text-orange-500"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 10l9-6 9 6M5 10v8M9 10v8M15 10v8M19 10v8M3 18h18"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-lg mt-4">
                    Bank Transfer
                </h3>
                <p class="text-sm text-gray-500 mt-2">
                    Transfer directly to your bank account.
                </p>
            </button>
            <!-- ================================= -->
            <!-- GCASH -->
            <!-- ================================= -->
            <button
                type="button"
                @click="paymentMethod='gcash'"
                :class="paymentMethod=='gcash'
                    ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-200 scale-105'
                    : 'border-gray-300 bg-white'"
                class="relative border rounded-2xl p-6 transition duration-200">
                <template x-if="paymentMethod=='gcash'">
                    <div
                        class="absolute top-3 right-3 w-6 h-6 rounded-full bg-orange-500 text-white flex items-center justify-center text-xs">
                        ✓
                    </div>
                </template>
                <div class="flex justify-center">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-14 h-14 text-blue-500"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24">
                        <rect
                            x="5"
                            y="2"
                            width="14"
                            height="20"
                            rx="2"/>
                        <line
                            x1="12"
                            y1="18"
                            x2="12"
                            y2="18"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-lg mt-4">
                    GCash
                </h3>
                <p class="text-sm text-gray-500 mt-2">
                    Send directly to your GCash wallet.
                </p>
            </button>
            <!-- ================================= -->
            <!-- CHECK -->
            <!-- ================================= -->
            <button
                type="button"
                @click="paymentMethod='check'"
                :class="paymentMethod=='check'
                    ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-200 scale-105'
                    : 'border-gray-300 bg-white'"
                class="relative border rounded-2xl p-6 transition duration-200">
                <template x-if="paymentMethod=='check'">
                    <div
                        class="absolute top-3 right-3 w-6 h-6 rounded-full bg-orange-500 text-white flex items-center justify-center text-xs">
                        ✓
                    </div>
                </template>
                <div class="flex justify-center">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-14 h-14 text-green-600"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24">
                        <rect
                            x="3"
                            y="5"
                            width="18"
                            height="14"
                            rx="2"/>
                        <line
                            x1="8"
                            y1="10"
                            x2="16"
                            y2="10"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-lg mt-4">
                    Check
                </h3>
                <p class="text-sm text-gray-500 mt-2">
                    Pick up your check from the office.
                </p>
            </button>
        </div>

        <!-- =========================================== -->
        <!-- BANK DETAILS -->
        <!-- =========================================== -->

        <div
            x-show="paymentMethod=='bank'"
            x-transition
            class="mt-8">
            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-2">
                        Bank Name
                    </label>
                    <select
                        name="bank"
                        x-model="bank"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3">
                        <option value="">Select Bank</option>
                        <option>BDO</option>
                        <option>BPI</option>
                        <option>LandBank</option>
                        <option>Metrobank</option>
                        <option>PNB</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">
                        Account Number
                    </label>
                    <input
                        name="accountNumber"
                        x-model="accountNumber"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">
                        Account Name
                    </label>
                    <input
                        name="accountName"
                        x-model="accountName"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">
                        Branch
                    </label>
                    <input
                        name="branch"
                        x-model="branch"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3">
                </div>
            </div>
        </div>
        <!-- =========================================== -->
        <!-- GCASH DETAILS -->
        <!-- =========================================== -->
        <div
            x-show="paymentMethod=='gcash'"
            x-transition
            class="mt-8">
            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-2">
                        GCash Number
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        name="gcashNumber"
                        type="text"
                        x-model="gcashNumber"
                        placeholder="09XXXXXXXXX"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-orange-300">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">
                        Account Name
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        name="gcashName"
                        type="text"
                        x-model="gcashName"
                        placeholder="Juan Dela Cruz"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-orange-300">
                </div>
            </div>
        </div>

        <!-- =========================================== -->
        <!-- CHECK PAYMENT -->
        <!-- =========================================== -->

        <div
            x-show="paymentMethod=='check'"
            x-transition
            class="mt-8">
            <div class="rounded-2xl border border-yellow-300 bg-yellow-50 p-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-yellow-200 flex items-center justify-center">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-6 h-6 text-yellow-700"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M13 16h-1v-4h-1m1-4h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-yellow-800">
                            Check Payment Selected
                        </h3>
                        <p class="mt-2 text-sm text-yellow-700 leading-relaxed">
                            Your approved claim will be released through
                            <strong>check payment</strong>.
                            <br><br>
                            You will receive a notification once your check
                            is ready for pickup at the Faculty Association Office.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================== -->
    <!-- NAVIGATION -->
    <!-- =========================================== -->

    <div class="flex justify-between mt-6">
        <button
            type="button"
            @click="previousStep()"
            class="px-8 py-3 rounded-xl bg-gray-300 hover:bg-gray-400 font-medium transition">
            ← Back
        </button>
        <button
            type="button"
            @click="nextStep()"
            class="px-8 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold transition">
            Continue →
        </button>
    </div>
</section>
