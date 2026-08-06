<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Claim;
use App\Models\Campus;
use App\Models\Member;
use App\Models\SystemSetting;
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

        // 2. Define the months loop config
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

        $targetStatus = !empty($filters['status']) ? $filters['status'] : null;

        // 3. Loop through months to calculate data
        foreach ($months as $month) {
            $year = (!empty($filters['date_from'])) ? date('Y', strtotime($filters['date_from'])) : date('Y');

            $claimsCount = (clone $baseQuery)
                ->whereYear('submitted_at', $year)
                ->whereMonth('submitted_at', $month['num'])
                ->when(!empty($filters['status']), function ($query) use ($filters) {
                    return $query->where('status', $filters['status']);
                })
                ->count();

            $pendingCount = ($targetStatus === null || $targetStatus === 'Pending') ? (clone $baseQuery)
                ->whereYear('submitted_at', $year)
                ->whereMonth('submitted_at', $month['num'])
                ->where('status', 'Pending')
                ->count() : 0;

            $processingCount = ($targetStatus === null || $targetStatus === 'Processing') ? (clone $baseQuery)
                ->whereYear('submitted_at', $year)
                ->whereMonth('submitted_at', $month['num'])
                ->where('status', 'Processing')
                ->count() : 0;

            $approvedCount = ($targetStatus === null || $targetStatus === 'Approved') ? (clone $baseQuery)
                ->whereYear('submitted_at', $year)
                ->whereMonth('submitted_at', $month['num'])
                ->where('status', 'Approved')
                ->count() : 0;

            $rejectedCount = ($targetStatus === null || $targetStatus === 'Rejected') ? (clone $baseQuery)
                ->whereYear('submitted_at', $year)
                ->whereMonth('submitted_at', $month['num'])
                ->where('status', 'Rejected')
                ->count() : 0;

            $disbursedCount = ($targetStatus === null || $targetStatus === 'Disbursed') ? (clone $baseQuery)
                ->whereYear('submitted_at', $year)
                ->whereMonth('submitted_at', $month['num'])
                ->where('status', 'Disbursed')
                ->count() : 0;

            $totalClaims += $claimsCount;
            $totalPending += $pendingCount;
            $totalProcessing += $processingCount;
            $totalApproved += $approvedCount;
            $totalRejected += $rejectedCount;
            $totalDisbursed += $disbursedCount;

            // Build the base row
            $row = [
                'month' => $month['name'],
                'claims' => $claimsCount,
            ];

            // Only add status keys to row data if they are relevant to the view
            if ($targetStatus === null || $targetStatus === 'Pending') $row['pending'] = $pendingCount;
            if ($targetStatus === null || $targetStatus === 'Processing') $row['processing'] = $processingCount;
            if ($targetStatus === null || $targetStatus === 'Approved') $row['approved'] = $approvedCount;
            if ($targetStatus === null || $targetStatus === 'Rejected') $row['rejected'] = $rejectedCount;
            if ($targetStatus === null || $targetStatus === 'Disbursed') $row['disbursed'] = $disbursedCount;

            $rows[] = $row;
        }

        // 4. Define Master Definitions for UI Structural Layouts
        $allCards = [
            'claims' => ['key' => 'claims', 'label' => 'Claims Filed', 'value' => $totalClaims],
            'pending' => ['key' => 'pending', 'label' => 'Pending', 'value' => $totalPending],
            'processing' => ['key' => 'processing', 'label' => 'Processing', 'value' => $totalProcessing],
            'approved' => ['key' => 'approved', 'label' => 'Approved', 'value' => $totalApproved],
            'rejected' => ['key' => 'rejected', 'label' => 'Rejected', 'value' => $totalRejected],
            'disbursed' => ['key' => 'disbursed', 'label' => 'Disbursed', 'value' => $totalDisbursed],
        ];

        $allColumns = [
            'month' => ['key' => 'month', 'label' => 'Month', 'type' => 'text'],
            'claims' => ['key' => 'claims', 'label' => 'Claims', 'type' => 'number'],
            'pending' => ['key' => 'pending', 'label' => 'Pending', 'type' => 'number'],
            'processing' => ['key' => 'processing', 'label' => 'Processing', 'type' => 'number'],
            'approved' => ['key' => 'approved', 'label' => 'Approved', 'type' => 'number'],
            'rejected' => ['key' => 'rejected', 'label' => 'Rejected', 'type' => 'number'],
            'disbursed' => ['key' => 'disbursed', 'label' => 'Disbursed', 'type' => 'number'],
        ];

        // 5. Filter out structural layout items based on status
        if ($targetStatus === null) {
            $cardsOutput = array_values($allCards);
            $columnsOutput = array_values($allColumns);
        } else {
            $statusKey = strtolower($targetStatus);

            // Always display the summary totals card alongside the targeted status card
            $cardsOutput = [$allCards['claims'], $allCards[$statusKey]];
            // Always display month and total claims columns alongside the targeted status column
            $columnsOutput = [$allColumns['month'], $allColumns['claims'], $allColumns[$statusKey]];
        }

        return [
            'context' => $this->buildContext(
                'Monthly Claims Summary',
                'Monthly claim statistics.',
                $filters
            ),
            'cards' => $cardsOutput,
            'columns' => $columnsOutput,
            'rows' => $rows,
        ];
    }


    // protected function monthlySummary(array $filters): array
    // {
    //     // 1. Build a base query that applies all global filters
    //     $baseQuery = Claim::query()
    //         ->when(!empty($filters['campus_id']), function ($query) use ($filters) {
    //             return $query->whereHas('member', function ($subQuery) use ($filters) {
    //                 return $subQuery->where('campus_id', $filters['campus_id']);
    //             });
    //         })
    //         ->when(!empty($filters['claim_type']), function ($query) use ($filters) {
    //             return $query->where('claim_type_id', $filters['claim_type']);
    //         })
    //         ->when(!empty($filters['member_id']), function ($query) use ($filters) {
    //             return $query->where('member_id', $filters['member_id']);
    //         })
    //          ->when(!empty($filters['status']), function ($query) use ($filters) {
    //             return $query->where('status', $filters['status']);
    //         });

    //     // 2. Define the months you want to loop through
    //     $months = [
    //         ['name' => 'January', 'num' => '01'],
    //         ['name' => 'February', 'num' => '02'],
    //         ['name' => 'March', 'num' => '03'],
    //         ['name' => 'April', 'num' => '04'],
    //         ['name' => 'May', 'num' => '05'],
    //         ['name' => 'June', 'num' => '06'],
    //         ['name' => 'July', 'num' => '07'],
    //         ['name' => 'August', 'num' => '08'],
    //         ['name' => 'September', 'num' => '09'],
    //         ['name' => 'October', 'num' => '10'],
    //         ['name' => 'November', 'num' => '11'],
    //         ['name' => 'December', 'num' => '12'],
    //     ];

    //     $rows = [];
    //     $totalClaims = 0;
    //     $totalPending = 0;
    //     $totalProcessing = 0;
    //     $totalApproved = 0;
    //     $totalRejected = 0;
    //     $totalDisbursed = 0;

    //     $targetStatus = !empty($filters['status']) ? $filters['status'] : null;

    //     // 3. Loop through months to calculate dynamic rows
    //     foreach ($months as $month) {
    //         $year = (!empty($filters['date_from'])) ? date('Y', strtotime($filters['date_from'])) : date('Y');

    //         // Clone base query to prevent query pollution across iterations
    //         $claimsCount = (clone $baseQuery)
    //             ->whereYear('submitted_at', $year)
    //             ->whereMonth('submitted_at', $month['num'])
    //             ->count();

    //         // Dynamically compute columns or zero them out if filtered out
    //         $pendingCount = ($targetStatus === null || $targetStatus === 'Pending') ? (clone $baseQuery)
    //             ->whereYear('submitted_at', $year)
    //             ->whereMonth('submitted_at', $month['num'])
    //             ->where('status', 'Pending')
    //             ->count() : 0;

    //         $processingCount = ($targetStatus === null || $targetStatus === 'Processing') ? (clone $baseQuery)
    //             ->whereYear('submitted_at', $year)
    //             ->whereMonth('submitted_at', $month['num'])
    //             ->where('status', 'Processing') // Example status match
    //             ->count() : 0;


    //         $approvedCount = ($targetStatus === null || $targetStatus === 'Approved') ? (clone $baseQuery)
    //             ->whereYear('submitted_at', $year)
    //             ->whereMonth('submitted_at', $month['num'])
    //             ->where('status', 'Approved') // Example status match
    //             ->count() : 0;

    //         $rejectedCount = ($targetStatus === null || $targetStatus === 'Rejected') ? (clone $baseQuery)
    //             ->whereYear('submitted_at', $year)
    //             ->whereMonth('submitted_at', $month['num'])
    //             ->where('status', 'Rejected')
    //             ->count() : 0;

    //         $disbursedCount = ($targetStatus === null || $targetStatus === 'Disbursed') ? (clone $baseQuery)
    //             ->whereYear('submitted_at', $year)
    //             ->whereMonth('submitted_at', $month['num'])
    //             ->where('status', 'Disbursed')
    //             ->count() : 0;

    //         // Accumulate totals for top-level summary cards
    //         $totalClaims += $claimsCount;
    //         $totalPending += $pendingCount;
    //         $totalProcessing += $processingCount;
    //         $totalApproved += $approvedCount;
    //         $totalRejected += $rejectedCount;
    //         $totalDisbursed += $disbursedCount;

    //         $rows[] = [
    //             'month' => $month['name'],
    //             'claims' => $claimsCount,
    //             'pending' => $totalPending,
    //             'processing' => $totalProcessing,
    //             'approved' => $approvedCount,
    //             'rejected' => $rejectedCount,
    //             'disbursed' => $disbursedCount,
    //         ];
    //     }

    //     return [
    //         'context' => $this->buildContext(
    //             'Monthly Claims Summary',
    //             'Monthly claim statistics.',
    //             $filters
    //         ),

    //         'cards' => [
    //             ['key' => 'claims', 'label' => 'Claims Filed', 'value' => $totalClaims,],
    //             ['key' => 'pending', 'label' => 'Pending', 'value' => $totalPending,],
    //             ['key' => 'processing', 'label' => 'Processing', 'value' => $totalProcessing,],
    //             ['key' => 'approved', 'label' => 'Approved', 'value' => $totalApproved,],
    //             ['key' => 'rejected', 'label' => 'Rejected', 'value' => $totalRejected,],
    //             ['key' => 'disbursed', 'label' => 'Disbursed', 'value' => $totalDisbursed,],
    //         ],

    //         'columns' => [
    //             ['key' => 'month', 'label' => 'Month', 'type' => 'text'],
    //             ['key' => 'claims', 'label' => 'Claims', 'type' => 'number'],
    //             ['key' => 'pending', 'label' => 'Pending', 'type' => 'number'],
    //             ['key' => 'processing', 'label' => 'Processing', 'type' => 'number'],
    //             ['key' => 'approved', 'label' => 'Approved', 'type' => 'number'],
    //             ['key' => 'rejected', 'label' => 'Rejected', 'type' => 'number'],
    //             ['key' => 'disbursed', 'label' => 'Disbursed', 'type' => 'number'],
    //         ],

    //         'rows' => $rows,
    //     ];
    // }


    protected function campusSummary(array $filters): array
    {
        // 1. Fetch campuses and dynamically apply the campus filter if present
        $campuses = Campus::query()
            ->where('is_active', true)
            ->when(!empty($filters['campus_id']), function ($query) use ($filters) {
                return $query->where('id', $filters['campus_id']);
            })
            ->get();

        $rows = [];
        $totalMembers = 0;
        $totalClaims = 0;
        $totalAmount = 0;

        // 2. Loop through each campus to aggregate values dynamically
        foreach ($campuses as $campus) {

            // Count members tied to this campus
            $membersCount = $campus->members()
                ->when(!empty($filters['member_id']), function ($query) use ($filters) {
                    return $query->where('id', $filters['member_id']);
                })
                ->count();

            // Build a subquery for claims filtered by our global contexts
            $claimsQuery = Claim::whereHas('member', function ($query) use ($campus) {
                    return $query->where('campus_id', $campus->id);
                })
                ->when(!empty($filters['claim_type']), function ($query) use ($filters) {
                    return $query->where('claim_type_id', $filters['claim_type']);
                })
                ->when(!empty($filters['member_id']), function ($query) use ($filters) {
                    return $query->where('member_id', $filters['member_id']);
                })
                ->when(!empty($filters['status']), function ($query) use ($filters) {
                    return $query->where('status', $filters['status']);
                });

            // Get count and sum values from the query
            $claimsCount = (clone $claimsQuery)->count();
            $claimsAmount = (clone $claimsQuery)->sum('requested_amount'); // Replace 'amount' with your actual column name

            // Accumulate baseline totals for top cards
            $totalMembers += $membersCount;
            $totalClaims += $claimsCount;
            $totalAmount += $claimsAmount;

            $rows[] = [                                         
                'campus' => $campus->acronym, // Replace 'name' with your actual campus name column
                'members' => $membersCount,
                'claims' => $claimsCount,
                'amount' => $claimsAmount,
            ];
        }

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
                    'value' => $campuses->count(),
                ],
                [
                    'key' => 'members',
                    'label' => 'Members',
                    'value' => $totalMembers,
                ],
                [
                    'key' => 'claims',
                    'label' => 'Claims',
                    'value' => $totalClaims,
                ],
                [
                    'key' => 'amount',
                    'label' => 'Claim Amount',
                    'value' => $totalAmount,
                ],
            ],

            'columns' => [
                ['key' => 'campus', 'label' => 'Campus', 'type' => 'text'],
                ['key' => 'members', 'label' => 'Members', 'type' => 'number'],
                ['key' => 'claims', 'label' => 'Claims', 'type' => 'number'],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'currency'],
            ],

            'rows' => $rows,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Financial Reports
    |--------------------------------------------------------------------------
    */

    protected function disbursementSummary(array $filters): array
    {
        $baseQuery = $this->buildClaimQuery($filters)
            ->where('status', 'Disbursed');

        $releasedCount = $baseQuery->count();
        $amountReleased = (float) (clone $baseQuery)->sum('approved_amount');
        $averageReleased = $releasedCount > 0 ? $amountReleased / $releasedCount : 0.0;

        $pendingReleaseCount = $this->buildClaimQuery($filters)
            ->where('status', 'Approved')
            ->count();

        $rows = Campus::query()
            ->where('is_active', true)
            ->when(!empty($filters['campus_id']), function ($query) use ($filters) {
                return $query->where('id', $filters['campus_id']);
            })
            ->get()
            ->map(function ($campus) use ($filters) {
                $campusClaims = Claim::whereHas('member', function ($query) use ($campus) {
                        return $query->where('campus_id', $campus->id);
                    })
                    ->when(!empty($filters['claim_type']), function ($query) use ($filters) {
                        return $query->where('claim_type_id', $filters['claim_type']);
                    })
                    ->when(!empty($filters['member_id']), function ($query) use ($filters) {
                        return $query->where('member_id', $filters['member_id']);
                    });

                $released = (clone $campusClaims)->where('status', 'Disbursed')->count();
                $amount = (float) (clone $campusClaims)->where('status', 'Disbursed')->sum('approved_amount');
                $average = $released > 0 ? $amount / $released : 0.0;

                return [
                    'campus' => $campus->acronym,
                    'released' => $released,
                    'amount' => $amount,
                    'average' => round($average, 2),
                ];
            })
            ->toArray();

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
                    'value' => $releasedCount,
                ],
                [
                    'key' => 'amount',
                    'label' => 'Amount Released',
                    'value' => $amountReleased,
                ],
                [
                    'key' => 'average',
                    'label' => 'Average',
                    'value' => round($averageReleased, 2),
                ],
                [
                    'key' => 'pending',
                    'label' => 'Pending Release',
                    'value' => $pendingReleaseCount,
                ],
            ],

            'columns' => [
                ['key' => 'campus', 'label' => 'Campus', 'type' => 'text'],
                ['key' => 'released', 'label' => 'Released', 'type' => 'number'],
                ['key' => 'amount', 'label' => 'Amount Released', 'type' => 'currency'],
                ['key' => 'average', 'label' => 'Average', 'type' => 'currency'],
            ],

            'rows' => $rows,
        ];
    }

        /*
    |--------------------------------------------------------------------------
    | Allowance Reports
    |--------------------------------------------------------------------------
    */

    protected function allowanceUtilization(array $filters): array
    {
        [$cycleStart, $cycleEnd, $cycleLabel] = $this->resolveCycleBounds($filters['cycle'] ?? 'current');

        $members = Member::query()
            ->active()
            ->when(!empty($filters['campus_id']), fn($query) => $query->where('campus_id', $filters['campus_id']))
            ->when(!empty($filters['member_id']), fn($query) => $query->where('id', $filters['member_id']))
            ->with('campus')
            ->get();

        $rows = $members->map(function (Member $member) use ($cycleStart, $cycleEnd, $cycleLabel) {
            $cycleClaims = $member->claims()
                ->whereBetween('submitted_at', [$cycleStart, $cycleEnd]);

            $latestClaim = (clone $cycleClaims)
                ->latest('submitted_at')
                ->first();

            $baseLimit = $latestClaim && $latestClaim->annual_limit
                ? (float) $latestClaim->annual_limit
                : (float) SystemSetting::annualLimit($member->campus_id)->first()?->setting_value ?? 23000.00;

            $approvedAmount = (float) (clone $cycleClaims)
                ->whereIn('status', ['Approved', 'Disbursed'])
                ->sum('approved_amount');

            $disbursedAmount = (float) (clone $cycleClaims)
                ->where('status', 'Disbursed')
                ->sum('approved_amount');

            $remaining = max(0, $baseLimit - $disbursedAmount);

            return [
                'member' => $member->full_name,
                'campus' => $member->campus?->acronym ?? 'Unassigned',
                'allowance' => $baseLimit,
                'approved' => $approvedAmount,
                'disbursed' => $disbursedAmount,
                'remaining' => $remaining,
                'cycle' => $cycleLabel,
            ];
        })->toArray();

        return [
            'context' => $this->buildContext(
                'Allowance Utilization',
                'Member allowance usage for the selected cycle.',
                $filters
            ),

            'cards' => [
                ['key' => 'allowance', 'label' => 'Total Allowance', 'value' => collect($rows)->sum('allowance')],
                ['key' => 'approved', 'label' => 'Approved', 'value' => collect($rows)->sum('approved')],
                ['key' => 'disbursed', 'label' => 'Disbursed', 'value' => collect($rows)->sum('disbursed')],
                ['key' => 'remaining', 'label' => 'Remaining', 'value' => collect($rows)->sum('remaining')],
            ],

            'columns' => [
                ['key' => 'member', 'label' => 'Member', 'type' => 'text'],
                ['key' => 'campus', 'label' => 'Campus', 'type' => 'text'],
                ['key' => 'allowance', 'label' => 'Allowance', 'type' => 'currency'],
                ['key' => 'approved', 'label' => 'Approved', 'type' => 'currency'],
                ['key' => 'disbursed', 'label' => 'Disbursed', 'type' => 'currency'],
                ['key' => 'remaining', 'label' => 'Remaining', 'type' => 'currency'],
                ['key' => 'cycle', 'label' => 'Cycle', 'type' => 'text'],
            ],

            'rows' => $rows,
        ];
    }



    protected function allowanceForfeiture(array $filters): array
    {
        [$cycleStart, $cycleEnd, $cycleLabel] = $this->resolveCycleBounds($filters['cycle'] ?? 'current');

        $members = Member::query()
            ->active()
            ->when(!empty($filters['campus_id']), fn($query) => $query->where('campus_id', $filters['campus_id']))
            ->when(!empty($filters['member_id']), fn($query) => $query->where('id', $filters['member_id']))
            ->with('campus')
            ->get();

        $rows = $members->map(function (Member $member) use ($cycleStart, $cycleEnd, $cycleLabel) {
            $cycleClaims = $member->claims()
                ->whereBetween('submitted_at', [$cycleStart, $cycleEnd]);

            $latestClaim = (clone $cycleClaims)
                ->latest('submitted_at')
                ->first();

            $baseLimit = $latestClaim && $latestClaim->annual_limit
                ? (float) $latestClaim->annual_limit
                : (float) SystemSetting::annualLimit($member->campus_id)->first()?->setting_value ?? 23000.00;

            $claimedAmount = (float) (clone $cycleClaims)
                ->whereIn('status', ['Approved', 'Disbursed'])
                ->sum('approved_amount');

            $forfeited = max(0, $baseLimit - $claimedAmount);

            return [
                'member' => $member->full_name,
                'campus' => $member->campus?->acronym ?? 'Unassigned',
                'cycle' => $cycleLabel,
                'allowance' => $baseLimit,
                'claimed' => $claimedAmount,
                'forfeited' => $forfeited,
            ];
        })->toArray();

        return [

            'context' => $this->buildContext(
                'Allowance Forfeiture',
                'Unused allowance after the end of a cycle.',
                $filters
            ),

            'cards' => [
                ['key' => 'members', 'label' => 'Members', 'value' => count($rows)],
                ['key' => 'allowance', 'label' => 'Total Allowance', 'value' => collect($rows)->sum('allowance')],
                ['key' => 'claimed', 'label' => 'Claimed', 'value' => collect($rows)->sum('claimed')],
                ['key' => 'forfeited', 'label' => 'Forfeited Amount', 'value' => collect($rows)->sum('forfeited')],
            ],

            'columns' => [
                ['key' => 'member', 'label' => 'Member', 'type' => 'text'],
                ['key' => 'campus', 'label' => 'Campus', 'type' => 'text'],
                ['key' => 'cycle', 'label' => 'Cycle', 'type' => 'text'],
                ['key' => 'allowance', 'label' => 'Allowance', 'type' => 'currency'],
                ['key' => 'claimed', 'label' => 'Claimed', 'type' => 'currency'],
                ['key' => 'forfeited', 'label' => 'Forfeited Amount', 'type' => 'currency'],
            ],

            'rows' => $rows,

        ];
    }

        /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function buildClaimQuery(array $filters)
    {
        return Claim::query()
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
            })
            ->when(!empty($filters['status']), function ($query) use ($filters) {
                return $query->where('status', $filters['status']);
            })
            ->when(!empty($filters['date_from']), function ($query) use ($filters) {
                return $query->whereDate('submitted_at', '>=', $filters['date_from']);
            })
            ->when(!empty($filters['date_to']), function ($query) use ($filters) {
                return $query->whereDate('submitted_at', '<=', $filters['date_to']);
            });
    }

    private function resolveCycleBounds(?string $cycle): array
    {
        if (empty($cycle) || $cycle === 'current') {
            $now = Carbon::now();
            $startYear = $now->month >= 10 ? $now->year : $now->year - 1;
            $cycleStart = Carbon::create($startYear, 10, 1, 0, 0, 0);
            $cycleEnd = (clone $cycleStart)->addYears(2)->subSecond();
            return [$cycleStart, $cycleEnd, 'Current Cycle'];
        }

        $parts = explode('-', $cycle);
        if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
            $cycleStart = Carbon::create((int) $parts[0], 10, 1, 0, 0, 0);
            $cycleEnd = (clone $cycleStart)->addYears(2)->subSecond();
            return [$cycleStart, $cycleEnd, trim($cycle)];
        }

        return $this->resolveCycleBounds('current');
    }

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

    public function getMembers(): array
    {
        return Member::query()
            ->active()
            ->orderBy('last_name')
            ->with('campus')
            ->get()
            ->map(function (Member $member) {
                return [
                    'id' => $member->id,
                    'name' => $member->full_name,
                ];
            })
            ->toArray();
    }

    public function getAllowanceCycles(): array
    {
        $cycles = [
            ['value' => 'current', 'label' => 'Current Cycle'],
        ];

        $now = Carbon::now();
        $pivotYear = $now->month >= 10 ? $now->year : $now->year - 1;

        for ($offset = 2; $offset <= 6; $offset += 2) {
            $startYear = $pivotYear - $offset;
            $cycles[] = [
                'value' => "{$startYear}-" . ($startYear + 2),
                'label' => "{$startYear} - " . ($startYear + 2),
            ];
        }

        return $cycles;
    }
}