<?php

namespace App\Services;

use App\Models\User;
use App\Models\Member;
use App\Models\Claim;
use App\Models\SystemSetting;
use App\Models\TreasuryLedger;
use App\Models\Scopes\CampusMemberScope;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    public function __construct(private CycleService $cycleService)
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
                'baseLimit'           => 0.00,
            ];
        }

        [$currentCycleStart] = $this->cycleService->current($member->campus_id);

        // --- STEP 4: FETCH THE CORRECT RELEVANT LIMIT ---
        // Look for a claim submitted *inside* this specific cycle window
        $latestClaimInCycle = $member->claims()
            ->where('submitted_at', '>=', $currentCycleStart)
            ->latest('submitted_at')
            ->first();

        // If they have a claim this cycle, use its limit snapshot. 
        // Otherwise, dynamically pull the current active System Setting limit!
        // $baseLimit = $latestClaimInCycle 
        //     ? (float) $latestClaimInCycle->annual_limit 
        //     : SystemSetting::getGlobalLimit();
        $baseLimit = (float) SystemSetting::getGlobalLimit($user->member->campus_id);

        // --- STEP 5: AGGREGATE CLAIMS WITHIN THIS SPECIFIC LIFECYCLE WINDOW ---
        $claimsQuery = $member->claims()->where('submitted_at', '>=', $currentCycleStart);

        $totalApprovedCycle = (float) (clone $claimsQuery)
            ->whereIn('status', ['approved', 'disbursed']) 
            ->sum('approved_amount');

        $totalDisbursedCycle = (float) (clone $claimsQuery)
            ->where('status', 'disbursed')
            ->sum('approved_amount');

        // --- STEP 6: COMPUTE BALANCES ---
        $approvedBalance = max(0, $baseLimit - $totalApprovedCycle);
        $actualBalance   = max(0, $baseLimit - $totalDisbursedCycle);

        return [
            'approvedBalance'     => $approvedBalance,
            'actualBalance'       => $actualBalance,
            'totalApprovedCycle'  => $totalApprovedCycle,
            'totalDisbursedCycle' => $totalDisbursedCycle,
            'cycleStartDate'      => $currentCycleStart->toDateString(),
            'baseLimit'           => $baseLimit, // Passed out so your view can display: "Limit: P23,000"
        ];
    }
}
