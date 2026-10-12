<div
    x-data="{
        filePreviewOpen: false,
        filePreviewUrl: '',
        filePreviewName: '',
        filePreviewIsImage: false,
        zoom: 1,

        // Callers may pass { image: true } when neither the name nor the URL has an extension (e.g. blob URLs)
        get isImage() {
            return this.filePreviewIsImage
                || [this.filePreviewName, this.filePreviewUrl].some(source =>
                    /\.(jpg|jpeg|png|gif|webp|bmp|svg)$/.test((source || '').split('?')[0].toLowerCase()));
        },

        openPreview(event) {
            this.filePreviewUrl = event.detail.url;
            this.filePreviewName = event.detail.name || 'File preview';
            this.filePreviewIsImage = !!event.detail.image;
            this.zoom = 1;
            this.filePreviewOpen = true;
        },

        closePreview() {
            this.filePreviewOpen = false;
            this.filePreviewUrl = '';
            this.filePreviewName = '';
            this.filePreviewIsImage = false;
            this.zoom = 1;
        },

        zoomIn() {
            this.zoom = Math.min(this.zoom + 0.25, 4);
        },

        zoomOut() {
            this.zoom = Math.max(this.zoom - 0.25, 0.25);
        },

        resetZoom() {
            this.zoom = 1;
        }
    }"
    x-show="filePreviewOpen"
    x-cloak
    class="fixed inset-0 z-60 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    @open-file-preview.window="openPreview($event)"
    @keydown.escape.window="closePreview()"
>
    <!-- Backdrop -->
    <div
        class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm"
        @click="closePreview()">
    </div>

    <!-- Modal container -->
    <div class="flex min-h-full items-center justify-center p-4">

        <div
            class="relative flex h-[min(90vh,900px)] w-full max-w-5xl
                   flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
            @click.stop
        >

            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">

                <h2
                    class="truncate pr-4 text-sm font-semibold text-slate-800"
                    x-text="filePreviewName">
                </h2>

                <div class="flex items-center gap-2">

                    <!-- Image controls -->
                    <template x-if="isImage">
                        <div class="flex items-center gap-1">

                            <!-- Zoom out -->
                            <button
                                type="button"
                                @click="zoomOut()"
                                :disabled="zoom <= 0.25"
                                title="Zoom out"
                                class="rounded-lg p-2 text-slate-500 transition
                                       hover:bg-slate-100 hover:text-slate-700
                                       disabled:cursor-not-allowed disabled:opacity-30"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M20 20l-4.35-4.35M9 11h6m2 0a6 6 0 11-12 0 6 6 0 0112 0z"
                                    />
                                </svg>
                            </button>

                            <!-- Zoom percentage -->
                            <button
                                type="button"
                                @click="resetZoom()"
                                title="Reset zoom"
                                class="min-w-[60px] rounded-lg px-2 py-1.5
                                       text-xs font-medium text-slate-600
                                       transition hover:bg-slate-100"
                                x-text="Math.round(zoom * 100) + '%'"
                            >
                            </button>

                            <!-- Zoom in -->
                            <button
                                type="button"
                                @click="zoomIn()"
                                :disabled="zoom >= 4"
                                title="Zoom in"
                                class="rounded-lg p-2 text-slate-500 transition
                                       hover:bg-slate-100 hover:text-slate-700
                                       disabled:cursor-not-allowed disabled:opacity-30"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M20 20l-4.35-4.35M9 11h6m-3-3v6m5-3a6 6 0 11-12 0 6 6 0 0112 0z"
                                    />
                                </svg>
                            </button>

                        </div>
                    </template>

                    <!-- Close -->
                    <button
                        type="button"
                        @click="closePreview()"
                        aria-label="Close file preview"
                        class="rounded-lg p-2 text-slate-400 transition
                               hover:bg-slate-100 hover:text-slate-700"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>

                </div>
            </div>

            <!-- Preview -->
            <div class="min-h-0 flex-1 bg-slate-100">

                <!-- IMAGE PREVIEW -->
                <template x-if="isImage">
                    <div
                        class="flex h-full w-full items-center justify-center
                               overflow-auto p-4"
                    >
                        <img
                            :src="filePreviewUrl"
                            :alt="filePreviewName"
                            :style="`
                                transform: scale(${zoom});
                                transform-origin: center center;
                            `"
                            class="max-h-full max-w-full object-contain
                                   transition-transform duration-200"
                        >
                    </div>
                </template>

                <!-- DOCUMENT PREVIEW -->
                <template x-if="!isImage">
                    <div class="h-full w-full p-2">
                        <iframe
                            :src="filePreviewUrl"
                            title="File preview"
                            class="h-full w-full rounded-lg border
                                   border-slate-200 bg-white"
                        >
                        </iframe>
                    </div>
                </template>

            </div>

        </div>
    </div>
</div>