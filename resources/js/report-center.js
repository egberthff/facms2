window.reportCenter = function(options)
{
    return {

        /*
        |--------------------------------------------------------------------------
        | Static Configuration
        |--------------------------------------------------------------------------
        */

        configuration: options.configuration,

        campuses: options.campuses,

        members: options.members,

        cycles: options.cycles,

        claimTypes: options.claimTypes,

        generateUrl: options.generateUrl,

        payrollPrintUrl: options.payrollPrintUrl,

        collectionSummaryPrintUrl: options.collectionSummaryPrintUrl,

        facultyDeductionSummary: options.facultyDeductionSummaryPrintUrl,

        medicalAssistanceReport: options.medicalAssistanceReportPrintUrl,

        monthlyClaimsSummaryReport: options.monthlyClaimsSummary,

        unifiedReportLinkAggregator: options.unifiedReportUrl,

        /*
        |--------------------------------------------------------------------------
        | UI State
        |--------------------------------------------------------------------------
        */

        loading: false,

        hasGenerated: false,



        /*
        |--------------------------------------------------------------------------
        | Selected Report
        |--------------------------------------------------------------------------
        */

        selectedReport: Object.keys(options.configuration.types)[0],



        /*
        |--------------------------------------------------------------------------
        | Active Report Result
        |--------------------------------------------------------------------------
        */

        context: {

            title: '',

            description: '',

            generated_at: '',

            filters: []

        },



        cards: [],

        columns: [],

        rows: [],



        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        filters: {

            campus_id: '',

            member_id: '',

            cycle: 'current',

            status: '',

            claim_type: '',

            date_from: '',

            date_to: '',

            claim_month: '',

            payroll_month: '',

            report_month: '',
            
            reporting_year: '',

        },



        /*
        |--------------------------------------------------------------------------
        | Alert
        |--------------------------------------------------------------------------
        */

        alert: {

            show: false,

            type: 'info',

            title: '',

            message: ''

        },



        /*
        |--------------------------------------------------------------------------
        | Initialize
        |--------------------------------------------------------------------------
        */

        initialize()
        {
            this.changeReport();
        },



        /*
        |--------------------------------------------------------------------------
        | Report Changed
        |--------------------------------------------------------------------------
        */

        changeReport()
        {
            this.resetFilters();

            this.hasGenerated = false;

            this.cards = [];

            this.rows = [];

            this.columns = [];

            this.context = {

                title: '',

                description: '',

                generated_at: '',

                filters: []

            };
        },



        /*
        |--------------------------------------------------------------------------
        | Current Report
        |--------------------------------------------------------------------------
        */

        get currentReport()
        {
            return this.configuration.types[this.selectedReport];
        },



        /*
        |--------------------------------------------------------------------------
        | Group reports by category
        |--------------------------------------------------------------------------
        */

        reportsByCategory(category)
        {
            return Object.fromEntries(

                Object.entries(this.configuration.types)

                    .filter(

                        ([key, report]) =>

                            report.category === category

                    )

            );
        },



        /*
        |--------------------------------------------------------------------------
        | Show Filter?
        |--------------------------------------------------------------------------
        */

        showFilter(filter)
        {
            return this.currentReport.filters.includes(filter);
        },



        /*
        |--------------------------------------------------------------------------
        | Reset Filters
        |--------------------------------------------------------------------------
        */

        resetFilters()
        {
            this.filters = {

                campus_id: '',

                member_id: '',

                cycle: 'current',

                status: '',

                claim_type: '',

                date_from: '',

                date_to: '',

                claim_month: '',

                payroll_month: '',

                reporting_year: '',

            };

        },
                /*
        |--------------------------------------------------------------------------
        | Generate Report
        |--------------------------------------------------------------------------
        */

        async generateReport()
        {
            this.loading = true;

            this.hasGenerated = false;

            try {

                const payload = {

                    report_type: this.selectedReport,

                    ...this.filters

                };

                const response = await axios.post(

                    this.generateUrl,

                    payload

                );

                const report = response.data;

                this.context = report.context ?? {};

                this.cards = report.cards ?? [];

                this.columns = report.columns ?? [];

                this.rows = report.rows ?? [];

                this.hasGenerated = true;

                this.showAlert(

                    'success',

                    'Report Generated',

                    'The report has been generated successfully.'

                );

            }
            catch (error) {

                console.error(error);

                this.showAlert(

                    'error',

                    'Generation Failed',

                    error.response?.data?.message ??
                    'Unable to generate report.'

                );

            }
            finally {

                this.loading = false;

            }

        },



        /*
        |--------------------------------------------------------------------------
        | Export PDF
        |--------------------------------------------------------------------------
        */

        exportPdf()
        {
            if (!this.hasGenerated) {

                return;

            }

            this.$refs.exportForm.action = '/reports/export/pdf';

            this.$refs.exportForm.submit();

        },



        /*
        |--------------------------------------------------------------------------
        | Export Excel
        |--------------------------------------------------------------------------
        */

        exportExcel()
        {
            if (!this.hasGenerated) {

                return;

            }

            this.$refs.exportForm.action = '/reports/export/excel';

            this.$refs.exportForm.submit();

        },



        /*
        |--------------------------------------------------------------------------
        | Print
        |--------------------------------------------------------------------------
        */

        printReport()
        {
            if (!this.hasGenerated) {

                return;
            }

            if (this.selectedReport === 'payroll-deduction-request') {
                this.openPayrollDeductionReport();

                return;
            }

            if (this.selectedReport === 'collection-summary') {
                this.openCollectionSummaryReport();

                return;
            }

            if (this.selectedReport === 'faculty-deduction-summary') {
                this.openFacultyDeductionSummary();

                return;
            }

            if (this.selectedReport === 'medical-assistance-report') {
                this.openMedicalAssistanceReport();

                return;
            }

            if (this.selectedReport === 'monthly-summary') {
                this.openMonthlyClaimsSummaryReport();

                return;
            }

           return this.openUnifiedReportAggregator();
            // Swal.alert({
            //     icon: 'info',
            //     title: 'No Preview Available'
            // })

        },

        openPayrollDeductionReport()
        {
            if (!this.hasGenerated || this.selectedReport !== 'payroll-deduction-request') {
                return;
            }

            const params = new URLSearchParams();

            Object.entries(this.filters).forEach(([key, value]) => {
                if (value) {
                    params.set(key, value);
                }
            });

            window.open(`${this.payrollPrintUrl}?${params.toString()}`, '_blank');

        },

        openCollectionSummaryReport()
        {
            if (!this.hasGenerated || this.selectedReport !== 'collection-summary') {
                return;
            }

            const params = new URLSearchParams();

            if (this.filters.campus_id) {
                params.set('campus_id', this.filters.campus_id);
            }

            if (this.filters.date_from) {
                params.set('from', this.filters.date_from);
            }

            if (this.filters.date_to) {
                params.set('to', this.filters.date_to);
            }

            params.set('auto_print', '1');

            const printWindow = window.open(`${this.collectionSummaryPrintUrl}?${params.toString()}`, '_blank');
            if (printWindow) {
                printWindow.focus();
            }

        },

        openFacultyDeductionSummary()  {
            if(!this.hasGenerated || this.selectedReport !== 'faculty-deduction-summary') {
                return;
            }    

            if (this.filters.date_from && this.filters.date_to && !this.filters.campus_id) {
                return;
            }

            const params = new URLSearchParams();

            if (this.filters.date_from) {
                params.set('date_from', this.filters.date_from);
            }

            if (this.filters.date_to) {
                params.set('date_to', this.filters.date_to);
            }

            if (this.filters.campus_id) {
                params.set('campus_id', this.filters.campus_id);
            }

            params.set('auto_print', '1');
            const printWindow = window.open(`${this.facultyDeductionSummary}?${params.toString()}`, '_blank');
            if (printWindow) {
                printWindow.focus();
            }
        },

        openMedicalAssistanceReport() {
            if(!this.hasGenerated || this.selectedReport !== 'medical-assistance-report') {
                return;
            }  

            if (!this.filters.cycle && !this.filters.campus) {
                return;
            }
            const params = new URLSearchParams();

            if (this.filters.cycle) {
                params.set('cycle', this.filters.cycle);
            }

            if (this.filters.campus_id) {
                params.set('campus_id', this.filters.campus_id);
            }

            params.set('auto_print', '1');
            const printWindow = window.open(`${this.medicalAssistanceReport}?${params.toString()}`, '_blank');
            if (printWindow) {
                printWindow.focus();
            }

        },

        openMonthlyClaimsSummaryReport() {
            if(!this.hasGenerated || this.selectedReport !== 'monthly-summary') {
                return;
            }

            const params = new URLSearchParams();
            params.set('campus_id', this.filters.campus_id);
            params.set('claim_type', this.filters.claim_type);
            params.set('cycle', this.filters.cycle);
            params.set('reporting_year', this.filters.reporting_year);
            params.set('status', this.filters.status);

            params.set('auto_print', '1');
            const printWindow = window.open(`${this.monthlyClaimsSummaryReport}?${params.toString()}`, '_blank');
            if (printWindow) {
                printWindow.focus();
            }
        },

        openUnifiedReportAggregator() {
            if(!this.hasGenerated) {
                return;
            }
            const params = new URLSearchParams();
            console.log(JSON.parse(JSON.stringify(this.filters)));

            params.set('date_from', this.filters?.date_from ?? '');
            params.set('date_to', this.filters?.date_to ?? '');
            params.set('claim_month', this.filters?.claim_month ?? '');
            params.set('payroll_month', this.filters?.payroll_month ?? '');
            params.set('reporting_year', this.filters?.reporting_year ?? '');
            params.set('cycle', this.filters?.cycle ?? '');
            params.set('status', this.filters?.status ?? '');
            params.set('claim_type', this.filters?.claim_type ?? '');

            params.set('report_month', this.filters?.report_month ?? '');
            params.set('campus_id', this.filters?.campus_id ?? '');
            params.set('member_id', this.filters?.member_id ?? '');
            params.set('report_type', this.selectedReport ?? '');

            params.set('auto_print', '1');
            const printWindow = window.open(`${this.unifiedReportLinkAggregator}?${params.toString()}`, '_blank');
            if(printWindow) {
                printWindow.focus();
            }

        },

        /*
        |--------------------------------------------------------------------------
        | Toast Notification
        |--------------------------------------------------------------------------
        */

        showAlert(type, title, message)
        {
            this.alert = {

                show: true,

                type,

                title,

                message

            };

            setTimeout(() => {

                this.alert.show = false;

            }, 4000);

        },

                /*
        |--------------------------------------------------------------------------
        | Currency
        |--------------------------------------------------------------------------
        */

        formatCurrency(value)
        {
            if (
                value === null ||
                value === undefined ||
                value === ''
            ) {

                return '₱0.00';

            }

            return new Intl.NumberFormat(

                'en-PH',

                {

                    style: 'currency',

                    currency: 'PHP'

                }

            ).format(value);

        },



        /*
        |--------------------------------------------------------------------------
        | Numbers
        |--------------------------------------------------------------------------
        */

        formatNumber(value)
        {
            if (
                value === null ||
                value === undefined
            ) {

                return '0';

            }

            return Number(value).toLocaleString();

        },



        /*
        |--------------------------------------------------------------------------
        | Dates
        |--------------------------------------------------------------------------
        */

        formatDate(value)
        {
            if (!value) {

                return '';

            }

            return new Date(value).toLocaleDateString(

                'en-PH',

                {

                    year: 'numeric',

                    month: 'short',

                    day: 'numeric'

                }

            );

        },

                /*
        |--------------------------------------------------------------------------
        | Badge Colors
        |--------------------------------------------------------------------------
        */

        badgeClass(status)
        {
            switch(String(status).toLowerCase()) {

                case 'approved':

                    return 'bg-green-100 text-green-800';

                case 'pending':

                    return 'bg-yellow-100 text-yellow-800';

                case 'rejected':

                    return 'bg-red-100 text-red-800';

                case 'disbursed':

                    return 'bg-blue-100 text-blue-800';

                case 'processing':

                    return 'bg-purple-100 text-purple-800';

                default:

                    return 'bg-gray-100 text-gray-700';

            }

        },

                /*
        |--------------------------------------------------------------------------
        | Generic Helpers
        |--------------------------------------------------------------------------
        */

        clearReport()
        {
            this.hasGenerated = false;

            this.cards = [];

            this.columns = [];

            this.rows = [];

            this.context = {

                title: '',

                description: '',

                generated_at: '',

                filters: []

            };

        },



        /*
        |--------------------------------------------------------------------------
        | Refresh
        |--------------------------------------------------------------------------
        */

        refresh()
        {
            if (!this.hasGenerated) {

                return;

            }

            this.generateReport();

        }

    };

}