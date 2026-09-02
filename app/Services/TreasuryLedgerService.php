<?php

namespace App\Services;

use App\Models\TreasuryLedger;
use App\Models\Campus;
use App\Models\Scopes\CampusMemberScope;
use App\Services\CycleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class TreasuryLedgerService
{
    public function __construct(private CycleService $cycleService)
    {
    }

    /**
     * Safely record a transaction and update the rolling balance for a campus.
     */
    public function recordTransaction(array $data): TreasuryLedger
    {
        // Force wrap everything inside a strict database transaction
        return DB::transaction(function () use ($data) {
            $campusId = $data['campus_id'];
            $type = ucfirst($data['type']); // Ensure 'Credit' or 'Debit'
            $amount = $data['amount'];

            // 1. Lock the latest row for this campus to block race conditions
            $latestRow = TreasuryLedger::withoutGlobalScope(CampusMemberScope::class)
                ->where('campus_id', $campusId)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->lockForUpdate() // Crucial structural safety check
                ->first();

            $previousBalance = $latestRow ? (float) $latestRow->running_balance : 0.00;

            // 2. Mathematically compute the new running balance
            if ($type === 'Credit') {
                $newBalance = $previousBalance + $amount;
            } elseif ($type === 'Debit') {
                $newBalance = $previousBalance - $amount;
            } else {
                throw new Exception("Invalid transaction type provided.");
            }

            // 3. Write out the verified ledger row
            return TreasuryLedger::create([
                'campus_id'         => $campusId,
                'sourceable_type'   => $data['sourceable_type'] ?? null,
                'sourceable_id'     => $data['sourceable_id'] ?? null,
                'reference_number'  => $data['reference_number'],
                'description'       => $data['description'],
                'type'              => $type,
                'amount'            => $amount,
                'running_balance'   => $newBalance,
                'entry_by'          => Auth::id(),
            ]);
        });
    }

     /**
     * Fetch standard metrics for a regular campus user.
     */
        public function getStandardMetrics(?string $cycle = null, ?string $campusId = null): array
        {
            $ledgerQuery = TreasuryLedger::query();
            if ($cycle) {
                [$cycleStart, $cycleEnd] = $this->cycleService->resolve($cycle, $campusId);
                $ledgerQuery->whereBetween('created_at', [$cycleStart, $cycleEnd]);
            }
            $entries = (clone $ledgerQuery)->with('campus')->orderBy('created_at', 'desc')->paginate(15);
            $latestLedger = (clone $ledgerQuery)->orderBy('created_at', 'desc')->first();

            return [
                'isPresident' => false,
                'entries' => $entries, 
                'currentBalance' => (float) ($latestLedger?->running_balance ?? 0.00),
                'totalContributions' => 0.00,
                'totalDisbursements' => 0.00
            ];
        }

        /**
         * Fetch global aggregate metrics across all campuses for administrators.
         */
        public function getPresidentialMetrics(?string $selectedCampusId = null, ?string $cycle = null): array
        {
            $allCampuses = Campus::select('campuses.id', 'campuses.acronym')
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

            $totalGlobalBalance = (float) $allCampuses->sum('latest_balance');

            $currentBalance = $selectedCampusId
                ? (float) ($allCampuses->firstWhere('id', $selectedCampusId)?->latest_balance ?? 0.00)
                : 0.00;

            $filteredCampuses = $selectedCampusId 
                ? $allCampuses->where('id', $selectedCampusId) 
                : $allCampuses;

            $ledgerQuery = TreasuryLedger::withoutGlobalScope(CampusMemberScope::class);

            if ($selectedCampusId) {
                $ledgerQuery->where('campus_id', $selectedCampusId);
            }

            if ($cycle && $selectedCampusId) {
                [$cycleStart, $cycleEnd] = $this->cycleService->resolve($cycle, $selectedCampusId);
                $ledgerQuery->whereBetween('created_at', [$cycleStart, $cycleEnd]);
            }

            $entries = $ledgerQuery->with('campus')
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(15);

            return [
                'isPresident' => true,
                'allCampuses' => $allCampuses,             // Populates the entire selection dropdown menu
                'filteredCampuses' => $filteredCampuses,   // Controls the dynamic balance summary cards
                'totalGlobalBalance' => $totalGlobalBalance,
                'currentBalance' => $cycle && $selectedCampusId
                    ? (float) ($entries->first()?->running_balance ?? 0.00)
                    : $currentBalance,
                'entries' => $entries,
                'selectedCampusId' => $selectedCampusId ? (string) $selectedCampusId : null,
                'totalContributions' => 0.00, 
                'totalDisbursements' => 0.00 
            ];
        }

}
