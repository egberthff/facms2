<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\TreasuryLedger;
use App\Models\Scopes\CampusMemberScope;
use App\Services\TreasuryLedgerService;
use App\Services\ContributionService;
use App\Services\DisbursementService;
use App\Services\CycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TreasuryController extends Controller
{
     protected TreasuryLedgerService $treasuryService;
     protected ContributionService $contributionService;
     protected DisbursementService $disbursementService;
    protected CycleService $cycleService;
    public function __construct(
        TreasuryLedgerService $treasuryService,
        ContributionService $contributionService,
        DisbursementService $disbursementService,
        CycleService $cycleService
        )
    {
        $this->treasuryService = $treasuryService;
        $this->contributionService = $contributionService;
        $this->disbursementService = $disbursementService;
        $this->cycleService = $cycleService;
    }

   public function index(Request $request)
    {
        $user = Auth::user();
        $isPresident = $user->can('view all campus data') || $user->hasAnyRole(['Federated President', 'Federated Treasurer']);
        $selectedCampusId = $isPresident ? $request->input('campus_filter') : $user->getCachedCampusId();
        $cycle = $request->input('cycle');
        // 1. Check permissions and return the appropriate service payload
        if ($user->can('view all campus data') || $user->hasAnyRole(['Federated President', 'Federated Treasurer'])) {
            $data = $this->treasuryService->getPresidentialMetrics(
                $selectedCampusId,
                $cycle
            );
        }else{
            // 2. Fallback for standard campus members
            $data = $this->treasuryService->getStandardMetrics($cycle, $selectedCampusId);
        }

         // 2. Fetch the contextual aggregates from your external separate services
        $data['totalContributions'] = $this->contributionService->getTotalContributions($selectedCampusId, $cycle);
        $data['totalDisbursements'] = $this->disbursementService->getTotalDisbursements($selectedCampusId, $cycle);
        $data['selectedCycle'] = $cycle;
        $data['cycleOptions'] = $selectedCampusId ? $this->cycleService->list($selectedCampusId) : [];
        $data['isPresident'] = $isPresident;

        return view('treasury.index', $data);
    }

}
