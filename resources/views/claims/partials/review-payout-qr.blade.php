{{-- Attached payout QR on the review step. Expects $method: 'gcash' | 'bank' --}}
<div x-show="qr.{{ $method }}.file" x-cloak class="mt-4 flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
    <button
        type="button"
        title="View QR code"
        @click="$dispatch('open-file-preview', { url: qr.{{ $method }}.preview, name: qr.{{ $method }}.fileName, image: true })"
        class="shrink-0 rounded-md transition hover:ring-2 hover:ring-orange-300">
        <img
            :src="qr.{{ $method }}.preview"
            alt="Attached payout QR code"
            class="h-16 w-16 rounded-md border border-gray-200 bg-white object-contain">
    </button>
    <div class="min-w-0 flex-1">
        <p class="text-sm font-semibold text-gray-900">
            {{ $method === 'gcash' ? 'GCash QR code attached' : 'Bank QR code attached' }}
        </p>
        <p class="truncate text-xs text-gray-500" x-text="qr.{{ $method }}.fileName"></p>
    </div>
    <button
        type="button"
        @click="$dispatch('open-file-preview', { url: qr.{{ $method }}.preview, name: qr.{{ $method }}.fileName, image: true })"
        class="inline-flex shrink-0 items-center gap-1 rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200 transition hover:bg-blue-100">
        View QR
    </button>
</div>
