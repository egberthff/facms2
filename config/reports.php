<?php

use App\Services\Reports\CampusClaimsReport;
use App\Services\Reports\DisbursementSummaryReport;
use App\Services\Reports\MemberAllowanceReport;
use App\Services\Reports\MonthlySummaryReport;
use App\Services\Reports\AllowanceForfeitureReport;
use App\Services\ReportService;

return [

    /*
    |--------------------------------------------------------------------------
    | Report Categories
    |--------------------------------------------------------------------------
    */

    'categories' => [

        'operational' => [

            'label' => 'Operational Reports',

            'icon' => 'heroicon-o-document-chart-bar',

        ],

        'financial' => [

            'label' => 'Financial Reports',

            'icon' => 'heroicon-o-banknotes',

        ],

        'allowance' => [

            'label' => 'Allowance Reports',

            'icon' => 'heroicon-o-wallet',

        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Report Definitions
    |--------------------------------------------------------------------------
    */

    'types' => [

        /*
        |--------------------------------------------------------------------------
        | Monthly Claims Summary
        |--------------------------------------------------------------------------
        */

        'monthly-summary' => [

            'title' => 'Monthly Claims Summary',

            'description' => 'Monthly claim statistics grouped by filing month.',

            'category' => 'operational',

            'handler' => ReportService::class,

            'filters' => [

                // 'date_range',
                'year_range', 

                'campus',

                'status',

                'claim_type',

            ],

            'cards' => [

                'claims_filed',

                'approved',

                'rejected',

                'disbursed',

            ],

            'table' => 'monthly_summary',

        ],

        /*
        |--------------------------------------------------------------------------
        | Campus Claims Summary
        |--------------------------------------------------------------------------
        */

        'campus-summary' => [

            'title' => 'Campus Claims Summary',

            'description' => 'Claim statistics grouped per campus.',

            'category' => 'operational',

            // 'handler' => CampusClaimsReport::class,
            'handler' => ReportService::class,

            'filters' => [

                'campus',

                'status',

            ],

            'cards' => [

                'members',

                'claims',

                'approved',

                'amount',

            ],

            'table' => 'campus_summary',

        ],

        /*
        |--------------------------------------------------------------------------
        | Disbursement Summary
        |--------------------------------------------------------------------------
        */

        'disbursement-summary' => [

            'title' => 'Disbursement Summary',

            'description' => 'Released and paid claims.',

            'category' => 'financial',

            // 'handler' => DisbursementSummaryReport::class,
            'handler' => ReportService::class,

            'filters' => [

                'date_range',

                'campus',

            ],

            'cards' => [

                'released',

                'disbursed',

                'amount',

                'average',

            ],

            'table' => 'disbursement',

        ],

        'payroll-deduction-request' => [

            'title' => 'Payroll Deduction Request',

            'description' => 'Printable all-campus payroll deduction request for active claimants.',

            'category' => 'financial',

            'handler' => ReportService::class,

            'filters' => [

                'claim_month',

                'payroll_month',

                'member',

                'claim_type',

                'status',

            ],

            'cards' => [

                'members',

                'amount',

            ],

            'table' => 'payroll_deduction',

        ],

        /*
        |--------------------------------------------------------------------------
        | Member Allowance Utilization
        |--------------------------------------------------------------------------
        */

        'allowance-utilization' => [

            'title' => 'Member Allowance Utilization',

            'description' => 'Remaining claimable allowance of members.',

            'category' => 'allowance',

            // 'handler' => MemberAllowanceReport::class,
            'handler' => ReportService::class,

            'filters' => [

                'campus',

                'member',

                'cycle',

            ],

            'cards' => [

                'allowance',

                'approved',

                'disbursed',

                'remaining',

            ],

            'table' => 'allowance',

        ],

        /*
        |--------------------------------------------------------------------------
        | Allowance Forfeiture
        |--------------------------------------------------------------------------
        */

        'allowance-forfeiture' => [

            'title' => 'Allowance Forfeiture',

            'description' => 'Unused allowance forfeited after the cycle.',

            'category' => 'allowance',

            // 'handler' => AllowanceForfeitureReport::class,
            'handler' => ReportService::class,

            'filters' => [

                'campus',

                'cycle',

            ],

            'cards' => [

                'members',

                'allowance',

                'claimed',

                'forfeited',

            ],

            'table' => 'forfeiture',

        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Available Filters
    |--------------------------------------------------------------------------
    */

    'filters' => [

        'date_range' => [

            'label' => 'Date Range',

            'type' => 'date-range',

        ],

        'claim_month' => [

            'label' => 'Claim Submission Month',

            'type' => 'month',

        ],

        'payroll_month' => [

            'label' => 'Payroll Month',

            'type' => 'month',

        ],

        'campus' => [

            'label' => 'Campus',

            'type' => 'select',

        ],

        'member' => [

            'label' => 'Member',

            'type' => 'ajax-select',

        ],

        'cycle' => [

            'label' => 'Allowance Cycle',

            'type' => 'select',

        ],

        'status' => [

            'label' => 'Status',

            'type' => 'select',

        ],

        'claim_type' => [

            'label' => 'Claim Type',

            'type' => 'select',

        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Summary Cards
    |--------------------------------------------------------------------------
    */

    'cards' => [

        'claims_filed' => [

            'label' => 'Claims Filed',

            'icon' => 'clipboard-document-list',

        ],

        'approved' => [

            'label' => 'Approved',

            'icon' => 'check-circle',

        ],

        'rejected' => [

            'label' => 'Rejected',

            'icon' => 'x-circle',

        ],

        'disbursed' => [

            'label' => 'Disbursed',

            'icon' => 'banknotes',

        ],

        'members' => [

            'label' => 'Members',

            'icon' => 'users',

        ],

        'claims' => [

            'label' => 'Claims',

            'icon' => 'clipboard',

        ],

        'amount' => [

            'label' => 'Amount',

            'icon' => 'currency-dollar',

        ],

        'average' => [

            'label' => 'Average',

            'icon' => 'calculator',

        ],

        'allowance' => [

            'label' => 'Allowance',

            'icon' => 'wallet',

        ],

        'remaining' => [

            'label' => 'Remaining',

            'icon' => 'wallet',

        ],

        'claimed' => [

            'label' => 'Claimed',

            'icon' => 'receipt-percent',

        ],

        'forfeited' => [

            'label' => 'Forfeited',

            'icon' => 'archive-box-x-mark',

        ],

    ],

];