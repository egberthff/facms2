<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Claim;
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
        // 1. Build a base query that applies all global filters
        $baseQuery = Claim::query()
            ->when(!empty($filters['campus_id']), function ($query) use ($filters) {
                return $query->whereHas('member', function ($subQuery) use ($filters) {
                    return $subQuery->where('campus_id', $filters['campus_id']);
                });
            })
            ->when(!empty($filters['claim_type']), function ($query) use ($filters) {
                return $query->where('claim_type_id', $filters['claim_type']);
            })
            ->when(!empty($filters['member_id']), function ($query) use ($filters) {
                return $query->where('member_id', $filters['member_id']);
            });

        // 2. Define the months you want to loop through
        $months = [
            ['name' => 'January', 'num' => '01'],
            ['name' => 'February', 'num' => '02'],
            ['name' => 'March', 'num' => '03'],
            ['name' => 'April', 'num' => '04'],
            ['name' => 'May', 'num' => '05'],
            ['name' => 'June', 'num' => '06'],
            ['name' => 'July', 'num' => '07'],
            ['name' => 'August', 'num' => '08'],
            ['name' => 'September', 'num' => '09'],
            ['name' => 'October', 'num' => '10'],
            ['name' => 'November', 'num' => '11'],
            ['name' => 'December', 'num' => '12'],
        ];

        $rows = [];
        $totalClaims = 0;
        $totalPending = 0;
        $totalProcessing = 0;
        $totalApproved = 0;
        $totalRejected = 0;
        $totalDisbursed = 0;

        // 3. Loop through months to calculate dynamic rows
        foreach ($months as $month) {
            $year = (!empty($filters['date_from'])) ? date('Y', strtotime($filters['date_from'])) : date('Y'); // Or parse from $filters['date_from'] if needed

            // Clone base query to prevent query pollution across iterations
            $claimsCount = (clone $baseQuery)
                ->whereYear('submitted_at', $year)
                ->whereMonth('submitted_at', $month['num'])
                ->count();

            $pendingCount = (clone $baseQuery)
                ->whereYear('submitted_at', $year)
                ->whereMonth('submitted_at', $month['num'])
                ->where('status', 'Pending') // Example status match
                ->count();

            $processingCount = (clone $baseQuery)
                ->whereYear('submitted_at', $year)
                ->whereMonth('submitted_at', $month['num'])
                ->where('status', 'Processing') // Example status match
                ->count();


            $approvedCount = (clone $baseQuery)
                ->whereYear('submitted_at', $year)
                ->whereMonth('submitted_at', $month['num'])
                ->where('status', 'Approved') // Example status match
                ->count();

            $rejectedCount = (clone $baseQuery)
                ->whereYear('submitted_at', $year)
                ->whereMonth('submitted_at', $month['num'])
                ->where('status', 'Rejected')
                ->count();

            $disbursedCount = (clone $baseQuery)
                ->whereYear('submitted_at', $year)
                ->whereMonth('submitted_at', $month['num'])
                ->where('status', 'Disbursed')
                ->count();

            // Accumulate totals for top-level summary cards
            $totalClaims += $claimsCount;
            $totalPending += $pendingCount;
            $totalProcessing += $processingCount;
            $totalApproved += $approvedCount;
            $totalRejected += $rejectedCount;
            $totalDisbursed += $disbursedCount;

            $rows[] = [
                'month' => $month['name'],
                'claims' => $claimsCount,
                'pending' => $totalPending,
                'processing' => $totalProcessing,
                'approved' => $approvedCount,
                'rejected' => $rejectedCount,
                'disbursed' => $disbursedCount,
            ];
        }

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
                    'value' => $totalClaims,
                ],
                [
                    'key' => 'pending',
                    'label' => 'Pending',
                    'value' => $totalPending,
                ],
                [
                    'key' => 'processing',
                    'label' => 'Processing',
                    'value' => $totalProcessing,
                ],
                [
                    'key' => 'approved',
                    'label' => 'Approved',
                    'value' => $totalApproved,
                ],
                [
                    'key' => 'rejected',
                    'label' => 'Rejected',
                    'value' => $totalRejected,
                ],
                [
                    'key' => 'disbursed',
                    'label' => 'Disbursed',
                    'value' => $totalDisbursed,
                ],
            ],

            'columns' => [
                ['key' => 'month', 'label' => 'Month', 'type' => 'text'],
                ['key' => 'claims', 'label' => 'Claims', 'type' => 'number'],
                ['key' => 'pending', 'label' => 'Pending', 'type' => 'number'],
                ['key' => 'processing', 'label' => 'Processing', 'type' => 'number'],
                ['key' => 'approved', 'label' => 'Approved', 'type' => 'number'],
                ['key' => 'rejected', 'label' => 'Rejected', 'type' => 'number'],
                ['key' => 'disbursed', 'label' => 'Disbursed', 'type' => 'number'],
            ],

            'rows' => $rows,
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