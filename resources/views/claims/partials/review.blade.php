<section
            x-show="step == 4"
            x-transition.opacity.duration.250ms
            x-cloak>

            <div class="bg-white rounded-2xl border border-blue-700 shadow-sm p-6">
                <x-file-preview-modal />
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

                        <p class="font-semibold mt-1" x-text="claimTypeText || '-'"></p>

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

                                        <div class="flex flex-row gap-1">
                                            <span
                                                class="text-green-600 font-semibold">

                                                Uploaded

                                            </span>
                                            <button 
                                                type="button" 
                                                @click="window.dispatchEvent(new CustomEvent('open-file-preview', { detail: { url: window.URL.createObjectURL(file), name: file.name } }))" 
                                                class="inline-flex items-center gap-1 font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100/70 px-2.5 py-1 rounded transition shrink-0">
                                                View File
                                            </button>
                                        </div>
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

                            Disbursement Information

                        </h3>

                    </div>

                    <div class="p-5">

                        <div class="mb-5">

                            <label
                                class="text-sm text-gray-500">

                                Disbursement Method

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

                                    Check Disbursement

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