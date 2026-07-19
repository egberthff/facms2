<?php

namespace App\Services;

use App\Models\User;
use App\Models\Member;
use App\Models\Claim;
use App\Models\Campus;
use App\Models\Scopes\CampusMemberScope;
use App\Models\ContributionBatch;
use App\Models\ContributionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
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

    public function getBatchMonitoringDetails(string $batchId): ContributionBatch
    {
        return ContributionBatch::with([
            'claim.member', 
            'claim.claimType',
            'contributionDetails.member' // Pulls the members assigned to pay this batch
        ])->findOrFail($batchId);
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

     /**
     * Generate administrative batch dashboard payloads with campus scoping metrics.
     */
    public function getClaimsContributionData(?string $selectedCampusId = null): array
    {
        $isPresident = Auth::user()->hasRole('president') || Auth::user()->can('view all campus data');
        // 1. Prepare base query builder instance
        $query = ContributionBatch::with([
            'claim.member', 
            'claim.claimType', 
            'contributionDetails.member'
        ]);

        // 2. Apply Presidential Campus Filter logic
        if ($isPresident) {
            // Bypass Laravel Global Scopes if viewing global multi-campus matrix
            $query->withoutGlobalScope(CampusMemberScope::class);
            
            if ($selectedCampusId) {
                // Assuming your claims or members link the batch to a specific physical campus location
                $query->whereHas('claim.member', function ($q) use ($selectedCampusId) {
                    $q->where('campus_id', $selectedCampusId);
                });
            }
        }

        // 3. Process records with optimization mapping loops
        $batches = $query->latest('generated_at')->get()->map(function (ContributionBatch $batch) {
            $details = $batch->contributionDetails->map(function ($detail) {
                $member = $detail->member;
                return [
                    'id'              => $detail->id,
                    'member_name'     => trim(sprintf('%s %s %s', $member?->first_name, $member?->middle_name, $member?->last_name)),
                    'employee_number' => $member?->employee_number ?? 'N/A',
                    'department'      => $member?->department ?? 'N/A',
                    'amount'          => (float) $detail->amount,
                    'status'          => strtolower((string) $detail->status),
                    'paid_at'         => $detail->paid_at ? Carbon::parse($detail->paid_at)->format('M d, Y h:i A') : '—',
                ];
            });

            return [
                'id'                  => $batch->id,
                'claim_id'            => $batch->claim_id,
                'claim_number'        => $batch->claim?->claim_number ?? 'N/A',
                'claimant'            => trim(sprintf('%s %s %s', $batch->claim?->member?->first_name, $batch->claim?->member?->middle_name, $batch->claim?->member?->last_name)),
                'claim_type'          => $batch->claim?->claimType?->name ?? 'N/A',
                'campus_acronym'      => $batch->claim?->member?->campus?->acronym ?? 'SYS', // Bound for view row indicator labels
                'collectible_amount'  => (float) $batch->total_amount,
                'collected_amount'    => (float) $details->where('status', 'paid')->sum('amount'),
                'generated_at'        => $batch->generated_at ? Carbon::parse($batch->generated_at)->format('M d, Y h:i A') : '—',
                'total_members'       => (int) $batch->total_members,
                'details'             => $details->values()->all(),
            ];
        });

        // 4. Return clean, raw view value payloads
        return [
            'isPresident'      => $isPresident,
            'selectedCampusId' => $selectedCampusId,
            'allCampuses'   => Campus::where('is_active', true)->get(), // Reused your variable naming structure for alignment 
            'batches'          => $batches->values()->all(),
        ];
    }

    /**
     * Summary of getAnnualMetrics
     * @param mixed $selectedCampusId
     * @return array{allCampuses: mixed, filteredCampuses: mixed, isPresident: bool}
     */
    public function getAnnualContributionData(?string $selectedCampusId = null): array
    {
        // Pro-Tip: You can extract the common $allCampuses query logic into a private 
        // service helper method to avoid duplicating it across these two functions!
        // $allCampuses = $this->getAllCampusesWithBalances(); 
        $allCampuses = []; 

        // Build out your annual structural arrays here...
        return [
            'isPresident' => true,
            'allCampuses' => $allCampuses,
            'filteredCampuses' => $selectedCampusId ? $allCampuses->where('id', $selectedCampusId) : $allCampuses,
            // ... include totalGlobalBalance, currentBalance, entries, etc.
        ];
    }

}
