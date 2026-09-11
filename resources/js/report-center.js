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

            payroll_month: ''

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

                payroll_month: ''

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

            window.print();

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

            window.open(`${this.collectionSummaryPrintUrl}?${params.toString()}`, '_blank');

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