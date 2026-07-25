<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class ReportService
{
    /**
     * Generate report.
     */
    public function generate(string $reportType, array $filters = []): array
    {
        return match ($reportType) {

            'monthly-summary'
                => $this->monthlySummary($filters),

            'campus-summary'
                => $this->campusSummary($filters),

            'disbursement-summary'
                => $this->disbursementSummary($filters),

            'allowance-utilization'
                => $this->allowanceUtilization($filters),

            'allowance-forfeiture'
                => $this->allowanceForfeiture($filters),

            default => throw new InvalidArgumentException(
                "Unknown report type [{$reportType}]"
            ),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Operational Reports
    |--------------------------------------------------------------------------
    */

    protected function monthlySummary(array $filters): array
    {
        return [

            'context' => $this->buildContext(
                'Monthly Claims Summary',
                'Monthly claim statistics.',
                $filters
            ),

            'cards' => [

                [
                    'key' => 'claims',
                    'label' => 'Claims Filed',
                    'value' => 145,
                ],

                [
                    'key' => 'approved',
                    'label' => 'Approved',
                    'value' => 118,
                ],

                [
                    'key' => 'rejected',
                    'label' => 'Rejected',
                    'value' => 12,
                ],

                [
                    'key' => 'disbursed',
                    'label' => 'Disbursed',
                    'value' => 95,
                ],

            ],

            'columns' => [

                [
                    'key' => 'month',
                    'label' => 'Month',
                    'type' => 'text',
                ],

                [
                    'key' => 'claims',
                    'label' => 'Claims',
                    'type' => 'number',
                ],

                [
                    'key' => 'approved',
                    'label' => 'Approved',
                    'type' => 'number',
                ],

                [
                    'key' => 'rejected',
                    'label' => 'Rejected',
                    'type' => 'number',
                ],

                [
                    'key' => 'disbursed',
                    'label' => 'Disbursed',
                    'type' => 'number',
                ],

            ],

            'rows' => [

                [
                    'month' => 'January',
                    'claims' => 20,
                    'approved' => 16,
                    'rejected' => 2,
                    'disbursed' => 15,
                ],

                [
                    'month' => 'February',
                    'claims' => 28,
                    'approved' => 24,
                    'rejected' => 1,
                    'disbursed' => 22,
                ],

                [
                    'month' => 'March',
                    'claims' => 35,
                    'approved' => 30,
                    'rejected' => 2,
                    'disbursed' => 28,
                ],

            ],

        ];
    }

    protected function campusSummary(array $filters): array
    {
        return [

            'context' => $this->buildContext(
                'Campus Claims Summary',
                'Claims grouped by campus.',
                $filters
            ),

            'cards' => [

                [
                    'key' => 'campus',
                    'label' => 'Campuses',
                    'value' => 3,
                ],

                [
                    'key' => 'members',
                    'label' => 'Members',
                    'value' => 280,
                ],

                [
                    'key' => 'claims',
                    'label' => 'Claims',
                    'value' => 145,
                ],

                [
                    'key' => 'amount',
                    'label' => 'Claim Amount',
                    'value' => 548000,
                ],

            ],

            'columns' => [

                [
                    'key' => 'campus',
                    'label' => 'Campus',
                    'type' => 'text',
                ],

                [
                    'key' => 'members',
                    'label' => 'Members',
                    'type' => 'number',
                ],

                [
                    'key' => 'claims',
                    'label' => 'Claims',
                    'type' => 'number',
                ],

                [
                    'key' => 'amount',
                    'label' => 'Amount',
                    'type' => 'currency',
                ],

            ],

            'rows' => [

                [
                    'campus' => 'Main Campus',
                    'members' => 120,
                    'claims' => 60,
                    'amount' => 235000,
                ],

                [
                    'campus' => 'North Campus',
                    'members' => 90,
                    'claims' => 45,
                    'amount' => 168000,
                ],

                [
                    'campus' => 'South Campus',
                    'members' => 70,
                    'claims' => 40,
                    'amount' => 145000,
                ],

            ],

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Financial Reports
    |--------------------------------------------------------------------------
    */

    protected function disbursementSummary(array $filters): array
    {
        return [

            'context' => $this->buildContext(
                'Disbursement Summary',
                'Released and paid claims.',
                $filters
            ),

            'cards' => [

                [
                    'key' => 'released',
                    'label' => 'Released',
                    'value' => 95,
                ],

                [
                    'key' => 'amount',
                    'label' => 'Amount Released',
                    'value' => 485000,
                ],

                [
                    'key' => 'average',
                    'label' => 'Average',
                    'value' => 5105,
                ],

                [
                    'key' => 'pending',
                    'label' => 'Pending Release',
                    'value' => 12,
                ],

            ],

            'columns' => [],

            'rows' => [],

        ];
    }

        /*
    |--------------------------------------------------------------------------
    | Allowance Reports
    |--------------------------------------------------------------------------
    */

    protected function allowanceUtilization(array $filters): array
    {
        // TODO:
        // Replace this mock data by calling your
        // calculateMemberBalanceData() method.

        $rows = [

            [
                'member' => 'Juan Dela Cruz',
                'campus' => 'Main Campus',
                'allowance' => 25000,
                'approved' => 18000,
                'disbursed' => 15000,
                'remaining' => 7000,
                'cycle' => '2025 - 2027',
            ],

            [
                'member' => 'Maria Santos',
                'campus' => 'North Campus',
                'allowance' => 25000,
                'approved' => 22000,
                'disbursed' => 22000,
                'remaining' => 3000,
                'cycle' => '2025 - 2027',
            ],

            [
                'member' => 'Pedro Reyes',
                'campus' => 'South Campus',
                'allowance' => 25000,
                'approved' => 12000,
                'disbursed' => 9000,
                'remaining' => 13000,
                'cycle' => '2025 - 2027',
            ],

        ];

        return [

            'context' => $this->buildContext(
                'Allowance Utilization',
                'Member allowance usage for the selected cycle.',
                $filters
            ),

            'cards' => [

                [
                    'key' => 'allowance',
                    'label' => 'Total Allowance',
                    'value' => collect($rows)->sum('allowance'),
                ],

                [
                    'key' => 'approved',
                    'label' => 'Approved',
                    'value' => collect($rows)->sum('approved'),
                ],

                [
                    'key' => 'disbursed',
                    'label' => 'Disbursed',
                    'value' => collect($rows)->sum('disbursed'),
                ],

                [
                    'key' => 'remaining',
                    'label' => 'Remaining',
                    'value' => collect($rows)->sum('remaining'),
                ],

            ],

            'columns' => [

                [
                    'key' => 'member',
                    'label' => 'Member',
                    'type' => 'text',
                ],

                [
                    'key' => 'campus',
                    'label' => 'Campus',
                    'type' => 'text',
                ],

                [
                    'key' => 'allowance',
                    'label' => 'Allowance',
                    'type' => 'currency',
                ],

                [
                    'key' => 'approved',
                    'label' => 'Approved',
                    'type' => 'currency',
                ],

                [
                    'key' => 'disbursed',
                    'label' => 'Disbursed',
                    'type' => 'currency',
                ],

                [
                    'key' => 'remaining',
                    'label' => 'Remaining',
                    'type' => 'currency',
                ],

                [
                    'key' => 'cycle',
                    'label' => 'Cycle',
                    'type' => 'text',
                ],

            ],

            'rows' => $rows,

        ];
    }



    protected function allowanceForfeiture(array $filters): array
    {
        $rows = [

            [
                'member' => 'Maria Santos',
                'campus' => 'North Campus',
                'cycle' => '2023 - 2025',
                'forfeited' => 2500,
            ],

            [
                'member' => 'Pedro Reyes',
                'campus' => 'South Campus',
                'cycle' => '2023 - 2025',
                'forfeited' => 6000,
            ],

            [
                'member' => 'Ana Cruz',
                'campus' => 'Main Campus',
                'cycle' => '2023 - 2025',
                'forfeited' => 1250,
            ],

        ];

        return [

            'context' => $this->buildContext(
                'Allowance Forfeiture',
                'Unused allowance after the end of a cycle.',
                $filters
            ),

            'cards' => [

                [
                    'key' => 'members',
                    'label' => 'Members',
                    'value' => count($rows),
                ],

                [
                    'key' => 'forfeited',
                    'label' => 'Forfeited Amount',
                    'value' => collect($rows)->sum('forfeited'),
                ],

            ],

            'columns' => [

                [
                    'key' => 'member',
                    'label' => 'Member',
                    'type' => 'text',
                ],

                [
                    'key' => 'campus',
                    'label' => 'Campus',
                    'type' => 'text',
                ],

                [
                    'key' => 'cycle',
                    'label' => 'Cycle',
                    'type' => 'text',
                ],

                [
                    'key' => 'forfeited',
                    'label' => 'Forfeited',
                    'type' => 'currency',
                ],

            ],

            'rows' => $rows,

        ];
    }

        /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function buildContext(
        string $title,
        string $description,
        array $filters
    ): array {

        return [

            'title' => $title,

            'description' => $description,

            'generated_at' => Carbon::now()->format('F d, Y h:i A'),

            'filters' => [

                [
                    'label' => 'Campus',
                    'value' => $filters['campus_id'] ?? 'All Campuses',
                ],

                [
                    'label' => 'Cycle',
                    'value' => $filters['cycle'] ?? 'Current Cycle',
                ],

                [
                    'label' => 'Date Range',
                    'value' => ($filters['date_from'] ?? '-')
                        .' to '.
                        ($filters['date_to'] ?? '-'),
                ],

            ],

        ];
    }

    /**
     * Mock campuses.
     */
    public function getCampuses(): array
    {
        return [

            ['id' => 1, 'name' => 'Main Campus'],

            ['id' => 2, 'name' => 'North Campus'],

            ['id' => 3, 'name' => 'South Campus'],

        ];
    }

    /**
     * Mock members.
     */
    public function getMembers(): array
    {
        return [

            ['id' => 1, 'name' => 'Juan Dela Cruz'],

            ['id' => 2, 'name' => 'Maria Santos'],

            ['id' => 3, 'name' => 'Pedro Reyes'],

            ['id' => 4, 'name' => 'Ana Cruz'],

        ];
    }

    /**
     * Mock allowance cycles.
     */
    public function getAllowanceCycles(): array
    {
        return [

            [
                'value' => 'current',
                'label' => 'Current Cycle',
            ],

            [
                'value' => '2025-2027',
                'label' => '2025 - 2027',
            ],

            [
                'value' => '2023-2025',
                'label' => '2023 - 2025',
            ],

        ];
    }
}