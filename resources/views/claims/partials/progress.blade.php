       <div class="bg-white rounded-2xl shadow-sm p-5 mb-6">

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

                        Disbursement

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