<?php

namespace App\Services;

use App\Enums\ClaimStatus;
use App\Models\User;
use App\Models\Member;
use App\Models\Claim;
use App\Models\SystemSetting;
use App\Models\TreasuryLedger;
use App\Models\Scopes\CampusMemberScope;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    public function __construct(private MemberClaimCycleService $memberCycleService)
    {
    }

    /**
     * Get data tailored for the Admin / Management dashboard.
     */
    public function getAdminMetrics(): array
    {
        return [
            'title'          => 'Admin',
            'members'        => Member::count(),
            'pendingClaims'  => Claim::pending()->count(),
            'approvedClaims' => Claim::approved()->count(),
            'disbursedClaims'=> Claim::disbursed()->count(),
            'rejectedClaims' => Claim::rejected()->count(),
            'totalClaims'    => Claim::count(),
            'balance'        => (float) TreasuryLedger::withoutGlobalScope(CampusMemberScope::class)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get()
                ->unique('campus_id')
                ->sum('running_balance'),
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
            ];
        }

        return [
            'title'              => 'Main',
            'totalClaims'        => $member->claims()->count(),
            'processing'         => $member->claims()->where('status', 'Processing')->count(),
            'disbursed'          => $member->claims()->where('status', 'Disbursed')->count(),
            'pending'            => $member->claims()->pending()->count(),
            'balance'            => $this->calculateMemberBalanceData($user)['actualBalance'],
            'recentTransactions' => $member->claims()->latest('submitted_at')->take(5)->get(),
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
        \Log::error('DashboardService::calculateMemberBalanceData() - Retrieved cycle: ' . ($cycle ? $cycle->id : 'null') . ' for member_id: ' . $member->id);

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
