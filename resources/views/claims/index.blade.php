<x-app-layout>

<div
    x-data="claimWizard()"
    class="max-w-6xl mx-auto px-4 py-6">

    <form
        action="{{ route('claim.store') }}"
        method="POST"
        enctype="multipart/form-data">

        @csrf

        <!-- ================================================= -->
        <!-- PROGRESS STEPPER -->
        <!-- ================================================= -->

        <div class="bg-white rounded-2xl border border-blue-700 shadow-sm p-5 mb-6">

            <div class="flex flex-wrap items-center justify-center gap-5 md:gap-12">

                <!-- Step 1 -->

                <div
                    class="flex items-center gap-3 cursor-pointer"
                    @click="goToStep(1)">

                    <div
                        class="w-10 h-10 rounded-full border-2 flex items-center justify-center font-semibold transition-all duration-300"
                        :class="step >= 1
                            ? 'bg-orange-500 border-orange-500 text-white'
                            : 'border-gray-400 bg-white text-gray-700'">

                        1

                    </div>

                    <span
                        class="font-medium"
                        :class="step==1 ? 'text-orange-600' : ''">

                        Claim Details

                    </span>

                </div>

                <!-- Step 2 -->

                <div
                    class="flex items-center gap-3 cursor-pointer"
                    @click="step > 1 && goToStep(2)">

                    <div
                        class="w-10 h-10 rounded-full border-2 flex items-center justify-center font-semibold transition-all duration-300"
                        :class="step >= 2
                            ? 'bg-orange-500 border-orange-500 text-white'
                            : 'border-gray-400 bg-white text-gray-700'">

                        2

                    </div>

                    <span
                        class="font-medium"
                        :class="step==2 ? 'text-orange-600' : ''">

                        Documents

                    </span>

                </div>

                <!-- Step 3 -->

                <div
                    class="flex items-center gap-3 cursor-pointer"
                    @click="step > 2 && goToStep(3)">

                    <div
                        class="w-10 h-10 rounded-full border-2 flex items-center justify-center font-semibold transition-all duration-300"
                        :class="step >= 3
                            ? 'bg-orange-500 border-orange-500 text-white'
                            : 'border-gray-400 bg-white text-gray-700'">

                        3

                    </div>

                    <span
                        class="font-medium"
                        :class="step==3 ? 'text-orange-600' : ''">

                        Payment

                    </span>

                </div>

                <!-- Step 4 -->

                <div
                    class="flex items-center gap-3 cursor-pointer"
                    @click="step > 3 && goToStep(4)">

                    <div
                        class="w-10 h-10 rounded-full border-2 flex items-center justify-center font-semibold transition-all duration-300"
                        :class="step >= 4
                            ? 'bg-orange-500 border-orange-500 text-white'
                            : 'border-gray-400 bg-white text-gray-700'">

                        4

                    </div>

                    <span
                        class="font-medium"
                        :class="step==4 ? 'text-orange-600' : ''">

                        Review

                    </span>

                </div>

            </div>

        </div>

        <!-- ================================================= -->
        <!-- STEP 1 -->
        <!-- ================================================= -->

        <section
            x-show="step==1"
            x-transition.opacity.duration.250ms>

            <div class="bg-white rounded-2xl border border-blue-700 shadow-sm p-6">

                <div class="mb-6">

                    <h2 class="text-xl font-bold">

                        Claim Information

                    </h2>

                    <p class="text-gray-500 text-sm mt-1">

                        Enter the details regarding your claim request.

                    </p>

                </div>

                <div class="grid lg:grid-cols-2 gap-6">

                    <!-- Claim Type -->

                    <div>

                        <label class="block text-sm font-medium mb-2">

                            Claim Type
                            <span class="text-red-500">*</span>

                        </label>

                        <select
                            x-model="claimType"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-orange-300">

                            <option value="">

                                Select Claim Type

                            </option>

                            <option value="Medical">

                                Medical

                            </option>

                            <option value="Hospitalization">

                                Hospitalization

                            </option>

                            <option value="Bereavement">

                                Bereavement

                            </option>

                            <option value="Calamity">

                                Calamity

                            </option>

                        </select>

                    </div>

                    <!-- Amount -->

                    <div>

                        <label class="block text-sm font-medium mb-2">

                            Claim Amount

                            <span class="text-red-500">*</span>

                        </label>

                        <div class="relative">

                            <span
                                class="absolute left-4 top-3.5 text-gray-500">

                                ₱

                            </span>

                            <input
                                x-model="amount"
                                type="number"
                                class="w-full rounded-xl border border-gray-300 pl-9 pr-4 py-3 focus:ring-2 focus:ring-orange-300">

                        </div>

                    </div>

                </div>

                <!-- Description -->

                <div class="mt-6">

                    <label class="block text-sm font-medium mb-2">

                        Description

                        <span class="text-red-500">*</span>

                    </label>

                    <textarea
                        rows="5"
                        x-model="description"
                        class="w-full rounded-xl border border-gray-300 p-4 resize-none focus:ring-2 focus:ring-orange-300"
                        placeholder="Provide a brief description of your claim..."></textarea>

                </div>

                <!-- Bottom -->

                <div class="grid lg:grid-cols-2 gap-6 mt-6">

                    <div>

                        <label class="block text-sm font-medium mb-2">

                            Incident Date

                        </label>

                        <input
                            type="date"
                            x-model="incidentDate"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    </div>

                    <div>

                        <label class="block text-sm font-medium mb-2">

                            Hospital / Institution

                        </label>

                        <input
                            type="text"
                            x-model="hospital"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3"
                            placeholder="Hospital / Institution">

                    </div>

                </div>

            </div>

            <!-- Buttons -->

            <div class="flex justify-end mt-6">

                <button
                    type="button"
                    @click="nextStep()"
                    class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-semibold transition">

                    Continue

                </button>

            </div>

        </section>

                <!-- ================================================= -->
        <!-- STEP 2 - SUPPORTING DOCUMENTS -->
        <!-- ================================================= -->

        <section
            x-show="step == 2"
            x-transition.opacity.duration.250ms
            x-cloak>

            <div class="bg-white rounded-2xl border border-blue-700 shadow-sm p-6">

                <div class="mb-6">

                    <h2 class="text-xl font-bold">

                        Supporting Documents

                    </h2>

                    <p class="text-sm text-gray-500 mt-1">

                        Upload all required supporting documents.
                        Accepted formats:
                        <strong>PDF, JPG, JPEG, PNG</strong>

                    </p>

                </div>

                <!-- Upload Area -->

                <label
                    for="documents"
                    class="group
                           flex flex-col items-center justify-center
                           border-2 border-dashed border-blue-400
                           rounded-2xl
                           h-72
                           cursor-pointer
                           transition
                           hover:border-orange-500
                           hover:bg-orange-50">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-20 h-20 text-orange-400 group-hover:scale-110 transition"
                        fill="currentColor"
                        viewBox="0 0 24 24">

                        <path d="M10 4l2 2h8a1 1 0 011 1v1H3V5a1 1 0 011-1h6zm11 6v8a2 2 0 01-2 2H5a2 2 0 01-2-2v-8h18z"/>

                    </svg>

                    <h3 class="mt-4 font-semibold text-lg">

                        Drag & Drop Files Here

                    </h3>

                    <p class="text-gray-500">

                        or Click to Browse

                    </p>

                    <span class="text-sm text-gray-400 mt-3">

                        Maximum file size: 10 MB

                    </span>

                    <input
                        id="documents"
                        type="file"
                        multiple
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="hidden"

                        @change="

                            Array.from($event.target.files).forEach(file=>{

                                files.push(file)

                            })

                        ">

                </label>

                <!-- ================================================= -->
                <!-- FILE LIST -->
                <!-- ================================================= -->

                <div
                    x-show="files.length"
                    class="mt-8">

                    <div class="flex items-center justify-between mb-4">

                        <h3 class="font-semibold">

                            Selected Documents

                        </h3>

                        <span
                            class="text-sm text-gray-500">

                            <span x-text="files.length"></span>

                            file(s)

                        </span>

                    </div>

                    <div class="space-y-3">

                        <template
                            x-for="(file,index) in files"
                            :key="index">

                            <div
                                class="flex items-center justify-between
                                       rounded-xl
                                       border
                                       bg-gray-50
                                       px-4
                                       py-3">

                                <div class="flex items-center gap-4">

                                    <!-- Icon -->

                                    <div
                                        class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center">

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="w-6 h-6 text-orange-500"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            viewBox="0 0 24 24">

                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M7 7V3h10v18H7V7zm0 0h4V3"/>

                                        </svg>

                                    </div>

                                    <div>

                                        <p
                                            class="font-medium"
                                            x-text="file.name">
                                        </p>

                                        <p
                                            class="text-sm text-gray-500"
                                            x-text="(file.size/1024/1024).toFixed(2)+' MB'">
                                        </p>

                                    </div>

                                </div>

                                <!-- Remove -->

                                <button
                                    type="button"

                                    @click="files.splice(index,1)"

                                    class="text-red-500 hover:text-red-700">

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-6 h-6"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24">

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12"/>

                                    </svg>

                                </button>

                            </div>

                        </template>

                    </div>

                </div>

                <!-- Empty State -->

                <div
                    x-show="!files.length"
                    class="mt-8 rounded-xl bg-gray-50 p-6 text-center">

                    <p class="text-gray-500">

                        No documents selected yet.

                    </p>

                </div>

            </div>

            <!-- Buttons -->

            <div class="flex justify-between mt-6">

                <button
                    type="button"
                    @click="previousStep()"
                    class="px-7 py-3 rounded-xl bg-gray-300 hover:bg-gray-400 transition">

                    Back

                </button>

                <button
                    type="button"
                    @click="nextStep()"
                    class="px-8 py-3 rounded-xl bg-orange-500 text-white hover:bg-orange-600 transition">

                    Continue

                </button>

            </div>

        </section>

        <!-- ================================================= -->
<!-- STEP 3 - PAYMENT INFORMATION -->
<!-- ================================================= -->

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
                        x-model="accountNumber"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3">

                </div>

                <div>

                    <label class="block text-sm font-medium mb-2">

                        Account Name

                    </label>

                    <input
                        x-model="accountName"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3">

                </div>

                <div>

                    <label class="block text-sm font-medium mb-2">

                        Branch

                    </label>

                    <input
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
        <!-- ================================================= -->
        <!-- STEP 4 - REVIEW -->
        <!-- ================================================= -->

        <section
            x-show="step == 4"
            x-transition.opacity.duration.250ms
            x-cloak>

            <div class="bg-white rounded-2xl border border-blue-700 shadow-sm p-6">

                <div class="mb-6">

                    <h2 class="text-xl font-bold">

                        Review Your Claim

                    </h2>

                    <p class="text-gray-500 text-sm mt-1">

                        Please review all information before submitting your
                        claim request.

                    </p>

                </div>

                <!-- ================================= -->
                <!-- CLAIM DETAILS -->
                <!-- ================================= -->

                <div class="rounded-xl border border-gray-200 overflow-hidden mb-6">

                    <div class="bg-blue-50 px-5 py-3">

                        <h3 class="font-semibold">

                            Claim Information

                        </h3>

                    </div>

                    <div class="grid md:grid-cols-2 gap-5 p-5">

                        <div>

                            <label class="text-sm text-gray-500">

                                Claim Type

                            </label>

                            <p
                                class="font-semibold mt-1"
                                x-text="claimType || '-'">
                            </p>

                        </div>

                        <div>

                            <label class="text-sm text-gray-500">

                                Claim Amount

                            </label>

                            <p
                                class="font-semibold mt-1">

                                ₱
                                <span
                                    x-text="formatCurrency(amount)">
                                </span>

                            </p>

                        </div>

                        <div>

                            <label class="text-sm text-gray-500">

                                Incident Date

                            </label>

                            <p
                                class="font-semibold mt-1"
                                x-text="incidentDate || '-'">
                            </p>

                        </div>

                        <div>

                            <label class="text-sm text-gray-500">

                                Hospital / Institution

                            </label>

                            <p
                                class="font-semibold mt-1"
                                x-text="hospital || 'N/A'">
                            </p>

                        </div>

                    </div>

                    <div class="border-t p-5">

                        <label class="text-sm text-gray-500">

                            Description

                        </label>

                        <p
                            class="mt-2 leading-relaxed"
                            x-text="description">
                        </p>

                    </div>

                </div>

                <!-- ================================= -->
                <!-- DOCUMENTS -->
                <!-- ================================= -->

                <div class="rounded-xl border border-gray-200 overflow-hidden mb-6">

                    <div class="bg-blue-50 px-5 py-3">

                        <h3 class="font-semibold">

                            Supporting Documents

                        </h3>

                    </div>

                    <div class="p-5">

                        <template x-if="files.length">

                            <div class="space-y-3">

                                <template
                                    x-for="file in files"
                                    :key="file.name">

                                    <div
                                        class="flex justify-between items-center rounded-lg border p-3">

                                        <div class="flex items-center gap-3">

                                            <div
                                                class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">

                                                📄

                                            </div>

                                            <div>

                                                <p
                                                    class="font-medium"
                                                    x-text="file.name">
                                                </p>

                                                <p
                                                    class="text-sm text-gray-500"
                                                    x-text="(file.size/1024/1024).toFixed(2)+' MB'">
                                                </p>

                                            </div>

                                        </div>

                                        <span
                                            class="text-green-600 font-semibold">

                                            Uploaded

                                        </span>

                                    </div>

                                </template>

                            </div>

                        </template>

                        <template x-if="files.length==0">

                            <div
                                class="text-center text-gray-400 py-8">

                                No supporting documents uploaded.

                            </div>

                        </template>

                    </div>

                </div>

                <!-- ================================= -->
                <!-- PAYMENT -->
                <!-- ================================= -->

                <div class="rounded-xl border border-gray-200 overflow-hidden">

                    <div class="bg-blue-50 px-5 py-3">

                        <h3 class="font-semibold">

                            Payment Information

                        </h3>

                    </div>

                    <div class="p-5">

                        <div class="mb-5">

                            <label
                                class="text-sm text-gray-500">

                                Payment Method

                            </label>

                            <p
                                class="font-semibold capitalize mt-1"
                                x-text="paymentMethod">
                            </p>

                        </div>
                                                <!-- ================================= -->
                        <!-- BANK SUMMARY -->
                        <!-- ================================= -->

                        <div
                            x-show="paymentMethod == 'bank'"
                            x-transition>

                            <div class="grid md:grid-cols-2 gap-5">

                                <div>

                                    <label class="text-sm text-gray-500">

                                        Bank

                                    </label>

                                    <p
                                        class="font-semibold mt-1"
                                        x-text="bank || '-'">
                                    </p>

                                </div>

                                <div>

                                    <label class="text-sm text-gray-500">

                                        Account Number

                                    </label>

                                    <p
                                        class="font-semibold mt-1"
                                        x-text="accountNumber || '-'">
                                    </p>

                                </div>

                                <div>

                                    <label class="text-sm text-gray-500">

                                        Account Name

                                    </label>

                                    <p
                                        class="font-semibold mt-1"
                                        x-text="accountName || '-'">
                                    </p>

                                </div>

                                <div>

                                    <label class="text-sm text-gray-500">

                                        Branch

                                    </label>

                                    <p
                                        class="font-semibold mt-1"
                                        x-text="branch || 'N/A'">
                                    </p>

                                </div>

                            </div>

                        </div>

                        <!-- ================================= -->
                        <!-- GCASH SUMMARY -->
                        <!-- ================================= -->

                        <div
                            x-show="paymentMethod == 'gcash'"
                            x-transition>

                            <div class="grid md:grid-cols-2 gap-5">

                                <div>

                                    <label class="text-sm text-gray-500">

                                        GCash Number

                                    </label>

                                    <p
                                        class="font-semibold mt-1"
                                        x-text="gcashNumber || '-'">
                                    </p>

                                </div>

                                <div>

                                    <label class="text-sm text-gray-500">

                                        Account Name

                                    </label>

                                    <p
                                        class="font-semibold mt-1"
                                        x-text="gcashName || '-'">
                                    </p>

                                </div>

                            </div>

                        </div>

                        <!-- ================================= -->
                        <!-- CHECK SUMMARY -->
                        <!-- ================================= -->

                        <div
                            x-show="paymentMethod == 'check'"
                            x-transition>

                            <div class="rounded-xl bg-yellow-50 border border-yellow-300 p-5">

                                <h4 class="font-semibold text-yellow-700">

                                    Check Payment

                                </h4>

                                <p class="text-sm text-yellow-700 mt-2 leading-relaxed">

                                    Your approved claim will be released through
                                    check payment.

                                    Once approved, you will receive a notification
                                    informing you that your check is ready for
                                    pickup at the Faculty Association Office.

                                </p>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- ================================= -->
                <!-- DECLARATION -->
                <!-- ================================= -->

                <div
                    class="mt-6 rounded-xl border border-blue-200 bg-blue-50 p-5">

                    <label class="flex items-start gap-3">

                        <input
                            type="checkbox"
                            x-model="agree"
                            class="mt-1 rounded">

                        <span class="text-sm leading-relaxed">

                            I hereby certify that all information provided,
                            together with the uploaded documents, is true,
                            correct, and complete to the best of my knowledge.

                            I understand that any false or misleading
                            information may result in the rejection of my
                            claim request.

                        </span>

                    </label>

                </div>

            </div>

            <!-- ================================= -->
            <!-- BUTTONS -->
            <!-- ================================= -->

            <div class="flex justify-between mt-6">

                <button
                    type="button"
                    @click="previousStep()"
                    class="px-8 py-3 rounded-xl bg-gray-300 hover:bg-gray-400 transition">

                    ← Back

                </button>

                <button
                    type="submit"
                    :disabled="!agree"
                    :class="agree
                        ? 'bg-orange-500 hover:bg-orange-600'
                        : 'bg-gray-400 cursor-not-allowed'"

                    class="px-8 py-3 rounded-xl text-white font-semibold transition">

                    Submit Claim

                </button>

            </div>

        </section>
        <script>
function claimWizard() {

    return {

        /* =====================================================
         * CURRENT STEP
         * ===================================================== */

        step: 1,

        agree: false,

        /* =====================================================
         * FORM MODEL
         * ===================================================== */

        form: {

            // STEP 1

            claimType: '',
            amount: '',
            description: '',
            incidentDate: '',
            hospital: '',

            // STEP 2

            files: [],

            // STEP 3

            paymentMethod: '',

            bank: '',
            accountName: '',
            accountNumber: '',
            branch: '',

            gcashName: '',
            gcashNumber: '',

        },

        /* =====================================================
         * ALIASES
         * (Keeps existing Blade working)
         * ===================================================== */

        get claimType() {
            return this.form.claimType;
        },
        set claimType(value) {
            this.form.claimType = value;
        },

        get amount() {
            return this.form.amount;
        },
        set amount(value) {
            this.form.amount = value;
        },

        get description() {
            return this.form.description;
        },
        set description(value) {
            this.form.description = value;
        },

        get incidentDate() {
            return this.form.incidentDate;
        },
        set incidentDate(value) {
            this.form.incidentDate = value;
        },

        get hospital() {
            return this.form.hospital;
        },
        set hospital(value) {
            this.form.hospital = value;
        },

        get files() {
            return this.form.files;
        },
        set files(value) {
            this.form.files = value;
        },

        get paymentMethod() {
            return this.form.paymentMethod;
        },
        set paymentMethod(value) {
            this.form.paymentMethod = value;
        },

        get bank() {
            return this.form.bank;
        },
        set bank(value) {
            this.form.bank = value;
        },

        get accountName() {
            return this.form.accountName;
        },
        set accountName(value) {
            this.form.accountName = value;
        },

        get accountNumber() {
            return this.form.accountNumber;
        },
        set accountNumber(value) {
            this.form.accountNumber = value;
        },

        get branch() {
            return this.form.branch;
        },
        set branch(value) {
            this.form.branch = value;
        },

        get gcashName() {
            return this.form.gcashName;
        },
        set gcashName(value) {
            this.form.gcashName = value;
        },

        get gcashNumber() {
            return this.form.gcashNumber;
        },
        set gcashNumber(value) {
            this.form.gcashNumber = value;
        },

        /* =====================================================
         * STEP NAVIGATION
         * ===================================================== */

        nextStep() {

            if (!this.validateStep()) {
                return;
            }

            if (this.step < 4) {
                this.step++;
            }

        },

        previousStep() {

            if (this.step > 1) {
                this.step--;
            }

        },

        goToStep(step) {

            if (step <= this.step) {
                this.step = step;
            }

        },

        /* =====================================================
         * VALIDATION
         * ===================================================== */

        validateStep() {

            switch (this.step) {

                case 1:

                    if (!this.claimType) {
                        alert('Please select a Claim Type.');
                        return false;
                    }

                    if (!this.amount) {
                        alert('Please enter the Claim Amount.');
                        return false;
                    }

                    if (!this.description) {
                        alert('Please provide a Description.');
                        return false;
                    }

                    return true;

                case 2:

                    if (this.files.length === 0) {

                        if (!confirm(
                            'No supporting documents have been uploaded. Continue anyway?'
                        )) {
                            return false;
                        }

                    }

                    return true;

                case 3:

                    if (!this.paymentMethod) {
                        alert('Please select a payment method.');
                        return false;
                    }

                    if (this.paymentMethod === 'bank') {

                        if (
                            !this.bank ||
                            !this.accountNumber ||
                            !this.accountName
                        ) {

                            alert(
                                'Please complete all required bank information.'
                            );

                            return false;
                        }

                    }

                    if (this.paymentMethod === 'gcash') {

                        if (
                            !this.gcashName ||
                            !this.gcashNumber
                        ) {

                            alert(
                                'Please complete your GCash details.'
                            );

                            return false;
                        }

                    }

                    return true;

                default:

                    return true;

            }

        },

        /* =====================================================
         * HELPERS
         * ===================================================== */

        formatCurrency(value) {

            if (!value) {
                return '0.00';
            }

            return Number(value).toLocaleString(
                'en-PH',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );

        },

        resetForm() {

            this.step = 1;

            this.agree = false;

            this.form = {

                claimType: '',
                amount: '',
                description: '',
                incidentDate: '',
                hospital: '',

                files: [],

                paymentMethod: '',

                bank: '',
                accountName: '',
                accountNumber: '',
                branch: '',

                gcashName: '',
                gcashNumber: '',

            };

        }

    }

}
</script>

    </form>

</div>

</x-app-layout>