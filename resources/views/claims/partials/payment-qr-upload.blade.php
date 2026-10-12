{{-- QR upload that auto-fills the payout fields. Expects $method: 'gcash' | 'bank' --}}
<div class="mb-5 rounded-xl border border-dashed border-gray-300 bg-gray-50/60 p-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex min-w-0 items-center gap-3">
            <template x-if="qr.{{ $method }}.preview">
                <button
                    type="button"
                    title="View QR code"
                    @click="$dispatch('open-file-preview', { url: qr.{{ $method }}.preview, name: qr.{{ $method }}.fileName, image: true })"
                    class="shrink-0 rounded-lg transition hover:ring-2 hover:ring-orange-300">
                    <img
                        :src="qr.{{ $method }}.preview"
                        alt="Uploaded QR code"
                        class="h-14 w-14 rounded-lg border border-gray-200 bg-white object-contain">
                </button>
            </template>
            <template x-if="!qr.{{ $method }}.preview">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-orange-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-orange-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm4 0h2v2h-2v-2z"/>
                    </svg>
                </div>
            </template>
            <div class="min-w-0">
                <p class="text-sm font-medium text-gray-900">
                    {{ $method === 'gcash' ? 'Have a GCash QR code?' : 'Have a bank QR code (InstaPay / QR Ph)?' }}
                </p>
                <p
                    class="truncate text-xs text-gray-500"
                    x-text="qr.{{ $method }}.fileName || 'Upload it and we\'ll send your payout to it, or enter your details manually.'">
                </p>
            </div>
        </div>
        <div class="flex shrink-0 items-center gap-2">
            <label
                class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-xs transition hover:bg-gray-50"
                :class="{ 'pointer-events-none opacity-60': qr.{{ $method }}.loading }">
                <span x-text="qr.{{ $method }}.loading ? 'Reading QR…' : (qr.{{ $method }}.preview ? 'Replace QR' : 'Upload QR')"></span>
                <input
                    type="file"
                    accept="image/png,image/jpeg,image/webp"
                    class="hidden"
                    @change="handlePaymentQr($event, '{{ $method }}')">
            </label>
            <button
                type="button"
                x-show="qr.{{ $method }}.preview"
                x-cloak
                @click="removePaymentQr('{{ $method }}')"
                class="rounded-lg px-2 py-2 text-xs font-medium text-red-600 transition hover:bg-red-50">
                Remove
            </button>
        </div>
    </div>
    <p x-show="qr.{{ $method }}.message" x-text="qr.{{ $method }}.message" x-cloak class="mt-3 text-xs text-green-700"></p>
    <p id="{{ $method }}Qr-error" x-show="errors.{{ $method }}Qr" x-text="errors.{{ $method }}Qr" x-cloak class="mt-3 text-xs text-red-600"></p>
</div>
