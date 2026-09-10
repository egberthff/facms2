<div
    x-data="{ filePreviewOpen: false, filePreviewUrl: '', filePreviewName: '' }"
    x-show="filePreviewOpen"
    x-cloak
    class="fixed inset-0 z-60 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    @open-file-preview.window="filePreviewUrl = $event.detail.url; filePreviewName = $event.detail.name || 'File preview'; filePreviewOpen = true"
    @keydown.escape.window="filePreviewOpen = false">
    <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" @click="filePreviewOpen = false"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative flex h-[min(90vh,900px)] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                <h2 class="truncate pr-4 text-sm font-semibold text-slate-800" x-text="filePreviewName"></h2>
                <button type="button" @click="filePreviewOpen = false" aria-label="Close file preview" class="shrink-0 rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="min-h-0 flex-1 bg-slate-100 p-2">
                <iframe x-bind:src="filePreviewUrl" title="File preview" class="h-full w-full rounded-lg border border-slate-200 bg-white"></iframe>
            </div>
        </div>
    </div>
</div>