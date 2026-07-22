<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\TreasuryLedger;
use App\Models\Scopes\CampusMemberScope;
use App\Services\TreasuryLedgerService;
use App\Services\ContributionService;
use App\Services\DisbursementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TreasuryController extends Controller
{
     protected TreasuryLedgerService $treasuryService;
     protected ContributionService $contributionService;
     protected DisbursementService $disbursementService;
    public function __construct(
        TreasuryLedgerService $treasuryService,
        ContributionService $contributionService,
        DisbursementService $disbursementService
        )
    {
        $this->treasuryService = $treasuryService;
        $this->contributionService = $contributionService;
        $this->disbursementService = $disbursementService;
    }

   public function index(Request $request)
    {
        $user = Auth::user();
        $selectedCampusId = $request->input('campus_filter');
        // 1. Check permissions and return the appropriate service payload
        if ($user->hasRole('president') || $user->can('view all campus data')) {
            $data = $this->treasuryService->getPresidentialMetrics(
                $request->input('campus_filter')
            );
        }else{
            // 2. Fallback for standard campus members
            $data = $this->treasuryService->getStandardMetrics();
        }

         // 2. Fetch the contextual aggregates from your external separate services
        $data['totalContributions'] = $this->contributionService->getTotalContributions($selectedCampusId);
        $data['totalDisbursements'] = $this->disbursementService->getTotalDisbursements($selectedCampusId);

        return view('treasury.index', $data);
    }

}
