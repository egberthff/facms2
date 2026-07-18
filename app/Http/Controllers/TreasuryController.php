<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\TreasuryLedger;
use App\Models\Scopes\CampusMemberScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TreasuryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if the user is authorized to see everything
        if ($user->hasRole('president') || $user->can('view all campus data')) {
            return $this->renderPresidentialMetrics($request);
        }

        // Standard user fallback: Laravel automatically applies CampusMemberScope
        $ledgerEntries = TreasuryLedger::orderBy('created_at', 'desc')->paginate(15);
        $currentBalance = TreasuryLedger::orderBy('created_at', 'desc')->first()?->running_balance ?? 0.00;

        return view('treasury.index', [
            'isPresident' => false,
            'entries' => $ledgerEntries,
            'currentBalance' => $currentBalance
        ]);
    }

    /**
     * Generates a multi-campus breakdown specifically for administrative accounts.
     */
    protected function renderPresidentialMetrics(Request $request)
    {
        // 1. Get the latest running balance for EVERY single campus in a single clean query
        // This builds an array of rows containing campus name, id, and their current cash balance
        $campusBalances = Campus::select('campuses.id', 'campuses.acronym')
            ->where('campuses.is_active', true)
            ->selectSub(function ($query) {
                $query->select('running_balance')
                    ->from('treasury_ledger')
                    ->whereColumn('treasury_ledger.campus_id', 'campuses.id')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->limit(1);
            }, 'latest_balance')
            ->get();

        // Calculate total combined organization cash pool
        $totalGlobalBalance = $campusBalances->sum('latest_balance');

        // 2. Handle optional per-campus filtering dropdown selection
        $selectedCampusId = $request->input('campus_filter');
        
        $ledgerQuery = TreasuryLedger::withoutGlobalScope(CampusMemberScope::class);

        if ($selectedCampusId) {
            $ledgerQuery->where('campus_id', $selectedCampusId);
        }

        $ledgerEntries = $ledgerQuery->orderBy('created_at', 'desc')->paginate(15);

        return view('treasury.index', [
            'isPresident' => true,
            'campusBalances' => $campusBalances,
            'totalGlobalBalance' => $totalGlobalBalance,
            'entries' => $ledgerEntries,
            'selectedCampusId' => $selectedCampusId
        ]);
    }
}
