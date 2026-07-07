<x-app-layout>

<div
    x-data="claimWizard()"
    class="max-w-6xl mx-auto px-4 py-6">

    <form
        id="claim-form"
        action="{{ route('claim.store') }}"
        method="POST"
        enctype="multipart/form-data"
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
             * FORM MODEL
             * ===================================================== */

            form: {

                // STEP 1

                claimType: '',
                amount: '',
                description: '',
                incidentDate: '',
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
                return this.form.amount;
            },
            set amount(value) {
                this.form.amount = value;
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
                           alert('Please accept the declaration.');
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

                        formData.append('paymentMethod', this.form.paymentMethod);

                        formData.append('bank', this.form.bank);
                        formData.append('accountName', this.form.accountName);
                        formData.append('accountNumber', this.form.accountNumber);
                        formData.append('branch', this.form.branch);

                        formData.append('gcashName', this.form.gcashName);
                        formData.append('gcashNumber', this.form.gcashNumber);

                        this.form.files.forEach(file => {
                            formData.append('documents[]', file);
                        });
                    
                       try {
                        
                           const response = await fetch(form.action, {
                            
                               method: 'POST',
                            
                               headers: {
                                   'X-CSRF-TOKEN': document
                                       .querySelector('meta[name="csrf-token"]')
                                       .content,
                                
                                   'Accept': 'application/json',
                                   'X-Requested-With': 'XMLHttpRequest',
                               },
                            
                               body: formData
                            
                           });
                          console.log(typeof response);
                          console.log(response);
                           const data = await response.json();
                        
                           if (!response.ok) {
                            
                               throw data;
                            
                           }
                        
                           alert(data.message);
                        
                           window.location.href = data.redirect;
                        
                       } catch (error) {
                        
                           console.log(error);
                        
                       }
                    
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

                switch (this.step) {

                    case 1:

                        if (!this.claimType) {
                            alert('Please select a Claim Type.');
                            return false;
                        }

                        if (!this.amount) {
                            alert('Please enter the Claim Amount.');
                            return false;
                        }

                        if (!this.description) {
                            alert('Please provide a Description.');
                            return false;
                        }

                        return true;

                    case 2:

                        if (this.files.length === 0) {

                            if (!confirm(
                                'No supporting documents have been uploaded. Continue anyway?'
                            )) {
                                return false;
                            }

                        }

                        return true;

                    case 3:

                        if (!this.paymentMethod) {
                            alert('Please select a payment method.');
                            return false;
                        }

                        if (this.paymentMethod === 'bank') {

                            if (
                                !this.bank ||
                                !this.accountNumber ||
                                !this.accountName
                            ) {

                                alert(
                                    'Please complete all required bank information.'
                                );

                                return false;
                            }

                        }

                        if (this.paymentMethod === 'gcash') {

                            if (
                                !this.gcashName ||
                                !this.gcashNumber
                            ) {

                                alert(
                                    'Please complete your GCash details.'
                                );

                                return false;
                            }

                        }

                        return true;

                    default:

                        return true;

                }

            },

            /* =====================================================
             * HELPERS
             * ===================================================== */

            formatCurrency(value) {

                if (!value) {
                    return '0.00';
                }

                return Number(value).toLocaleString(
                    'en-PH',
                    {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }
                );

            },

            resetForm() {

                this.step = 1;

                this.agree = false;

                this.form = {

                    claimType: '',
                    amount: '',
                    description: '',
                    incidentDate: '',
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

</div>

</x-app-layout>