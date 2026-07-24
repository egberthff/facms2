<?php

namespace App\Services\Reports;

class MemberAllowanceReport extends BaseReport
{
    /**
     * Build the report.
     */
    protected function build(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Context
        |--------------------------------------------------------------------------
        */

        $this->context = [

            'title' => 'Member Allowance Utilization',

            'description' => 'Shows the utilization of member allowances for the selected cycle.',

            'generated_at' => now()->format('F d, Y h:i A'),

            'filters' => [

                [

                    'label' => 'Campus',

                    'value' => $this->filters['campus_id'] ?: 'All Campuses'

                ],

                [

                    'label' => 'Cycle',

                    'value' => $this->filters['cycle'] ?: 'Current Cycle'

                ]

            ]

        ];

        /*
        |--------------------------------------------------------------------------
        | Mock Data
        |--------------------------------------------------------------------------
        */

        $members = [

            [
                'member' => 'Juan Dela Cruz',
                'campus' => 'Main Campus',
                'allowance' => 25000,
                'approved' => 18000,
                'disbursed' => 15000,
            ],

            [
                'member' => 'Maria Santos',
                'campus' => 'North Campus',
                'allowance' => 25000,
                'approved' => 22000,
                'disbursed' => 22000,
            ],

            [
                'member' => 'Pedro Reyes',
                'campus' => 'South Campus',
                'allowance' => 25000,
                'approved' => 12000,
                'disbursed' => 9000,
            ],

            [
                'member' => 'Ana Cruz',
                'campus' => 'Main Campus',
                'allowance' => 25000,
                'approved' => 25000,
                'disbursed' => 25000,
            ],

            [
                'member' => 'Mark Ramos',
                'campus' => 'North Campus',
                'allowance' => 25000,
                'approved' => 6000,
                'disbursed' => 6000,
            ],

        ];

        /*
        |--------------------------------------------------------------------------
        | Totals
        |--------------------------------------------------------------------------
        */

        $totalAllowance = 0;

        $totalApproved = 0;

        $totalDisbursed = 0;

        $totalRemaining = 0;

        foreach ($members as $member) {

            $remaining = max(
                0,
                $member['allowance'] - $member['approved']
            );

            $this->rows[] = [

                'member' => $member['member'],

                'campus' => $member['campus'],

                'allowance' => $member['allowance'],

                'approved' => $member['approved'],

                'disbursed' => $member['disbursed'],

                'remaining' => $remaining,

                'cycle' => $this->filters['cycle'] ?: 'Current'

            ];

            $totalAllowance += $member['allowance'];

            $totalApproved += $member['approved'];

            $totalDisbursed += $member['disbursed'];

            $totalRemaining += $remaining;

        }

        /*
        |--------------------------------------------------------------------------
        | KPI Cards
        |--------------------------------------------------------------------------
        */

        $this->cards = [

            $this->card(
                'allowance',
                'Total Allowance',
                $totalAllowance
            ),

            $this->card(
                'approved',
                'Approved Claims',
                $totalApproved
            ),

            $this->card(
                'disbursed',
                'Disbursed',
                $totalDisbursed
            ),

            $this->card(
                'remaining',
                'Remaining Balance',
                $totalRemaining
            ),

        ];

        /*
        |--------------------------------------------------------------------------
        | Table Columns
        |--------------------------------------------------------------------------
        */

        $this->columns = [

            $this->column(
                'member',
                'Member'
            ),

            $this->column(
                'campus',
                'Campus'
            ),

            $this->column(
                'allowance',
                'Allowance',
                'currency'
            ),

            $this->column(
                'approved',
                'Approved',
                'currency'
            ),

            $this->column(
                'disbursed',
                'Disbursed',
                'currency'
            ),

            $this->column(
                'remaining',
                'Remaining',
                'currency'
            ),

            $this->column(
                'cycle',
                'Cycle'
            ),

        ];

        /*
        |--------------------------------------------------------------------------
        | Meta
        |--------------------------------------------------------------------------
        */

        $this->meta = [

            'report_key' => 'allowance-utilization',

            'empty_message' => 'No allowance records found.',

        ];

    }
}