<section
    x-show="step == 3"
    x-transition.opacity.duration.250ms
    x-cloak>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs sm:p-6">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900">
                Disbursement Information
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Select how you would like to receive your approved claim.
            </p>
        </div>
        <!-- =========================================== -->
        <!-- PAYMENT OPTIONS -->
        <!-- =========================================== -->
        <div class="grid gap-4 sm:grid-cols-3 sm:gap-5">
            <!-- ================================= -->
            <!-- BANK -->
            <!-- ================================= -->
            <button
                type="button"
                @click="paymentMethod='bank'; errors.paymentMethod = ''"
                :class="paymentMethod=='bank'
                    ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-200 scale-105'
                    : 'border-gray-300 bg-white'"
                class="relative w-full rounded-xl border p-5 text-center transition duration-200 sm:p-6">
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
                <h3 class="mt-3 text-base font-semibold text-gray-900">
                    Bank Transfer
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Transfer directly to your bank account.
                </p>
            </button>
            <!-- ================================= -->
            <!-- GCASH -->
            <!-- ================================= -->
            <button
                type="button"
                @click="paymentMethod='gcash'; errors.paymentMethod = ''"
                :class="paymentMethod=='gcash'
                    ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-200 scale-105'
                    : 'border-gray-300 bg-white'"
                class="relative w-full rounded-xl border p-5 text-center transition duration-200 sm:p-6">
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
                <h3 class="mt-3 text-base font-semibold text-gray-900">
                    GCash
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Send directly to your GCash wallet.
                </p>
            </button>
            <!-- ================================= -->
            <!-- CHECK -->
            <!-- ================================= -->
            <button
                type="button"
                @click="paymentMethod='check'; errors.paymentMethod = ''"
                :class="paymentMethod=='check'
                    ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-200 scale-105'
                    : 'border-gray-300 bg-white'"
                class="relative w-full rounded-xl border p-5 text-center transition duration-200 sm:p-6">
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
                <h3 class="mt-3 text-base font-semibold text-gray-900">
                    Check
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Pick up your check from the office.
                </p>
            </button>
        </div>
        <p id="paymentMethod-error" x-show="errors.paymentMethod" x-text="errors.paymentMethod" x-cloak class="mt-3 text-xs text-red-600"></p>

        <!-- =========================================== -->
        <!-- BANK DETAILS -->
        <!-- =========================================== -->

        <div
            x-show="paymentMethod=='bank'"
            x-transition
            class="mt-8">
            @include('claims.partials.payment-qr-upload', ['method' => 'bank'])
            <div class="grid gap-4 md:grid-cols-2 md:gap-5">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Bank Name
                        <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="bank"
                        x-model="bank"
                        :class="{ 'error-border': errors.bank }"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm shadow-xs">
                        <option value="">Select Bank</option>
                        <option>BDO</option>
                        <option>BPI</option>
                        <option>LandBank</option>
                        <option>Metrobank</option>
                        <option>PNB</option>
                    </select>
                    <p id="bank-error" x-show="errors.bank" x-text="errors.bank" x-cloak class="mt-1.5 text-xs text-red-600"></p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Account Number
                        <span class="text-red-500">*</span>
                    </label>
                   <input
                        type="text"
                        inputmode="numeric"
                        name="accountNumber"
                        x-model="accountNumber"
                        :class="{ 'error-border': errors.accountNumber }"
                        minlength="8"
                        maxlength="20"
                        x-on:input="
                            $event.target.value = $event.target.value.replace(/[^0-9]/g, '');
                            accountNumber = $event.target.value;
                        "
                        autocomplete="off"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm shadow-xs"
                        placeholder="Enter account number"
                    >
                    <p id="accountNumber-error" x-show="errors.accountNumber" x-text="errors.accountNumber" x-cloak class="mt-1.5 text-xs text-red-600"></p>

                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Account Name
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="accountName"
                        x-model="accountName"
                        :class="{ 'error-border': errors.accountName }"
                         x-on:input="
                            let value = $event.target.value;                    

                            // Allow letters and spaces only
                            value = value.replace(/[^a-zA-Z\s.]/g, '');                  

                            // Prevent multiple consecutive spaces
                            value = value.replace(/\s+/g, ' ');                 

                            // Convert to Title Case
                            value = value
                                .toLowerCase()
                                .replace(/\b[a-z]/g, char => char.toUpperCase());                   

                            $event.target.value = value;
                            accountName = value;
                        "    
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm shadow-xs"
                        placeholder="Enter account name"
                    >
                    <p id="accountName-error" x-show="errors.accountName" x-text="errors.accountName" x-cloak class="mt-1.5 text-xs text-red-600"></p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Branch (Optional)
                    </label>
                    <input
                        name="branch"
                        x-model="branch"
                        x-on:input="
                            let value = $event.target.value;

                            value = value
                                .toLowerCase()
                                .replace(/\b\w/g, char => char.toUpperCase());

                            $event.target.value = value;
                            branch = value;
                        "
                        :class="{ 'error-border': errors.branch }"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm shadow-xs">
                    <p id="branch-error" x-show="errors.branch" x-text="errors.branch" x-cloak class="mt-1.5 text-xs text-red-600"></p>
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
            @include('claims.partials.payment-qr-upload', ['method' => 'gcash'])
            <div class="grid gap-4 md:grid-cols-2 md:gap-5">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        GCash Number
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        name="gcashNumber"
                        type="text"
                        inputmode="numeric"
                        x-model="gcashNumber"
                        :class="{ 'error-border': errors.gcashNumber }"
                        maxlength="11"
                        minlength="11"
                        pattern="09[0-9]{9}"
                        placeholder="09XXXXXXXXX"

                        x-on:input="
                            let value = $event.target.value.replace(/[^0-9]/g, '');

                            // First digit must be 0
                            if (value.length >= 1 && value[0] !== '0') {
                                value = '';
                            }

                            // Second digit must be 9
                            if (value.length >= 2 && value[1] !== '9') {
                                value = '0';
                            }

                            // Maximum 11 digits
                            value = value.slice(0, 11);

                            $event.target.value = value;
                            gcashNumber = value;
                        "

                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm shadow-xs"
                    >
                    <p id="gcashNumber-error" x-show="errors.gcashNumber" x-text="errors.gcashNumber" x-cloak class="mt-1.5 text-xs text-red-600"></p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Account Name
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        name="gcashName"
                        type="text"
                        x-model="gcashName"
                        :class="{ 'error-border': errors.gcashName }"                 

                        x-on:input="
                            let value = $event.target.value;                    

                            // Allow letters and spaces only
                            value = value.replace(/[^a-zA-Z\s.]/g, '');                  

                            // Prevent multiple consecutive spaces
                            value = value.replace(/\s+/g, ' ');                 

                            // Convert to Title Case
                            value = value
                                .toLowerCase()
                                .replace(/\b[a-z]/g, char => char.toUpperCase());                   

                            $event.target.value = value;
                            gcashName = value;
                        "                   

                        placeholder="Juan Dela Cruz"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm shadow-xs"
                    >
                    <p id="gcashName-error" x-show="errors.gcashName" x-text="errors.gcashName" x-cloak class="mt-1.5 text-xs text-red-600"></p>
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
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 sm:p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100">
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
                            Check Disbursement Selected
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

    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
        <button
            type="button"
            @click="previousStep()"
            class="w-full rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-medium text-gray-700 shadow-xs transition hover:bg-gray-50 sm:w-auto">
                Back
        </button>
        <button
            type="button"
            @click="nextStep()"
            class="w-full rounded-lg bg-orange-500 px-6 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-orange-600 sm:w-auto">
            Continue
        </button>
    </div>
</section>
