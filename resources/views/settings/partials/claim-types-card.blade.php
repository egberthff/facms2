<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-6 py-5">
        <h3 class="text-lg font-bold text-slate-950">Claim types</h3>
        <p class="mt-1 text-sm text-slate-500">Claim types are shared by all campuses. Each type sets the supporting document and amount members can claim.</p>
    </div>

    <div class="grid gap-6 p-6 lg:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-5 transition hover:border-blue-200 hover:bg-blue-50/20 lg:col-span-2">
            <div class="mb-4">
                <h4 class="text-sm font-bold text-slate-900">Add claim type</h4>
                <p class="mt-1 text-xs leading-5 text-slate-500">New types become available on the claim form when marked active.</p>
            </div>

            <form method="POST" action="{{ route('settings.claim-types.store') }}" class="setting-ajax-form space-y-4" data-can-edit="true" data-confirm-text="Add this claim type? It will be available to all campuses.">
                @csrf
                <input type="hidden" name="campus_filter" value="{{ $selectedCampusId ?? '' }}">

                <div>
                    <label for="claim-type-name" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Name</label>
                    <input id="claim-type-name" name="name" type="text" maxlength="100" data-title-case value="{{ old('name') }}" required placeholder="e.g. Outpatient" class="block w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="claim-type-document" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Required document</label>
                        <input id="claim-type-document" name="required_document" type="text" maxlength="255" value="{{ old('required_document') }}" required placeholder="e.g. Medical Certificate" class="block w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="claim-type-amount" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Required amount</label>
                        <input id="claim-type-amount" name="required_amount" type="text" inputmode="decimal" autocomplete="off" placeholder="0.00" data-amount-format value="{{ old('required_amount') }}" required class="block w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="claim-type-description" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Description <span class="font-normal normal-case text-slate-400">(optional)</span></label>
                    <textarea id="claim-type-description" name="description" rows="3" maxlength="1000" class="block w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <label for="claim-type-active" class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="hidden" name="is_active" value="0">
                        <input id="claim-type-active" name="is_active" type="checkbox" value="1" @checked(old('is_active', true)) class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        Active
                    </label>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Add claim type</button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 lg:col-span-3">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Required document</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($claimTypes as $claimType)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-900">{{ $claimType->name }}</p>
                                @if($claimType->description)
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $claimType->description }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $claimType->required_document }}</td>
                            <td class="px-4 py-3 text-right font-medium text-slate-900">@money($claimType->required_amount)</td>
                            <td class="px-4 py-3">
                                @if($claimType->is_active)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">Active</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-500">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" aria-expanded="false" aria-controls="claim-type-edit-{{ $claimType->id }}" onclick="const row = document.getElementById('claim-type-edit-{{ $claimType->id }}'); row.classList.toggle('hidden'); this.setAttribute('aria-expanded', String(!row.classList.contains('hidden')));" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500">Edit</button>
                            </td>
                        </tr>
                        <tr id="claim-type-edit-{{ $claimType->id }}" class="hidden bg-blue-50/20">
                            <td colspan="5" class="px-4 py-4">
                                <form method="POST" action="{{ route('settings.claim-types.update', $claimType) }}" class="setting-ajax-form space-y-4" data-can-edit="true" data-confirm-text="Save changes to this claim type? The amount and status apply to claims that have not been processed yet.">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="campus_filter" value="{{ $selectedCampusId ?? '' }}">

                                    <div class="grid gap-4 sm:grid-cols-3">
                                        <div>
                                            <label for="claim-type-name-{{ $claimType->id }}" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Name</label>
                                            <input id="claim-type-name-{{ $claimType->id }}" name="name" type="text" maxlength="100" data-title-case value="{{ $claimType->name }}" required class="block w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label for="claim-type-document-{{ $claimType->id }}" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Required document</label>
                                            <input id="claim-type-document-{{ $claimType->id }}" name="required_document" type="text" maxlength="255" value="{{ $claimType->required_document }}" required class="block w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label for="claim-type-amount-{{ $claimType->id }}" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Required amount</label>
                                            <input id="claim-type-amount-{{ $claimType->id }}" name="required_amount" type="text" inputmode="decimal" autocomplete="off" placeholder="0.00" data-amount-format value="{{ number_format((float) $claimType->required_amount, 2) }}" required class="block w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                    </div>

                                    <div>
                                        <label for="claim-type-description-{{ $claimType->id }}" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Description <span class="font-normal normal-case text-slate-400">(optional)</span></label>
                                        <textarea id="claim-type-description-{{ $claimType->id }}" name="description" rows="2" maxlength="1000" class="block w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ $claimType->description }}</textarea>
                                    </div>

                                    <div class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <label for="claim-type-active-{{ $claimType->id }}" class="inline-flex items-center gap-2 text-sm text-slate-700">
                                                <input type="hidden" name="is_active" value="0">
                                                <input id="claim-type-active-{{ $claimType->id }}" name="is_active" type="checkbox" value="1" @checked($claimType->is_active) class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                                Active
                                            </label>
                                            <p class="mt-1 text-xs text-slate-400">Inactive types are hidden from the claim form, and their pending claims cannot be processed.</p>
                                        </div>
                                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Save changes</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">No claim types have been added yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
    (() => {
        // Keeps only digits and one decimal point (max 2 decimals), then groups the whole part with commas.
        const formatAmount = (value) => {
            const cleaned = value.replace(/[^\d.]/g, '');
            const [whole, ...decimals] = cleaned.split('.');
            const grouped = whole.replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',');

            return cleaned.includes('.') ? `${grouped || '0'}.${decimals.join('').slice(0, 2)}` : grouped;
        };

        const toTitleCase = (value) => value
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase()
            .replace(/(^|[\s\-\/(])(\p{L})/gu, (match, separator, letter) => separator + letter.toUpperCase());

        document.querySelectorAll('[data-amount-format]').forEach((input) => {
            input.value = formatAmount(input.value);

            input.addEventListener('input', () => {
                // Restore the caret after re-formatting by counting the digits before it.
                const caret = input.selectionStart ?? input.value.length;
                const digitsBeforeCaret = input.value.slice(0, caret).replace(/[^\d.]/g, '').length;

                input.value = formatAmount(input.value);

                let position = 0;
                for (let seen = 0; position < input.value.length && seen < digitsBeforeCaret; position++) {
                    if (/[\d.]/.test(input.value[position])) seen++;
                }
                input.setSelectionRange(position, position);
            });

            input.addEventListener('blur', () => {
                if (input.value === '') return;
                const amount = Number(input.value.replace(/,/g, ''));
                if (Number.isFinite(amount)) {
                    input.value = amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            });
        });

        document.querySelectorAll('[data-title-case]').forEach((input) => {
            input.addEventListener('blur', () => {
                input.value = toTitleCase(input.value);
            });
        });
    })();
</script>
