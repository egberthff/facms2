<?php

namespace App\Services;

use App\Models\User;
use App\Models\Member;
use App\Models\Claim;
use App\Models\ContributionBatch;
use App\Models\ContributionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;

class ContributionService
{
    /**
     * Store a new contribution batch and generate detail records for all active members.
     */
    public function createBatch(array $data): ContributionBatch
    {
        return DB::transaction(function () use ($data) {
            $claim = Claim::findOrFail($data['claim_id']);
            $amountPerMember = (float) $data['amount_per_member'];

            // Fetch all active members who need to contribute
            $activeMembers = Member::where('status', 'active')->get();
            $totalMembers = $activeMembers->count();

            if ($totalMembers === 0) {
                throw new Exception('Cannot create a batch because there are no active members.');
            }

            $totalAmount = $amountPerMember * $totalMembers;

            // 1. Create the Batch Record
            $batch = ContributionBatch::create([
                'claim_id'          => $claim->id,
                'amount_per_member' => $amountPerMember,
                'total_members'     => $totalMembers,
                'total_amount'      => $totalAmount, 
                'generated_by'      => Auth::id(),
                'generated_at'      => now(),
            ]);

            // 2. Generate Individual Contribution Details
            foreach ($activeMembers as $member) {
                ContributionDetail::create([
                    'contribution_batch_id' => $batch->id,
                    'member_id'             => $member->id,
                    'amount'                => $amountPerMember,
                    'status'                => 'pending',
                    'paid_at'               => null, 
                ]);
            }

            return $batch;
        });
    }

    /**
     * View all batches with optional filters and pagination.
     */
    public function getFilteredBatches(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ContributionBatch::with(['claim', 'generatedBy']);

        if (!empty($filters['claim_id'])) {
            $query->where('claim_id', $filters['claim_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('generated_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('generated_at', '<=', $filters['end_date']);
        }

        return $query->latest('generated_at')->paginate($perPage);
    }

    /**
     * View contributions owed by a specific user's member account.
     */
    public function getUserOwedContributions(User $user, string $status = null): Collection
    {
        $member = $user->member;

        if (!$member) {
            return new Collection();
        }

        return ContributionDetail::with([
                    // 1. Load the batch
                    'contributionBatch', 
                    // 2. Load the claim via the batch
                    'contributionBatch.claim', 
                    // 3. Load the specific claimant member via the claim
                    'contributionBatch.claim.member', 
                    // 4. Load the type of claim via the claim
                    'contributionBatch.claim.claimType'
                ])
                ->where('member_id', $member->id)
                ->when($status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->latest('created_at')
                ->get();
    }

    /**
     * Record a payment for an individual contribution detail line item.
     */
    public function markAsPaid(string $detailId): ContributionDetail
    {
        $detail = ContributionDetail::findOrFail($detailId);
        
        $detail->update([
            'status'  => 'Paid',
            'paid_at' => now(),
        ]);

        return $detail;
    }
}
