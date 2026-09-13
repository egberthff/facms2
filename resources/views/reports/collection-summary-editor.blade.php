<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 py-6">
        <div class="mb-6 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Fund Transfer Entry</h1>
                <p class="text-sm text-gray-500">Record a campus remittance or an FFA disbursement. Approved transfers appear automatically in the collection summary.</p>
            </div>
            {{-- <a href="{{ route('reports.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700">Back to Reports</a> --}}
            <a href="{{ route('treasury.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700">Back to Treasury</a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('reports.collection-summary.store') }}" class="space-y-6">
            @csrf

            <div class="grid md:grid-cols-2 gap-4 bg-white rounded-xl border border-gray-200 p-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Campus</label>
                    <select name="campus_id" class="mt-1 w-full rounded-lg border-gray-300">
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}">{{ $campus->campus_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Transfer type</label>
                    <select name="type" class="mt-1 w-full rounded-lg border-gray-300" required>
                        <option value="campus_remittance">Campus remittance to FFA</option>
                        <option value="ffa_disbursement">FFA disbursement to campus</option>
                    </select>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4 bg-white rounded-xl border border-gray-200 p-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Reference number</label>
                    <input type="text" name="reference_number" class="mt-1 w-full rounded-lg border-gray-300" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Amount</label>
                    <input type="number" name="amount" min="0.01" step="0.01" class="mt-1 w-full rounded-lg border-gray-300" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <input type="text" name="description" placeholder="Annual dues remittance" class="mt-1 w-full rounded-lg border-gray-300" required>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-white font-medium hover:bg-blue-700">Submit Transfer</button>
            </div>
        </form>

        @if(Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']) || Auth::user()->can('view all campus data'))
            <div class="mt-6 bg-white rounded-xl border border-gray-200 p-4">
                <h2 class="text-lg font-semibold mb-4">Pending transfers</h2>
                <div class="space-y-3">
                    @forelse($pendingTransfers as $transfer)
                        <div class="flex flex-col gap-3 border-b border-gray-100 pb-3 md:flex-row md:items-center md:justify-between">
                            <div class="text-sm text-gray-700">
                                <strong>{{ $transfer->reference_number }}</strong>
                                <span class="ml-2">{{ ucfirst(str_replace('_', ' ', $transfer->type)) }}</span>
                                <span class="ml-2">P {{ number_format($transfer->amount, 2) }}</span>
                            </div>
                            <form method="POST" action="{{ route('reports.fund-transfers.approve', $transfer) }}">
                                @csrf
                                <button type="submit" class="rounded-lg bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700">Approve and post</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No pending transfers.</p>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
