        <section
            x-show="step==1"
            x-transition.opacity.duration.250ms>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs sm:p-6">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Claim Information
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Enter the details regarding your claim request.
                    </p>
                </div>
                <div class="grid gap-5 lg:grid-cols-2">
                    <!-- Claim Type -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Claim Type
                            <span class="text-red-500">*</span>
                        </label>
                       <select 
                            id="claim-type-input"
                            name="claimType"
                            x-model="claimType"
                            @change="claimTypeText = $el.selectedIndex > 0 ? $el.options[$el.selectedIndex].text : ''"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm shadow-xs">

                            <option value="">Select Claim Type</option>

                            @foreach ($claimTypes as $type)
                                <option value="{{ $type->id }}">
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Amount -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Claim Amount
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-500">
                                ₱
                            </span>
                                <input
                                    id="claim-amount-input"
                                    name="amount"
                                    x-model="amount"
                                    type="text"
                                    inputmode="numeric"
                                    x-on:input="
                                        let value = $event.target.value.replace(/[^0-9]/g, '');

                                        if (value === '') {
                                            amount = '';
                                            $event.target.value = '';
                                            return;
                                        }

                                        amount = value;
                                        $event.target.value = Number(value).toLocaleString('en-US');
                                    "

                                    x-on:paste="
                                        setTimeout(() => {
                                            let value = $event.target.value.replace(/[^0-9]/g, '');

                                            if (value === '') {
                                                amount = '';
                                                $event.target.value = '';
                                                return;
                                            }

                                            amount = value;
                                            $event.target.value = Number(value).toLocaleString('en-US');
                                        }, 0);
                                    "

                                    @blur="checkRunningBalance()"

                                    class="w-full rounded-lg border border-gray-300 bg-white pl-8 pr-3.5 py-2.5 text-sm shadow-xs tabular-nums"
                                >
                        </div>
                    </div>
                </div>
                <!-- Description -->
                <div class="mt-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Description
                        <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="claim-description-input"
                        name="description"
                        rows="5"
                        x-model="description"
                         x-on:input="
                            let value = $event.target.value;

                            value = value
                                .toLowerCase()
                                .replace(/\b\w/g, char => char.toUpperCase());

                            $event.target.value = value;
                            description = value;
                        "
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm shadow-xs resize-none"
                        placeholder="Provide a brief description of your claim..."></textarea>
                </div>
                <!-- Bottom -->
                <div class="mt-5 grid gap-5 lg:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Treatment Start Date
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="claim-incident-date-input"
                            name="incidentDate"
                            type="date"
                            required
                            x-model="incidentDate"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm shadow-xs">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Treatment End Date
                        </label>
                        <input
                            id="claim-treatment-end-date-input"
                            name="treatmentEndDate"
                            type="date"
                            x-model="treatmentEndDate"
                            :min="incidentDate"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm shadow-xs">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Hospital / Institution
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="claim-hospital-input"
                            name="hospital"
                            type="text"
                            x-model="hospital"
                             x-on:input="
                                let value = $event.target.value;

                                value = value
                                    .toLowerCase()
                                    .replace(/\b\w/g, char => char.toUpperCase());

                                $event.target.value = value;
                                hospital = value;
                            "
                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm shadow-xs"
                            placeholder="Hospital / Institution">
                    </div>
                </div>
            </div>
            <!-- Buttons -->
            <div class="flex justify-end mt-6">
                <button
                    type="button"
                    @click="nextStep()"
                    class="w-full rounded-lg bg-orange-500 px-6 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-orange-600 sm:w-auto">
                    Continue
                </button>
            </div>
        </section>
