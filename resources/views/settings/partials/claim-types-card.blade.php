<x-settings-section title="Claim types" description="Shared by all campuses. Each type sets the supporting document and amount members can claim.">
    <div class="grid gap-4 lg:grid-cols-5">
        <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-3.5 lg:col-span-2">
            <div class="mb-3">
                <h4 class="text-sm font-semibold text-slate-900">Add claim type</h4>
                <p class="mt-0.5 text-xs leading-5 text-slate-500">New types become available on the claim form when marked active.</p>
            </div>

            <form method="POST" action="{{ route('settings.claim-types.store') }}" class="setting-ajax-form space-y-3" data-can-edit="true" novalidate data-confirm-text="Add this claim type? It will be available to all campuses.">
                @csrf
                <input type="hidden" name="campus_filter" value="{{ $selectedCampusId ?? '' }}">

                <div>
                    <label for="claim-type-name" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Name</label>
                    <input id="claim-type-name" name="name" type="text" maxlength="100" data-title-case data-label="claim type name" value="{{ old('name') }}" required placeholder="e.g. Outpatient" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-blue-500 focus:ring-blue-500">
                    <p data-error-for="name" class="mt-1.5 text-xs text-red-600 hidden"></p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="claim-type-document" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Required document</label>
                        <input id="claim-type-document" name="required_document" type="text" maxlength="255" data-label="required document" value="{{ old('required_document') }}" required placeholder="e.g. Medical Certificate" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-blue-500 focus:ring-blue-500">
                        <p data-error-for="required_document" class="mt-1.5 text-xs text-red-600 hidden"></p>
                    </div>
                    <div>
                        <label for="claim-type-amount" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Required amount</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500">₱</span>
                            <input id="claim-type-amount" name="required_amount" type="text" inputmode="decimal" autocomplete="off" placeholder="0.00" data-amount-format data-positive data-max="99999999.99" data-label="required amount" value="{{ old('required_amount') }}" required class="block w-full rounded-lg border border-slate-300 bg-white pl-7 pr-3 py-2 text-sm text-slate-900 shadow-xs tabular-nums focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <p data-error-for="required_amount" class="mt-1.5 text-xs text-red-600 hidden"></p>
                    </div>
                </div>

                <div>
                    <label for="claim-type-description" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Description <span class="font-normal normal-case text-slate-400">(optional)</span></label>
                    <textarea id="claim-type-description" name="description" rows="2" maxlength="1000" data-label="description" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                    <p data-error-for="description" class="mt-1.5 text-xs text-red-600 hidden"></p>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-3 sm:flex-row sm:items-center sm:justify-between">
                    <label for="claim-type-active" class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="hidden" name="is_active" value="0">
                        <input id="claim-type-active" name="is_active" type="checkbox" value="1" @checked(old('is_active', true)) class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        Active
                    </label>
                    <button type="submit" class="rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Add claim type</button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto rounded-lg border border-slate-200 lg:col-span-3">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2.5">Name</th>
                        <th class="px-3 py-2.5">Required document</th>
                        <th class="px-3 py-2.5 text-right">Amount</th>
                        <th class="px-3 py-2.5">Status</th>
                        <th class="px-3 py-2.5"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($claimTypes as $claimType)
                        <tr>
                            <td class="px-3 py-2.5">
                                <p class="font-semibold text-slate-900">{{ $claimType->name }}</p>
                                @if($claimType->description)
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $claimType->description }}</p>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-slate-600">{{ $claimType->required_document }}</td>
                            <td class="px-3 py-2.5 text-right font-medium text-slate-900">@money($claimType->required_amount)</td>
                            <td class="px-3 py-2.5">
                                @if($claimType->is_active)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">Active</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-500">Inactive</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-right">
                                <button type="button" aria-expanded="false" aria-controls="claim-type-edit-{{ $claimType->id }}" onclick="const row = document.getElementById('claim-type-edit-{{ $claimType->id }}'); row.classList.toggle('hidden'); this.setAttribute('aria-expanded', String(!row.classList.contains('hidden')));" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500">Edit</button>
                            </td>
                        </tr>
                        <tr id="claim-type-edit-{{ $claimType->id }}" class="hidden bg-blue-50/20">
                            <td colspan="5" class="px-3 py-3">
                                <form method="POST" action="{{ route('settings.claim-types.update', $claimType) }}" class="setting-ajax-form space-y-3" data-can-edit="true" novalidate data-confirm-text="Save changes to this claim type? The amount and status apply to claims that have not been processed yet.">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="campus_filter" value="{{ $selectedCampusId ?? '' }}">

                                    <div class="grid gap-3 sm:grid-cols-3">
                                        <div>
                                            <label for="claim-type-name-{{ $claimType->id }}" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Name</label>
                                            <input id="claim-type-name-{{ $claimType->id }}" name="name" type="text" maxlength="100" data-title-case data-label="claim type name" value="{{ $claimType->name }}" required class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-blue-500 focus:ring-blue-500">
                                            <p data-error-for="name" class="mt-1.5 text-xs text-red-600 hidden"></p>
                                        </div>
                                        <div>
                                            <label for="claim-type-document-{{ $claimType->id }}" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Required document</label>
                                            <input id="claim-type-document-{{ $claimType->id }}" name="required_document" type="text" maxlength="255" data-label="required document" value="{{ $claimType->required_document }}" required class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-blue-500 focus:ring-blue-500">
                                            <p data-error-for="required_document" class="mt-1.5 text-xs text-red-600 hidden"></p>
                                        </div>
                                        <div>
                                            <label for="claim-type-amount-{{ $claimType->id }}" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Required amount</label>
                                            <div class="relative">
                                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500">₱</span>
                                                <input id="claim-type-amount-{{ $claimType->id }}" name="required_amount" type="text" inputmode="decimal" autocomplete="off" placeholder="0.00" data-amount-format data-positive data-max="99999999.99" data-label="required amount" value="{{ number_format((float) $claimType->required_amount, 2) }}" required class="block w-full rounded-lg border border-slate-300 bg-white pl-7 pr-3 py-2 text-sm text-slate-900 shadow-xs tabular-nums focus:border-blue-500 focus:ring-blue-500">
                                            </div>
                                            <p data-error-for="required_amount" class="mt-1.5 text-xs text-red-600 hidden"></p>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="claim-type-description-{{ $claimType->id }}" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Description <span class="font-normal normal-case text-slate-400">(optional)</span></label>
                                        <textarea id="claim-type-description-{{ $claimType->id }}" name="description" rows="2" maxlength="1000" data-label="description" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-blue-500 focus:ring-blue-500">{{ $claimType->description }}</textarea>
                                        <p data-error-for="description" class="mt-1.5 text-xs text-red-600 hidden"></p>
                                    </div>

                                    <div class="flex flex-col gap-3 border-t border-slate-200 pt-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <label for="claim-type-active-{{ $claimType->id }}" class="inline-flex items-center gap-2 text-sm text-slate-700">
                                                <input type="hidden" name="is_active" value="0">
                                                <input id="claim-type-active-{{ $claimType->id }}" name="is_active" type="checkbox" value="1" @checked($claimType->is_active) class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                                Active
                                            </label>
                                            <p class="mt-1 text-xs text-slate-400">Inactive types are hidden from the claim form, and their pending claims cannot be processed.</p>
                                        </div>
                                        <button type="submit" class="rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Save changes</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500">No claim types have been added yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-settings-section>

<script>
    (() => {
        const toTitleCase = (value) => value
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase()
            .replace(/(^|[\s\-\/(])(\p{L})/gu, (match, separator, letter) => separator + letter.toUpperCase());

        document.querySelectorAll('[data-title-case]').forEach((input) => {
            input.addEventListener('blur', () => {
                input.value = toTitleCase(input.value);
            });
        });
    })();
</script>
