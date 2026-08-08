import './bootstrap';
import Alpine from 'alpinejs';
import {DataTable} from "simple-datatables";
// import ApexCharts from 'apexcharts';

// flatpickr
// import flatpickr from 'flatpickr';
// import 'flatpickr/dist/flatpickr.min.css';
// FullCalendar
// import { Calendar } from '@fullcalendar/core';



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

    //Datatables 
    const tables = document.querySelectorAll(".dt-table");
     tables.forEach(tableElement => {
        // 3. Initialize the library directly on the specific element
        new DataTable(tableElement, {
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
    });

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
            console.log(normalizedStatus);
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

window.markContributionAsPaid = function(detailId) {
    if (!detailId) return;

    Swal.fire({
        title: 'Mark as paid?',
        text: 'This will override the selected contribution detail to Paid.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, mark as paid'
    }).then((result) => {
        if (!result.isConfirmed) return;

        fetch(`/contributions/details/${detailId}/mark-paid`, {
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
    
