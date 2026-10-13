@php
    $money = fn ($value) => number_format((float) $value, 2);
@endphp
<x-app-layout>
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-500">{{ $associationName }}</p>
                <h1 class="text-xl font-semibold tracking-tight text-gray-900 sm:text-2xl">Campus Cash Flow <span class="text-gray-500 font-medium">(As of {{ $asOf }})</span></h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $period->period_from->format('F d, Y') }} – {{ $period->period_to->format('F d, Y') }}
                    @if($period->isFinalized())
                        <span class="ml-2 rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Finalized {{ $period->finalized_at?->format('M d, Y') }}</span>
                    @else
                        <span class="ml-2 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Draft</span>
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reports.cash-flow.index', ['campus_filter' => $period->campus_id]) }}" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-xs">All periods</a>
                <button type="button"
                    @click="window.dispatchEvent(new CustomEvent('open-file-preview', { detail: { url: '{{ route('reports.cash-flow.print', $period) }}', name: 'Campus Cash Flow – {{ $asOf }}' } }))"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-xs transition hover:bg-gray-50">Print preview</button>
                @if($canManage)
                    @php
                        $periodRange = $period->period_from->format('F d, Y').' – '.$period->period_to->format('F d, Y');
                    @endphp
                    <form method="POST" action="{{ route('reports.cash-flow.refresh', $period) }}"
                        x-data="cashFlowForm({ confirm: (v, h) => ({
                            icon: 'question',
                            title: 'Refresh from system?',
                            html: 'This re-pulls the approved FFA transfers and released medical assistance for <b>' + h.esc(@js($periodRange)) + '</b>, and updates the forwarded balance from the previous finalized period.<br><br>Your manual lines and any label or note edits are kept.',
                            confirmText: 'Yes, refresh',
                            loadingText: 'Pulling the latest funds and releases...',
                        }) })"
                        @submit.prevent="submit">
                        @csrf
                        <button type="submit" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100">Refresh from system</button>
                    </form>
                    <form method="POST" action="{{ route('reports.cash-flow.finalize', $period) }}"
                        x-data="cashFlowForm({ confirm: (v, h) => ({
                            icon: 'warning',
                            title: 'Finalize this period?',
                            html: 'The period <b>' + h.esc(@js($periodRange)) + '</b> will be locked and can no longer be edited.<br><br>Manual lines will be posted to the campus treasury ledger, and the cash on hand of <b>P' + h.money(@js($period->cash_on_hand)) + '</b> becomes the next period\'s forwarded balance.',
                            confirmText: 'Yes, finalize',
                            color: '#16a34a',
                            loadingText: 'Finalizing the period and posting to the ledger...',
                        }) })"
                        @submit.prevent="submit">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-green-600 px-3 py-2 text-sm font-medium text-white shadow-xs transition hover:bg-green-700">Finalize</button>
                    </form>
                    <form method="POST" action="{{ route('reports.cash-flow.destroy', $period) }}"
                        x-data="cashFlowForm({ confirm: (v, h) => ({
                            icon: 'warning',
                            title: 'Delete this draft?',
                            html: 'The draft for <b>' + h.esc(@js($periodRange)) + '</b> and all of its lines will be permanently deleted.<br><br>This cannot be undone.',
                            confirmText: 'Yes, delete draft',
                            color: '#dc2626',
                            loadingText: 'Deleting the draft...',
                        }) })"
                        @submit.prevent="submit">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg border border-rose-200 bg-white px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50">Delete draft</button>
                    </form>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Totals --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                'Forwarded balance' => $period->forwarded_balance,
                'Total (with forwarded)' => $period->total_funds,
                'Disbursements & expenses' => $period->total_disbursements,
                'Cash on hand' => $period->cash_on_hand,
            ] as $label => $value)
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</p>
                    <p class="mt-1 text-xl font-bold {{ $loop->last ? 'text-blue-700' : 'text-gray-900' }}">P{{ $money($value) }}</p>
                </div>
            @endforeach
        </div>

        {{-- Forwarded balance --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Forwarded Balance</h2>
                    <p class="text-sm text-gray-500">
                        {{ $period->forwarded_label }} · P{{ $money($period->forwarded_balance) }}
                        @unless($openingEditable)
                            <span class="text-gray-400">(cash on hand of the previous finalized period)</span>
                        @endunless
                    </p>
                </div>
                @if($canManage && $openingEditable)
                    <form method="POST" action="{{ route('reports.cash-flow.opening-balance', $period) }}" class="flex flex-wrap items-start gap-2" novalidate
                        x-data="cashFlowForm({
                            validate: cashFlowRules.openingBalance,
                            confirm: (v, h) => ({
                                icon: 'question',
                                title: 'Update forwarded balance?',
                                html: 'The forwarded balance will be set to <b>P' + h.money(v.opening_balance) + '</b>'
                                    + (v.opening_label.trim() ? ' as of <b>' + h.esc(v.opening_label.trim()) + '</b>' : '')
                                    + '.<br><br>Totals and cash on hand will be recalculated.',
                                confirmText: 'Yes, update',
                            }),
                        })"
                        @submit.prevent="submit" @input="clearError($event.target.name)" @change="clearError($event.target.name)">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Balance as of</label>
                            <input type="text" name="opening_label" value="{{ $period->forwarded_label }}" :class="{ 'error-border': errors.opening_label }" class="mt-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-xs">
                            <p x-show="errors.opening_label" x-text="errors.opening_label" x-cloak class="mt-1.5 max-w-48 text-xs text-red-600"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Amount</label>
                            <input type="number" name="opening_balance" step="0.01" value="{{ $period->forwarded_balance }}" :class="{ 'error-border': errors.opening_balance }" class="mt-1 w-36 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-xs">
                            <p x-show="errors.opening_balance" x-text="errors.opening_balance" x-cloak class="mt-1.5 max-w-48 text-xs text-red-600"></p>
                        </div>
                        <button type="submit" class="mt-5 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-xs transition hover:bg-blue-700">Save</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Add funds --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xs">
            <div class="border-b border-gray-200 px-4 py-3">
                <h2 class="text-lg font-semibold text-gray-900">Add Funds <span class="text-sm font-normal italic text-gray-500">(Received from FFA)</span></h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @forelse($funds as $item)
                            @include('reports.cash-flow.partials.item-row', ['item' => $item, 'canManage' => $canManage])
                        @empty
                            <tr><td class="px-4 py-4 text-gray-500">No approved FFA transfers to this campus in the period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Less --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xs">
            <div class="border-b border-gray-200 px-4 py-3">
                <h2 class="text-lg font-semibold text-gray-900">Less <span class="text-sm font-normal italic text-gray-500">(Disbursements and Expenses)</span></h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @foreach($expenses as $item)
                            @include('reports.cash-flow.partials.item-row', ['item' => $item, 'canManage' => $canManage])
                        @endforeach

                        @if($medicalByMonth->isNotEmpty())
                            <tr class="bg-gray-50">
                                <td colspan="5" class="px-4 py-2.5 font-semibold text-gray-800">Medical Assistance Reimbursement (P{{ $money($medicalTotal) }})</td>
                            </tr>
                            @foreach($medicalByMonth as $month => $items)
                                <tr>
                                    <td colspan="5" class="px-4 py-2 pl-8 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $month }}</td>
                                </tr>
                                @foreach($items as $item)
                                    @include('reports.cash-flow.partials.item-row', ['item' => $item, 'canManage' => $canManage, 'indent' => 'pl-12'])
                                @endforeach
                            @endforeach
                        @endif

                        @if($expenses->isEmpty() && $medicalByMonth->isEmpty())
                            <tr><td class="px-4 py-4 text-gray-500">No disbursements yet. Add campus expenses below; released medical assistance appears automatically.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Add a manual line --}}
        @if($canManage)
            <form method="POST" action="{{ route('reports.cash-flow.items.store', $period) }}" class="space-y-4 rounded-xl border border-gray-200 bg-white p-4 shadow-xs" novalidate
                x-data="cashFlowForm({
                    validate: cashFlowRules.item,
                    confirm: (v, h) => ({
                        icon: 'question',
                        title: 'Add this line?',
                        html: '<b>' + h.esc(v.label.trim()) + '</b>' + (v.notes.trim() ? ' (' + h.esc(v.notes.trim()) + ')' : '')
                            + ' – <b>P' + h.money(v.amount) + '</b> will be added under <b>' + (v.section === 'funds' ? 'Add Funds' : 'Less (expense)') + '</b>'
                            + (v.entry_date ? ', dated ' + h.date(v.entry_date) : '') + '.'
                            + '<br><br>It will be posted to the campus treasury ledger when the period is finalized.',
                        confirmText: 'Yes, add line',
                    }),
                })"
                @submit.prevent="submit" @input="clearError($event.target.name)" @change="clearError($event.target.name)">
                @csrf
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Add a line</h2>
                    <p class="text-sm text-gray-500">Record campus expenses paid from FA funds (e.g. tarpaulin printing, snacks, released CNA share). Manual lines are posted to the campus treasury ledger when the period is finalized.</p>
                </div>
                <div class="grid gap-4 md:grid-cols-12">
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Section</label>
                        <select name="section" :class="{ 'error-border': errors.section }" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-xs">
                            <option value="disbursements" @selected(old('section', 'disbursements') === 'disbursements')>Less (expense)</option>
                            <option value="funds" @selected(old('section') === 'funds')>Add Funds</option>
                        </select>
                        <p x-show="errors.section" x-text="errors.section" x-cloak class="mt-1.5 text-xs text-red-600"></p>
                    </div>
                    <div class="md:col-span-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Description <span class="text-red-500">*</span></label>
                        <input type="text" name="label" value="{{ old('label') }}" placeholder="Tarpaulin Printing (CNA Day December 2025)" :class="{ 'error-border': errors.label }" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-xs">
                        <p x-show="errors.label" x-text="errors.label" x-cloak class="mt-1.5 text-xs text-red-600"></p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="entry_date" value="{{ old('entry_date') }}" :class="{ 'error-border': errors.entry_date }" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-xs">
                        <p x-show="errors.entry_date" x-text="errors.entry_date" x-cloak class="mt-1.5 text-xs text-red-600"></p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Amount <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount') }}" :class="{ 'error-border': errors.amount }" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-xs">
                        <p x-show="errors.amount" x-text="errors.amount" x-cloak class="mt-1.5 text-xs text-red-600"></p>
                    </div>
                    <div class="md:col-span-2 md:pt-7">
                        <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-xs transition hover:bg-blue-700">Add</button>
                    </div>
                    <div class="md:col-span-12">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Note <span class="font-normal text-gray-400">(optional, printed in parentheses)</span></label>
                        <input type="text" name="notes" value="{{ old('notes') }}" :class="{ 'error-border': errors.notes }" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-xs">
                        <p x-show="errors.notes" x-text="errors.notes" x-cloak class="mt-1.5 text-xs text-red-600"></p>
                    </div>
                </div>
            </form>
        @endif

        {{-- Signatories --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-600 shadow-xs">
            <span class="font-semibold text-gray-800">Prepared by:</span> {{ $preparedBy ?: '—' }}
            <span class="ml-6 font-semibold text-gray-800">Checked by:</span> {{ $checkedBy ?: '—' }}
            @unless($period->isFinalized())
                <span class="ml-2 text-gray-400">Assign these on the Settings page (Report Signatories).</span>
            @endunless
        </div>
    </div>

    <x-file-preview-modal />

    <script>
        /**
         * Client-side rules mirroring CashFlowController. Each returns { field: message }.
         */
        window.cashFlowRules = (() => {
            const amountError = (value, { positive = false } = {}) => {
                const raw = String(value ?? '').trim();

                if (raw === '') return 'Please enter the amount.';
                if (!/^-?\d+(\.\d{1,2})?$/.test(raw)) return 'Enter a valid amount with up to 2 decimal places.';
                if (positive && Number(raw) <= 0) return 'Amount must be greater than zero.';

                return '';
            };

            const maxLength = (value, max, label) =>
                String(value ?? '').trim().length > max ? `${label} may not be longer than ${max} characters.` : '';

            return {
                openingBalance(values) {
                    return {
                        opening_balance: amountError(values.opening_balance),
                        opening_label: maxLength(values.opening_label, 100, 'The "balance as of" label'),
                    };
                },

                // System lines only send label and notes; amount and date are checked when present
                item(values) {
                    const errors = {};

                    if ('section' in values && !['funds', 'disbursements'].includes(values.section)) {
                        errors.section = 'Please select a section.';
                    }

                    if (!String(values.label ?? '').trim()) {
                        errors.label = 'Please enter a description.';
                    } else {
                        errors.label = maxLength(values.label, 255, 'Description');
                    }

                    if ('amount' in values) {
                        errors.amount = amountError(values.amount, { positive: true });
                    }

                    if (values.entry_date && Number.isNaN(Date.parse(values.entry_date))) {
                        errors.entry_date = 'Please enter a valid date.';
                    }

                    errors.notes = maxLength(values.notes, 255, 'Note');

                    return errors;
                },
            };
        })();

        /**
         * Confirms with SweetAlert, shows field errors inline (client and server 422),
         * then submits over fetch and reloads on success.
         */
        window.cashFlowForm = function ({ confirm, validate = () => ({}) } = {}) {
            const helpers = {
                esc: (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]),
                money: (value) => Number(value ?? 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                date: (value) => new Date(`${value}T00:00:00`).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }),
            };

            return {
                errors: {},

                clearError(name) {
                    if (name && this.errors[name]) {
                        this.errors[name] = '';
                    }
                },

                /**
                 * Replace the form's errors and focus the first invalid field.
                 * Returns true when there are no errors.
                 */
                setErrors(errors) {
                    this.errors = Object.fromEntries(Object.entries(errors).filter(([, message]) => message));

                    const firstInvalid = Object.keys(this.errors)[0];

                    if (firstInvalid) {
                        this.$nextTick(() => this.$root.querySelector(`[name="${firstInvalid}"]`)?.focus());
                        return false;
                    }

                    return true;
                },

                /**
                 * Show 422 errors under their fields. Returns the messages that have
                 * no field on this form (e.g. "period is finalized").
                 */
                showServerErrors(serverErrors) {
                    const fieldErrors = {};
                    const unmatched = [];

                    Object.entries(serverErrors).forEach(([key, messages]) => {
                        const message = [].concat(messages)[0];

                        if (this.$root.querySelector(`[name="${key}"]:not([type="hidden"])`)) {
                            fieldErrors[key] = message;
                        } else {
                            unmatched.push(message);
                        }
                    });

                    this.setErrors(fieldErrors);

                    return unmatched;
                },

                async submit(event) {
                    const form = event.target;
                    const formData = new FormData(form);
                    const values = Object.fromEntries(formData.entries());

                    if (!this.setErrors(validate(values))) {
                        return;
                    }

                    const options = typeof confirm === 'function' ? confirm(values, helpers) : confirm;

                    const result = await Swal.fire({
                        icon: options.icon || 'question',
                        title: options.title,
                        html: options.html,
                        showCancelButton: true,
                        confirmButtonText: options.confirmText || 'Yes, continue',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: options.color || '#2563eb',
                        cancelButtonColor: '#64748b',
                        focusCancel: options.icon === 'warning',
                    });

                    if (!result.isConfirmed) return;

                    Swal.fire({
                        title: 'Please wait',
                        text: options.loadingText || 'Saving your changes...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => Swal.showLoading(),
                    });

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST', // PUT and DELETE are spoofed through the form's _method field
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: formData,
                        });

                        const payload = await response.json().catch(() => ({}));

                        if (response.status === 422 && payload.errors) {
                            const unmatched = this.showServerErrors(payload.errors);

                            if (unmatched.length) {
                                Swal.fire({ icon: 'error', title: 'Action not allowed', text: unmatched.join(' '), confirmButtonColor: '#2563eb' });
                            } else {
                                Swal.close();
                            }

                            return;
                        }

                        if (!response.ok) {
                            throw new Error(response.status === 403
                                ? 'You are not allowed to change this cash flow period.'
                                : (payload.message || 'Something went wrong. Please try again.'));
                        }

                        await Swal.fire({ icon: 'success', title: 'Success!', text: payload.message, confirmButtonColor: '#2563eb' });

                        if (payload.redirect) {
                            window.location.href = payload.redirect;
                        } else {
                            window.location.reload();
                        }
                    } catch (error) {
                        Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'Something went wrong. Please try again.', confirmButtonColor: '#2563eb' });
                    }
                },
            };
        };
    </script>
</x-app-layout>
