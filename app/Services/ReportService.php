<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Claim;
use App\Models\Campus;
use App\Models\Member;
use App\Models\ContributionBatch;
use App\Models\SystemSetting;
use App\Models\ClaimsCycle;
use InvalidArgumentException;

class ReportService
{
    public function __construct(private CycleService $cycleService)
    {
    }

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

            'collection-summary'
                => $this->collectionSummary($filters),

            'allowance-utilization'
                => $this->allowanceUtilization($filters),

            'allowance-forfeiture'
                => $this->allowanceForfeiture($filters),

            'payroll-deduction-request'
                => $this->payrollDeductionRequest($filters),

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
        $monthlyStats = Claim::query()
            ->selectRaw("
                MONTH(submitted_at) as month_num,
                COUNT(*) as total_claims,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'Processing' THEN 1 ELSE 0 END) as processing_count,
                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected_count,
                SUM(CASE WHEN status = 'Disbursed' THEN 1 ELSE 0 END) as disbursed_count
            ")
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
            ->when(!empty($filters['reporting_year']), function ($query) use ($filters) {
                return $query->whereYear('submitted_at', $filters['reporting_year']);
            })
            ->when(!empty($filters['status']), function ($query) use ($filters) {
                return $query->where('status', $filters['status']);
            })
            ->groupByRaw('MONTH(submitted_at)')
            ->get()
            ->keyBy('month_num'); // Keys the data by month integer (1 to 12)

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

        // 2. Loop your months array to cleanly map the data with defaults
        foreach ($months as $month) {
            // Cast month number string '01' to integer 1 to match the database key
            $monthInt = (int)$month['num'];
            
            // Grab the database row for this month, or fallback to an empty object if no rows exist
            $data = $monthlyStats->get($monthInt);
        
            $claimsCount = $data ? $data->total_claims : 0;
        
            $pendingCount = ($targetStatus === null || $targetStatus === 'Pending') 
                ? ($data ? (int)$data->pending_count : 0) 
                : 0;
        
            $processingCount = ($targetStatus === null || $targetStatus === 'Processing') 
                ? ($data ? (int)$data->processing_count : 0) 
                : 0;
        
            $approvedCount = ($targetStatus === null || $targetStatus === 'Approved') 
                ? ($data ? (int)$data->approved_count : 0) 
                : 0;
        
            $rejectedCount = ($targetStatus === null || $targetStatus === 'Rejected') 
                ? ($data ? (int)$data->rejected_count : 0) 
                : 0;
        
            $disbursedCount = ($targetStatus === null || $targetStatus === 'Disbursed') 
                ? ($data ? (int)$data->disbursed_count : 0) 
                : 0;

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
        \Log::error($filters['campus_id']);
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

    protected function collectionSummary(array $filters): array
    {
        $campusId = $filters['campus_id'] ?? null;
        $summaryService = app(\App\Services\CollectionSummaryService::class);
        $summary = $summaryService->latestForCampus($campusId);

        $rows = $summary?->items ?? collect();

        $collections = $rows->where('section', 'collections')->values();
        $disbursements = $rows->where('section', 'disbursements')->values();

        return [
            'context' => $this->buildContext(
                'Collection Summary',
                'Campus treasury collection and disbursement summary.',
                $filters
            ),
            'cards' => [
                ['key' => 'collections', 'label' => 'Collections', 'value' => (float) $collections->sum('amount')],
                ['key' => 'disbursements', 'label' => 'Disbursements', 'value' => (float) $disbursements->sum('amount')],
            ],
            'columns' => [
                ['key' => 'label', 'label' => 'Description', 'type' => 'text'],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'currency'],
            ],
            'rows' => [
                ...$collections->map(fn ($item) => ['label' => $item->label, 'amount' => (float) $item->amount, 'section' => 'collections'])->toArray(),
                ...$disbursements->map(fn ($item) => ['label' => $item->label, 'amount' => (float) $item->amount, 'section' => 'disbursements'])->toArray(),
            ],
            'summary' => $summary ? [
                'id' => $summary->id,
                'title' => $summary->title,
                'period_label' => $summary->period_label,
            ] : null,
        ];
    }

        /*
    |--------------------------------------------------------------------------
    | Allowance Reports
    |--------------------------------------------------------------------------
    */

    protected function allowanceUtilization(array $filters): array
    {
        $members = Member::query()
            ->active()
            ->when(!empty($filters['campus_id']), fn($query) => $query->where('campus_id', $filters['campus_id']))
            ->when(!empty($filters['member_id']), fn($query) => $query->where('id', $filters['member_id']))
            ->with('campus')
            ->get();

        $rows = $members->map(function (Member $member) use ($filters) {
            [$cycleStart, $cycleEnd, $cycleLabel] = $this->resolveCycleBounds(
                $filters['cycle'] ?? 'current',
                $member->campus_id
            );
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
        // Fetch active members and eager load claims to eliminate the N+1 query performance bottleneck.
        $members = Member::query()
            ->active()
            ->when(!empty($filters['campus_id']), fn($query) => $query->where('campus_id', $filters['campus_id']))
            ->when(!empty($filters['member_id']), fn($query) => $query->where('id', $filters['member_id']))
            ->with(['campus', 'claims']) 
            ->get();

        // 3. Eager load annual_limit configurations for all applicable campuses
        $campusIds = $members->pluck('campus_id')->unique()->filter()->toArray();
        $settingsMap = SystemSetting::whereIn('campus_id', $campusIds)
            ->where('setting_key', 'annual_claim_limit')
            ->get()
            ->groupBy('campus_id');

        // 4. Map values strictly using secure, in-memory computations
        $rows = $members->map(function (Member $member) use ($filters, $settingsMap) {
            [$cycleStart, $cycleEnd, $cycleLabel] = $this->resolveCycleBounds(
                $filters['cycle'] ?? 'current',
                $member->campus_id
            );
            $targetCycle = $cycleLabel === 'Current Cycle'
                ? $this->cycleService->currentRecord($member->campus_id)->cycle_label
                : $cycleLabel;

            // Isolate claims submitted strictly inside this cycle's date boundaries
            $cycleClaims = $member->claims->filter(function ($claim) use ($cycleStart, $cycleEnd) {
                return $claim->submitted_at >= $cycleStart && $claim->submitted_at <= $cycleEnd;
            });

            // --- THE FIXED LOGIC HIERARCHY ---
            $campusSettings = $settingsMap->get($member->campus_id) ?? collect();

            // Priority 1: Check for an explicit historical setting recorded for this specific cycle
            $matchedSetting = $campusSettings->firstWhere('cycle_label', $targetCycle);

            if ($matchedSetting) {
                $baseLimit = (float) $matchedSetting->setting_value;
            } else {
                // Priority 2: Fall back to the snapshot on the latest claim if no cycle setting is found
                $latestClaim = $cycleClaims->sortByDesc('submitted_at')->first();

                if ($latestClaim && $latestClaim->annual_limit) {
                    $baseLimit = (float) $latestClaim->annual_limit;
                } else {
                    // Priority 3: Fall back to the global active system setting row as a last resort
                    $activeSetting = $campusSettings->firstWhere('cycle_label', null);
                    $baseLimit = $activeSetting ? (float) $activeSetting->setting_value : 0.00;
                }
            }
            // ---------------------------------

            // 5. Aggregate financial metrics accurately
            $claimedAmount = (float) $cycleClaims
                ->whereIn('status', ['Approved', 'Disbursed'])
                ->sum('approved_amount');

            // Ensure forfeited allowance never drops below zero
            $forfeited = max(0, $baseLimit - $claimedAmount);

            return [
                'member'    => $member->full_name,
                'campus'    => $member->campus?->acronym ?? 'Unassigned',
                'cycle'     => $cycleLabel,
                'allowance' => $baseLimit,
                'claimed'   => $claimedAmount,
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

    protected function payrollDeductionRequest(array $filters): array
    {
        $campusId = $filters['treasurer_campus_id'] ?? null;

        $query = ContributionBatch::query()
            ->with([
                'claim.member' => fn ($query) => $query->withoutGlobalScopes(),
                'claim.member.campus' => fn ($query) => $query->withoutGlobalScopes(),
                'claim.claimType',
            ])
            ->whereHas('contributionDetails')
            ->whereHas('claim', function ($query) use ($campusId, $filters) {
                $query->when(
                    empty($filters['status']),
                    fn ($query) => $query->whereIn('status', ['Approved', 'Disbursed'])
                )
                    ->when(!empty($filters['claim_month']), function ($query) use ($filters) {
                        [$year, $month] = explode('-', $filters['claim_month']);

                        $query->whereYear('submitted_at', $year)
                            ->whereMonth('submitted_at', $month);
                    })
                    ->whereHas('member', function ($query) use ($campusId, $filters) {
                        $query->withoutGlobalScopes()
                            ->where('membership_status', 'Active')
                            ->when($campusId, fn ($query) => $query->where('campus_id', $campusId))
                            ->when(!empty($filters['member_id']), fn ($query) => $query->whereKey($filters['member_id']));
                    });
            })
            ->when(!empty($filters['claim_type']), function ($query) use ($filters) {
                $query->whereHas('claim', fn ($claim) =>
                    $claim->where('claim_type_id', $filters['claim_type']));
            })
            ->when(!empty($filters['status']), function ($query) use ($filters) {
                $query->whereHas('claim', fn ($claim) =>
                    $claim->where('status', $filters['status']));
            })
            ->latest('generated_at');

        $rows = $query->get()
            ->map(function (ContributionBatch $batch) use ($filters) {
                $claim = $batch->claim;
                $claimant = $claim?->member;

                return [
                    'member' => $claimant?->full_name ?? 'Unknown Member',
                    'campus' => $claimant?->campus?->acronym ?? 'Unassigned',
                    'claim_type' => $claim?->claimType?->name ?? 'N/A',
                    'approved' => (float) ($claim?->approved_amount ?? 0),
                    'deduction' => (float) $batch->amount_per_member,
                    'coverage' => $this->formatPayrollCoverage($claim),
                ];
            })
            ->values()
            ->all();

        $totalDeduction = collect($rows)->sum('deduction');

        return [
            'context' => $this->buildContext(
                'Payroll Deduction Request',
                'All-campus payroll deductions for active claimants submitted in the selected month.',
                $filters
            ),
            'cards' => [
                ['key' => 'members', 'label' => 'Claimants', 'value' => count($rows)],
                ['key' => 'amount', 'label' => 'Total Deduction', 'value' => $totalDeduction],
            ],
            'columns' => [
                ['key' => 'member', 'label' => 'A Member-Claimant', 'type' => 'text'],
                ['key' => 'campus', 'label' => 'Campus', 'type' => 'text'],
                ['key' => 'claim_type', 'label' => 'Type of Claim', 'type' => 'text'],
                ['key' => 'approved', 'label' => 'Approved Claim', 'type' => 'currency'],
                ['key' => 'deduction', 'label' => 'Deduction', 'type' => 'currency'],
                ['key' => 'coverage', 'label' => 'Medical Aid Deduction Coverage', 'type' => 'text'],
            ],
            'rows' => $rows,
            'total_deduction' => $totalDeduction,
        ];
    }

    private function formatPayrollCoverage(?Claim $claim): string
    {
        $campusId = $claim?->member?->campus_id;
        $submittedAt = $claim?->submitted_at;

        if (!$campusId || !$submittedAt) {
            return 'N/A';
        }

        $cycle = ClaimsCycle::query()
            ->where('campus_id', $campusId)
            ->where('start_date', '<=', $submittedAt)
            ->where('end_date', '>=', $submittedAt)
            ->first();

        return $cycle
            ? $cycle->start_date->format('F Y') . '-' . $cycle->end_date->format('F Y')
            : 'N/A';
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

    private function resolveCycleBounds(?string $cycle, ?string $campusId = null): array
    {
        return $this->cycleService->resolve($cycle, $campusId);
    }

    protected function buildContext(
        string $title,
        string $description,
        array $filters
    ): array {
        // 1. Resolve readable names for IDs
        $campusName = 'All Campuses';
        if (!empty($filters['campus_id'])) {
            $campus = Campus::find($filters['campus_id']);
            $campusName = $campus ? $campus->acronym : 'All Campuses';
        }

        // 2. Format a clean date range string based on dates or reporting year
        $dateRange = 'All Dates';

        if (!empty($filters['claim_month'])) {
            $dateRange = 'Claims submitted in ' . Carbon::parse($filters['claim_month'] . '-01')->format('F Y');
        } elseif (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            // User explicitly picked specific date boundaries
            $from = !empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->format('M d, Y') : 'Beginning';
            $to = !empty($filters['date_to']) ? Carbon::parse($filters['date_to'])->format('M d, Y') : 'Present';
            $dateRange = "{$from} to {$to}";
        } elseif (!empty($filters['reporting_year'])) {
            // No custom dates picked, fall back to the selected reporting year scope
            $year = $filters['reporting_year'];
            $dateRange = "January {$year} to December {$year}";
        }

        return [
            'title' => $title,
            'description' => $description,
            'generated_at' => Carbon::now()->format('F d, Y h:i A'),
            'filters' => [
                [
                    'label' => 'Reporting Year',
                    'value' => $filters['reporting_year'] ?? 'All Years',
                ],
                [
                    'label' => 'Campus',
                    'value' => $campusName,
                ],
                // [
                //     'label' => 'Cycle',
                //     'value' => !empty($filters['cycle']) ? ucfirst($filters['cycle']) : 'Current Cycle',
                // ],
                [
                    'label' => 'Date Range',
                    'value' => $dateRange,
                ],
                ...(!empty($filters['payroll_month']) ? [[
                    'label' => 'Payroll Month',
                    'value' => Carbon::parse($filters['payroll_month'] . '-01')->format('F Y'),
                ]] : []),
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

        $campusIds = Campus::query()->where('is_active', true)->pluck('id');
        $storedCycles = collect();

        foreach ($campusIds as $campusId) {
            $this->cycleService->list($campusId);
            $storedCycles = $storedCycles->merge(
                ClaimsCycle::where('campus_id', $campusId)
                    ->orderByDesc('start_date')
                    ->limit(4)
                    ->pluck('cycle_label')
            );
        }

        foreach ($storedCycles->unique()->values() as $label) {
            $cycles[] = [
                'value' => $label,
                'label' => str_replace('-', ' - ', $label),
            ];
        }

        return $cycles;
    }
}