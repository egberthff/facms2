<x-app-layout>

<div
    x-data="{
        ...claimWizard(),
        userId: '{{ auth()->id() }}',
        claimableBalanceOk: true,
        rawAmount: '',
        claimTypeText: '',
        get amount() {
            if (!this.rawAmount) return '';
            // Format the raw number with commas for display
            return Number(this.rawAmount).toLocaleString('en-US');
        }, 
        set amount(value) {
            // Strip out everything except digits and decimal points
            let cleanValue = value.replace(/[^0-9.]/g, '');

            // Prevent multiple decimals
            const points = cleanValue.split('.');
            if (points.length > 2) {
                cleanValue = points[0] + '.' + points.slice(1).join('');
            }

            this.rawAmount = cleanValue;
        },
        async checkRunningBalance() {
            // Don't check anything if the field is empty
            if (!this.rawAmount) return;

            try {
                // Build the URL matching your route: claims/running-balance/{user}
                const url = `/claims/running-balance/${this.userId}`;

                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) throw new Error('Network response failure');

                const data = await response.json();          

                // 1. Safely parse the inputted amount (fallback to 0 if empty/invalid)
                const inputAmount = parseFloat(this.rawAmount.replaceAll(',', '')) || 0;          

                // 2. Safely parse the controller amount (handles numbers or strings with formatters)
                let rawBalanceValue = data.amount;
                if (typeof rawBalanceValue === 'string') {
                    rawBalanceValue = rawBalanceValue.replace(/[^0-9.]/g, ''); // Strip currency signs or commas if present
                }
                const runningBalance = parseFloat(rawBalanceValue) || 0;

               // 3. Flag the amount field if it exceeds the claimable balance
                if (inputAmount > runningBalance) {
                    this.claimableBalanceOk = false;
                    this.errors.amount = `Amount exceeds your claimable balance of ₱${runningBalance.toLocaleString('en-PH')}.`;
                }else{
                    this.claimableBalanceOk = true;
                    this.errors.amount = '';
                }
            } catch (error) {
                console.error('Error fetching running balance:', error);
            }
        }
    }"
    class="mx-auto max-w-5xl py-2 sm:py-4">

    <form
        id="claim-form"
        action="{{ route('claim.store') }}"
        method="POST"
        enctype="multipart/form-data"
        @input="clearError($event.target.name)"
        @change="clearError($event.target.name)"
        @submit.prevent="submitForm">

        @csrf

        <!-- ================================================= -->
        <!-- PROGRESS STEPPER -->
        <!-- ================================================= -->
        @include('claims.partials.progress')
       
        <!-- ================================================= -->
        <!-- STEP 1 -->
        <!-- ================================================= -->
        @include('claims.partials.claim-details')
        <!-- ================================================= -->
        <!-- STEP 2 - SUPPORTING DOCUMENTS -->
        <!-- ================================================= -->
        @include('claims.partials.documents')

        <!-- ================================================= -->
        <!-- STEP 3 - PAYMENT INFORMATION -->
        <!-- ================================================= -->
        @include('claims.partials.payment')
        <!-- ================================================= -->
        <!-- STEP 4 - REVIEW -->
        <!-- ================================================= -->
        @include('claims.partials.review')
        
        <script>
        function claimWizard() {

        return {

            /* =====================================================
             * CURRENT STEP
             * ===================================================== */

            step: 1,

            agree: false,

            /* =====================================================
             * VALIDATION ERRORS (field name => message)
             * ===================================================== */

            errors: {},

            /* =====================================================
             * PAYOUT QR UPLOAD (per payment method)
             * ===================================================== */

            qr: {
                gcash: { file: null, payload: '', preview: null, fileName: '', message: '', loading: false },
                bank:  { file: null, payload: '', preview: null, fileName: '', message: '', loading: false },
            },

            /* =====================================================
             * FORM MODEL
             * ===================================================== */

            form: {

                // STEP 1

                claimType: '',
                amount: '',
                description: '',
                incidentDate: '',
                treatmentEndDate: '',
                hospital: '',

                // STEP 2

                files: [],

                // STEP 3

                paymentMethod: '',

                bank: '',
                accountName: '',
                accountNumber: '',
                branch: '',

                gcashName: '',
                gcashNumber: '',

            },

            /* =====================================================
             * ALIASES
             * (Keeps existing Blade working)
             * ===================================================== */

            get claimType() {
                return this.form.claimType;
            },
            set claimType(value) {
                this.form.claimType = value;
            },

            get amount() {
                // return this.form.amount;
                return parseFloat(String(this.form.amount).replace(/,/g, ''));
            },

            set amount(value) {
              const cleaned = String(value).replace(/,/g, ''); 
              this.form.amount = parseFloat(cleaned) || 0; 
            },

            get description() {
                return this.form.description;
            },
            set description(value) {
                this.form.description = value;
            },

            get incidentDate() {
                return this.form.incidentDate;
            },
            set incidentDate(value) {
                this.form.incidentDate = value;
            },

            get treatmentEndDate() {
                return this.form.treatmentEndDate;
            },
            set treatmentEndDate(value) {
                this.form.treatmentEndDate = value;
            },

            get hospital() {
                return this.form.hospital;
            },
            set hospital(value) {
                this.form.hospital = value;
            },

            get files() {
                return this.form.files;
            },
            set files(value) {
                this.form.files = value;
            },

            get paymentMethod() {
                return this.form.paymentMethod;
            },
            set paymentMethod(value) {
                this.form.paymentMethod = value;
            },

            get bank() {
                return this.form.bank;
            },
            set bank(value) {
                this.form.bank = value;
            },

            get accountName() {
                return this.form.accountName;
            },
            set accountName(value) {
                this.form.accountName = value;
            },

            get accountNumber() {
                return this.form.accountNumber;
            },
            set accountNumber(value) {
                this.form.accountNumber = value;
            },

            get branch() {
                return this.form.branch;
            },
            set branch(value) {
                this.form.branch = value;
            },

            get gcashName() {
                return this.form.gcashName;
            },
            set gcashName(value) {
                this.form.gcashName = value;
            },

            get gcashNumber() {
                return this.form.gcashNumber;
            },
            set gcashNumber(value) {
                this.form.gcashNumber = value;
            },

             loading: false,
             async submitForm() {
            
                       if (!this.agree) {
                        //    alert('Please accept the declaration.');
                           Swal.fire('Please accept the declaration.');
                           return;
                       }
                    
                    const form = document.getElementById('claim-form');
                    
                    //    const formData = new FormData(form);

                       const formData = new FormData();

                        formData.append('claimType', this.form.claimType);
                        formData.append('amount', this.form.amount);
                        formData.append('description', this.form.description);
                        formData.append('incidentDate', this.form.incidentDate);
                        formData.append('hospital', this.form.hospital);
                        formData.append('treatmentEndDate', this.form.treatmentEndDate || '');

                         const paymentMethodMap = {
                            'check': '019f32cb-c177-7193-a110-d5cc8aeb9c60',
                            'gcash': '019f32cb-c172-72d3-97bd-b1b888fe3611',
                            'bank':  '019f32cb-c17d-7362-94af-09d027798fca'
                        };
                        const selectedId = paymentMethodMap[this.form.paymentMethod] || this.form.paymentMethod || paymentMethodMap[this.paymentMethod] || this.paymentMethod;
                        formData.append('paymentMethod', selectedId);
                        // formData.append('paymentMethod', this.form.paymentMethod);

                        formData.append('bank', this.form.bank);
                        formData.append('accountName', this.form.accountName);
                        formData.append('accountNumber', this.form.accountNumber);
                        formData.append('branch', this.form.branch);

                        formData.append('gcashName', this.form.gcashName);
                        formData.append('gcashNumber', this.form.gcashNumber);

                        const payoutQr = this.qr[this.paymentMethod];
                        if (payoutQr?.file) {
                            formData.append('payoutQr', payoutQr.file);
                            formData.append('payoutQrPayload', payoutQr.payload);
                        }

                        this.form.files.forEach(file => {
                            formData.append('documents[]', file);
                        });

                        Swal.fire({
                            title: 'Are you sure?',
                            text: 'Ensure all details are correct. Once submitted, it cannot be edited.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, submit it!'
                        }).then(async (result) => {
                            if (result.isConfirmed) {

                                // FORCED SEAMLESS TRANSITION: Re-fire/Update immediately to lock it into a loading state
                                Swal.fire({
                                    title: 'Submitting...',
                                    text: 'Please wait.',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });

                                try {
                                    const response = await fetch(form.action, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest',
                                        },
                                        body: formData
                                    });
                                
                                    const data = await response.json();

                                    if (!response.ok) {
                                        throw data;
                                    }
                                
                                    // Target the active loader alert and convert it to Success
                                    Swal.hideLoading();
                                    Swal.update({
                                        icon: "success",
                                        title: "Success!",
                                        text: data.message,
                                        showConfirmButton: true,
                                        allowOutsideClick: true,
                                        allowEscapeKey: true
                                    });
                                
                                    // Reload page on user acknowledgement
                                    Swal.getConfirmButton().addEventListener('click', () => {
                                        window.location.reload();
                                    });
                                
                                } catch (error) {
                                    // Field validation errors: show them under their inputs
                                    if (error && error.errors) {
                                        Swal.close();
                                        this.showServerErrors(error.errors);
                                        return;
                                    }

                                    let errorMessage = "Something went wrong. Please try again.";
                                    if (error && error.success === false && error.message) {
                                        errorMessage = error.message;
                                    }
                                
                                    // Target the active loader alert and convert it to Error
                                    Swal.hideLoading();
                                    Swal.update({
                                        icon: "error",
                                        title: "Oops...",
                                        text: errorMessage,
                                        showConfirmButton: true,
                                        allowOutsideClick: true,
                                        allowEscapeKey: true
                                    });
                                }
                            }
                        });
                   },

            /* =====================================================
             * STEP NAVIGATION
             * ===================================================== */

            nextStep() {

                if (!this.validateStep()) {
                    return;
                }

                if (this.step < 4) {
                    this.step++;
                }

            },

            previousStep() {

                if (this.step > 1) {
                    this.step--;
                }

            },

            goToStep(step) {

                if (step <= this.step) {
                    this.step = step;
                }

            },

            /* =====================================================
             * VALIDATION
             * ===================================================== */

            validateStep() {

                const errors = {};

                switch (this.step) {

                    case 1:

                        if (!this.claimType) {
                            errors.claimType = 'Please select a claim type.';
                        }

                        if (!this.amount) {
                            errors.amount = 'Please enter the claim amount.';
                        } else if (!this.claimableBalanceOk) {
                            errors.amount = this.errors.amount || 'Requested amount is greater than your claimable balance.';
                        }

                        if (!this.description) {
                            errors.description = 'Please provide a description.';
                        }

                        if (!this.incidentDate) {
                            errors.incidentDate = 'Please provide the treatment start date.';
                        }

                        if (!this.hospital) {
                            errors.hospital = 'Please provide the hospital / institution.';
                        }

                        if (!this.setStepErrors(errors, ['claimType', 'amount', 'description', 'incidentDate', 'hospital'])) {
                            return false;
                        }

                        this.form.claimType = this.claimType;
                        this.form.amount = parseFloat(String(this.amount).replace(/,/g, ''));
                        this.form.description = this.description;
                        this.form.incidentDate = this.incidentDate;
                        this.form.hospital = this.hospital;
                        return true;

                    case 2:

                        if (this.files.length === 0) {
                            errors.documents = 'Please upload the required supporting documents.';
                        }

                        return this.setStepErrors(errors, ['documents']);

                    case 3:

                        if (!this.paymentMethod) {
                            errors.paymentMethod = 'Please select your preferred disbursement method.';
                        }

                        // An attached payment QR replaces the typed account details
                        if (this.paymentMethod === 'bank') {

                            const hasQr = !!this.qr.bank.file;

                            if (!this.bank && !hasQr) {
                                errors.bank = 'Please select a bank or upload your bank QR code.';
                            }

                            if (!this.accountNumber) {
                                if (!hasQr) errors.accountNumber = 'Please enter your account number or upload your bank QR code.';
                            } else if (this.accountNumber.length < 8 || this.accountNumber.length > 20) {
                                errors.accountNumber = 'Account number must be 8 to 20 digits.';
                            }

                            if (!this.accountName && !hasQr) {
                                errors.accountName = 'Please enter the account name or upload your bank QR code.';
                            }

                        }

                        if (this.paymentMethod === 'gcash') {

                            const hasQr = !!this.qr.gcash.file;

                            if (!this.gcashNumber) {
                                if (!hasQr) errors.gcashNumber = 'Please enter your GCash number or upload your GCash QR code.';
                            } else if (this.gcashNumber.length !== 11) {
                                errors.gcashNumber = 'GCash number must be 11 digits (09XXXXXXXXX).';
                            }

                            if (!this.gcashName && !hasQr) {
                                errors.gcashName = 'Please enter the account name or upload your GCash QR code.';
                            }

                        }

                        if (!this.setStepErrors(errors, ['paymentMethod', 'bank', 'accountNumber', 'accountName', 'branch', 'gcashNumber', 'gcashName'])) {
                            return false;
                        }

                        this.form.paymentMethod = this.selectedId;
                        this.form.bank = this.bank;
                        this.form.accountNumber = this.accountNumber;
                        this.form.accountName = this.accountName;
                        this.form.branch = this.branch;
                        this.form.gcashName = this.gcashName;
                        this.form.gcashNumber = this.gcashNumber;
                        return true;

                    default:

                        return true;

                }

            },

            /**
             * Replace the errors of the given step fields, then focus the
             * first invalid one. Returns true when the step has no errors.
             */
            setStepErrors(errors, fields) {
                fields.forEach(field => {
                    this.errors[field] = errors[field] || '';
                });

                const firstInvalid = fields.find(field => errors[field]);

                if (firstInvalid) {
                    this.focusField(firstInvalid);
                    return false;
                }

                return true;
            },

            focusField(field) {
                this.$nextTick(() => {
                    const el = document.querySelector(`#claim-form [name="${field}"]`);
                    if (el && el.type !== 'file') {
                        el.focus();
                    } else {
                        document.getElementById(`${field}-error`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            },

            /**
             * Decode an uploaded GCash / bank QR and fill in the payout fields.
             */
            async handlePaymentQr(event, method) {
                const file = event.target.files[0];
                event.target.value = '';
                if (!file) return;

                const state = this.qr[method];
                const errorKey = `${method}Qr`;

                this.errors[errorKey] = '';
                state.message = '';

                if (!file.type.startsWith('image/')) {
                    this.errors[errorKey] = 'Please upload the QR code as an image (PNG or JPG).';
                    return;
                }

                state.loading = true;

                try {
                    const details = await window.readPaymentQr(file);

                    if (!details.raw.startsWith('000201')) {
                        throw new Error('This is not a payment QR code. Please upload the QR from your GCash or banking app.');
                    }

                    // Wallets often leave the number out or mask the name; fill only what is readable
                    const filled = [];

                    if (method === 'gcash') {
                        if (details.provider !== 'GCash') {
                            throw new Error(details.provider
                                ? `This looks like a ${details.provider} QR, not a GCash QR.`
                                : 'This is not a GCash QR code.');
                        }

                        if (details.number) {
                            this.gcashNumber = details.number;
                            this.errors.gcashNumber = '';
                            filled.push('number');
                        }

                        if (details.name && !details.nameMasked) {
                            this.gcashName = details.name;
                            this.errors.gcashName = '';
                            filled.push('name');
                        }
                    }

                    if (method === 'bank') {
                        if (details.provider === 'GCash') {
                            throw new Error('This is a GCash QR. Select GCash as your disbursement method instead.');
                        }

                        if (details.number) {
                            this.accountNumber = details.number.slice(0, 20);
                            this.errors.accountNumber = '';
                            filled.push('account number');
                        }

                        if (details.bank && Array.from(document.querySelectorAll('#claim-form [name="bank"] option')).some(o => o.value === details.bank)) {
                            this.bank = details.bank;
                            this.errors.bank = '';
                            filled.push('bank');
                        }

                        if (details.name && !details.nameMasked) {
                            this.accountName = details.name;
                            this.errors.accountName = '';
                            filled.push('name');
                        }
                    }

                    if (state.preview) URL.revokeObjectURL(state.preview);
                    Object.assign(state, {
                        file,
                        payload: details.raw,
                        preview: URL.createObjectURL(file),
                        fileName: file.name,
                        message: 'QR attached. It will be used to send your payout'
                            + (filled.length ? `, and your ${filled.join(', ')} was filled in from it.` : '. The fields below are optional.'),
                    });
                } catch (e) {
                    this.errors[errorKey] = e.message || 'The QR code could not be read.';
                } finally {
                    state.loading = false;
                }
            },

            removePaymentQr(method) {
                const state = this.qr[method];
                if (state.preview) URL.revokeObjectURL(state.preview);
                Object.assign(state, { file: null, payload: '', preview: null, fileName: '', message: '' });
                this.errors[`${method}Qr`] = '';
            },

            clearError(name) {
                if (!name || name === 'documents[]') return;
                if (this.errors[name]) {
                    this.errors[name] = '';
                }
            },

            /**
             * Show server-side (422) validation errors under their fields
             * and jump back to the step of the first invalid field.
             */
            showServerErrors(serverErrors) {
                const stepFields = {
                    1: ['claimType', 'amount', 'description', 'incidentDate', 'treatmentEndDate', 'hospital'],
                    2: ['documents'],
                    3: ['paymentMethod', 'bank', 'accountName', 'accountNumber', 'branch', 'gcashName', 'gcashNumber', 'gcashQr', 'bankQr'],
                };

                let firstStep = null;
                let firstField = null;

                Object.entries(serverErrors).forEach(([key, messages]) => {
                    // documents.0 => documents; QR errors show under the active method's upload card
                    let field = key.split('.')[0];
                    if (field === 'payoutQr' || field === 'payoutQrPayload') {
                        field = `${this.paymentMethod}Qr`;
                    }
                    if (!this.errors[field]) {
                        this.errors[field] = [].concat(messages)[0];
                    }

                    const step = Number(Object.keys(stepFields).find(s => stepFields[s].includes(field)));
                    if (step && (firstStep === null || step < firstStep)) {
                        firstStep = step;
                        firstField = field;
                    }
                });

                if (firstStep !== null) {
                    this.step = firstStep;
                    this.focusField(firstField);
                }
            },


            /* =====================================================
             * HELPERS
             * ===================================================== */

           formatCurrency(value) {
              // 1. Handle null, undefined, 0, or empty string safely
              if (!value) { 
                return '0.00'; 
              }         

              // 2. Convert to string and strip out ALL commas
              const sanitizedValue = String(value).replace(/,/g, '');           

              // 3. Convert to a number
              const numberValue = Number(sanitizedValue);           

              // 4. Return formatted string (falls back to 0.00 if the input wasn't a valid number)
              if (isNaN(numberValue)) {
                return '0.00';
              }         

              return numberValue.toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
              });
            },

            resetForm() {

                this.step = 1;

                this.agree = false;

                this.errors = {};

                this.removePaymentQr('gcash');
                this.removePaymentQr('bank');

                this.form = {

                    claimType: '',
                    amount: '',
                    description: '',
                    incidentDate: '',
                    treatmentEndDate: '',
                    hospital: '',

                    files: [],

                    paymentMethod: '',

                    bank: '',
                    accountName: '',
                    accountNumber: '',
                    branch: '',

                    gcashName: '',
                    gcashNumber: '',

                };

            }

        }

    }

    // document.getElementById('claim-form').addEventListener('submit', (event) => {
    //     event.preventDefault();
    //     let formData = new FormData(event.target);
    //     console.log(formData);
    //     alert('Form submitted successfully!');
    // });

</script>

    </form>

    {{-- Shared by every step (documents, payout QR, review); kept outside the step sections so it is never hidden with them --}}
    <x-file-preview-modal />

</div>

</x-app-layout>