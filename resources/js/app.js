import './bootstrap';
import Alpine from 'alpinejs';
import {DataTable} from "simple-datatables";
// import ApexCharts from 'apexcharts';

// flatpickr
// import flatpickr from 'flatpickr';
// import 'flatpickr/dist/flatpickr.min.css';
// FullCalendar
// import { Calendar } from '@fullcalendar/core';


window.simpleDatatables = { DataTable };
window.Alpine = Alpine;
// window.ApexCharts = ApexCharts;
// window.flatpickr = flatpickr;
// window.FullCalendar = Calendar;
Alpine.start();

import 'flowbite';

import Swal from 'sweetalert2';

window.Swal = Swal;

import Chart from 'chart.js/auto';

window.Chart = Chart;

import './report-center';
import { all } from 'axios';

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', () => {

    const initializeDataTable = (tableElement) => {
        const dtInstance = new DataTable(tableElement, {
            searchable: true,
            paging: true,
            perPage: 5,
            sortable: true,
            firstLast: true,
            nextPrev: true,
            classes: {
                wrapper: "datatable-wrapper sans-serif",
                table: "w-full text-md text-left text-gray-500", 
                thead: "text-md text-gray-700 uppercase bg-gray-50", 
            }
        });
        tableElement.dataTableInstance = dtInstance;
        return dtInstance;
    };

    window.initializeDataTable = initializeDataTable;

    // Datatables
    document.querySelectorAll(".dt-table:not([data-datatable-defer])").forEach(initializeDataTable);

    // Map imports
    // if (document.querySelector('#mapOne')) {
    //     import('./components/map').then(module => module.initMap());
    // }

    // Chart imports
    // if (document.querySelector('#chartOne')) {
    //     import('./components/chart/chart-1').then(module => module.initChartOne());
    // }
    // if (document.querySelector('#chartTwo')) {
    //     import('./components/chart/chart-2').then(module => module.initChartTwo());
    // }
    // if (document.querySelector('#chartThree')) {
    //     import('./components/chart/chart-3').then(module => module.initChartThree());
    // }
    // if (document.querySelector('#chartSix')) {
    //     import('./components/chart/chart-6').then(module => module.initChartSix());
    // }
    // if (document.querySelector('#chartEight')) {
    //     import('./components/chart/chart-8').then(module => module.initChartEight());
    // }
    // if (document.querySelector('#chartThirteen')) {
    //     import('./components/chart/chart-13').then(module => module.initChartThirteen());
    // }

    // Calendar init
    // if (document.querySelector('#calendar')) {
    //     import('./components/calendar-init').then(module => module.calendarInit());
    // }
});

window.updateClaimStatus = function(claimId, status, claimData = {}) {
            const normalizedStatus = String(status || '').trim();
            const isDisbursed = normalizedStatus.toLowerCase() === 'disbursed';

            if (isDisbursed) {
                Swal.fire({
                    title: 'Prepare disbursement',
                    html: `
                        <div class="text-left space-y-3">
                            <label class="block text-sm">
                                <span class="mb-1 block font-semibold text-slate-700">Amount</span>
                                <input id="disbursement-amount" type="number" step="0.01" min="0" value="${claimData.approved_amount ?? claimData.requested_amount ?? ''}" class="swal2-input" />
                            </label>
                            <label class="block text-sm">
                                <span class="mb-1 block font-semibold text-slate-700">Reference Code</span>
                                <input id="disbursement-reference" type="text" class="swal2-input" />
                            </label>
                            <label class="block text-sm">
                                <span class="mb-1 block font-semibold text-slate-700">Remarks</span>
                                <textarea id="disbursement-remarks" class="swal2-textarea" rows="3"></textarea>
                            </label>
                            <label class="block text-sm">
                                <span class="mb-1 block font-semibold text-slate-700">Proof / Receipt</span>
                                <input id="disbursement-proof" type="file" class="swal2-file" />
                            </label>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Confirm disbursement'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    const amount = document.getElementById('disbursement-amount')?.value?.trim();
                    const referenceNumber = document.getElementById('disbursement-reference')?.value?.trim() || '';
                    const remarks = document.getElementById('disbursement-remarks')?.value?.trim() || '';
                    const proofFile = document.getElementById('disbursement-proof')?.files?.[0] || null;

                    if (!amount) {
                        Swal.showValidationMessage('Amount is required.');
                        return;
                    }

                    sendStatusRequest(claimId, normalizedStatus, {
                        status: normalizedStatus,
                        amount,
                        reference_number: referenceNumber,
                        payment_remarks: remarks,
                        proof_file: proofFile,
                    });
                });

                return;
            }
            Swal.fire({
                title: 'Are you sure?',
                text: normalizedStatus.toLowerCase() === 'approved'
                    ? 'You are about to approve this claim.'
                    : 'You are about to update this claim status.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#d33',
                confirmButtonText: normalizedStatus.toLowerCase() === 'approved' ? 'Yes, approve it!' : 'Yes, continue'
            }).then((result) => {
                if (result.isConfirmed) {
                    sendStatusRequest(claimId, normalizedStatus, {
                        status: normalizedStatus,
                        remarks: document.getElementById('claim-remarks-field')?.value || ''
                    });
                }
            });
         }

window.markContributionAsPaid = function(id, tag) {
    if (!id) return;

    let postUrl = "";
    switch (tag) {
        case 'claims' : {
            postUrl = `/contributions/details/${id}/mark-paid`;
            break;
        }
        case 'annual' : {
            postUrl = `/contributions/annual-contributions/${id}/mark-paid`;
            break;
        }
        
    }

    console.log(`Tag: ${tag}`);
    console.log(`Post Url: ${postUrl}`);

    Swal.fire({
        title: 'Mark as paid?',
        text: 'This will mark the selected contribution detail to Paid.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, mark as paid'
    }).then((result) => {
        if (!result.isConfirmed) return;
        fetch(postUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then((response) => response.json().then((payload) => ({ ok: response.ok, payload })))
        .then(({ ok, payload }) => {
            if (!ok || !payload.success) {
                throw new Error(payload.message || 'Unable to update contribution status.');
            }

            Swal.fire({
                title: 'Success',
                text: payload.message || 'Contribution marked as paid.',
                icon: 'success'
            }).then(() => window.location.reload());
        })
        .catch((error) => {
            Swal.fire('Error', error.message || 'Unable to update contribution status.', 'error');
        });
    });
};

 window.sendStatusRequest = function(claimId, status, extraData = {}) {
            const normalizedStatus = String(status || '').trim();
            const formData = new FormData();
            // (status.toLowerCase().trim() === 'disbursed') ?  formData.append('_method', 'POST') : formData.append('_method', 'PUT');
            formData.append('_method', 'PUT');
            formData.append('status', normalizedStatus);
            formData.append('remarks', ['Approved', 'Rejected'].includes(normalizedStatus)
                ? document.getElementById('claim-remarks-field')?.value || ''
                : (extraData.remarks || ''));

            if (extraData.amount) formData.append('amount', extraData.amount);
            if (extraData.reference_number) formData.append('reference_number', extraData.reference_number);
            if (extraData.payment_remarks) formData.append('payment_remarks', extraData.payment_remarks);
            if (extraData.proof_file) formData.append('proof_file', extraData.proof_file);
           console.log(Object.fromEntries(formData.entries()));
            fetch(`/claims/${claimId}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                // 3. Show success alert and handle UI update
                Swal.fire({
                    title: 'SUCCESS',
                    icon: 'success',
                    text: data['message'],
                });

                window.dispatchEvent(new CustomEvent('close-modal')); // Close your Alpine modal if needed
                
                // Optional: Refresh the page or update your Alpine state to reflect the change
                window.location.reload(); 
            })
            .catch(error => {
                Swal.fire('Error!', 'Something went wrong while updating.', 'error');
                console.error(error);
            });
        }

    // /**
    // * Processes and submits the Annual Contribution Batch Setup Form.
    // * @param {HTMLFormElement} formElement - The submitted form DOM reference.
    // */
    window.sendAnnualContributionBatchSetup = function(event, formElement) {
        // Prevent native submission so failed requests keep the Alpine modal open.
        event.preventDefault();
    
        // 2. Extract input values directly from Form Data
        const formData = new FormData(formElement);
        const contributionYear = formData.get('contribution_year'); 
        const campusFilter = formData.get('campus_filter');
        const isFederatedUser = formElement.getAttribute('data-user-is-federated') === 'true';
        
        // 3. Fallback Client-side Sanity Check Validation
        const parsedYear = parseInt(contributionYear, 10);
    
        if (!contributionYear || isNaN(parsedYear) || parsedYear < 2000 || parsedYear > 2099) {
            Swal.fire({
                icon: 'error', // or 'error'
                title: 'Invalid Year',
                text: 'Please enter a valid 4-digit setup year between 2000 and 2099.',
                confirmButtonColor: '#3b82f6'
            });
            return false;
        }

         if (isFederatedUser && (!campusFilter || campusFilter.trim() === '')) {
            Swal.fire({
                icon: 'error',
                title: 'Campus Filter Required',
                text: 'Please select a campus filter before proceeding.',
                confirmButtonColor: '#3b82f6'
            });
            return false;
        }
    
        // 4. Prompt SweetAlert2 Confirmation Modal Before Submitting
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to initialize the batch setup configuration for the year ${parsedYear}.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb', // Tailwind bg-blue-600
            cancelButtonColor: '#6b7280',  // Tailwind bg-gray-500
            confirmButtonText: 'Yes, start setup!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            // If user cancelled, drop execution context gracefully
            if (!result.isConfirmed) return;
    
            // 5. Visual loading state feedback (Button Spinner + Swal Loading Screen)
            const submitBtn = formElement.querySelector('button[type="submit"]');
            let originalBtnText = '';
            
            if (submitBtn) {
                originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://w3.org" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Processing...
                `;
            }
    
            Swal.fire({
                title: 'Generating Batch Setup',
                text: 'Please wait while the system processes the annual configurations...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
    
            // 6. Send Network Request to your Laravel Destination
            fetch(formElement.action, { 
                method: formElement.method, 
                body: formData, 
                headers: { 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 
                    'Accept': 'application/json' 
                } 
            })
            .then(async (response) => { 
                const data = await response.json(); 
                
                if (!response.ok) { 
                    // Checks if data.error or data.message exists, otherwise uses the fallback string
                    throw new Error(data.error || data.message || 'Something went wrong during form initialization.'); 
                }
                
                // 7. Success State Execution UI Updates 
                Swal.fire({ 
                    icon: 'success', 
                    title: 'Success!', 
                    text: data.message || `Annual batch setup for ${parsedYear} has been generated successfully.`, 
                    confirmButtonColor: '#2563eb' 
                }).then(() => { 
                    // Safely close Alpine modal overlay using element references 
                    if (typeof Alpine !== 'undefined') { 
                        const alpineContainer = formElement.closest('[x-data]'); 
                        if (alpineContainer) { 
                            const dataStore = alpineContainer.__x ? alpineContainer.__x.$data : Alpine.$data(alpineContainer); 
                            if (dataStore) { 
                                dataStore.openAnnualContributionSetupModal = false; 
                                dataStore.contributionYear = ''; 
                            } 
                        } 
                    } 
                    window.location.reload();
                }); 
            }) 
            .catch((error) => { 
                // 8. Error/Exception Handling Notification Output 
                console.error('Form submission failed:', error); 
                
                // Fallback message if error.message is completely empty
                const errorMsg = error.message || 'An unexpected error occurred.';
            
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error', 
                    text: errorMsg, 
                    confirmButtonColor: '#ef4444' 
                }); 
            }) 
            .finally(() => { 
                // 9. Reset button UI state context values 
                if (submitBtn) { 
                    submitBtn.disabled = false; 
                    submitBtn.innerHTML = originalBtnText; 
                } 
                 
            });
            
            // fetch(formElement.action, {
            //     method: formElement.method,
            //     body: formData,
            //     headers: {
            //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            //         'Accept': 'application/json'
            //     }
            // })
            // .then(async (response) => {
            //     const data = await response.json();
                
            //     if (!response.ok) {
            //         throw new Error(data.message || 'Something went wrong during form initialization.');
            //     }
    
            //     // 7. Success State Execution UI Updates
            //     Swal.fire({
            //         icon: 'success',
            //         title: 'Success!',
            //         text: data.message || `Annual batch setup for ${parsedYear} has been generated successfully.`,
            //         confirmButtonColor: '#2563eb'
            //     }).then(() => {
            //         // Safely close Alpine modal overlay using element references
            //         if (typeof Alpine !== 'undefined') {
            //             const alpineContainer = formElement.closest('[x-data]');
            //             if (alpineContainer) {
            //                 // Works for Alpine v3 ($data) and v2 (__x) configurations
            //                 const dataStore = alpineContainer.__x ? alpineContainer.__x.$data : Alpine.$data(alpineContainer);
            //                 if (dataStore) {
            //                     dataStore.openAnnualContributionSetupModal = false;
            //                     dataStore.contributionYear = ''; 
            //                 }
            //             }
            //         }
                    
            //         // Optional: window.location.reload(); 
            //     });
            // })
            // .catch((error) => {
            //     // 8. Error/Exception Handling Notification Output
            //     console.error('Submission Error:', error);
            //     Swal.fire({
            //         icon: 'error',
            //         title: 'Setup Failed',
            //         text: error.message || 'Network connectivity fault or system error. Please try again.',
            //         confirmButtonColor: '#ef4444' // Tailwind bg-red-500
            //     });
            // })
            // .finally(() => {
            //     // 9. Reset button UI state context values
            //     if (submitBtn) {
            //         submitBtn.disabled = false;
            //         submitBtn.innerHTML = originalBtnText;
            //     }
            // });
        });

        return false;
    };
    
        
    
