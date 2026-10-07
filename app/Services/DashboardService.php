<?php

namespace App\Services;

use App\Enums\ClaimStatus;
use App\Models\Campus;
use App\Models\ClaimType;
use App\Models\User;
use App\Models\Member;
use App\Models\Claim;
use App\Models\SystemSetting;
use App\Models\TreasuryLedger;
use App\Models\Scopes\CampusMemberScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    public function __construct(private MemberClaimCycleService $memberCycleService)
    {
    }

    /**
     * Get data tailored for the Admin / Management dashboard.
     */
    public function getAdminMetrics(?User $user = null): array
    {
        $user ??= Auth::user();
        $campusId = $this->campusIdFor($user);

        $statusCounts = $this->claimsQuery($campusId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total);

        $campusBalances = $this->latestBalancesByCampus($campusId);

        return [
            'title'          => 'Admin',
            'members'        => Member::count(),
            'pendingClaims'  => $statusCounts->get(ClaimStatus::Pending->value, 0),
            'approvedClaims' => $statusCounts->get(ClaimStatus::Approved->value, 0),
            'disbursedClaims'=> $statusCounts->get(ClaimStatus::Disbursed->value, 0),
            'rejectedClaims' => $statusCounts->get(ClaimStatus::Rejected->value, 0),
            'totalClaims'    => $statusCounts->sum(),
            'balance'        => (float) $campusBalances->sum(),
            'chartData'      => [
                'monthlyDisbursements' => $this->monthlyDisbursements($campusId),
                'claimsByStatus'       => $this->statusSeries($statusCounts),
                'amountByClaimType'    => $this->disbursedAmountByClaimType($campusId),
                // A single-campus user would only ever see one bar, so the chart is federated-only
                'balanceByCampus'      => $campusId === null ? $this->balanceSeries($campusBalances) : null,
            ],
        ];
    }

    /**
     * The campus the user is limited to, or null when they may see every campus.
     * Mirrors CampusMemberScope; an unassigned user gets '' so every query comes back empty.
     */
    private function campusIdFor(?User $user): ?string
    {
        if (!$user || $user->can('view all campus data')) {
            return null;
        }

        return $user->member()->withoutGlobalScopes()->value('campus_id') ?? '';
    }

    /**
     * Claims carry no campus_id, so they are scoped through their member's campus.
     */
    private function claimsQuery(?string $campusId): Builder
    {
        return Claim::query()->when(
            $campusId !== null,
            fn (Builder $query) => $query->whereHas(
                'member',
                fn (Builder $member) => $member->withoutGlobalScopes()->where('campus_id', $campusId)
            )
        );
    }

    /**
     * Latest running balance per campus, keyed by campus id.
     */
    private function latestBalancesByCampus(?string $campusId): Collection
    {
        return TreasuryLedger::withoutGlobalScope(CampusMemberScope::class)
            ->when($campusId !== null, fn (Builder $query) => $query->where('campus_id', $campusId))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get(['campus_id', 'running_balance'])
            ->unique('campus_id')
            ->mapWithKeys(fn ($entry) => [$entry->campus_id => (float) $entry->running_balance]);
    }

    /**
     * Disbursed totals for the last 12 months, oldest first; months without releases are 0.
     * Grouped in PHP so the query stays portable across MySQL and SQLite.
     */
    private function monthlyDisbursements(?string $campusId): array
    {
        $start = now()->startOfMonth()->subMonths(11);

        $totals = $this->claimsQuery($campusId)
            ->where('status', ClaimStatus::Disbursed->value)
            ->where('disbursed_at', '>=', $start)
            ->get(['disbursed_at', 'approved_amount'])
            ->groupBy(fn (Claim $claim) => $claim->disbursed_at->format('Y-m'))
            ->map(fn (Collection $claims) => (float) $claims->sum('approved_amount'));

        $labels = [];
        $values = [];

        for ($month = $start->copy(); $month->lte(now()); $month->addMonth()) {
            $labels[] = $month->format('M Y');
            $values[] = round($totals->get($month->format('Y-m'), 0.0), 2);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Claim counts in workflow order, including statuses with no claims yet.
     */
    private function statusSeries(Collection $statusCounts): array
    {
        $order = [
            ClaimStatus::Pending,
            ClaimStatus::ForReviewFp,
            ClaimStatus::Processing,
            ClaimStatus::Approved,
            ClaimStatus::ForDisbursement,
            ClaimStatus::Disbursed,
            ClaimStatus::Rejected,
        ];

        return [
            'labels' => array_map(fn (ClaimStatus $status) => $status->value, $order),
            'values' => array_map(fn (ClaimStatus $status) => (int) $statusCounts->get($status->value, 0), $order),
        ];
    }

    /**
     * Total disbursed amount per claim type, largest first.
     */
    private function disbursedAmountByClaimType(?string $campusId): array
    {
        $totals = $this->claimsQuery($campusId)
            ->where('status', ClaimStatus::Disbursed->value)
            ->selectRaw('claim_type_id, SUM(approved_amount) as total')
            ->groupBy('claim_type_id')
            ->pluck('total', 'claim_type_id')
            ->map(fn ($total) => round((float) $total, 2))
            ->sortDesc();

        $names = ClaimType::whereIn('id', $totals->keys())->pluck('name', 'id');

        return [
            'labels' => $totals->keys()->map(fn ($id) => $names->get($id, 'Unknown'))->values()->all(),
            'values' => $totals->values()->all(),
        ];
    }

    private function balanceSeries(Collection $campusBalances): array
    {
        $acronyms = Campus::withoutGlobalScopes()
            ->whereIn('id', $campusBalances->keys())
            ->pluck('acronym', 'id');

        $sorted = $campusBalances->sortDesc();

        return [
            // Ledger rows saved without a campus still count toward the total, so they get their own bar
            'labels' => $sorted->keys()->map(fn ($id) => $acronyms->get($id, 'Unassigned'))->values()->all(),
            'values' => $sorted->map(fn ($balance) => round($balance, 2))->values()->all(),
        ];
    }

    /**
     * Get data tailored for the standard Member dashboard page.
     */
    public function getMemberMetrics(User $user): array
    {
        $member = $user->member;

        // Secure fallback package if no member profile exists
        if (!$member) {
            return [
                'title'              => 'Main',
                'totalClaims'        => 0.00,
                'processing'         => 0.00,
                'disbursed'          => 0.00,
                'pending'            => 0.00,
                'balance'            => 0.00,
                'recentTransactions' => [],
                'allowance'          => null,
                'chartData'          => ['claimsByStatus' => $this->statusSeries(collect())],
            ];
        }

        $statusCounts = $member->claims()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total);

        $balanceData = $this->calculateMemberBalanceData($user);

        return [
            'title'              => 'Main',
            'totalClaims'        => $statusCounts->sum(),
            'processing'         => $statusCounts->get(ClaimStatus::Processing->value, 0),
            'disbursed'          => $statusCounts->get(ClaimStatus::Disbursed->value, 0),
            'pending'            => $statusCounts->get(ClaimStatus::Pending->value, 0),
            'balance'            => $balanceData['actualBalance'],
            'recentTransactions' => $member->claims()->latest('submitted_at')->take(5)->get(),
            'allowance'          => $this->allowanceBreakdown($balanceData),
            'chartData'          => ['claimsByStatus' => $this->statusSeries($statusCounts)],
        ];
    }

    /**
     * Splits the cycle limit into segments that add up to it: disbursed, approved but not
     * yet released, reserved by claims still under review, and what is left to claim.
     */
    private function allowanceBreakdown(array $balanceData): array
    {
        $limit = (float) $balanceData['baseLimit'];
        $disbursed = (float) $balanceData['totalDisbursedCycle'];
        $approved = max(0.0, (float) $balanceData['totalApprovedCycle'] - $disbursed);
        $remaining = (float) $balanceData['remainingAllowance'];
        $reserved = max(0.0, $limit - $disbursed - $approved - $remaining);

        return [
            'limit'     => $limit,
            'disbursed' => $disbursed,
            'approved'  => $approved,
            'reserved'  => $reserved,
            'remaining' => $remaining,
        ];
    }

    /**
     * Handle the complex business math for a member's financial balance tracking.
     */
    public function calculateMemberBalanceData(User $user): array
    {
        $member = $user->member;

        if (!$member) {
            return [
                'approvedBalance'     => 0.00,
                'actualBalance'       => 0.00,
                'totalApprovedCycle'  => 0.00,
                'totalDisbursedCycle' => 0.00,
                'cycleStartDate'      => null,
                'cycleEndDate'        => null,
                'cycleYears'          => SystemSetting::getCycleYears(),
                'remainingAllowance'  => 0.00,
                'baseLimit'           => 0.00,
            ];
        }

        // The member's own claim cycle; none exists until their next qualifying claim
        $cycle = $this->memberCycleService->getActiveCycleForMember($member);

        if (!$cycle) {
            $baseLimit = (float) SystemSetting::getGlobalLimit($member->campus_id);

            return [
                'approvedBalance'     => $baseLimit,
                'actualBalance'       => $baseLimit,
                'remainingAllowance'  => $baseLimit,
                'totalApprovedCycle'  => 0.00,
                'totalDisbursedCycle' => 0.00,
                'cycleStartDate'      => null,
                'cycleEndDate'        => null,
                'cycleYears'          => SystemSetting::getCycleYears($member->campus_id),
                'baseLimit'           => $baseLimit,
            ];
        }

        $baseLimit = (float) $cycle->claim_limit;
        $claimsQuery = $member->claims()->where('member_claim_cycle_id', $cycle->id);

        $totalApprovedCycle = (float) (clone $claimsQuery)
            ->whereIn('status', MemberClaimCycleService::approvedStatuses())
            ->sum('approved_amount');

        $totalDisbursedCycle = (float) (clone $claimsQuery)
            ->where('status', ClaimStatus::Disbursed->value)
            ->sum('approved_amount');

        // Remaining allowance reserves in-flight claims; this is what claim submission validates against
        $remainingAllowance = $this->memberCycleService->remainingForCycle($cycle);

        return [
            'approvedBalance'     => max(0, $baseLimit - $totalApprovedCycle),
            'actualBalance'       => $remainingAllowance,
            'remainingAllowance'  => $remainingAllowance,
            'totalApprovedCycle'  => $totalApprovedCycle,
            'totalDisbursedCycle' => $totalDisbursedCycle,
            'cycleStartDate'      => $cycle->cycle_start->toDateString(),
            'cycleEndDate'        => $cycle->cycle_end->toDateString(),
            'cycleYears'          => $cycle->duration_years,
            'baseLimit'           => $baseLimit, // Passed out so your view can display: "Limit: P23,000"
        ];
    }
}
