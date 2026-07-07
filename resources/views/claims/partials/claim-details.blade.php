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
                            name="claimType"
                            x-model="claimType"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-orange-300">
                            <option value="">
                                Select Claim Type
                            </option>
                            @foreach ($claimTypes as $claimType )
                                <option value="{{ $claimType->id }}">
                                {{ $claimType->name }}
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
                            <span
                                class="absolute left-4 top-3.5 text-gray-500">
                                ₱
                            </span>
                            <input
                                name="amount"
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
                        name="description"
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
                            name="incidentDate"
                            type="date"
                            x-model="incidentDate"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">
                            Hospital / Institution
                        </label>
                        <input
                            name="hospital"
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
