        <section
            x-show="step==1"
            x-transition.opacity.duration.250ms>
            <div class="bg-white rounded-2xl shadow-sm p-6">
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
                            id="claim-type-input"
                            name="claimType"
                            x-model="claimType"
                            @change="claimTypeText = $el.selectedIndex > 0 ? $el.options[$el.selectedIndex].text : ''"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3">

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
                        <label class="block text-sm font-medium mb-2">
                            Claim Amount
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-gray-500">
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

                                    class="w-full rounded-xl border border-gray-300 pl-9 pr-4 py-3"
                                >
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
                        class="w-full rounded-xl border border-gray-300 p-4 resize-none"
                        placeholder="Provide a brief description of your claim..."></textarea>
                </div>
                <!-- Bottom -->
                <div class="grid lg:grid-cols-3 gap-6 mt-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">
                            Treatment Start Date
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="claim-incident-date-input"
                            name="incidentDate"
                            type="date"
                            required
                            x-model="incidentDate"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">
                            Treatment End Date
                        </label>
                        <input
                            id="claim-treatment-end-date-input"
                            name="treatmentEndDate"
                            type="date"
                            x-model="treatmentEndDate"
                            :min="incidentDate"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">
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
