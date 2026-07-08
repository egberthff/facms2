<?php

namespace App\Services;

use App\Models\User;
use App\Models\Member;
use App\Models\Claim;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
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
            'totalClaims'    => Claim::count(), // Fixed bug: previously assigned to rejected count
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

        // Replace these placeholders with actual queries when your database schema is ready
        return [
            'title'              => 'Main',
            'totalClaims'        => $member->claims()->count(), 
            'processing'         => $member->claims()->where('status', 'processing')->count(), // Example status query
            'disbursed'          => $member->claims()->where('status', 'disbursed')->count(),  // Example status query
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

        // --- STEP 1: FIND WHEN THE COUNTING TRULY STARTED ---
        $firstClaim = $member->claims()->oldest('submitted_at')->first();
        $absoluteStart = $firstClaim ? Carbon::parse($firstClaim->submitted_at) : Carbon::parse($member->created_at);

        $now = now();

        // --- STEP 2: CALCULATE NUMBER OF OCTOBER CROSSINGS ---
        $octoberCrossings = 0;
        for ($year = $absoluteStart->year; $year <= $now->year; $year++) {
            $octoberFirst = Carbon::create($year, 10, 1, 0, 0, 0);
            if ($octoberFirst->greaterThanOrEqualTo($absoluteStart) && $octoberFirst->lessThanOrEqualTo($now)) {
                $octoberCrossings++;
            }
        }

        // --- STEP 3: DETERMINE THE STARTING DATE OF THE CURRENT 2-YEAR CYCLE ---
        $completedCycles = floor($octoberCrossings / 2);

        if ($completedCycles == 0) {
            $currentCycleStart = $absoluteStart->startOfDay();
        } else {
            $cycleStartYear = $absoluteStart->year + ($completedCycles * 2);
            if ($absoluteStart->month >= 10) {
                $cycleStartYear--;
            }
            $currentCycleStart = Carbon::create($cycleStartYear, 10, 1, 0, 0, 0);
        }

        // --- STEP 4: FETCH THE CORRECT RELEVANT LIMIT ---
        // Look for a claim submitted *inside* this specific cycle window
        $latestClaimInCycle = $member->claims()
            ->where('submitted_at', '>=', $currentCycleStart)
            ->latest('submitted_at')
            ->first();

        // If they have a claim this cycle, use its limit snapshot. 
        // Otherwise, dynamically pull the current active System Setting limit!
        $baseLimit = $latestClaimInCycle 
            ? (float) $latestClaimInCycle->annual_limit 
            : SystemSetting::getGlobalLimit();

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
