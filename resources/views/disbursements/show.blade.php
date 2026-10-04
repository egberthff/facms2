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
            </div>
        </div>
    </div>
</x-app-layout>
