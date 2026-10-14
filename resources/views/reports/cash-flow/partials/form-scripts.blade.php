<script>
    /**
     * Claim-style amount input: keeps digits (and up to 2 decimals) and adds
     * thousands separators while typing. Paste fires "input" too.
     */
    window.formatAmountInput = function (el, { negative = false } = {}) {
        let value = el.value.replace(negative ? /[^0-9.\-]/g : /[^0-9.]/g, '');

        const isNegative = negative && value.startsWith('-');
        value = value.replace(/-/g, '');

        const hasDecimal = value.includes('.');
        const [whole, ...rest] = value.split('.');
        const decimals = rest.join('').slice(0, 2);
        const digits = whole.replace(/^0+(?=\d)/, '');

        if (digits === '' && !hasDecimal) {
            el.value = isNegative ? '-' : '';
            return;
        }

        el.value = (isNegative ? '-' : '')
            + (digits === '' ? '0' : Number(digits).toLocaleString('en-US'))
            + (hasDecimal ? '.' + decimals : '');
    };

    /**
     * Client-side rules mirroring CashFlowController. Each returns { field: message }.
     */
    window.cashFlowRules = (() => {
        const amountError = (value, { positive = false } = {}) => {
            const raw = String(value ?? '').replace(/,/g, '').trim();

            if (raw === '') return 'Please enter the amount.';
            if (!/^-?\d+(\.\d{1,2})?$/.test(raw)) return 'Enter a valid amount with up to 2 decimal places.';
            if (positive && Number(raw) <= 0) return 'Amount must be greater than zero.';

            return '';
        };

        const maxLength = (value, max, label) =>
            String(value ?? '').trim().length > max ? `${label} may not be longer than ${max} characters.` : '';

        const isDate = (value) => !Number.isNaN(Date.parse(value));

        return {
            period(values) {
                const errors = {};

                if (!values.period_from) {
                    errors.period_from = 'Please select the start date.';
                } else if (!isDate(values.period_from)) {
                    errors.period_from = 'Please enter a valid date.';
                }

                if (!values.period_to) {
                    errors.period_to = 'Please select the "as of" date.';
                } else if (!isDate(values.period_to)) {
                    errors.period_to = 'Please enter a valid date.';
                } else if (values.period_from && values.period_to < values.period_from) {
                    errors.period_to = 'The "as of" date cannot be before the start date.';
                }

                if ('opening_balance' in values) {
                    errors.opening_balance = amountError(values.opening_balance);
                }

                if ('opening_label' in values) {
                    errors.opening_label = maxLength(values.opening_label, 100, 'The "balance as of" label');
                }

                return errors;
            },

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

                if (values.entry_date && !isDate(values.entry_date)) {
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

                // Amount fields show thousands separators; send the raw number
                form.querySelectorAll('[data-amount]').forEach((el) => {
                    formData.set(el.name, el.value.replace(/,/g, '').trim());
                });

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
