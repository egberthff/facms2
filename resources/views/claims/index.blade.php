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

               // 3. Fire SweetAlert if running balance is greater than the input amount
                if (inputAmount > runningBalance) {
                    this.claimableBalanceOk = false
                    Swal.fire({
                        title: 'Balance Threshold Exceeded',
                        text: `The inputted amount (₱${inputAmount.toLocaleString('en-PH')}) is greater than claimable balance balance (₱${runningBalance.toLocaleString('en-PH')}).`,
                        icon: 'warning',
                        confirmButtonColor: '#FDBA74', // Tailwind orange-300 matching focus ring
                        confirmButtonText: 'Understood',
                    });
                     $('#claim-amount-input').toggleClass('error-border', true);
                }else{
                    this.claimableBalanceOk = true;
                }
            } catch (error) {
                console.error('Error fetching running balance:', error);
            }
        }
    }"
    class="mx-auto px-4 py-6">

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

            // PHP silently drops uploads beyond max_file_uploads, so cap selection at the same limit
            maxFiles: {{ (int) ini_get('max_file_uploads') ?: 20 }},

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

                        this.form.files.forEach(file => {
                            formData.append('documents[]', file);
                        });
                        // Lets the server detect files PHP dropped before the request reached Laravel
                        formData.append('documentsCount', this.form.files.length);

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
                                
                                    // PHP startup warnings (e.g. upload limits) can be printed before the JSON body
                                    const text = await response.text();
                                    let data;
                                    try {
                                        data = JSON.parse(text.slice(text.indexOf('{')));
                                    } catch (e) {
                                        throw { success: false, message: 'The server returned an unexpected response. Please try again.' };
                                    }

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
                                    let errorMessage = "Something went wrong. Please try again.";
                                    if (error && error.success === false && error.message) {
                                        errorMessage = error.message;  
                                    } else if (error && error.errors) {
                                        errorMessage = Object.values(error.errors).flat().join('\n'); 
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

                switch (this.step) {

                    case 1:

                        if (!this.claimType) {
                            Swal.fire('Please select a Claim Type.');
                            this.setErrorFocus(step = 1)
                            return false;
                        }

                        if (!this.amount) {
                             Swal.fire('Please enter the Claim Amount');
                            this.setErrorFocus(step = 1)
                            return false;
                        }

                        if (!this.description) {
                            Swal.fire('Please provide a Description.');
                            this.setErrorFocus(step = 1)
                            return false;
                        }

                        if(!this.incidentDate) {
                            Swal.fire("Please provide the incident date");
                            this.setErrorFocus(step = 1)
                            return false;
                        }

                        if(!this.hospital) {
                            Swal.fire("Please provide the hospital/institution"); 
                            this.setErrorFocus(step = 1)
                            return false;
                        }

                        if(!this.claimableBalanceOk)  {
                            Swal.fire('Requested amount is greater than claimable balance');
                            this.setErrorFocus(step = 1)
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
                                Swal.fire("Please provide the required documents");
                                this.setErrorFocus(step = 2)
                                    return false;
                            } else {
                               return true;
                            }
                    case 3:

                        if (!this.paymentMethod) {
                            Swal.fire("Please select your preferred disbursement method");
                            return false;
                        }

                        if (this.paymentMethod === 'bank') {

                            if (
                                !this.bank ||
                                !this.accountNumber ||
                                !this.accountName
                            ) {

                                Swal.fire(
                                    'Please complete all required bank information.'
                                );
                                this.setErrorFocus(step = 3)
                                return false;
                            }

                         if (this.accountNumber.length < 8 || this.accountNumber.length > 20) { 
                            Swal.fire('Please double check account number.'); 
                            this.setErrorFocus(step = 3); 
                            return false; 
                        }
                            
                            this.form.paymentMethod = this.selectedId;
                            this.form.bank = this.bank;
                            this.form.accountNumber = this.accountNumber;
                            this.form.accountName = this.accountName;

                        }

                        if (this.paymentMethod === 'gcash') {

                            if (
                                !this.gcashName ||
                                !this.gcashNumber
                            ) {

                                Swal.fire(
                                    'Please complete your GCash details.'
                                );
                                this.setErrorFocus(step = 3);
                                return false;
                            }

                             if (this.gcashNumber.length < 11 || this.gcashNumber.length > 11) { 
                                Swal.fire('Please double check account number.'); 
                                this.setErrorFocus(step = 3); 
                                return false; 
                            }
                            
                            this.form.paymentMethod = this.selectedId;
                            this.form.gcashName = this.gcashName;
                            this.form.gcashNumber = this.gcashNumber;

                        }

                        this.form.paymentMethod = this.selectedId;
                        return true;

                    default:

                        return true;

                }

            },

            setErrorFocus(step = 1) {
                if (step === 1) {
                  const isClaimTypeInvalid = !this.claimType;
                  $('#claim-type-input').toggleClass('error-border', isClaimTypeInvalid);
                  const isAmountInvalid = !this.amount;
                  $('#claim-amount-input').toggleClass('error-border', isAmountInvalid);
                  const isDescriptionInvalid = !this.description
                  $('#claim-description-input').toggleClass('error-border', isDescriptionInvalid);
                  const isIncidentDatInvalid = !this.incidentDate;
                  $('#claim-incident-date-input').toggleClass('error-border', isIncidentDatInvalid);
                  const isHospitalInvalid = !this.hospital;
                  $('#claim-hospital-input').toggleClass('error-border', isHospitalInvalid);
                  const isClaimableBalanceValid = !this.claimableBalanceOk;
                  $('#claim-amount-input').toggleClass('error-border', isClaimableBalanceValid);
                
                  // 3. Focus the first invalid field found
                  if (isClaimTypeInvalid) {
                    $('#claim-type-input').focus();
                  }else if (isAmountInvalid || !this.claimableBalanceOk) {
                    $('#claim-amount-input').focus();
                  }else if (isDescriptionInvalid) {
                    $('#claim-description-input').focus();
                  }else if (isIncidentDatInvalid) {
                    $('#claim-incident-date-input').focus();
                  }else if (isHospitalInvalid) {
                    $('#claim-hospital-input').focus();
                  }
                } 

                if (step == 2) {
                    const isDocumentsValid = !this.files.length === 0
                    $('#documents').toggleClass('error-border', isDocumentsValid);
                }

                if (step == 3) {
                    if(this.paymentMethod === 'bank') {
                        $('[name="bank"]').toggleClass('error-border', !this.bank);
                        $('[name="accountNumber"]').toggleClass('error-border', !this.accountNumber);
                        $('[name="accountName"]').toggleClass('error-border', !this.accountName);

                        const isAccountNumberInvalid = this.accountNumber.length < 8 || this.accountNumber.length > 20;                     
                        $('[name="accountNumber"]').toggleClass('error-border', isAccountNumberInvalid);

                        if(!this.bank){
                            $('[name="bank"]').focus();
                        } else if(!this.accountNumber){
                             $('[name="accountNumber"]').focus();
                        }else if(!this.accountName) {
                            $('[name="accountName"]').focus();
                        }else if(isAccountNumberInvalid) {
                             $('[name="accountNumber"]').focus();
                        }
                    }

                    if(this.paymentMethod === 'gcash') {
                        $('[name="gcashNumber"]').toggleClass('error-border', !this.gcashNumber);
                        $('[name="gcashName"]').toggleClass('error-border', !this.gcashName);
                        const isGcashNumberInvalid = this.accountNumber.length < 11 || this.accountNumber.length > 11;                     
                        $('[name="gcashNumber"]').toggleClass('error-border', isGcashNumberInvalid);
                        if(!this.gcashNumber){
                            $('[name="gcashNumber"]').focus();
                        }else if(!this.gcashName) {
                            $('[name="gcashName"]').focus();
                        }else if(isGcashNumberInvalid) {
                            $('[name="gcashNumber"]').focus();
                        }
                    }
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

</div>

</x-app-layout>