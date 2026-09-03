<?php

namespace App\Services;

use App\Models\User;
use App\Models\Member;
use App\Models\Claim;
use App\Models\Campus;
use App\Models\Scopes\CampusMemberScope;
use App\Models\ContributionBatch;
use App\Models\ContributionDetail;
use App\Models\AnnualContribution;
use App\Models\SystemSetting;
use App\Services\TreasuryLedgerService;
USE App\Enums\ContributionStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Exception;
use InvalidArgumentException;

class ContributionService
{
    private TreasuryLedgerService $ledgerService;

    public function __construct(TreasuryLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }
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
     * Generate annual contribution batch record
     */
    public function generateAnnualContributionsBatch(int $year, ?string $campus_filter = null): array
    {
        if(blank($year) || $year < 2000 || $year > (now()->year + 1)) {
            throw new Exception("Invalid year provided for annual contribution generation.");
        }
        if (blank($campus_filter)) {
            // Bulletproof fallback: drops back to null safely if relationships don't exist
            $campus_filter = Auth::user()->member?->campus?->id;
        }

        return DB::transaction(function () use ($year, $campus_filter) {

            // 1. Strict Fetch for the setup configuration
           $setting = SystemSetting::where('setting_key', 'annual_contribution')
            ->where(function($query) use ($year) {
                $query->where('cycle_label', (string) $year)
                      ->orWhereNull('cycle_label');
            })
            // FIX: Dynamically appends campus filtering if $campus_filter is not null
            ->when($campus_filter, function($query, $campus_filter) {
                return $query->where('campus_id', $campus_filter); 
            })
            // MySQL-compatible alternative to NULLS LAST
            ->orderByRaw('cycle_label IS NULL ASC') 
            ->orderByDesc('cycle_label')
            ->first();

            // 2. Clear, actionable error message instead of a hardcoded fallback
            if (!$setting) {
                throw new Exception(
                    "Generation failed: The standard annual contribution amount configuration is missing. " .
                    "Please navigate to System Settings and create a setup record with the key 'annual_contribution' " .
                    "and a valid numeric value before attempting to mass-generate batches."
                );
            }

            $fixedAmount = (float) $setting->setting_value;

            // 3. Fetch active members who do not have records yet for this year
            $membersToProcess = Member::where('membership_status', 'active')
                ->when($campus_filter, function($query, $campus_filter) {
                    return $query->where('campus_id', $campus_filter); 
                })
                ->whereDoesntHave('annualContributions', function ($query) use ($year) {
                    $query->where('contribution_year', $year);
                })->get();

            $totalMembers = $membersToProcess->count();

            if ($totalMembers === 0) {
                throw new Exception("All active members already have a contribution record for the year {$year}.");
            }

            $batchTimestamp = now()->format('YmdHis');

            // 4. Generate records using the dynamic amount
            foreach ($membersToProcess as $index => $member) {
                AnnualContribution::create([
                    'id'                       => (string) Str::uuid(),
                    'reference_number'         => 'AC-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                    'member_id'                => $member->id,
                    'payment_method_id'        => null,
                    'payment_reference_number' => null,
                    'contribution_year'        => $year,
                    'amount_paid'              => $fixedAmount,
                    'status'                   => 'Pending',
                    'remarks'                  => "Mass-generated annual contribution allocation. Configured Rate: ₱" . number_format($fixedAmount, 2),
                    'paid_at'                  => null,
                ]);
            }

            return [
                'success' => true,
                'records_created' => $totalMembers,
                'year' => $year,
                'amount_applied' => $fixedAmount
            ];
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

    public function getUserOwedContributionsAnnual(User $user, string $status = null): Collection
    {
        $member = $user->member;
        
        if (!$member) {
            return new Collection();
        }

        return AnnualContribution::with(['member', 'paymentMethod'])
                                    ->where('member_id', $member->id)
                                    ->when($status, function ($query, $status){
                                        return $query->where('status');
                                    })
                                    ->latest('created_at')
                                    ->get();
    }

    /**
     * Record a payment for an individual contribution detail line item.
     */
    public function markAsPaid(string $id, string $tag, array $data = [])
    {
        return DB::transaction(function () use ($id, $tag, $data) {
            
            $detail = match ($tag) {
                'claims' => ContributionDetail::findOrFail($id),
                'annual' => AnnualContribution::findOrFail($id),
                default  => throw new InvalidArgumentException("Invalid contribution tag: {$tag}"),
            };

            $detail->update([
                'status'  => ContributionStatus::Paid->value,
                'paid_at' => now(),
            ]);

            $name = ($tag === 'claims') ? 'Claims' : 'Annual';
            $description = "Receipt for {$name} Contribution - Member ID: " . $detail->member_id;

            $this->ledgerService->recordTransaction([
                'campus_id'        => $detail->member->campus_id,
                'sourceable_type'  => get_class($detail),
                'sourceable_id'    => $detail->id,
                'reference_number' => $data['reference_number'] ?? 'GL-' . time(),
                'description'      => $description,
                'type'             => 'Credit',
                'amount'           => $detail->amount,
            ]);

            return $detail;
        });
    }

     /**
     * Generate administrative batch dashboard payloads with campus scoping metrics.
     */
    public function getClaimsContributionData(?string $selectedCampusId = null): array
    {
        $hasFullAccess = Auth::user()->can('view all campus data') || Auth::user()->hasAnyRole(['President', 'Treasurer']);
        // 1. Prepare base query builder instance
        $query = ContributionBatch::with([
            'claim.member', 
            'claim.claimType', 
            'contributionDetails.member'
        ]);

        // 2. Apply Presidential Campus Filter logic
        if ($hasFullAccess) {
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
            'isPresident'      => $hasFullAccess,
            'selectedCampusId' => $selectedCampusId,
            'allCampuses'      => Campus::where('is_active', true)->get(), // Reused your variable naming structure for alignment 
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
        $hasFullAccess = Auth::user()->can('view all campus data') || Auth::user()->hasAnyRole(['President', 'Treasurer']);

        // 1. Corrected base query eager loading
        $query = AnnualContribution::with(['member.campus']);

        // 2. Apply Presidential Campus Filter logic
        if ($hasFullAccess) {
            $query->withoutGlobalScope(CampusMemberScope::class);

            if ($selectedCampusId) {
                $query->whereHas('member', function ($q) use ($selectedCampusId) {
                    $q->where('campus_id', $selectedCampusId);
                });
            }
        }

        // 3. Process records, structuring them to MATCH the claims batches structure
        $batches = $query->latest('contribution_year')->get()->map(function (AnnualContribution $annualContribution) {
            $member = $annualContribution->member;

            // Match statuses against your Migration enum values: 'Pending', 'Verified', 'Failed'
            // We use lowercase 'verified' to match your claim details string style standard
            $status = strtolower((string) $annualContribution->status); 
            $isCollected = ($status === 'verified' || $status === 'paid');

            // Mock a single-item "details" array so it mirrors the structural signature of a claim batch
            $details = [
                [
                    'id'              => $annualContribution->id,
                    'member_name'     => trim(sprintf('%s %s %s', $member?->first_name, $member?->middle_name, $member?->last_name)),
                    'employee_number' => $member?->employee_number ?? 'N/A',
                    'department'      => $member?->department ?? 'N/A',
                    'amount'          => (float) $annualContribution->amount_paid,
                    'status'          => $status,
                    'paid_at'         => $annualContribution->paid_at ? Carbon::parse($annualContribution->paid_at)->format('M d, Y h:i A') : '—',
                ]
            ];

            return [
                'id'                  => $annualContribution->id,
                'claim_id'            => $annualContribution->contribution_year, // Null because it's a fixed annual payment, not a claim
                'claim_number'        => $annualContribution->reference_number,
                'claimant'            => trim(sprintf('%s %s %s', $member?->first_name, $member?->middle_name, $member?->last_name)),
                'claim_type'          => 'Annual Contribution',
                'campus_acronym'      => $member?->campus?->acronym ?? 'SYS', 

                // Financial payload alignment
                'collectible_amount'  => (float) $annualContribution->amount_paid,
                'collected_amount'    => $isCollected ? (float) $annualContribution->amount_paid : 0.00,

                // Reused 'created_at' as generation timestamp for interface mapping
                'generated_at'        => $annualContribution->created_at ? Carbon::parse($annualContribution->created_at)->format('M d, Y h:i A') : '—',
                'total_members'       => 1, // Always 1 because it represents this individual member's record

                // Injected mock detail profile matching the format of $details in your reference function
                'details'             => $details,
                'payment_reference_number' => $annualContribution->payment_reference_number ?? 'N/A',
                'contribution_year'   => $annualContribution->contribution_year,
            ];
        });

        // 4. Return identical array payload signatures
        return [
            'isPresident'      => $hasFullAccess,
            'selectedCampusId' => $selectedCampusId,
            'allCampuses'      => Campus::where('is_active', true)->get(), 
            'batches'          => $batches->values()->all(),
        ];
    }

     /**
     * Get the total paid contributions sum, filtered by optional campus.
     */
    public function getTotalContributions(?string $selectedCampusId = null, ?string $cycle = null): float
    {
        $query = ContributionDetail::where('contribution_details.status', 'Paid')
            ->join('members', 'contribution_details.member_id', '=', 'members.id');

        if ($selectedCampusId) {
            $query->where('members.campus_id', $selectedCampusId);
        }

        if ($cycle && $selectedCampusId) {
            [$cycleStart, $cycleEnd] = app(\App\Services\CycleService::class)->resolve($cycle, $selectedCampusId);
            $query->whereBetween('contribution_details.paid_at', [$cycleStart, $cycleEnd]);
        }

        return (float) $query->sum('contribution_details.amount');
    }

}
