{{-- Payout account details / QR the member submitted. Used inside the claim details modal (Alpine activeClaim). --}}
<template x-if="activeClaim.payout_account_number || activeClaim.payout_account_name || activeClaim.payout_qr_path">
    <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
        <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Payout Details</span>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
            <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-2">
                <div x-show="activeClaim.payout_bank_name">
                    <span class="block text-xs font-medium text-slate-400 uppercase">Bank</span>
                    <span class="font-semibold text-slate-800" x-text="activeClaim.payout_bank_name"></span>
                </div>
                <div>
                    <span class="block text-xs font-medium text-slate-400 uppercase">Account Number</span>
                    <span class="font-semibold text-slate-800 tabular-nums" x-text="activeClaim.payout_account_number || '—'"></span>
                </div>
                <div>
                    <span class="block text-xs font-medium text-slate-400 uppercase">Account Name</span>
                    <span class="font-semibold text-slate-800" x-text="activeClaim.payout_account_name || '—'"></span>
                </div>
                <div x-show="activeClaim.payout_branch">
                    <span class="block text-xs font-medium text-slate-400 uppercase">Branch</span>
                    <span class="font-semibold text-slate-800" x-text="activeClaim.payout_branch"></span>
                </div>
            </div>
            <template x-if="activeClaim.payout_qr_path">
                <button
                    type="button"
                    @click="$dispatch('open-file-preview', { url: '/storage/' + activeClaim.payout_qr_path, name: 'Payout QR - ' + activeClaim.claim_number, image: true })"
                    class="group shrink-0 text-center">
                    <img
                        :src="'/storage/' + activeClaim.payout_qr_path"
                        alt="Payout QR code"
                        class="h-28 w-28 rounded-lg border border-slate-200 bg-white object-contain transition group-hover:ring-2 group-hover:ring-blue-300">
                    <span class="mt-1 block text-[11px] font-semibold text-blue-700">Scan to pay · View</span>
                </button>
            </template>
        </div>
    </div>
</template>
