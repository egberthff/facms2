<section
            x-show="step == 2"
            x-transition.opacity.duration.250ms
            x-cloak>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs sm:p-6">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Supporting Documents
                        <span class="text-red-500">*</span>
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
                    :class="{ 'error-border': errors.documents }"
                    class="group flex h-56 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-blue-300 bg-gray-50/50 px-4 text-center transition hover:border-orange-400 hover:bg-orange-50/60 sm:h-64">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-14 w-14 text-orange-400"
                        fill="currentColor"
                        viewBox="0 0 24 24">
                        <path d="M10 4l2 2h8a1 1 0 011 1v1H3V5a1 1 0 011-1h6zm11 6v8a2 2 0 01-2 2H5a2 2 0 01-2-2v-8h18z"/>
                    </svg>
                    <h3 class="mt-3 text-base font-semibold text-gray-900">
                        Drag & Drop Files Here
                    </h3>
                    <p class="text-sm text-gray-500">
                        or Click to Browse
                    </p>
                    <span class="mt-2 text-xs text-gray-400">
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
                            const rejected = [];

                            Array.from($event.target.files).forEach(file => {
                            
                                const extension = file.name
                                    .split('.')
                                    .pop()
                                    .toLowerCase();
                            
                                const validExtension = allowedExtensions.includes(extension);
                                const validType = allowedTypes.includes(file.type);
                            
                                if (!validExtension || !validType) {
                                    rejected.push(`${file.name} is not supported. Only PDF, JPG, JPEG, and PNG files are allowed.`);
                                    return;
                                }

                                if (file.size > maxSize) {
                                    rejected.push(`${file.name} exceeds the maximum file size of 10 MB.`);
                                    return;
                                }
                            
                                files.push(file);
                            });
                        
                            errors.documents = rejected.join(' ');
                            $event.target.value = '';
                        "
                    >
                </label>
                <p id="documents-error" x-show="errors.documents" x-text="errors.documents" x-cloak class="mt-1.5 text-xs text-red-600"></p>

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
                                class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-3 py-2.5 sm:px-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <!-- Icon -->
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-orange-100">
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
                                <div class="flex shrink-0 items-center gap-1">
                                    <button 
                                        type="button" 
                                        @click="window.dispatchEvent(new CustomEvent('open-file-preview', { detail: { url: window.URL.createObjectURL(file), name: file.name } }))" 
                                        class="inline-flex shrink-0 items-center gap-1 rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200 transition hover:bg-blue-100">
                                        View File
                                    </button>
                                    <!-- Remove -->
                                    <button
                                        type="button"
                                        @click="files.splice(index,1)"
                                        class="rounded-md p-1 text-red-500 transition hover:bg-red-50 hover:text-red-700">
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
                    class="mt-6 rounded-xl border border-dashed border-gray-200 bg-gray-50 p-6 text-center">
                    <p class="text-sm text-gray-500">
                        No documents selected yet.
                    </p>
                </div>
            </div>
            <!-- Buttons -->
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