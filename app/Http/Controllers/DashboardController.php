<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Member;
use App\Models\Claim;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        if (Auth::user()->hasRole('Member')) {
            return view('dashboard.member', [
                'totalCliams' => 1000,
                'processing' => 4,
                'disbursed' => 5,
                'pending' => 7,
                'balance' => 10000,
                'recentTransactions' => [],
                'title' => 'Main'
            ]);
        }

        return view('dashboard.admin', [
            'members' => Member::count(),
            'pendingClaims' => Claim::pending()->count(),
            'approvedClaims' => Claim::approved()->count(),
            'rejectedClaims' => Claim::rejected()->count(),
            'totalClaims' => Claim::rejected()->count(),
            'title' => 'Admin'
        ]);
    }

    public function index()
    {
        $user = Auth::user();

        if ($user && $user->hasAnyRole([
            'President',
            'Treasurer',
        ])) {
            return view('dashboard.admin', [
                'members' => Member::count(),
                'pendingClaims' => Claim::pending()->count(),
                'approvedClaims' => Claim::approved()->count(),
                'rejectedClaims' => Claim::rejected()->count(),
            ]);
        }

        return view('dashboard.member');
    }

    public function memberBalance(Request $request){
        {
            $user = Auth::user();
    
            // 1. Authorize against the DashboardPolicy
            // Get::authorize('viewMetrics', ['dashboard', $user]);
    
            // 2. Safely capture the associated member record
            $member = $user->member;
    
            if (!$member) {
                return view('dashboard', [
                    'balanceThisYear' => 0.00,
                    'disbursedThisMonth' => 0.00
                ]);
            }
    
            // 3. Extract annual limit configuration setup from the most recent claim
            $latestClaim = $member->claims()->latest('submitted_at')->first();
            $annualLimit = $latestClaim ? (float) $latestClaim->annual_limit : 23000.00;
    
            // 4. Calculate total metrics
            $approvedYearTotal = $member->claims()->approvedThisYear()->sum('approved_amount');
            $data['balanceThisYear'] = max(0, $annualLimit - $approvedYearTotal);
    
            $data['disbursedThisMonth'] = $member->claims()->disbursedThisMonth()->sum('approved_amount');
    
            return view('balance.member-balance', $data);
        }
    }
}