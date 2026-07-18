<?php

namespace App\Services;

use App\Models\TreasuryLedger;
use App\Models\Scopes\CampusMemberScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class TreasuryLedgerService
{
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
}
