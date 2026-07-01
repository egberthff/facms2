<?php

namespace App\Http\Controllers;
use App\Models\Member;
use App\Models\Claim;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        if (Auth::user()->hasRole('Member')) {
            return view('dashboard.member');
        }

        return view('dashboard.admin', [
            'members' => Member::count(),
            'pendingClaims' => Claim::pending()->count(),
            'approvedClaims' => Claim::approved()->count(),
            'rejectedClaims' => Claim::rejected()->count(),
            'totalClaims' => Claim::rejected()->count(),
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
}