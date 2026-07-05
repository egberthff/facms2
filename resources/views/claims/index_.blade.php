<x-app-layout>

<div
    x-data="{
        step: 1,
        claimType: '',
        amount: '',
        description: '',
        incidentDate: '',
        hospital: '',
    }"
    class="max-w-5xl mx-auto px-3 py-4">

    <!-- ============================== -->
    <!-- PROGRESS STEPPER -->
    <!-- ============================== -->

    <div class="bg-white border border-blue-700 rounded-2xl p-4 mb-4">

        <div class="flex flex-wrap justify-center items-center gap-5 md:gap-8">

            <!-- Step 1 -->
            <div class="flex items-center gap-2">

                <div
                    :class="step >= 1
                        ? 'bg-orange-500 text-white border-orange-500'
                        : 'bg-white text-gray-700 border-gray-400'"
                    class="w-8 h-8 md:w-9 md:h-9 rounded-full border-2 flex items-center justify-center text-sm font-bold transition">

                    1

                </div>

                <span class="text-sm md:text-base font-medium">
                    Claim Details
                </span>

            </div>

            <!-- Step 2 -->
            <div class="flex items-center gap-2">

                <div
                    :class="step >= 2
                        ? 'bg-orange-500 text-white border-orange-500'
                        : 'bg-white text-gray-700 border-gray-400'"
                    class="w-8 h-8 md:w-9 md:h-9 rounded-full border-2 flex items-center justify-center text-sm font-bold transition">

                    2

                </div>

                <span class="text-sm md:text-base font-medium">
                    Documents
                </span>

            </div>

            <!-- Step 3 -->
            <div class="flex items-center gap-2">

                <div
                    :class="step >= 3
                        ? 'bg-orange-500 text-white border-orange-500'
                        : 'bg-white text-gray-700 border-gray-400'"
                    class="w-8 h-8 md:w-9 md:h-9 rounded-full border-2 flex items-center justify-center text-sm font-bold transition">

                    3

                </div>

                <span class="text-sm md:text-base font-medium">
                    Payment Info
                </span>

            </div>

            <!-- Step 4 -->
            <div class="flex items-center gap-2">

                <div
                    :class="step >= 4
                        ? 'bg-orange-500 text-white border-orange-500'
                        : 'bg-white text-gray-700 border-gray-400'"
                    class="w-8 h-8 md:w-9 md:h-9 rounded-full border-2 flex items-center justify-center text-sm font-bold transition">

                    4

                </div>

                <span class="text-sm md:text-base font-medium">
                    Review
                </span>

            </div>

        </div>

    </div>

    <!-- ============================== -->
    <!-- STEP 1 -->
    <!-- ============================== -->

    <div x-show="step == 1">

        <div class="bg-white border border-blue-700 rounded-2xl p-4 md:p-6">

            <h2 class="text-lg font-semibold">
                Claim Information
            </h2>

            <hr class="my-4">

            <!-- Top Inputs -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <!-- Claim Type -->
                <div>

                    <label class="block mb-1.5 text-sm font-medium">
                        Claim Type
                    </label>

                    <select
                        x-model="claimType"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200">

                        <option value="">-- Select Claim Type --</option>
                        <option>Medical</option>
                        <option>Hospitalization</option>
                        <option>Bereavement</option>
                        <option>Calamity</option>

                    </select>

                </div>

                <!-- Amount -->
                <div>

                    <label class="block mb-1.5 text-sm font-medium">
                        Claim Amount (₱)
                    </label>

                    <div class="relative">

                        <span class="absolute left-3 top-2.5 text-sm font-medium text-gray-600">
                            ₱
                        </span>

                        <input
                            x-model="amount"
                            type="number"
                            class="w-full rounded-lg border border-gray-300 pl-8 pr-3 py-2.5 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200">

                    </div>

                </div>

            </div>

            <!-- Description -->
            <div class="mt-5">

                <label class="block mb-1.5 text-sm font-medium">
                    Description / Purpose
                </label>

                <textarea
                    x-model="description"
                    rows="4"
                    class="w-full rounded-xl border border-gray-300 p-3 text-sm resize-none focus:border-orange-400 focus:ring-2 focus:ring-orange-200"></textarea>

            </div>

            <!-- Bottom Inputs -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">

                <div>

                    <label class="block mb-1.5 text-sm font-medium">
                        Incident / Event Date
                    </label>

                    <input
                        x-model="incidentDate"
                        type="date"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200">

                </div>

                <div>

                    <label class="block mb-1.5 text-sm font-medium">
                        Hospital / Institution (if applicable)
                    </label>

                    <input
                        x-model="hospital"
                        type="text"
                        placeholder="Hospital / Institution"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200">

                </div>

            </div>

        </div>

        <!-- Buttons -->
        <div class="flex justify-end mt-5">

            <button
                @click="step = 2"
                class="px-6 py-2.5 bg-orange-400 hover:bg-orange-500 rounded-lg font-semibold text-sm transition">

                Next

            </button>

        </div>

    </div>

</div>

<!-- ============================== -->
<!-- STEP 2 -->
<!-- ============================== -->

<div x-show="step == 2" x-transition>

    <div class="bg-white border border-blue-700 rounded-2xl p-4 md:p-6">

        <h2 class="text-lg font-semibold">
            Supporting Documents
        </h2>

        <hr class="my-3">

        <p class="text-center text-sm text-gray-700 mb-6">
            Upload all required supporting documents.
            Accepted: <strong>PDF, JPG, PNG</strong>
            (Maximum 10MB each)
        </p>

        <!-- Upload Box -->
        <label
            for="documents"
            class="mx-auto max-w-2xl h-60 border-2 border-dashed border-blue-500 rounded-2xl flex flex-col items-center justify-center cursor-pointer hover:bg-gray-50 transition">

            <!-- Folder Icon -->
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-20 h-20 text-amber-400 mb-4"
                 fill="currentColor"
                 viewBox="0 0 24 24">
                <path d="M10 4l2 2h8a1 1 0 011 1v1H3V5a1 1 0 011-1h6zm11 6v8a2 2 0 01-2 2H5a2 2 0 01-2-2v-8h18z"/>
            </svg>

            <p class="font-medium text-lg">
                Drag & Drop files here
            </p>

            <p class="text-gray-600">
                or browse
            </p>

            <p class="text-sm text-gray-500 mt-2">
                PDF, JPG, PNG up to 10MB
            </p>

            <input
                id="documents"
                type="file"
                name="documents[]"
                multiple
                accept=".pdf,.jpg,.jpeg,.png"
                class="hidden"
                @change="
                    files = [...$event.target.files];
                ">

        </label>

        <!-- Selected Files -->
        <div
            x-show="files.length"
            class="max-w-2xl mx-auto mt-5">

            <h3 class="font-medium mb-2">
                Selected Files
            </h3>

            <template
                x-for="file in files"
                :key="file.name">

                <div class="flex justify-between items-center border rounded-lg px-4 py-2 mb-2">

                    <span
                        class="text-sm truncate"
                        x-text="file.name">
                    </span>

                    <span
                        class="text-xs text-gray-500"
                        x-text="(file.size / 1024 / 1024).toFixed(2) + ' MB'">
                    </span>

                </div>

            </template>

        </div>

    </div>

    <!-- Navigation -->
    <div class="flex justify-between mt-5">

        <button
            type="button"
            @click="step = 1"
            class="px-7 py-2.5 bg-gray-300 rounded-lg font-semibold hover:bg-gray-400">

            Back

        </button>

        <button
            type="button"
            @click="step = 3"
            class="px-7 py-2.5 bg-orange-400 rounded-lg font-semibold hover:bg-orange-500">

            Next

        </button>

    </div>

</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    <!-- Bank Transfer -->
    <button
        type="button"
        @click="paymentMethod='bank'"
        :class="paymentMethod=='bank'
            ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-300'
            : 'border-gray-300'"
        class="border rounded-2xl p-6 text-center transition">

        <svg class="w-10 h-10 mx-auto mb-3 text-orange-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M3 10L12 4l9 6M5 10v8M9 10v8M15 10v8M19 10v8M3 18h18"/>
        </svg>

        <h3 class="font-semibold">
            Bank Transfer
        </h3>

        <p class="text-sm text-gray-600">
            Direct to your bank account
        </p>

    </button>

    <!-- GCash -->
    <button
        type="button"
        @click="paymentMethod='gcash'"
        :class="paymentMethod=='gcash'
            ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-300'
            : 'border-gray-300'"
        class="border rounded-2xl p-6 text-center transition">

        <svg class="w-10 h-10 mx-auto mb-3 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M17 4H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2z"/>
        </svg>

        <h3 class="font-semibold">
            GCash
        </h3>

        <p class="text-sm text-gray-600">
            Via GCash mobile wallet
        </p>

    </button>

    <!-- Check -->
    <button
        type="button"
        @click="paymentMethod='check'"
        :class="paymentMethod=='check'
            ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-300'
            : 'border-gray-300'"
        class="border rounded-2xl p-6 text-center transition">

        <svg class="w-10 h-10 mx-auto mb-3 text-green-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M4 7h16v10H4z"/>
            <path d="M8 12h4"/>
        </svg>

        <h3 class="font-semibold">
            Check
        </h3>

        <p class="text-sm text-gray-600">
            Pick up at the office
        </p>

    </button>

</div>

<div
    x-show="paymentMethod=='bank'"
    x-transition>

    <div class="grid md:grid-cols-2 gap-5">

        <div>

            <label>Bank Name</label>

            <select
                x-model="bank"
                class="w-full rounded-lg border px-3 py-2">

                <option value="">Select Bank</option>
                <option>BDO</option>
                <option>BPI</option>
                <option>LandBank</option>
                <option>Metrobank</option>

            </select>

        </div>

        <div>

            <label>Account Number</label>

            <input
                x-model="accountNumber"
                type="text"
                class="w-full rounded-lg border px-3 py-2">

        </div>

        <div>

            <label>Account Name</label>

            <input
                x-model="accountName"
                type="text"
                class="w-full rounded-lg border px-3 py-2">

        </div>

        <div>

            <label>Branch (Optional)</label>

            <input
                x-model="branch"
                type="text"
                class="w-full rounded-lg border px-3 py-2">

        </div>

    </div>

</div>

<div
    x-show="paymentMethod=='gcash'"
    x-transition>

    <div class="grid md:grid-cols-2 gap-5">

        <div>

            <label>GCash Number</label>

            <input
                x-model="gcashNumber"
                type="text"
                placeholder="09XXXXXXXXX"
                class="w-full rounded-lg border px-3 py-2">

        </div>

        <div>

            <label>Account Name</label>

            <input
                x-model="gcashName"
                type="text"
                class="w-full rounded-lg border px-3 py-2">

        </div>

    </div>

</div>

<div
    x-show="paymentMethod=='check'"
    x-transition>

    <div
        class="mt-6 rounded-2xl border bg-gray-50 p-8 text-center">

        <p class="text-lg">

            📌 Your check will be ready for pickup at the
            <strong>Faculty Association Office</strong>
            within <strong>5–7 business days</strong>
            after approval.

        </p>

    </div>

</div>

<!-- ============================== -->
<!-- STEP 4 -->
<!-- ============================== -->

<div x-show="step == 4" x-transition>

    <div class="bg-white border border-blue-700 rounded-2xl p-4 md:p-6">

        <h2 class="text-lg font-semibold">
            Review & Submit
        </h2>

        <hr class="my-4">

        <!-- ====================== -->
        <!-- Claim Details -->
        <!-- ====================== -->

        <div class="mb-6">

            <h3 class="text-base font-semibold mb-3">
                Claim Information
            </h3>

            <div class="border rounded-xl p-5 bg-gray-50">

                <div class="grid md:grid-cols-2 gap-4">

                    <div>
                        <label class="text-sm text-gray-500">Claim Type</label>
                        <p x-text="claimType" class="font-medium"></p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-500">Claim Amount</label>
                        <p class="font-medium">
                            ₱<span x-text="amount"></span>
                        </p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-500">Incident Date</label>
                        <p x-text="incidentDate" class="font-medium"></p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-500">Hospital / Institution</label>
                        <p x-text="hospital || 'N/A'" class="font-medium"></p>
                    </div>

                </div>

                <div class="mt-4">

                    <label class="text-sm text-gray-500">
                        Description
                    </label>

                    <p
                        x-text="description"
                        class="mt-1">
                    </p>

                </div>

            </div>

        </div>

        <!-- ====================== -->
        <!-- Documents -->
        <!-- ====================== -->

        <div class="mb-6">

            <h3 class="text-base font-semibold mb-3">
                Supporting Documents
            </h3>

            <div class="border rounded-xl p-5 bg-gray-50">

                <template x-if="files.length">

                    <ul class="space-y-2">

                        <template
                            x-for="file in files"
                            :key="file.name">

                            <li class="flex justify-between">

                                <span x-text="file.name"></span>

                                <span class="text-green-600">
                                    ✓
                                </span>

                            </li>

                        </template>

                    </ul>

                </template>

                <p
                    x-show="files.length==0"
                    class="text-gray-500">

                    No documents uploaded.

                </p>

            </div>

        </div>

        <!-- ====================== -->
        <!-- Payment -->
        <!-- ====================== -->

        <div class="mb-6">

            <h3 class="text-base font-semibold mb-3">
                Payment Information
            </h3>

            <div class="border rounded-xl p-5 bg-gray-50">

                <div class="mb-4">

                    <label class="text-sm text-gray-500">
                        Payment Method
                    </label>

                    <p class="font-medium capitalize"
                       x-text="paymentMethod">
                    </p>

                </div>

                <!-- Bank -->

                <div
                    x-show="paymentMethod=='bank'"
                    class="grid md:grid-cols-2 gap-4">

                    <div>

                        <label class="text-sm text-gray-500">
                            Bank Name
                        </label>

                        <p x-text="bank"></p>

                    </div>

                    <div>

                        <label class="text-sm text-gray-500">
                            Account Number
                        </label>

                        <p x-text="accountNumber"></p>

                    </div>

                    <div>

                        <label class="text-sm text-gray-500">
                            Account Name
                        </label>

                        <p x-text="accountName"></p>

                    </div>

                    <div>

                        <label class="text-sm text-gray-500">
                            Branch
                        </label>

                        <p x-text="branch || 'N/A'"></p>

                    </div>

                </div>

                <!-- GCash -->

                <div
                    x-show="paymentMethod=='gcash'"
                    class="grid md:grid-cols-2 gap-4">

                    <div>

                        <label class="text-sm text-gray-500">
                            GCash Number
                        </label>

                        <p x-text="gcashNumber"></p>

                    </div>

                    <div>

                        <label class="text-sm text-gray-500">
                            Account Name
                        </label>

                        <p x-text="gcashName"></p>

                    </div>

                </div>

                <!-- Check -->

                <div
                    x-show="paymentMethod=='check'">

                    <div class="rounded-lg bg-yellow-50 border border-yellow-300 p-4">

                        📌 Check payment will be available for pickup at the
                        <strong>Faculty Association Office</strong>
                        within
                        <strong>5–7 business days</strong>
                        after approval.

                    </div>

                </div>

            </div>

        </div>

        <!-- ====================== -->
        <!-- Declaration -->
        <!-- ====================== -->

        <div class="border rounded-xl p-4 bg-blue-50">

            <label class="flex items-start gap-3">

                <input
                    type="checkbox"
                    required
                    class="mt-1">

                <span class="text-sm">

                    I certify that the information and uploaded documents are
                    true, complete, and accurate. I understand that providing
                    false or misleading information may result in the rejection
                    of my claim.

                </span>

            </label>

        </div>

    </div>

    <!-- Buttons -->

    <div class="flex justify-between mt-5">

        <button
            type="button"
            @click="step=3"
            class="px-6 py-2.5 bg-gray-300 rounded-lg font-semibold hover:bg-gray-400">

            Back

        </button>

        <button
            type="submit"
            class="px-7 py-2.5 bg-orange-500 hover:bg-orange-600 rounded-lg font-semibold text-white">

            Submit Claim

        </button>

    </div>

</div>

</x-app-layout>