<?php 

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Handles the root routing entry point to the dashboard layouts.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Member')) {
            $data = $this->dashboardService->getMemberMetrics($user);
            return view('dashboard.member', $data);
        }

        $data = $this->dashboardService->getAdminMetrics();
        return view('dashboard.admin', $data);
    }

    /**
     * Dedicated endpoint for the isolated member dashboard components.
     */
    public function memberDashboard(Request $request)
    {
        $data = $this->dashboardService->getMemberMetrics(Auth::user());

        return view('dashboard.member', $data);
    }

    /**
     * Dedicated endpoint to display financial metric logs.
     */
    public function memberBalance(Request $request)
    {
        // Gate::authorize('viewMetrics', ['dashboard', Auth::user()]);

        $data = $this->dashboardService->calculateMemberBalanceData(Auth::user());

        return view('balance.member-balance', $data);
    }
}
