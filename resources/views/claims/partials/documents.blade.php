<section
            x-show="step == 2"
            x-transition.opacity.duration.250ms
            x-cloak>
            <div class="bg-white rounded-2xl border border-blue-700 shadow-sm p-6">
                <div class="mb-6">
                    <h2 class="text-xl font-bold">
                        Supporting Documents
                        <span class="text-red-500">*</span>
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Upload all required supporting documents.
                        Accepted formats:
                        <strong>PDF, JPG, JPEG, PNG</strong>
                    </p>
                </div>
                <x-file-preview-modal />
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
                        name="documents[]"
                        id="documents"
                        type="file"
                        multiple
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="hidden"

                        @change="
                            const allowedTypes = [
                                'application/pdf',
                                'image/jpeg',
                                'image/png'
                            ];

                            const allowedExtensions = [
                                'pdf',
                                'jpg',
                                'jpeg',
                                'png'
                            ];

                            const maxSize = 10 * 1024 * 1024; // 10 MB

                            Array.from($event.target.files).forEach(file => {
                            
                                const extension = file.name
                                    .split('.')
                                    .pop()
                                    .toLowerCase();
                            
                                const validExtension = allowedExtensions.includes(extension);
                                const validType = allowedTypes.includes(file.type);
                            
                                if (!validExtension || !validType) {
                                    
                                    Swal.fire({
                                        html: `${file.name} is not supported.</br>Only PDF, JPG, JPEG, and PNG files are allowed.`
                                    });
                                    return;
                                }
                            
                                if (file.size > maxSize) {
                                    
                                    Swal.fire(`${file.name} exceeds the maximum file size of 10 MB.`);
                                    return;
                                }
                            
                                files.push(file);
                            });
                        
                            $event.target.value = '';
                        "
                    >
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
                                <!-- Preview -->
                                <div class="flex flex-row gap-1">
                                    <button 
                                        type="button" 
                                        @click="window.dispatchEvent(new CustomEvent('open-file-preview', { detail: { url: window.URL.createObjectURL(file), name: file.name } }))" 
                                        class="inline-flex items-center gap-1 font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100/70 px-2.5 py-1 rounded transition shrink-0">
                                        View File
                                    </button>
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