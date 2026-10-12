       <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-xs sm:p-5">

            <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-8 md:gap-12">

                <!-- Step 1 -->

                <div
                    class="flex items-center gap-3 cursor-pointer"
                    @click="goToStep(1)">

                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 text-sm font-semibold transition-colors duration-300"
                        :class="step >= 1
                            ? 'bg-orange-500 border-orange-500 text-white'
                            : 'border-gray-400 bg-white text-gray-700'">

                        1

                    </div>

                    <span
                        class="hidden text-sm font-medium sm:inline"
                        :class="step==1 ? 'text-orange-600' : ''">

                        Claim Details

                    </span>

                </div>

                <!-- Step 2 -->

                <div
                    class="flex items-center gap-3 cursor-pointer"
                    @click="step > 1 && goToStep(2)">

                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 text-sm font-semibold transition-colors duration-300"
                        :class="step >= 2
                            ? 'bg-orange-500 border-orange-500 text-white'
                            : 'border-gray-400 bg-white text-gray-700'">

                        2

                    </div>

                    <span
                        class="hidden text-sm font-medium sm:inline"
                        :class="step==2 ? 'text-orange-600' : ''">

                        Documents

                    </span>

                </div>

                <!-- Step 3 -->

                <div
                    class="flex items-center gap-3 cursor-pointer"
                    @click="step > 2 && goToStep(3)">

                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 text-sm font-semibold transition-colors duration-300"
                        :class="step >= 3
                            ? 'bg-orange-500 border-orange-500 text-white'
                            : 'border-gray-400 bg-white text-gray-700'">

                        3

                    </div>

                    <span
                        class="hidden text-sm font-medium sm:inline"
                        :class="step==3 ? 'text-orange-600' : ''">

                        Disbursement

                    </span>

                </div>

                <!-- Step 4 -->

                <div
                    class="flex items-center gap-3 cursor-pointer"
                    @click="step > 3 && goToStep(4)">

                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 text-sm font-semibold transition-colors duration-300"
                        :class="step >= 4
                            ? 'bg-orange-500 border-orange-500 text-white'
                            : 'border-gray-400 bg-white text-gray-700'">

                        4

                    </div>

                    <span
                        class="hidden text-sm font-medium sm:inline"
                        :class="step==4 ? 'text-orange-600' : ''">

                        Review

                    </span>

                </div>

            </div>

        </div>