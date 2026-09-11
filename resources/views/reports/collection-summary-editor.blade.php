<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 py-6">
        <div class="mb-6 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Collection Summary Entry</h1>
                <p class="text-sm text-gray-500">Treasurer entry page for campus collection and disbursement details.</p>
            </div>
            <a href="{{ route('reports.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700">Back to Reports</a>
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
                    <label class="block text-sm font-medium text-gray-700">Period Label</label>
                    <input type="text" name="period_label" value="NOVEMBER 2024 - SEPT 2025" class="mt-1 w-full rounded-lg border-gray-300" required>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h2 class="text-lg font-semibold mb-4">Collections</h2>
                <div id="collections-items" class="space-y-3">
                    <div class="grid md:grid-cols-3 gap-3 item-row">
                        <input type="text" name="items[0][section]" value="collections" hidden>
                        <input type="text" name="items[0][label]" placeholder="Item label" class="rounded-lg border-gray-300" required>
                        <input type="number" step="0.01" name="items[0][amount]" placeholder="0.00" class="rounded-lg border-gray-300" required>
                        <input type="text" name="items[0][notes]" placeholder="Notes" class="rounded-lg border-gray-300">
                    </div>
                </div>
                <button type="button" onclick="addRow('collections')" class="mt-4 rounded-lg border border-gray-300 px-3 py-2 text-sm">Add Collection Row</button>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h2 class="text-lg font-semibold mb-4">Disbursements</h2>
                <div id="disbursements-items" class="space-y-3">
                    <div class="grid md:grid-cols-3 gap-3 item-row">
                        <input type="text" name="items[1][section]" value="disbursements" hidden>
                        <input type="text" name="items[1][label]" placeholder="Item label" class="rounded-lg border-gray-300" required>
                        <input type="number" step="0.01" name="items[1][amount]" placeholder="0.00" class="rounded-lg border-gray-300" required>
                        <input type="text" name="items[1][notes]" placeholder="Notes" class="rounded-lg border-gray-300">
                    </div>
                </div>
                <button type="button" onclick="addRow('disbursements')" class="mt-4 rounded-lg border border-gray-300 px-3 py-2 text-sm">Add Disbursement Row</button>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-white font-medium hover:bg-blue-700">Save Summary</button>
            </div>
        </form>
    </div>

    <script>
        let collectionCounter = 2;
        let disbursementCounter = 2;

        function addRow(section) {
            const container = document.getElementById(section + '-items');
            const row = document.createElement('div');
            row.className = 'grid md:grid-cols-3 gap-3 item-row';

            const index = section === 'collections' ? collectionCounter++ : disbursementCounter++;
            row.innerHTML = `
                <input type="text" name="items[${index}][section]" value="${section}" hidden>
                <input type="text" name="items[${index}][label]" placeholder="Item label" class="rounded-lg border-gray-300" required>
                <input type="number" step="0.01" name="items[${index}][amount]" placeholder="0.00" class="rounded-lg border-gray-300" required>
                <input type="text" name="items[${index}][notes]" placeholder="Notes" class="rounded-lg border-gray-300">
            `;

            container.appendChild(row);
        }
    </script>
</x-app-layout>
