<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\TreasuryLedger;
use App\Models\Scopes\CampusMemberScope;
use App\Services\TreasuryLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TreasuryController extends Controller
{
     protected $treasuryService;
    public function __construct(TreasuryLedgerService $treasuryService)
    {
        $this->treasuryService = $treasuryService;
    }

   public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Check permissions and return the appropriate service payload
        if ($user->hasRole('president') || $user->can('view all campus data')) {
            $data = $this->treasuryService->getPresidentialMetrics(
                $request->input('campus_filter')
            );
            // \Log::error(print_r($data, true));
            return view('treasury.index', $data);
        }

        // 2. Fallback for standard campus members
        $data = $this->treasuryService->getStandardMetrics();

        return view('treasury.index', $data);
    }

}
