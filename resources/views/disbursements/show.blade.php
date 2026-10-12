<x-app-layout>
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xs">
            <div class="flex flex-col gap-3 border-b border-gray-100 bg-gray-50/50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Disbursement Details</h2>
                    <p class="text-sm text-gray-500 mt-1">Claim reference: {{ $claimPayment->claim?->claim_number ?? 'N/A' }}</p>
                </div>
                <a href="{{ route('disbursement.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">← Back to list</a>
            </div>

            <div class="grid grid-cols-1 gap-4 p-4 sm:p-6 md:grid-cols-2 md:gap-6">
                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Member</p>
                    <p class="mt-2 text-lg font-semibold text-gray-900">{{ trim(sprintf('%s %s %s', $claimPayment->claim?->member?->first_name, $claimPayment->claim?->member?->middle_name, $claimPayment->claim?->member?->last_name)) ?: 'N/A' }}</p>
                    <p class="mt-1 text-sm text-gray-600">{{ $claimPayment->claim?->member?->employee_number ?? 'N/A' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Claim Type</p>
                    <p class="mt-2 text-lg font-semibold text-gray-900">{{ $claimPayment->claim?->claimType?->name ?? 'N/A' }}</p>
                    <p class="mt-1 text-sm text-gray-600">{{ $claimPayment->claim?->claim_type_id ? 'Claim record linked' : 'No linked claim type' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Amount</p>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-gray-900 tabular-nums">₱{{ number_format((float) $claimPayment->amount, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Payment Method</p>
                    <p class="mt-2 text-lg font-semibold text-gray-900">{{ $claimPayment->paymentMethod?->name ?? 'N/A' }}</p>
                    <p class="mt-1 text-sm text-gray-600">{{ $claimPayment->reference_number ?? 'No reference number' }}</p>
                </div>
                @php($claim = $claimPayment->claim)
                @if ($claim && ($claim->payout_account_number || $claim->payout_account_name || $claim->payout_qr_path))
                    <div class="rounded-xl border border-gray-200 p-5 md:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Member Payout Details</p>
                        <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-start">
                            <dl class="grid flex-1 grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                                @if ($claim->payout_bank_name)
                                    <div>
                                        <dt class="text-gray-500">Bank</dt>
                                        <dd class="font-semibold text-gray-900">{{ $claim->payout_bank_name }}</dd>
                                    </div>
                                @endif
                                <div>
                                    <dt class="text-gray-500">Account Number</dt>
                                    <dd class="font-semibold text-gray-900 tabular-nums">{{ $claim->payout_account_number ?: '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Account Name</dt>
                                    <dd class="font-semibold text-gray-900">{{ $claim->payout_account_name ?: '—' }}</dd>
                                </div>
                                @if ($claim->payout_branch)
                                    <div>
                                        <dt class="text-gray-500">Branch</dt>
                                        <dd class="font-semibold text-gray-900">{{ $claim->payout_branch }}</dd>
                                    </div>
                                @endif
                            </dl>
                            @if ($claim->payout_qr_path)
                                <button
                                    type="button"
                                    x-data
                                    @click="$dispatch('open-file-preview', { url: @js(asset('storage/' . $claim->payout_qr_path)), name: @js('Payout QR - ' . $claim->claim_number), image: true })"
                                    class="group shrink-0 text-center">
                                    <img src="{{ asset('storage/' . $claim->payout_qr_path) }}" alt="Payout QR code" class="h-32 w-32 rounded-lg border border-gray-200 bg-white object-contain transition group-hover:ring-2 group-hover:ring-blue-300">
                                    <span class="mt-1 block text-xs font-semibold text-blue-600">Scan to pay · View</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('components.file-preview-modal')
</x-app-layout>
