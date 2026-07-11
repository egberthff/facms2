import './bootstrap';
import Alpine from 'alpinejs';
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

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', () => {
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
                                <span class="mb-1 block font-semibold text-slate-700">Reference Number</span>
                                <input id="disbursement-reference" type="text" class="swal2-input" />
                            </label>
                            <label class="block text-sm">
                                <span class="mb-1 block font-semibold text-slate-700">Remarks</span>
                                <textarea id="disbursement-remarks" class="swal2-textarea" rows="3"></textarea>
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

                    if (!amount) {
                        Swal.showValidationMessage('Amount is required.');
                        return;
                    }

                    sendStatusRequest(claimId, normalizedStatus, {
                        amount,
                        reference_number: referenceNumber,
                        payment_remarks: remarks,
                    });
                });

                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: normalizedStatus === 'approved'
                    ? 'You are about to approve this claim.'
                    : 'You are about to update this claim status.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#d33',
                confirmButtonText: normalizedStatus === 'approved' ? 'Yes, approve it!' : 'Yes, continue'
            }).then((result) => {
                if (result.isConfirmed) {
                    sendStatusRequest(claimId, normalizedStatus, {
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

            fetch(`/claims/${claimId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    status: normalizedStatus,
                    remarks: ['Approved', 'Rejected'].includes(normalizedStatus)
                        ? document.getElementById('claim-remarks-field')?.value || ''
                        : (extraData.remarks || ''),
                    ...extraData,
                })
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
    
