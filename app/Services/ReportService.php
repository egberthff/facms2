<?php

namespace App\Services;

use Carbon\Carbon;
use App\Enums\ClaimStatus;
use App\Models\Claim;
use App\Models\Campus;
use App\Models\Member;
use App\Models\ContributionBatch;
use App\Models\SystemSetting;
use App\Models\MemberClaimCycle;
use App\Models\ClaimType;
use App\Models\Scopes\CampusMemberScope;
use App\Services\MemberService;
use App\Services\ContributionService;
use App\Services\MemberClaimCycleService;
use App\Services\ClaimService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class ReportService
{
    private MemberClaimCycleService $memberCycleService;
    private MemberService $memberService;
    private ContributionService $contributionService;
    private ClaimService $claimService;
    public function __construct(
        MemberClaimCycleService $memberCycleService,
        MemberService $memberService,
        ContributionService $contributionService,
        ClaimService $claimService)
    {
        $this->memberCycleService = $memberCycleService;
        $this->memberService = $memberService;
        $this->contributionService = $contributionService;
        $this->claimService = $claimService;
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

            'faculty-deduction-summary'
                => $this->facultyDeductionSummary($filters),

            'medical-assistance-report'
                => $this->medicalAssistanceReport($filters),

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

        // Claim cycles are per member, so the cycle filter selects claims by their member cycle
        $cycleIds = !empty($filters['cycle'])
            ? $this->memberCycleService->cyclesForSelector($filters['cycle'])->pluck('id')
            : null;

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
                })
               ->when($cycleIds !== null, function ($query) use ($cycleIds) {
                    return $query->whereIn('member_claim_cycle_id', $cycleIds->all());
                });


            // Get count and sum values from the query
            $claimsCount = (clone $claimsQuery)->count();
            $claimsAmount = (clone $claimsQuery)->sum('requested_amount'); // Replace 'amount' with your actual column name

            // Accumulate baseline totals for top cards
            $totalMembers += $membersCount;
            $totalClaims += $claimsCount;
            $totalAmount += $claimsAmount;

            $rows[] = [                                         
                'campus' => $campus->acronym, 
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
        abort_unless(
            Auth::user()->hasRole('Federated Treasurer'), 
            403, 
            'Only the Federated Treasurer can generate this report.'
        );
        \Log::error(print_r(Auth::user()->hasRole('Federated Treasurer'), true));

        $campusId = $filters['campus_id'] ?? null;
        $campusData = $this->memberService->getCampus($campusId);
        $campusLookups = collect();
        if ($campusData instanceof Campus) {
            $campusLookups->put($campusData->id, $campusData->acronym);
        } elseif ($campusData instanceof \Illuminate\Support\Collection) {
            // Key by the UUID string so we can perform quick lookups
            $campusLookups = $campusData->pluck('acronym', 'id');
        }

        $summaryService = app(\App\Services\CollectionSummaryService::class);
        $transfers = $summaryService->transfersForCampus($campusId, $filters['from'] ?? null, $filters['to'] ?? null);

        $collections = collect($transfers['collections'] ?? [])
            ->map(fn ($item) => ['campus' => $campusLookups->get($item->campus) ?? 'N/A', 'label' => $item->label, 'amount' => (float) $item->amount, 'section' => 'Collection'])
            ->values();

        $disbursements = collect($transfers['disbursements'] ?? [])
            ->map(fn ($item) => ['campus' => $campusLookups->get($item->campus) ?? 'N/A', 'label' => $item->label, 'amount' => (float) $item->amount, 'section' => 'Disbursement'])
            ->values();

        $rows = $collections->merge($disbursements)->values();
        return [
            'context' => $this->buildContext(
                'Collection Summary',
                'Campus treasury collection and disbursement summary.',
                $filters
            ),
            'cards' => [
                ['key' => 'collections', 'label' => 'Collections', 'value' => (float) ($transfers['collection_total'] ?? 0)],
                ['key' => 'disbursements', 'label' => 'Disbursements', 'value' => (float) ($transfers['disbursement_total'] ?? 0)],
            ],
            'columns' => [
                ['key' => 'campus', 'label' => 'Campus', 'type' => 'text'],
                ['key' => 'label', 'label' => 'Description', 'type' => 'text'],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'currency'],
                ['key' => 'section', 'label' => 'Section', 'type' => 'text'],
            ],
            'rows' => $rows->toArray(),
            'summary' => [
                'id' => null,
                'title' => 'Approved transfers',
                'period_label' => $summaryService->buildPrintableData($campusId, $filters['from'] ?? null, $filters['to'] ?? null)['period_label'] ?? 'All approved transfers',
            ],
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
            ->get()
            ->keyBy('id');

        // Each member is measured against their own claim cycle(s), not a shared campus window
        $selector = $this->memberCycleService->normalizeSelector($filters['cycle'] ?? null);
        $cycles = $this->memberCycleService->cyclesForSelector($selector, $members->keys());
        $totals = $this->memberCycleService->totalsForCycles($cycles->pluck('id'));

        $rows = $cycles
            ->filter(fn (MemberClaimCycle $cycle) => $members->has($cycle->member_id))
            ->map(function (MemberClaimCycle $cycle) use ($members, $totals) {
                $member = $members->get($cycle->member_id);
                $cycleTotals = $totals->get($cycle->id);
                $baseLimit = (float) $cycle->claim_limit;

                return [
                    'member' => $member->full_name,
                    'campus' => $member->campus?->acronym ?? 'Unassigned',
                    'allowance' => $baseLimit,
                    'approved' => $cycleTotals->approved ?? 0.0,
                    'disbursed' => $cycleTotals->disbursed ?? 0.0,
                    'remaining' => max(0, $baseLimit - ($cycleTotals->consumed ?? 0.0)),
                    'cycle' => $cycle->label(),
                ];
            });

        // With no active cycle the member's next claim opens a fresh one at the full campus limit
        if ($selector === MemberClaimCycleService::SELECTOR_CURRENT) {
            $withCycle = $cycles->pluck('member_id')->flip();
            $rows = $rows->concat(
                $members->reject(fn (Member $member) => $withCycle->has($member->id))
                    ->map(function (Member $member) {
                        $baseLimit = (float) SystemSetting::getGlobalLimit($member->campus_id);

                        return [
                            'member' => $member->full_name,
                            'campus' => $member->campus?->acronym ?? 'Unassigned',
                            'allowance' => $baseLimit,
                            'approved' => 0.0,
                            'disbursed' => 0.0,
                            'remaining' => $baseLimit,
                            'cycle' => 'No active cycle',
                        ];
                    })
            );
        }

        $rows = $rows->sortBy('member')->values()->toArray();

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
        $members = Member::query()
            ->active()
            ->when(!empty($filters['campus_id']), fn($query) => $query->where('campus_id', $filters['campus_id']))
            ->when(!empty($filters['member_id']), fn($query) => $query->where('id', $filters['member_id']))
            ->with('campus')
            ->get()
            ->keyBy('id');

        // Forfeiture only exists once a member's own cycle has expired: `current`/`previous` take each
        // member's latest expired cycle, `all` every expired cycle. The cycle carries its limit snapshot.
        $cycles = $this->memberCycleService->cyclesForSelector($filters['cycle'] ?? null, $members->keys(), null, true);
        $totals = $this->memberCycleService->totalsForCycles($cycles->pluck('id'));

        $rows = $cycles
            ->filter(fn (MemberClaimCycle $cycle) => $members->has($cycle->member_id))
            ->map(function (MemberClaimCycle $cycle) use ($members, $totals) {
                $member = $members->get($cycle->member_id);
                $baseLimit = (float) $cycle->claim_limit;
                $claimedAmount = $totals->get($cycle->id)->consumed ?? 0.0;

                return [
                    'member'    => $member->full_name,
                    'campus'    => $member->campus?->acronym ?? 'Unassigned',
                    'cycle'     => $cycle->label(),
                    'allowance' => $baseLimit,
                    'claimed'   => $claimedAmount,
                    // Unused allowance never rolls over; it is forfeited when the cycle ends
                    'forfeited' => max(0, $baseLimit - $claimedAmount),
                ];
            })
            ->sortBy('member')
            ->values()
            ->toArray();

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
        abort_unless(
            Auth::user()->hasRole('Treasurer'), 
            403, 
            'Only the Campus Treasurer can generate this report.'
        );

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

    public function facultyDeductionSummary(array $filters): array
    {
        $request = new Request($filters);
        $campusId = $filters['campus_id'] ?? null;
    
        $dateFrom = isset($filters['date_from'])
            ? Carbon::parse($filters['date_from'])->format('F Y')
            : '';
    
        $dateTo = isset($filters['date_to'])
            ? Carbon::parse($filters['date_to'])->format('F Y')
            : '';
    
        $period = ($dateFrom && $dateTo)
            ? "{$dateFrom} to {$dateTo}"
            : '';
    
        /*
         * -------------------------------------------------------------
         * Fetch report data.
         *
         * getContributionPerMonth() remains the source of truth for
         * all contribution calculations.
         * -------------------------------------------------------------
         */
        $campus = $this->memberService->getCampus($campusId);
    
        $campusTreasurer = $this->memberService->getTreasurer(
            $filters,
            $campusId
        );
    
        $campusPresident = $this->memberService->getPresident(
            $filters,
            $campusId
        );
    
        $members = $this->memberService->getMembers(
            $request,
            $campusId
        );
    
        $contributionPerMonth = $this->contributionService
            ->getContributionPerMonth($request, $campusId);
            /*
         * -------------------------------------------------------------
         * SOURCE DATA
         * -------------------------------------------------------------
         *
         * Keep the original structure untouched.
         */
        $memberData = $contributionPerMonth['members'] ?? [];
    
        /*
         * -------------------------------------------------------------
         * DYNAMIC MONTH COLUMNS
         * -------------------------------------------------------------
         *
         * header_months_amount is already ordered by
         * getContributionPerMonth().
         *
         * Do not include "total" as a month column.
         */
        $monthColumns = [];
    
        foreach (
            ($contributionPerMonth['header_months_amount'] ?? []) as $month => $amount
        ) {
            if ($month === 'total') {
                continue;
            }
    
            $monthColumns[] = [
                'key' => $month,
                'label' => ucwords($month),
                'type' => 'currency',
            ];
        }
    
        /*
         * -------------------------------------------------------------
         * CNA SHARE YEAR COLUMNS
         * -------------------------------------------------------------
         *
         * The years are derived from the actual member data.
         *
         * Example:
         *
         * cna_share:
         * [
         *     2026 => 500
         * ]
         *
         * becomes:
         *
         * cna_share_2026
         */
        $cnaShareYears = [];
    
        foreach ($memberData as $member) {
            foreach (($member['cna_share'] ?? []) as $year => $amount) {
                $cnaShareYears[(string) $year] = true;
            }
        }
    
        $cnaShareYears = array_keys($cnaShareYears);
    
        sort($cnaShareYears, SORT_NUMERIC);
    
        $cnaShareColumns = [];
    
        foreach ($cnaShareYears as $year) {
            $cnaShareColumns[] = [
                'key' => "cna_share_{$year}",
                'label' => "CNA Share {$year}",
                'type' => 'currency',
            ];
        }
    
        /*
         * -------------------------------------------------------------
         * ANNUAL DUE YEAR COLUMNS
         * -------------------------------------------------------------
         */
        $annualDueYears = [];
    
        foreach ($memberData as $member) {
            foreach (($member['annual_due'] ?? []) as $year => $amount) {
                $annualDueYears[(string) $year] = true;
            }
        }
    
        $annualDueYears = array_keys($annualDueYears);
    
        sort($annualDueYears, SORT_NUMERIC);
    
        $annualDueColumns = [];
    
        foreach ($annualDueYears as $year) {
            $annualDueColumns[] = [
                'key' => "annual_due_{$year}",
                'label' => "Annual Due {$year}",
                'type' => 'currency',
            ];
        }
    
        /*
         * -------------------------------------------------------------
         * STANDARDIZED ROWS
         * -------------------------------------------------------------
         *
         * Flatten the nested member structure ONLY for the new
         * standardized "rows" property.
         *
         * The original nested structure remains available under
         * "table_data".
         */
        $rows = [];
    
        foreach ($memberData as $member) {
            $row = [
                'member_id' => $member['member_id'] ?? null,
                'member' => $member['name'] ?? 'Unknown Member',
            ];

            /*
            * CNA Share.
            */
           foreach (($member['cna_share'] ?? []) as $year => $amount) {
               $row["cna_share_{$year}"] = (float) $amount;
           }
   
           /*
            * Annual Due.
            */
           foreach (($member['annual_due'] ?? []) as $year => $amount) {
               $row["annual_due_{$year}"] = (float) $amount;
           }
    
            /*
             * Monthly contributions.
             */
            foreach (($member['months'] ?? []) as $month => $amount) {
                $row[$month] = (float) $amount;
            }
    
            /*
             * Total contribution.
             */
            $row['total_contributed'] = (float) (
                $member['total_contributed'] ?? 0
            );
    
            $rows[] = $row;
        }
    
        /*
         * -------------------------------------------------------------
         * SUMMARY TOTALS
         * -------------------------------------------------------------
         *
         * These values already exist in the contribution service.
         *
         * Do not sum the entire arrays because some structures contain
         * nested arrays.
         */
    
        $totalMonthlyCollectionTarget = (float) (
            $contributionPerMonth['total_monthly_collection_target']['total'] ?? 0
        );
    
        $actualMonthlyCollection = (float) (
            $contributionPerMonth['actual_monthly_collection']['total'] ?? 0
        );
    
        $balanceCollectible = (float) (
            $contributionPerMonth['balance_collectible']['total'] ?? 0
        );
    
        /*
         * -------------------------------------------------------------
         * OTHER SUMMARY TOTALS
         * -------------------------------------------------------------
         *
         * These are exposed as separate report-level values so the
         * standardized report can display them without touching the
         * original table_data.
         */
    
        $totalCnaShareCollectionTarget = 0;
    
        foreach (
            ($contributionPerMonth['total_cna_share_collection_target'] ?? [])
            as $yearData
        ) {
            if (is_array($yearData)) {
                $totalCnaShareCollectionTarget += (float) (
                    $yearData['total'] ?? 0
                );
            }
        }
    
        $actualCnaShareCollection = 0;
    
        foreach (
            ($contributionPerMonth['actual_cna_share_collection'] ?? [])
            as $yearData
        ) {
            if (is_array($yearData)) {
                $actualCnaShareCollection += (float) (
                    $yearData['total'] ?? 0
                );
            }
        }
    
        $totalAnnualDueCollectionTarget = 0;
    
        foreach (
            ($contributionPerMonth['total_annual_due_collection_target'] ?? [])
            as $yearData
        ) {
            if (is_array($yearData)) {
                $totalAnnualDueCollectionTarget += (float) (
                    $yearData['total'] ?? 0
                );
            }
        }
    
        $actualAnnualDueCollection = 0;
    
        foreach (
            ($contributionPerMonth['actual_annual_due_collection'] ?? [])
            as $yearData
        ) {
            if (is_array($yearData)) {
                $actualAnnualDueCollection += (float) (
                    $yearData['total'] ?? 0
                );
            }
        }
    
        /*
         * -------------------------------------------------------------
         * STANDARDIZED COLUMNS
         * -------------------------------------------------------------
         *
         * Order:
         *
         * Member
         * Oct - Dec of the previous year (early fiscal-year months)
         * CNA Share
         * Annual Due
         * January onward
         * Total Contributed
         */
        $preJanuaryKeys = $contributionPerMonth['pre_january_month_label'] ?? [];

        $preJanuaryColumns = array_values(array_filter(
            $monthColumns,
            fn ($column) => in_array($column['key'], $preJanuaryKeys, true)
        ));

        $remainingMonthColumns = array_values(array_filter(
            $monthColumns,
            fn ($column) => !in_array($column['key'], $preJanuaryKeys, true)
        ));

        $columns = array_merge(
            [
                [
                    'key' => 'member',
                    'label' => 'Member',
                    'type' => 'text',
                ],
            ],
            $preJanuaryColumns,
            $cnaShareColumns,
            $annualDueColumns,
            $remainingMonthColumns,
            [
                [
                    'key' => 'total_contributed',
                    'label' => 'Total Contributed',
                    'type' => 'currency',
                ],
            ]
        );
    
        /*
         * -------------------------------------------------------------
         * RETURN STANDARDIZED REPORT STRUCTURE
         * -------------------------------------------------------------
         */
        return [
            'context' => $this->buildContext(
                'Faculty Deduction Summary',
                'Faculty contribution deductions for members within the selected period.',
                $filters
            ),
    
            'cards' => [
                [
                    'key' => 'members',
                    'label' => 'Members',
                    'value' => count($memberData),
                ],
    
                [
                    'key' => 'target',
                    'label' => 'Monthly Collection Target',
                    'value' => $totalMonthlyCollectionTarget,
                ],
    
                [
                    'key' => 'actual',
                    'label' => 'Actual Monthly Collection',
                    'value' => $actualMonthlyCollection,
                ],
    
                [
                    'key' => 'balance',
                    'label' => 'Balance Collectible',
                    'value' => $balanceCollectible,
                ],
    
                [
                    'key' => 'cna_share_target',
                    'label' => 'CNA Share Collection Target',
                    'value' => $totalCnaShareCollectionTarget,
                ],
    
                [
                    'key' => 'cna_share_actual',
                    'label' => 'Actual CNA Share Collection',
                    'value' => $actualCnaShareCollection,
                ],
    
                [
                    'key' => 'annual_due_target',
                    'label' => 'Annual Due Collection Target',
                    'value' => $totalAnnualDueCollectionTarget,
                ],
    
                [
                    'key' => 'annual_due_actual',
                    'label' => 'Actual Annual Due Collection',
                    'value' => $actualAnnualDueCollection,
                ],
            ],
    
            'columns' => $columns,
    
            'rows' => $rows,
    
            /*
             * Report-level scalar values.
             */
            'total_collection_target' => $totalMonthlyCollectionTarget,
    
            'actual_collection' => $actualMonthlyCollection,
    
            'balance_collectible' => $balanceCollectible,
    
            'total_cna_share_collection_target' =>
                $totalCnaShareCollectionTarget,
    
            'actual_cna_share_collection' =>
                $actualCnaShareCollection,
    
            'total_annual_due_collection_target' =>
                $totalAnnualDueCollectionTarget,
    
            'actual_annual_due_collection' =>
                $actualAnnualDueCollection,
    
            /*
             * ---------------------------------------------------------
             * BACKWARD COMPATIBILITY
             * ---------------------------------------------------------
             *
             * Existing frontend code continues to receive the complete
             * original contributionPerMonth structure.
             */
            // getCampus() returns a Collection when no campus is selected.
            'campus' => $campus instanceof \App\Models\Campus ? $campus->campus_name : '',
    
            'period' => $period ?: ($contributionPerMonth['period'] ?? ''),

            'table_data' => $contributionPerMonth,
    
            'signatories' => [
                'campus_treasurer' => $campusTreasurer?->name,
                'campus_president' => $campusPresident?->name,
                'campus_auditor' => '',
            ],
        ];
    }
 

    // public function facultyDeductionSummary(array $filters): array 
    // {
         
    //     $request = new Request($filters);
    //     $campusId = $request['campus_id'] ?? null;

    //     $dateFrom = isset($filters['date_from']) ? Carbon::parse($filters['date_from'])->format('F Y') : '';
    //     $dateTo = isset($filters['date_to']) ? Carbon::parse($filters['date_to'])->format('F Y') : '';
    //     $period = ($dateFrom && $dateTo) ? "{$dateFrom} to {$dateTo}" : '';

    //     // 2. Fetch required services data
    //     $campus = $this->memberService->getCampus($campusId);
    //     $campusTreasurer = $this->memberService->getTreasurer($filters, $campusId);
    //     $campusPresident = $this->memberService->getPresident($filters, $campusId);
    //     $members = $this->memberService->getMembers($request, $campusId);
    //     $contributionPerMonth = $this->contributionService->getContributionPerMonth($request, $campusId);

    //     return [
    //         'campus' => $campus?->campus_name ?? '',
    //         'period' => $period,
    //         'table_data' => $contributionPerMonth,
    //         'signatories' => [
    //             'campus_treasurer' => $campusTreasurer?->name,
    //             'campus_president' => $campusPresident?->name,
    //             'campus_auditor'   => '',
    //         ],
    //     ];
    // }

    public function medicalAssistanceReport(array $filters): array
    {
        $request = new Request($filters);
            abort_unless(
            Auth::user()->hasRole('Federated Treasurer'), 
            403, 
            'Only the Federated Treasurer can generate this report.'
        );

        $campusId = $request->input('campus_id');
        $cycleParam = $request->input('cycle');
    
        /*
        |--------------------------------------------------------------------------
        | 1. Resolve cycle
        |--------------------------------------------------------------------------
        */
    
        // Claim cycles are per member: select each claimant's own cycle rather than a campus window
        $selector = $this->memberCycleService->normalizeSelector($cycleParam);
        $cycles = $this->memberCycleService->cyclesForSelector($selector)->keyBy('id');

        $cycleLabel = collect($this->memberCycleService->selectorOptions())
            ->firstWhere('value', $selector)['label'];

        /*
        |--------------------------------------------------------------------------
        | 2. Retrieve claims
        |--------------------------------------------------------------------------
        */

        // Disbursed claims only, oldest first so each row can look back at earlier releases
        $claims = $this->claimService->getAllClaims([], $campusId)
            ->filter(fn (Claim $claim) => $cycles->has($claim->member_claim_cycle_id)
                && $claim->status === ClaimStatus::Disbursed->value)
            ->sortBy([['submitted_at', 'asc'], ['created_at', 'asc']])
            ->values();

        $includedCycles = $cycles->only($claims->pluck('member_claim_cycle_id')->unique()->all());
        $startDate = $includedCycles->min('cycle_start');
        $endDate = $includedCycles->max('cycle_end');

        /*
        |--------------------------------------------------------------------------
        | 3. Build ledger records: one row per disbursed claim
        |--------------------------------------------------------------------------
        |
        | "Received previous claims" and the running balance only look at the
        | member's earlier disbursements in the same claim cycle, so no amount is
        | counted twice. The records are also retained as the legacy `records`
        | payload for backward compatibility.
        |
        */

        $releasedByCycle = [];

        $formattedRecords = $claims
            ->map(function (Claim $claim) use ($cycles, &$releasedByCycle) {
                $cycle = $cycles->get($claim->member_claim_cycle_id);
                $earlierClaims = collect($releasedByCycle[$cycle->id] ?? []);

                $member = $claim->member;
                $campus = $member?->campus;
                $approvedAmount = (float) $claim->approved_amount;

                /*
                 * Disbursements released to this member earlier in the same cycle.
                 */
                $receivedFromPreviousClaims = (float) $earlierClaims->sum('approved_amount');
                $hasExistingPriorClaim = $receivedFromPreviousClaims > 0;

                /*
                 * Cycle claim limit (snapshotted when the member's cycle started)
                 * less everything released up to and including this claim.
                 */
                $maxAnnualLimit = (float) $cycle->claim_limit;
                $remainingBalance = max(0, $maxAnnualLimit - $receivedFromPreviousClaims - $approvedAmount);

                $remarks = (float) $remainingBalance === 0.00
                    ? 'Can claim by ' . $cycle->cycle_end->copy()->addDay()->format('F Y')
                    : '';

                $releasedByCycle[$cycle->id][] = $claim;

                return [
                    'year' => Carbon::parse($claim->submitted_at)->format('Y'),

                    'month' => Carbon::parse($claim->submitted_at)->format('F'),

                    'campus' => $campus?->acronym
                        ?? $campus?->campus_name
                        ?? 'N/A',

                    'name_of_claimant' => trim($member?->user?->name ?? 'Unknown Member'),

                    'type_of_claim' => $claim->claimType?->name ?? '',

                    'approved_amount_ac' => $approvedAmount,

                    'received_previous_claims' => $receivedFromPreviousClaims,

                    'month_year_previous' => $hasExistingPriorClaim
                        ? Carbon::parse($earlierClaims->last()->submitted_at)->format('F Y')
                        : 'N/A',

                    'max_claimable_amount' => $maxAnnualLimit,

                    'balance' => (float) $remainingBalance,

                    'remarks' => $remarks,

                    'is_blue_indicator' => $hasExistingPriorClaim,
                ];
            })
            ->values()
            ->all();
    
        /*
        |--------------------------------------------------------------------------
        | 4. Calculate report-level values
        |--------------------------------------------------------------------------
        */
    
        $totalApprovedAmount = (float) collect($formattedRecords)
            ->sum('approved_amount_ac');
    
        $campus = $this->memberService->getCampus($campusId);
    
        $campusTreasurer = $this->memberService->getTreasurer(
            $filters,
            $campusId
        );
    
        $federatedTreasurer = $this->memberService->getTreasurer($filters);
    
        $federatedPresident = $this->memberService->getPresident($filters);
    
        $maxClaimableAmount = (float) SystemSetting::getGlobalLimit($campusId);
    
        /*
        |--------------------------------------------------------------------------
        | 5. Standardized rows
        |--------------------------------------------------------------------------
        |
        | `rows` follows the same flat-record convention used by the
        | other standardized reports.
        |
        */
    
        $rows = collect($formattedRecords)
            ->map(function (array $record) {
                return [
                    'year' => $record['year'],
                    'month' => $record['month'],
                    'campus' => $record['campus'],
                    'member' => $record['name_of_claimant'],
                    'claim_type' => $record['type_of_claim'],
                    'approved_amount' => (float) $record['approved_amount_ac'],
                    'received_previous_claims' => (float) $record['received_previous_claims'],
                    'previous_claim_month' => $record['month_year_previous'],
                    'balance' => (float) $record['balance'],
                    'max_claimable_amount' => (float) $record['max_claimable_amount'],
                    'remarks' => $record['remarks'],
                    // 'is_blue_indicator' => (bool) $record['is_blue_indicator'],
                ];
            })
            ->values()
            ->all();
    
        /*
        |--------------------------------------------------------------------------
        | 6. Standardized columns
        |--------------------------------------------------------------------------
        */
    
        $columns = [
            [
                'key' => 'year',
                'label' => 'Year',
                'type' => 'text',
            ],
            [
                'key' => 'month',
                'label' => 'Month',
                'type' => 'text',
            ],
            [
                'key' => 'campus',
                'label' => 'Campus',
                'type' => 'text',
            ],
            [
                'key' => 'member',
                'label' => 'A Member-Claimant',
                'type' => 'text',
            ],
            [
                'key' => 'claim_type',
                'label' => 'Type of Claim',
                'type' => 'text',
            ],
            [
                'key' => 'approved_amount',
                'label' => 'Approved Amount',
                'type' => 'currency',
            ],
            [
                'key' => 'received_previous_claims',
                'label' => 'Received Previous Claims',
                'type' => 'currency',
            ],
            [
                'key' => 'previous_claim_month',
                'label' => 'Previous Claim Month',
                'type' => 'text',
            ],
            [
                'key' => 'max_claimable_amount',
                'label' => 'Max Claimable Amount',
                'type' => 'currency',
            ],
            [
                'key' => 'balance',
                'label' => 'Balance',
                'type' => 'currency',
            ],
            [
                'key' => 'remarks',
                'label' => 'Remarks',
                'type' => 'text',
            ],
            // [
            //     'key' => 'is_blue_indicator',
            //     'label' => 'Prior Claim Indicator',
            //     'type' => 'boolean',
            // ],
        ];
    
        /*
        |--------------------------------------------------------------------------
        | 7. Standardized report structure
        |--------------------------------------------------------------------------
        |
        | The standardized structure is additive.
        | Existing metadata/signatories/records remain below.
        |
        */
    
        return [
            /*
            |--------------------------------------------------------------------------
            | Standardized report contract
            |--------------------------------------------------------------------------
            */
    
            'context' => [
                'report_title' => 'Medical Assistance Report',
                'description' => 'Disbursed medical assistance claims within each member\'s claim cycle.',
                'association' => 'CVSCAFT FACULTY ASSOCIATION',
                'campus' => $campus?->acronym ?? '',
                'cycle' => $cycleLabel,
                'date_from' => $startDate,
                'date_to' => $endDate,
                'filters' => $filters,
            ],
    
            'cards' => [
                [
                    'key' => 'claimants',
                    'label' => 'Claimants',
                    'value' => count($rows),
                ],
                [
                    'key' => 'approved_amount',
                    'label' => 'Total Approved Amount',
                    'value' => $totalApprovedAmount,
                ],
                // [
                //     'key' => 'max_claimable_amount',
                //     'label' => 'Maximum Claimable Amount',
                //     'value' => $maxClaimableAmount,
                // ],
            ],
    
            'columns' => $columns,
    
            'rows' => $rows,
    
            'total_approved_amount' => $totalApprovedAmount,
    
            // 'max_claimable_amount' => $maxClaimableAmount,
    
            /*
            |--------------------------------------------------------------------------
            | Legacy structure
            |--------------------------------------------------------------------------
            |
            | These fields intentionally remain unchanged so the current
            | implementation does not need to be rewritten immediately.
            |
            */
    
            'metadata' => [
                'association' => 'CVSCAFT FACULTY ASSOCIATION',
                'report_title' => 'Medical Assistance Report',
                'campus_filter' => $campus?->acronym ?? '',
                'cycle_label' => $cycleLabel,
    
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate,
                ],
    
                'generation_date' => now()->format('m/d/y'),
                'contextual_date' => now()->format('F Y'),
                'total_approved_amount' => $totalApprovedAmount,
                'max_claimable_amount' => $maxClaimableAmount,
            ],
    
            'signatories' => [
                'campusTreasurer' => $campusTreasurer?->name ?? '',
                'federatedTreasurer' => $federatedTreasurer?->name ?? '',
                'federatedPresident' => $federatedPresident?->name ?? '',
            ],
    
            'records' => $formattedRecords,
        ];
    }
    
    
    // public function medicalAssistanceReport(array $filters): array
    // {
    //     $request = new Request($filters);
    //     $campusId = $request->input('campus_id') ?? null;
    //     $cycleParam = $request->input('cycle') ?? null;
    
    //     // 1. Resolve cycle array context bounds using your helper class method
    //     // Returns array structure: [0 => start_date, 1 => end_date, 2 => cycle_label]
    //     $resolvedCycle = $this->cycleService->resolve($cycleParam, $campusId);

    //     $startDate = $resolvedCycle[0];
    //     $endDate = $resolvedCycle[1];
    //     // $cycleLabel = $resolvedCycle[2];
    //     $cycleLabel = $resolvedCycle[0]->format('F Y') . ' - ' . $resolvedCycle[1]->format('F Y');

    //     // 2. Query structural dataset inside this cycle boundary
    //     $claims = $this->claimService->getAllClaims([], $campusId, [$startDate, $endDate]);
    
    //     // 3. Transform and group dataset to build out tabular matrix per member
    //     $formattedRecords = $claims->groupBy('member_id')
    //         ->filter(function ($memberClaims) {
    //             return $memberClaims->contains('status', 'Approved');
    //         })
    //         ->map(function ($memberClaims, $memberId) use ($startDate, $endDate) {

    //         $firstClaim = $memberClaims->first();
    //         $member = $firstClaim->member->user->name;
            
    //         // Assemble Full Name matching the image format
    //         // $middleInitial = $member->middle_name ? ' ' . substr($member->middle_name, 0, 1) . '.' : '';
    //         // $fullName = trim("{$member->first_name}{$middleInitial} {$member->last_name} {$member->suffix}");
    //         $fullName = trim($member);
    
    //         // Filter and evaluate aggregate sums
    //         // A: Total approved amount up for action/disbursement right now in this cycle window
    //         $approvedAmountForReimbursement = $memberClaims->where('status', 'Approved')->sum('approved_amount');
    //         $previouslyDisbursedAmount = $memberClaims->where('status', 'Disbursed')->sum('approved_amount'); 
    //         // B: Historically received funds (Disbursed status) within this same cycle window
    //         $receivedFromPreviousClaims = Claim::withoutGlobalScope(CampusMemberScope::class)
    //             ->where('member_id', $memberId)
    //             ->where('status', 'Disbursed')
    //             ->whereBetween('submitted_at', [$startDate, $endDate])
    //             ->sum('approved_amount');
    
    //         // Dynamic visual layout balance check (TC (23k) - AC)
    //         $maxAnnualLimit = $firstClaim->annual_limit ?? 23000.00; 
    //         $remainingBalance = $maxAnnualLimit - $previouslyDisbursedAmount;
    
    //         // Custom business logic matching your document's footnote rule indicator:
    //         // Set indicator if a historic balance distribution already exists
    //         $hasExistingPriorClaim = $receivedFromPreviousClaims > 0;
    //         $remarks = (float)$remainingBalance === 0.00 ? 'Can claim by ' . $endDate->copy()->addMonth()->format('F Y') : '';
    //         return [
    //             'year' => Carbon::parse($firstClaim->submitted_at)->format('Y'),
    //             'month' => Carbon::parse($firstClaim->submitted_at)->format('F'),
    //             'campus' => $member->campus->acronym ?? $member->campus->campus_name ?? 'N/A',
    //             'name_of_claimant' => $fullName,
    //             'type_of_claim' => $memberClaims->pluck('claimType.name')->unique()->implode(', '),
    //             'approved_amount_ac' => (float)$approvedAmountForReimbursement,
    //             'received_previous_claims' => (float)$receivedFromPreviousClaims,
    //             'month_year_previous' => $hasExistingPriorClaim ? Carbon::parse($startDate)->format('F Y') : 'N/A',
    //             'balance' => (float)$remainingBalance,
    //             // 'remarks' => $firstClaim->remarks ?? '',
    //             'remarks' => $remarks,
    //             'is_blue_indicator' => $hasExistingPriorClaim
    //         ];
    //     })->values()->toArray();
    
    //     // 4. Calculate table aggregates for the footer summary blocks
    //     $totalApprovedAmt = collect($formattedRecords)->sum('approved_amount_ac');
    //     $campusFilter = $this->memberService->getCampus($campusId);
        
    //     $campusTreasurer = $this->memberService->getTreasurer($filters, $campusId);
    //     $federatedTreasurer = $this->memberService->getTreasurer($filters);
    //     $federatedPresident = $this->memberService->getPresident($filters);
    //     $max_claimable_amount = SystemSetting::getGlobalLimit($campusId);
    //     // Return production payload structured cleanly for JSON serialization or rendering pipelines
    //     return [
    //         'metadata' => [
    //             'association' => 'CVSCAFT FACULTY ASSOCIATION',
    //             'report_title' => 'Medical Assistance Report',
    //             'campus_filter' => $campusFilter?->acronym ?? '',
    //             'cycle_label' => $cycleLabel,
    //             'date_range' => [
    //                 'start' => $startDate,
    //                 'end' => $endDate
    //             ],
    //             'generation_date' => now()->format('m/d/y'),
    //             'contextual_date' => now()->format('F Y'),
    //             'total_approved_amount' => $totalApprovedAmt,
    //             'max_claimable_amount' => $max_claimable_amount,
    //         ],
    //         'signatories' => [
    //             'campusTreasurer' => $campusTreasurer?->name ?? '',
    //             'federatedTreasurer' => $federatedTreasurer?->name ?? '',
    //             'federatedPresident' => $federatedPresident?->name ?? '',
    //         ],
    //         'records' => $formattedRecords
    //     ];
    // }

    public function monthlyClaimsSummary(array $data): array
    {
        return $this->monthlySummary($data);
    }

    public function unifiedReportAggregator(array $data): array
    {
        switch ($data['report_type']) {
            case 'campus-summary' : return $this->campusSummary($data); break;
            case 'disbursement-summary' : return $this->disbursementSummary($data); break;
            case 'collection-summary' : return $this->collectionSummary($data); break;
            case 'allowance-utilization' : return $this->allowanceUtilization($data); break;
            case 'allowance-forfeiture' : return $this->allowanceForfeiture($data); break;
            default : return [];
        }
    } 


    private function formatPayrollCoverage(?Claim $claim): string
    {
        $campusId = $claim?->member?->campus_id;
        $submittedAt = $claim?->submitted_at;

        if (!$campusId || !$submittedAt) {
            return 'N/A';
        }

        // Coverage is the claimant's own claim cycle
        $cycle = $this->memberCycleService->getCycleForClaim($claim);

        return $cycle
            ? $cycle->cycle_start->format('F Y') . '-' . $cycle->cycle_end->format('F Y')
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

    protected function buildContext(
        string $title,
        string $description,
        array $filters
    ): array {
        // 1. Resolve Campus Name
        $campusName = 'All Campuses';
        if (filled($campusId = $filters['campus_id'] ?? null)) {
            $campusName = Campus::find($campusId)?->acronym ?? 'All Campuses';
        }

        // 2. Resolve Date Range
        $dateRange = 'All Dates';
        if (filled($claimMonthFilter = $filters['claim_month'] ?? null)) {
            $dateRange = 'Claims submitted in ' . Carbon::parse("{$claimMonthFilter}-01")->format('F Y');
        } elseif (filled($filters['date_from'] ?? null) || filled($filters['date_to'] ?? null)) {
            $from = filled($filters['date_from'] ?? null) ? Carbon::parse($filters['date_from'])->format('M d, Y') : 'Beginning';
            $to = filled($filters['date_to'] ?? null) ? Carbon::parse($filters['date_to'])->format('M d, Y') : 'Present';
            $dateRange = "{$from} to {$to}";
        } elseif (filled($reportingYear = $filters['reporting_year'] ?? null)) {
            $dateRange = "January {$reportingYear} to December {$reportingYear}";
        }

        // 3. Resolve Status
        $status = filled($statusFilter = $filters['status'] ?? null) 
            ? ucwords($statusFilter) 
            : 'All Statuses';

        // 4. Resolve Claim Month Text
        $claimMonth = filled($claimMonthFilter) 
            ? Carbon::parse($claimMonthFilter)->format('F Y') 
            : '';

        // 5. Resolve Claim Type
        $claimType = 'All Types';
        if (filled($claimTypeId = $filters['claim_type'] ?? null)) {
            $claimType = ucwords(ClaimType::find($claimTypeId)?->name ?? 'All Types');
        }

        // 6. Resolve Cycle
        $cycle = $filters['cycle'] ?? '';

        // 7. Resolve Member Name
        $member = 'All Members';
        if (filled($memberId = $filters['member_id'] ?? null)) {
            $memberRecord = Member::with('user')->find($memberId);
            $member = $memberRecord?->user?->name ?? 'All Members';
        }

        // 8. Map specific report filter lists
        $reportType = $filters['report_type'] ?? '';
        $specificFilters = match ($reportType) {
            'monthly-summary' => [
                ['label' => 'Reporting Year', 'value' => $filters['reporting_year'] ?? 'All Years'],
                // ['label' => 'Campus', 'value' => $campusName],
                ['label' => 'Status', 'value' => $status],
                ['label' => 'Claim Type', 'value' => $claimType],
            ],
            'campus-summary' => [
                ['label' => 'Date Range', 'value' => $dateRange],
                // ['label' => 'Campus', 'value' => $campusName],
                ['label' => 'Status', 'value' => $status],
            ],
            'disbursement-summary', 'faculty-deduction-summary' => [
                // ['label' => 'Campus', 'value' => $campusName],
                // ['label' => 'Date Range', 'value' => $dateRange],
            ],
            'collection-summary' => [
                // ['label' => 'Campus', 'value' => $campusName],
                ['label' => 'Date Range', 'value' => $dateRange],
            ],
            'payroll-deduction-request' => [
                ['label' => 'Claims Submitted', 'value' => $claimMonth],
                ['label' => 'Status', 'value' => $status],
                ['label' => 'Claim Type', 'value' => $claimType],
            ],
            'medical-assistance-report' => [
                ['label' => 'Cycle', 'value' => $cycle],
                // ['label' => 'Campus', 'value' => $campusName],
            ],
            'allowance-utilization' => [
                // ['label' => 'Campus', 'value' => $campusName],
                ['label' => 'Cycle', 'value' => $cycle],
                ['label' => 'Member', 'value' => $member],
            ],
            'allowance-forfeiture' => [
                // ['label' => 'Campus', 'value' => $campusName],
                ['label' => 'Cycle', 'value' => $cycle],
            ],
            default => [],
        };

        // Note: The base array logic matches your original output architecture. 
        // If "Reporting Year" and "Campus" are being doubled in your UI for monthly-summary,
        // you may safely change this array to just return $specificFilters.
        return [
            'title' => $title,
            'description' => $description,
            'generated_at' => Carbon::now()->format('F d, Y h:i A'),
            'filters' => array_merge([
                ['label' => 'Reporting Year', 'value' => $filters['reporting_year'] ?? 'All Years'],
                ['label' => 'Campus', 'value' => $campusName],
            ], $specificFilters),
        ];
    }


    // protected function buildContext(
    //     string $title,
    //     string $description,
    //     array $filters
    // ): array {
    //     // 1. Resolve readable names for IDs
    //     $campusName = 'All Campuses';
    //     if (!empty($filters['campus_id'])) {
    //         $campus = Campus::find($filters['campus_id']);
    //         $campusName = $campus ? $campus->acronym : 'All Campuses';
    //     }

    //     // 2. Format a clean date range string based on dates or reporting year
    //     $dateRange = 'All Dates';

    //     if (!empty($filters['claim_month'])) {
    //         $dateRange = 'Claims submitted in ' . Carbon::parse($filters['claim_month'] . '-01')->format('F Y');
    //     } elseif (!empty($filters['date_from']) || !empty($filters['date_to'])) {
    //         // User explicitly picked specific date boundaries
    //         $from = !empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->format('M d, Y') : 'Beginning';
    //         $to = !empty($filters['date_to']) ? Carbon::parse($filters['date_to'])->format('M d, Y') : 'Present';
    //         $dateRange = "{$from} to {$to}";
    //     } elseif (!empty($filters['reporting_year'])) {
    //         // No custom dates picked, fall back to the selected reporting year scope
    //         $year = $filters['reporting_year'];
    //         $dateRange = "January {$year} to December {$year}";
    //     } 

    //     $status = 'All Statuses';
    //     if (!empty($filters['status'])) {
    //         $status = ucwords($filters['status']);
    //     }

    //     $claimMonth = '';
    //     if (!empty($filters['claim_month'])) {
    //         $claimMonth = Carbon::parse($filters['claim_month'])->format('F Y');
    //     }

    //     $claimType = 'All Types';
    //     if(filled($id = $filters['claim_type'])) {
    //         $name = ClaimType::find($id)?->name;
    //         $claimType = ucwords($name ?? 'All Types');
    //     }

    //     $cycle = '';
    //     if(!empty($filters['cycle'])) {
    //         $cycle = $filters['cycle'];
    //     }
        
    //     $member = 'All Members';
    //     if(!empty($filters['member_id'])) {
    //         $name = Member::with('user')->where('id', $filters['member_id'])->get();
    //         $member = $name?->user?->name;
    //     }
        
    //     $filtersArray = [];
    //     switch ($filters['report_type']) {
    //         case 'monthly-summary' : {
    //             $filtersArray = [ 
    //                 [
    //                     'label' => 'Reporting Year',
    //                     'value' => $filters['reporting_year'] ?? 'All Years',
    //                 ],
    //                 [
    //                     'label' => 'Campus',
    //                     'value' => $campusName,
    //                 ],
    //                 [
    //                     'label' => 'Status',
    //                     'value' => $status,
    //                 ],
    //                 [
    //                     'label' => 'Claim Type',
    //                     'value' => $claimType,
    //                 ]
    //             ];
    //             break;
    //         }    
    //         case 'campus-summary' : {

    //             $filtersArray = [
    //                 [
    //                     'label' => 'Date Range',
    //                     'value' => $dateRange,
    //                 ],
    //                 [
    //                     'label' => 'Campus',
    //                     'value' => $campusName,
    //                 ],
    //                 [
    //                     'label' => 'Status',
    //                     'value' => $status,
    //                 ]
    //             ];
    //             break;
    //         } 
    //         case 'disbursement-summary' : {
    //             $filtersArray = [
    //                 [
    //                     'label' => 'Date Range',
    //                     'value' => $dateRange,
    //                 ],
    //                 [
    //                     'label' => 'Campus',
    //                     'value' => $campusName,
    //                 ],
    //             ];
    //             break;
    //         }
    //         case 'collection-summary' : {
    //             $filtersArray = [
    //                 [
    //                     'label' => 'Campus',
    //                     'value' => $campusName,
    //                 ],
    //                 [
    //                     'label' => 'Date Range',
    //                     'value' => $dateRange,
    //                 ]
    //             ];
    //             break;
    //         }
    //         case 'payroll-deduction-request' : {
    //             $filtersArray = [
    //                 [
    //                     'label' => 'Claims Submitted',
    //                     'value' => $claimMonth,
    //                 ],
    //                 [
    //                     'label' => 'Status',
    //                     'value' => $status,
    //                 ],
    //                 [
    //                     'label' => 'Claim Type',
    //                     'value' => $claimType,
    //                 ]
    //             ];
    //             break;
    //         }
    //         case 'faculty-deduction-summary' : {
    //             $filtersArray = [
    //                 [
    //                     'label' => 'Campus',
    //                     'value' => $campusName,
    //                 ],
    //                 [
    //                     'label' => 'Date Range',
    //                     'value' => $dateRange,
    //                 ]
    //             ];
    //             break;
    //         }
    //         case 'medical-assistance-report' : {
    //             $filtersArray = [
    //                 [
    //                     'label' => 'Cycle',
    //                     'value' => $cycle,
    //                 ],
    //                 [
    //                     'label' => 'Campus',
    //                     'value' => $campusName,
    //                 ]
    //             ];
    //             break;
    //         }
    //         case 'allowance-utilization' : {
    //             $filtersArray = [
    //                 [
    //                     'label' => 'Campus',
    //                     'value' => $campusName,
    //                 ],
    //                 [
    //                     'label' => 'Cycle',
    //                     'value' => $cycle,
    //                 ],
    //                 [
    //                     'label' => 'Member',
    //                     'value' => $member,
    //                 ]
    //             ];
    //             break;
    //         }
    //         case 'allowance-forfeiture' : {
    //             $filtersArray = [
    //                 [
    //                     'label' => 'Campus',
    //                     'value' => $campusName,
    //                 ],
    //                 [
    //                     'label' => 'Cycle',
    //                     'value' => $cycle,
    //                 ]
    //             ];
    //             break;
    //         }
    //         default : {
    //             $filtersArray = [];
    //             break;
    //         }
    //     }

    //     return [
    //         'title' => $title,
    //         'description' => $description,
    //         'generated_at' => Carbon::now()->format('F d, Y h:i A'),
    //         'filters' => [
    //             [
    //                 'label' => 'Reporting Year',
    //                 'value' => $filters['reporting_year'] ?? 'All Years',
    //             ],
    //             [
    //                 'label' => 'Campus',
    //                 'value' => $campusName,
    //             ],
    //             ...$filtersArray,
    //         ],
    //     ];
    // }


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
        // Members have independent cycles, so options select cycles relative to each member
        return $this->memberCycleService->selectorOptions();
    }
}