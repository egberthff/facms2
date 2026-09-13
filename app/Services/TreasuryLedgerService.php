<?php

namespace App\Services;

use App\Models\TreasuryLedger;
use App\Models\Campus;
use App\Models\FundTransfer;
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

    public function postApprovedTransfer(FundTransfer $transfer, int|string $approverId): FundTransfer
    {
        return DB::transaction(function () use ($transfer, $approverId) {
            $transfer = FundTransfer::query()->lockForUpdate()->findOrFail($transfer->id);

            if ($transfer->status !== 'pending') {
                throw new Exception('Only pending transfers can be approved.');
            }

            $isRemittance = $transfer->type === 'campus_remittance';
            $sourceCampusId = $isRemittance ? $transfer->source_campus_id : null;
            $destinationCampusId = $isRemittance ? null : $transfer->destination_campus_id;
            $sourceType = $isRemittance ? 'Debit' : 'Debit';
            $destinationType = $isRemittance ? 'Credit' : 'Credit';

            $sourceEntry = $this->recordTransferEntry([
                'campus_id' => $sourceCampusId,
                'fund_account_id' => $isRemittance ? null : $transfer->fund_account_id,
                'transfer_id' => $transfer->id,
                'reference_number' => $transfer->reference_number . '-SRC',
                'description' => $transfer->description,
                'type' => $sourceType,
                'amount' => $transfer->amount,
            ]);

            $destinationEntry = $this->recordTransferEntry([
                'campus_id' => $destinationCampusId,
                'fund_account_id' => $isRemittance ? $transfer->fund_account_id : null,
                'transfer_id' => $transfer->id,
                'reference_number' => $transfer->reference_number . '-DST',
                'description' => $transfer->description,
                'type' => $destinationType,
                'amount' => $transfer->amount,
            ]);

            $transfer->update([
                'status' => 'approved',
                'approved_by' => $approverId,
                'approved_at' => now(),
                'source_ledger_id' => $sourceEntry->id,
                'destination_ledger_id' => $destinationEntry->id,
            ]);

            return $transfer->fresh();
        });
    }

    private function recordTransferEntry(array $data): TreasuryLedger
    {
        $query = TreasuryLedger::withoutGlobalScope(CampusMemberScope::class)
            ->lockForUpdate()
            ->when($data['campus_id'], fn ($query) => $query->where('campus_id', $data['campus_id']))
            ->when($data['fund_account_id'], fn ($query) => $query->where('fund_account_id', $data['fund_account_id']))
            ->latest('created_at')
            ->latest('id');

        $latestRow = $query->first();
        $previousBalance = (float) ($latestRow?->running_balance ?? 0);
        $newBalance = $data['type'] === 'Credit'
            ? $previousBalance + (float) $data['amount']
            : $previousBalance - (float) $data['amount'];

        return TreasuryLedger::withoutGlobalScope(CampusMemberScope::class)->create([
            'campus_id' => $data['campus_id'],
            'fund_account_id' => $data['fund_account_id'],
            'sourceable_type' => FundTransfer::class,
            'sourceable_id' => $data['transfer_id'],
            'transfer_id' => $data['transfer_id'],
            'reference_number' => $data['reference_number'],
            'description' => $data['description'],
            'type' => $data['type'],
            'amount' => $data['amount'],
            'running_balance' => $newBalance,
            'entry_by' => Auth::id(),
        ]);
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
