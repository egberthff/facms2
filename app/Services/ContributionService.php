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
use App\Models\CollectionsAdjustments;
use App\Models\CNAShare;
use App\Models\MembershipFee;
use App\Services\TreasuryLedgerService;
USE App\Enums\ContributionStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
// use Illuminate\Support\Collection;
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

            if ($fixedAmount <= 0) {
                throw new Exception(
                    "Generation failed: The annual contribution amount for this campus must be greater than zero. " .
                    "Please update the 'annual_contribution' setting before generating records."
                );
            }

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
                'cna_share' => CNAShare::findOrFail($id),
                'membership_fee' =>MembershipFee::findOrFail($id),
                default  => throw new InvalidArgumentException("Invalid contribution tag: {$tag}"),
            };

            if ($tag == 'cna_share') {
                 $detail->update([
                    'paid_amount'  => $detail->due_amount,
                    'status'  => ContributionStatus::Paid->value,
                    'paid_at' => now(),
                ]);
            } else if ($tag == 'membership_fee') {
                $detail->update([
                    'status'  => ContributionStatus::Paid->value,
                    'paid_at' => now(),
                    'payment_method_id' => $data['payment_method_id'],
                    'reference_number' => $data['reference_number']
                ]);
            } else {
                $detail->update([
                    'status'  => ContributionStatus::Paid->value,
                    'paid_at' => now(),
                ]);
            }

            $name = match ($tag) {
                'claims' => 'Claims',
                'cna_share' => 'CNA Share',
                'annual' => 'Annual',
                'membership_fee' => 'Membership Fee',
                default => 'Standard'
            };
            $description = "Receipt for {$name} Contribution - Member ID: " . $detail->member_id;
            $amount = match ($tag) {
                'annual'    => $detail->amount_paid,
                'cna_share' => $detail->paid_amount,
                default     => $detail->amount,
            };


            if ($amount === null || (float) $amount < 0) {
                throw new InvalidArgumentException('The contribution amount must be a valid non-negative value.');
            }

            $this->ledgerService->recordTransaction([
                'campus_id'        => $detail->member->campus_id,
                'sourceable_type'  => get_class($detail),
                'sourceable_id'    => $detail->id,
                'reference_number' => $data['reference_number'] ?? 'GL-' . time(),
                'description'      => $description,
                'type'             => 'Credit',
                'amount'           => (float) $amount,
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
                'batch_reference'     => $batch->batch_reference,
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

    //Admin monitoring
    public function getCNAShareContributionData(?string $selectedCampusId = null): array
    {
        $hasFullAccess = Auth::user()->can('view all campus data') || Auth::user()->hasAnyRole(['President', 'Treasurer']);

        $batches = CNAShare::with(['member.campus'])
            ->when($hasFullAccess && $selectedCampusId, function ($query) use ($selectedCampusId) {
                $query->whereHas('member', function ($q) use ($selectedCampusId) {
                    $q->where('campus_id', $selectedCampusId);
                });
            })
            ->latest('created_at')
            ->get()
            ->map(function (CNAShare $cnaShare) {
                $member = $cnaShare->member;
                $status = strtolower((string) $cnaShare->status);

                return [
                    'id'                  => $cnaShare->id,
                    'claim_id'            => $cnaShare->year, // No claim association for CNA Share
                    'claim_number'        => $cnaShare->reference_number,
                    'claimant'            => trim(sprintf('%s %s %s', $member?->first_name, $member?->middle_name, $member?->last_name)),
                    'claim_type'          => 'CNA Share Contribution',
                    'campus_acronym'      => $member?->campus?->acronym ?? 'SYS',
                    'collectible_amount'  => (float) $cnaShare->due_amount,
                    'collected_amount'    => ($status === 'paid') ? (float) $cnaShare->paid_amount : 0.00,
                    'generated_at'        => $cnaShare->created_at ? Carbon::parse($cnaShare->created_at)->format('M d, Y h:i A') : '—',
                    'total_members'       => 1,
                    'details'             => [
                        [
                            'id'              => $cnaShare->id,
                            'member_name'     => trim(sprintf('%s %s %s', $member?->first_name, $member?->middle_name, $member?->last_name)),
                            'employee_number' => $member?->employee_number ?? 'N/A',
                            'department'      => $member?->department ?? 'N/A',
                            'amount'          => (float) $cnaShare->due_amount,
                            'status'          => $status,
                            'paid_at'         => $cnaShare->paid_at ? Carbon::parse($cnaShare->paid_at)->format('M d, Y h:i A') : '—',
                        ],
                    ],
                ];
            });   

        return [
            'isPresident'      => $hasFullAccess,
            'selectedCampusId' => $selectedCampusId,
            'allCampuses'      => Campus::where('is_active', true)->get(), 
            'batches'          => $batches->values()->all(),
        ];
    }

    public function getMembershipFeeData(?string $selectedCampusId = null): array
    {
        $hasFullAccess = Auth::user()->can('view all campus data') || Auth::user()->hasAnyRole(['President', 'Treasurer']);

        $batches = MembershipFee::with(['member.campus'])
            ->when($hasFullAccess && $selectedCampusId, function ($query) use ($selectedCampusId) {
                $query->whereHas('member', function ($q) use ($selectedCampusId) {
                    $q->where('campus_id', $selectedCampusId);
                });
            })
            ->latest('created_at')
            ->get()
            ->map(function (MembershipFee $membershipFee) {
                $member = $membershipFee->member;
                $status = strtolower((string) $membershipFee->status);

                return [
                    'id'                  => $membershipFee->id,
                    'claim_id'            => date('Y', strtotime($membershipFee->created_at) ?? ''), // No claim association for CNA Share
                    'claim_number'        => $membershipFee->reference_code,
                    'claimant'            => trim(sprintf('%s %s %s', $member?->first_name, $member?->middle_name, $member?->last_name)),
                    'claim_type'          => 'Membership Fee',
                    'campus_acronym'      => $member?->campus?->acronym ?? 'SYS',
                    'collectible_amount'  => (float) $membershipFee->amount,
                    'collected_amount'    => ($status === 'paid') ? (float) $membershipFee->amount : 0.00,
                    'generated_at'        => $membershipFee->created_at ? Carbon::parse($membershipFee->created_at)->format('M d, Y h:i A') : '—',
                    'total_members'       => 1,
                    'details'             => [
                        [
                            'id'              => $membershipFee->id,
                            'member_name'     => trim(sprintf('%s %s %s', $member?->first_name, $member?->middle_name, $member?->last_name)),
                            'employee_number' => $member?->employee_number ?? 'N/A',
                            'department'      => $member?->department ?? 'N/A',
                            'amount'          => (float) $membershipFee->amount,
                            'status'          => $status,
                            'paid_at'         => $membershipFee->paid_at ? Carbon::parse($membershipFee->paid_at)->format('M d, Y h:i A') : '—',
                        ],
                    ],
                ];
            });   

        return [
            'isPresident'      => $hasFullAccess,
            'selectedCampusId' => $selectedCampusId,
            'allCampuses'      => Campus::where('is_active', true)->get(), 
            'batches'          => $batches->values()->all(),
        ];
    }

    /**
     * Get members CNA shares contribution
     */
    public function getMemberCNAShareContributions(User $user, string $status = null): Collection
    {
        $member = $user->member;
        
        if (!$member) {
            return new Collection();
        }
        return CNAShare::with('member.user')->where('member_id', $member->id)->get();
    }

    /**
     * Get members Annual Duew contribution
     */
    public function gerMemberAnnualDueContributions(User $user, string $status = null): Collection
    {
        $member = $user->member;
        if(!$member){
            return new Collection();
        }
        return AnnualContribution::with('member.user')->where('member_id', $member->id)->get();
    }

    public function getMemberMembershipFee(User $user, string $status = null): Collection 
    {
        $member = $user->member;
        if(!$member){
            return new Collection();
        }
        return MembershipFee::with('member.user')->where('member_id', $member->id)->get();
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

    //Testing 3
    public function getContributionPerMonth(
    Request $request,
    ?string $campusId = null
): array {
    /*
     * =========================================================
     * 1. RESOLVE FISCAL YEAR
     * =========================================================
     *
     * FY N is named after the year in which it ENDS:
     *
     * FY 2026:
     *
     * October 15, 2025 00:00:00
     * through
     * October 14, 2026 23:59:59
     *
     * October 15 - December 31, 2025:
     * - Claim deductions (early-FY columns)
     *
     * January 2026:
     * - CNA Share
     * - Annual Dues
     * - Claim deductions
     *
     * February - October 2026:
     * - Normal claim deductions
     *
     * November 2026:
     * - Missed / unpaid deductions
     */

    $fiscalYear = $request->input('fiscal_year')
        ?? $request->input('reporting_year')
        ?? null;

    /*
     * Backward compatibility for the previous UI.
     *
     * A date on or after October 15 belongs to the fiscal year
     * ending in the following calendar year. Anything up to and
     * including October 14 belongs to the fiscal year ending in
     * that same calendar year.
     */
    if (!$fiscalYear && $request->filled('date_from')) {
        $selectedDate = Carbon::parse(
            $request->input('date_from')
        );

        $fiscalYear = $selectedDate->gte(
            Carbon::create($selectedDate->year, 10, 15)->startOfDay()
        )
            ? $selectedDate->year + 1
            : $selectedDate->year;
    }

    if (!$fiscalYear) {
        return [
            'period' => '',
            'header_months_amount' => [],
            'header_month_label' => [],
            'pre_january_month_label' => [],
            'total_monthly_collection_target' => [],
            'actual_monthly_collection' => [],
            'total_cna_share_collection_target' => [],
            'actual_cna_share_collection' => [],
            'total_annual_due_collection_target' => [],
            'actual_annual_due_collection' => [],
            'collection_adjustments' => [],
            'collection_adjustment_details' => [],
            'adjusted_monthly_collection' => [],
            'balance_collectible' => [],
            'cna_share' => [
                'amount' => 0,
                'total' => 0,
            ],
            'annual_due' => [
                'amount' => 0,
                'total' => 0,
            ],
            'members' => [],
        ];
    }

    $fiscalYear = (int) $fiscalYear;

    /*
     * =========================================================
     * 2. EXACT FISCAL-YEAR BOUNDARIES
     * =========================================================
     *
     * Edge cases:
     *
     * - Leap years cannot move the boundary. October 14 and 15
     *   exist in every year, and February 29 always falls inside
     *   the fiscal year, so no date math crosses it.
     * - startOfDay()/endOfDay() make both ends inclusive:
     *   Oct 15 00:00:00 is the first instant and Oct 14 23:59:59
     *   the last. A record at Oct 15 00:00:00 of FY's year belongs
     *   to FY + 1.
     * - Both bounds use the app timezone, the same as the
     *   timestamps Eloquent stores.
     */

    $dateFrom = Carbon::create(
        $fiscalYear - 1,
        10,
        15
    )->startOfDay();

    $dateTo = Carbon::create(
        $fiscalYear,
        10,
        14
    )->endOfDay();

    /*
     * January (CNA / Annual), February - October and the
     * settlement November all fall in the FY's own year.
     *
     * FY 2026 => report year 2026.
     */
    $reportYear = $fiscalYear;

    /*
     * CNA Share / Annual Dues are January obligations.
     */
    $shareYear = $reportYear;

    $januaryMonth = Carbon::create(
        $reportYear,
        1,
        1
    )->startOfMonth();

    $januaryMonthEnd = $januaryMonth
        ->copy()
        ->endOfMonth();

    /*
     * November is the settlement / unpaid deductions month.
     */
    $settlementMonth = Carbon::create(
        $reportYear,
        11,
        1
    )->startOfMonth();

    $settlementMonthEnd = $settlementMonth
        ->copy()
        ->endOfMonth();

    $settlementMonthKey = strtolower(
        $settlementMonth->format('F Y')
    );

    /*
     * =========================================================
     * 3. INITIALIZE REPORT ARRAYS
     * =========================================================
     */

    $headerMonthsAmount = [
        'total' => 0,
    ];

    $headerMonthLabel = [];

    $totalMonthlyCollectionTarget = [
        'total' => 0,
    ];

    $actualMonthlyCollection = [
        'total' => 0,
    ];

    $collectionAdjustments = [
        'total' => 0,
    ];

    $collectionAdjustmentDetails = [];

    $adjustedMonthlyCollection = [
        'total' => 0,
    ];

    $balanceCollectible = [
        'cna_share' => [],
        'annual_due' => [],
        'total' => 0,
    ];

    /*
     * =========================================================
     * 4. BUILD VISIBLE CLAIM-DEDUCTION MONTHS
     * =========================================================
     *
     * Based on the manual report, extended to cover the whole
     * fiscal year:
     *
     * Oct - Dec (FY-1) = Claim deductions from Oct 15 onward
     * January  = CNA Share / Annual Dues (own columns)
     * January  = Claim deductions
     * February = Claim deductions
     * ...
     * October  = Claim deductions up to Oct 14
     * November = Unpaid deductions
     *
     * The FY-1 months are also exposed as
     * pre_january_month_label so the view can render them
     * before the January CNA / Annual Dues columns.
     */

    $months = [];

    $preJanuaryMonthLabel = [];

    $claimMonthDates = [];

    for ($monthNumber = 10; $monthNumber <= 12; $monthNumber++) {
        $claimMonthDates[] = Carbon::create(
            $reportYear - 1,
            $monthNumber,
            1
        );
    }

    for ($monthNumber = 1; $monthNumber <= 10; $monthNumber++) {
        $claimMonthDates[] = Carbon::create(
            $reportYear,
            $monthNumber,
            1
        );
    }

    foreach ($claimMonthDates as $monthDate) {
        $monthKey = strtolower(
            $monthDate->format('F Y')
        );

        if ($monthDate->year < $reportYear) {
            $preJanuaryMonthLabel[] = $monthKey;
        }

        $months[$monthKey] = 0;

        $headerMonthsAmount[$monthKey] = 0;

        $totalMonthlyCollectionTarget[$monthKey] = 0;

        $actualMonthlyCollection[$monthKey] = 0;

        $collectionAdjustments[$monthKey] = 0;

        $adjustedMonthlyCollection[$monthKey] = 0;

        $balanceCollectible[$monthKey] = 0;

        $collectionAdjustmentDetails[$monthKey] = [];

        $headerMonthLabel[] = $monthKey;
    }

    /*
     * November settlement column.
     */
    $months[$settlementMonthKey] = 0;

    $headerMonthsAmount[$settlementMonthKey] = 0;

    $totalMonthlyCollectionTarget[$settlementMonthKey] = 0;

    $actualMonthlyCollection[$settlementMonthKey] = 0;

    $collectionAdjustments[$settlementMonthKey] = 0;

    $adjustedMonthlyCollection[$settlementMonthKey] = 0;

    $balanceCollectible[$settlementMonthKey] = 0;

    $collectionAdjustmentDetails[$settlementMonthKey] = [];

    $headerMonthLabel[] = $settlementMonthKey;

    /*
     * =========================================================
     * 5. GET CAMPUS MEMBERS
     * =========================================================
     */

    $members = Member::query()
        ->with('user')
        ->when(
            $campusId,
            function ($query, $campusId) {
                $query
                    ->withoutGlobalScope(
                        CampusMemberScope::class
                    )
                    ->where(
                        'campus_id',
                        $campusId
                    );
            }
        )
        ->get();

    $memberIds = $members->pluck('id');

    /*
     * =========================================================
     * 6. INITIALIZE MEMBER ROWS
     * =========================================================
     */

    $memberData = [];

    foreach ($members as $member) {
        $memberData[$member->id] = [
            'member_id' => $member->id,

            'name' => $member->user->name ?? '',

            /*
             * February -> October
             * plus November unpaid deductions.
             */
            'months' => $months,

            /*
             * Calculated again after all deductions have
             * been assigned to their final report columns.
             */
            'total_contributed' => 0,

            /*
             * January CNA Share column.
             */
            'cna_share' => [
                $shareYear => 0,
            ],

            /*
             * January Annual Dues column.
             */
            'annual_due' => [
                $shareYear => 0,
            ],
        ];
    }

    /*
     * =========================================================
     * 7. REPORT-LEVEL CNA / ANNUAL STRUCTURES
     * =========================================================
     *
     * amount = amount expected from ONE member
     * total  = amount expected from ALL applicable members
     */

    $reportCnaShare = [
        'amount' => 0,
        'total' => 0,
    ];

    $reportAnnualDues = [
        'amount' => 0,
        'total' => 0,
    ];

    $totalCNAShareCollectionTarget = [
        $shareYear => [
            'total' => 0,
        ],
    ];

    $actualCNAShareCollectionTarget = [
        $shareYear => [
            'total' => 0,
        ],
    ];

    $totalAnnualDueCollectionTarget = [
        $shareYear => [
            'total' => 0,
        ],
    ];

    $actualAnnualDueCollectionTarget = [
        $shareYear => [
            'total' => 0,
        ],
    ];

    $balanceCollectible['cna_share'][$shareYear] = [
        'total' => 0,
    ];

    $balanceCollectible['annual_due'][$shareYear] = [
        'total' => 0,
    ];

    /*
     * =========================================================
     * 8. HELPERS
     * =========================================================
     */

    $isSameMonth = function (
        Carbon $left,
        Carbon $right
    ): bool {
        return $left->year === $right->year
            && $left->month === $right->month;
    };

    /*
     * Add an obligation into the November unpaid-deductions
     * target without adding it again into the grand target.
     *
     * The obligation already existed in January / February-Oct.
     * November is only a carry-forward.
     */
    $addNovemberTarget = function (
        string $memberId,
        float $amount
    ) use (
        &$memberData,
        $settlementMonthKey
    ): void {
        if ($amount <= 0) {
            return;
        }

        if (!isset($memberData[$memberId])) {
            return;
        }

        $memberData[$memberId]
            ['months']
            [$settlementMonthKey] += $amount;

        /*
         * Not added to total_monthly_collection_target at all,
         * neither the November column nor the total. It is a
         * rollover of an obligation already counted in its
         * original column. Leaving November's target at 0 (as
         * the manual report does) makes every footer row
         * cross-foot: the November actual then shows up as a
         * negative November balance that cancels the positive
         * balances of the months it settles.
         */
    };

    /*
     * Record an actual salary deduction made during November.
     */
    $addNovemberActual = function (
        float $amount
    ) use (
        &$actualMonthlyCollection,
        $settlementMonthKey
    ): void {
        if ($amount <= 0) {
            return;
        }

        $actualMonthlyCollection
            [$settlementMonthKey] += $amount;

        $actualMonthlyCollection
            ['total'] += $amount;
    };

    /*
     * =========================================================
     * 9. LOAD CNA SHARE / ANNUAL DUES
     * =========================================================
     */

    $cnaShares = CnaShare::query()
        ->where(
            'year',
            $shareYear
        )
        ->whereIn(
            'member_id',
            $memberIds
        )
        ->get()
        ->groupBy('member_id');

    $annualDues = AnnualContribution::query()
        ->where(
            'contribution_year',
            $shareYear
        )
        ->whereIn(
            'member_id',
            $memberIds
        )
        ->get()
        ->groupBy('member_id');

    /*
     * =========================================================
     * 10. PROCESS JANUARY CNA SHARE
     * =========================================================
     */

    foreach ($members as $member) {
        $memberCnaShares = $cnaShares->get(
            $member->id,
            collect()
        );

        foreach ($memberCnaShares as $share) {
            $dueAmount = (float) $share->due_amount;

            $paidAmount = (float) $share->paid_amount;

            $paidAt = $share->paid_at
                ? Carbon::parse($share->paid_at)
                : null;

            /*
             * Header amount = standard amount per member.
             *
             * The manual report displays one amount in the
             * yellow CNA Share header.
             */
            if (
                $reportCnaShare['amount'] == 0
                && $dueAmount > 0
            ) {
                $reportCnaShare['amount'] =
                    $dueAmount;
            }

            /*
             * Total expected CNA collection.
             */
            $reportCnaShare['total'] +=
                $dueAmount;

            $totalCNAShareCollectionTarget
                [$shareYear]['total'] +=
                    $dueAmount;

            $totalMonthlyCollectionTarget
                ['total'] += $dueAmount;

            /*
             * -------------------------------------------------
             * Paid correctly during January
             * -------------------------------------------------
             */
            if (
                $paidAt
                && $isSameMonth(
                    $paidAt,
                    $januaryMonth
                )
            ) {
                $memberData
                    [$member->id]
                    ['cna_share']
                    [$shareYear] += $paidAmount;

                $actualCNAShareCollectionTarget
                    [$shareYear]['total'] +=
                        $paidAmount;

                $actualMonthlyCollection
                    ['total'] += $paidAmount;

                /*
                 * Partial January payment:
                 *
                 * only the unpaid remainder moves to November.
                 */
                $remainingAmount = max(
                    0,
                    $dueAmount - $paidAmount
                );

                $addNovemberTarget(
                    $member->id,
                    $remainingAmount
                );

                continue;
            }

            /*
             * -------------------------------------------------
             * Not deducted during January
             * -------------------------------------------------
             *
             * January displays 0.
             *
             * The full expected amount moves to November.
             */
            $addNovemberTarget(
                $member->id,
                $dueAmount
            );

            /*
             * If it was eventually deducted in settlement
             * November, count it ONLY in the November actual
             * collection. The January column keeps showing it as
             * uncollected so the footer rows cross-foot.
             */
            if (
                $paidAt
                && $isSameMonth(
                    $paidAt,
                    $settlementMonth
                )
            ) {
                $addNovemberActual(
                    $paidAmount
                );
            }
        }
    }

    /*
     * =========================================================
     * 11. PROCESS JANUARY ANNUAL DUES
     * =========================================================
     *
     * Existing implementation uses amount_paid as the annual
     * contribution amount/target, so that behavior is retained.
     */

    foreach ($members as $member) {
        $memberAnnualDues = $annualDues->get(
            $member->id,
            collect()
        );

        foreach ($memberAnnualDues as $due) {
            $annualAmount = (float)
                $due->amount_paid;

            $paidAt = $due->paid_at
                ? Carbon::parse($due->paid_at)
                : null;

            $status = $due->status;

            /*
             * Support both enum-cast and string status values.
             */
            $statusValue =
                $status instanceof ContributionStatus
                    ? $status->value
                    : $status;

            $isPaid =
                $statusValue ===
                ContributionStatus::Paid->value;

            if (
                $reportAnnualDues['amount'] == 0
                && $annualAmount > 0
            ) {
                $reportAnnualDues['amount'] =
                    $annualAmount;
            }

            $reportAnnualDues['total'] +=
                $annualAmount;

            $totalAnnualDueCollectionTarget
                [$shareYear]['total'] +=
                    $annualAmount;

            $totalMonthlyCollectionTarget
                ['total'] += $annualAmount;

            /*
             * -------------------------------------------------
             * Paid correctly during January
             * -------------------------------------------------
             */
            if (
                $isPaid
                && $paidAt
                && $isSameMonth(
                    $paidAt,
                    $januaryMonth
                )
            ) {
                $memberData
                    [$member->id]
                    ['annual_due']
                    [$shareYear] +=
                        $annualAmount;

                $actualAnnualDueCollectionTarget
                    [$shareYear]['total'] +=
                        $annualAmount;

                $actualMonthlyCollection
                    ['total'] += $annualAmount;

                continue;
            }

            /*
             * Otherwise the January obligation moves into
             * November unpaid deductions.
             */
            $addNovemberTarget(
                $member->id,
                $annualAmount
            );

            /*
             * Actual payment made during settlement November.
             * Counted only in the November column (see CNA above).
             */
            if (
                $isPaid
                && $paidAt
                && $isSameMonth(
                    $paidAt,
                    $settlementMonth
                )
            ) {
                $addNovemberActual(
                    $annualAmount
                );
            }
        }
    }

    /*
     * =========================================================
     * 12. GET ALL CLAIMS INSIDE THE EXACT FISCAL YEAR
     * =========================================================
     */

    $claims = Claim::query()
        ->with([
            'claimType',

            /*
             * contribution_batches has no campus_id column; batches
             * span all campuses, so campus scoping happens on the
             * details (members) below.
             */
            'contributionBatches',

            'contributionBatches.contributionDetails' =>
                function ($query) use ($campusId) {
                    $query->when(
                        $campusId,
                        function (
                            $query,
                            $campusId
                        ) {
                            $query->whereHas(
                                'member',
                                function (
                                    $query
                                ) use (
                                    $campusId
                                ) {
                                    $query->where(
                                        'campus_id',
                                        $campusId
                                    );
                                }
                            );
                        }
                    );
                },
        ])
        ->whereBetween(
            'created_at',
            [
                $dateFrom,
                $dateTo,
            ]
        )
        ->get();

    /*
     * =========================================================
     * 13. PROCESS CLAIM DEDUCTIONS
     * =========================================================
     *
     * Expected contribution month:
     *
     * claims.created_at
     *
     * Actual deduction timing:
     *
     * contribution_details.paid_at
     *
     * Rule:
     *
     * - paid in expected month => show in expected month
     * - missed expected month => expected month stays 0
     * - missed amount => move to November
     * - paid during settlement November => actual November
     */

    foreach ($claims as $claim) {
        $claimDate = Carbon::parse(
            $claim->created_at
        );

        /*
         * Exact defensive fiscal-boundary validation.
         */
        if (
            !$claimDate->between(
                $dateFrom,
                $dateTo,
                true
            )
        ) {
            continue;
        }

        $expectedMonthKey = strtolower(
            $claimDate->format('F Y')
        );

        /*
         * Every month of the fiscal year has a claim column
         * (October FY-1 through October FY), so this guard only
         * protects against unexpected dates.
         */
        if (
            !array_key_exists(
                $expectedMonthKey,
                $months
            )
            ||
            $expectedMonthKey ===
                $settlementMonthKey
        ) {
            continue;
        }

        foreach (
            $claim->contributionBatches
            as $batch
        ) {
            $amountPerMember = (float)
                $batch->amount_per_member;

            /*
             * Batches cover every campus, so total_members would
             * overstate a single campus's target. Count only the
             * (campus-filtered) members who were billed.
             */
            $memberCount = $batch->contributionDetails
                ->filter(fn ($detail) => isset($memberData[$detail->member_id]))
                ->count();

            /*
             * -------------------------------------------------
             * MONTH HEADER
             * -------------------------------------------------
             *
             * Manual report yellow header:
             *
             * e.g.
             *
             * February = 250
             * March    = 350
             *
             * If multiple contribution batches fall in the same
             * month, their per-member amounts are accumulated.
             */
            $headerMonthsAmount
                [$expectedMonthKey] +=
                    $amountPerMember;

            /*
             * Expected total collection for this batch.
             */
            $batchTarget =
                $amountPerMember
                * $memberCount;

            $totalMonthlyCollectionTarget
                [$expectedMonthKey] +=
                    $batchTarget;

            /*
             * Grand expected collection.
             *
             * November rollover is NOT included again here.
             */
            $totalMonthlyCollectionTarget
                ['total'] +=
                    $batchTarget;

            foreach (
                $batch->contributionDetails
                as $detail
            ) {
                $memberId =
                    $detail->member_id;

                if (!isset(
                    $memberData[$memberId]
                )) {
                    continue;
                }

                /*
                 * amount_per_member represents what should have
                 * been deducted from this member.
                 */
                $expectedAmount =
                    $amountPerMember;

                /*
                 * contribution_details.amount represents what
                 * was actually deducted/recorded.
                 */
                $actualAmount = (float)
                    $detail->amount;

                $paidAt = $detail->paid_at
                    ? Carbon::parse(
                        $detail->paid_at
                    )
                    : null;

                /*
                 * ---------------------------------------------
                 * A. PAID DURING EXPECTED MONTH
                 * ---------------------------------------------
                 */

                /*
                 * Only the calendar month matters here, not the
                 * fiscal boundary: a claim filed Oct 10 and
                 * deducted on the Oct 30 payroll is still an
                 * on-time October deduction.
                 */
                if (
                    $paidAt
                    && $paidAt->year ===
                        $claimDate->year
                    && $paidAt->month ===
                        $claimDate->month
                ) {
                    /*
                     * Show actual salary deduction in the
                     * correct month.
                     */
                    $memberData
                        [$memberId]
                        ['months']
                        [$expectedMonthKey] +=
                            $actualAmount;

                    $actualMonthlyCollection
                        [$expectedMonthKey] +=
                            $actualAmount;

                    $actualMonthlyCollection
                        ['total'] +=
                            $actualAmount;

                    /*
                     * Partial deduction:
                     *
                     * only the remainder moves to November.
                     */
                    $remainingAmount = max(
                        0,
                        $expectedAmount -
                            $actualAmount
                    );

                    $addNovemberTarget(
                        $memberId,
                        $remainingAmount
                    );

                    continue;
                }

                /*
                 * ---------------------------------------------
                 * B. MISSED EXPECTED MONTH
                 * ---------------------------------------------
                 *
                 * The expected month remains 0 for this member.
                 *
                 * The expected deduction moves to the November
                 * unpaid-deductions column.
                 */
                $addNovemberTarget(
                    $memberId,
                    $expectedAmount
                );

                /*
                 * ---------------------------------------------
                 * C. ACTUALLY PAID IN NOVEMBER
                 * ---------------------------------------------
                 *
                 * November is the ONLY valid settlement month
                 * for a missed deduction.
                 */
                if (
                    $paidAt
                    && $paidAt->between(
                        $settlementMonth,
                        $settlementMonthEnd,
                        true
                    )
                ) {
                    $addNovemberActual(
                        $actualAmount
                    );
                }

                /*
                 * Payments in another normal month are not moved
                 * to that month because the business rule says a
                 * missed deduction may only be settled during
                 * November.
                 */
            }
        }
    }

    /*
     * =========================================================
     * 14. CALCULATE CNA / ANNUAL BALANCES
     * =========================================================
     */

    $cnaTarget =
        $totalCNAShareCollectionTarget
            [$shareYear]['total'];

    $cnaActual =
        $actualCNAShareCollectionTarget
            [$shareYear]['total'];

    $balanceCollectible
        ['cna_share']
        [$shareYear]
        ['total'] =
            $cnaTarget - $cnaActual;

    $annualTarget =
        $totalAnnualDueCollectionTarget
            [$shareYear]['total'];

    $annualActual =
        $actualAnnualDueCollectionTarget
            [$shareYear]['total'];

    $balanceCollectible
        ['annual_due']
        [$shareYear]
        ['total'] =
            $annualTarget - $annualActual;

    /*
     * =========================================================
     * 15. CALCULATE HEADER GRAND TOTAL
     * =========================================================
     *
     * This represents the normal per-member deduction schedule.
     *
     * November is excluded because it contains carry-forward
     * amounts, not a new contribution requirement.
     */

    $headerGrandTotal =
        (float) $reportCnaShare['amount']
        +
        (float) $reportAnnualDues['amount'];

    foreach (
        $headerMonthLabel
        as $monthKey
    ) {
        if (
            $monthKey ===
            $settlementMonthKey
        ) {
            continue;
        }

        $headerGrandTotal +=
            (float) (
                $headerMonthsAmount
                    [$monthKey]
                ?? 0
            );
    }

    $headerMonthsAmount['total'] =
        $headerGrandTotal;

    /*
     * =========================================================
     * 16. GET COLLECTION ADJUSTMENTS
     * =========================================================
     *
     * Visible adjustment months:
     *
     * October FY-1 -> November FY.
     */

    $adjustmentDateFrom = Carbon::create(
        $reportYear - 1,
        10,
        1
    )->startOfMonth();

    $adjustmentDateTo = $settlementMonth
        ->copy()
        ->startOfMonth();

    $adjustments = CollectionsAdjustments::query()
        ->when(
            $campusId,
            function (
                $query,
                $campusId
            ) {
                $query->where(
                    'campus_id',
                    $campusId
                );
            }
        )
        ->whereBetween(
            'adjustment_month',
            [
                $adjustmentDateFrom,
                $adjustmentDateTo,
            ]
        )
        ->get();

    foreach ($adjustments as $adjustment) {
        $monthKey = strtolower(
            Carbon::parse(
                $adjustment->adjustment_month
            )->format('F Y')
        );

        /*
         * Ignore adjustment months that are not visible in
         * this manual report format.
         */
        if (
            !array_key_exists(
                $monthKey,
                $months
            )
        ) {
            continue;
        }

        $amount = (float)
            $adjustment->amount;

        $collectionAdjustments
            [$monthKey] += $amount;

        $collectionAdjustments
            ['total'] += $amount;

        $collectionAdjustmentDetails
            [$monthKey][] = [
                'id' =>
                    $adjustment->id,

                'amount' =>
                    $amount,

                'type' =>
                    $adjustment->adjustment_type,

                'description' =>
                    $adjustment->description,

                'reference_month' =>
                    $adjustment->reference_month
                        ? Carbon::parse(
                            $adjustment
                                ->reference_month
                        )->format('F Y')
                        : null,
            ];
    }

    /*
     * =========================================================
     * 17. APPLY COLLECTION ADJUSTMENTS
     * =========================================================
     */

    foreach (
        $months
        as $monthKey => $unused
    ) {
        $adjustedMonthlyCollection
            [$monthKey] =
                (
                    $actualMonthlyCollection
                        [$monthKey]
                    ?? 0
                )
                +
                (
                    $collectionAdjustments
                        [$monthKey]
                    ?? 0
                );
    }

    $adjustedMonthlyCollection['total'] =
        $actualMonthlyCollection['total']
        +
        $collectionAdjustments['total'];

    /*
     * =========================================================
     * 18. MONTHLY BALANCE COLLECTIBLE
     * =========================================================
     */

    foreach (
        $months
        as $monthKey => $unused
    ) {
        $target =
            $totalMonthlyCollectionTarget
                [$monthKey]
            ?? 0;

        $actual =
            $adjustedMonthlyCollection
                [$monthKey]
            ?? 0;

        $balanceCollectible[$monthKey] =
            $target - $actual;
    }

    /*
     * =========================================================
     * 19. GRAND BALANCE
     * =========================================================
     *
     * November target is intentionally NOT included in the
     * original grand collection target because it represents
     * obligations already counted in earlier columns.
     *
     * November ACTUAL payments, however, reduce the outstanding
     * fiscal-year balance.
     */

    $balanceCollectible['total'] =
        $totalMonthlyCollectionTarget['total']
        -
        $adjustedMonthlyCollection['total'];

    /*
     * =========================================================
     * 20. FINAL MEMBER TOTAL
     * =========================================================
     *
     * This now follows the manual "Deduction per Member" total.
     *
     * If a normal deduction was missed, its amount appears only
     * in November for the member row and is therefore not counted
     * twice.
     */

    foreach (
        $memberData
        as $memberId => &$row
    ) {
        $cnaTotal = array_sum(
            $row['cna_share']
        );

        $annualTotal = array_sum(
            $row['annual_due']
        );

        $monthlyTotal = array_sum(
            $row['months']
        );

        $row['total_contributed'] =
            $cnaTotal
            +
            $annualTotal
            +
            $monthlyTotal;
    }

    unset($row);

    /*
     * =========================================================
     * 21. RETURN REPORT-READY STRUCTURE
     * =========================================================
     *
     * Existing output keys are retained for backward
     * compatibility with the Blade/report implementation.
     */

    return [
        'period' =>
            $dateFrom->format('F Y')
            . ' to '
            . $settlementMonth->format('F Y'),

        'header_months_amount' =>
            $headerMonthsAmount,

        'header_month_label' =>
            $headerMonthLabel,

        'pre_january_month_label' =>
            $preJanuaryMonthLabel,

        'total_monthly_collection_target' =>
            $totalMonthlyCollectionTarget,

        'actual_monthly_collection' =>
            $actualMonthlyCollection,

        'total_cna_share_collection_target' =>
            $totalCNAShareCollectionTarget,

        'actual_cna_share_collection' =>
            $actualCNAShareCollectionTarget,

        'total_annual_due_collection_target' =>
            $totalAnnualDueCollectionTarget,

        'actual_annual_due_collection' =>
            $actualAnnualDueCollectionTarget,

        'collection_adjustments' =>
            $collectionAdjustments,

        'collection_adjustment_details' =>
            $collectionAdjustmentDetails,

        'adjusted_monthly_collection' =>
            $adjustedMonthlyCollection,

        'balance_collectible' =>
            $balanceCollectible,

        'cna_share' =>
            $reportCnaShare,

        'annual_due' =>
            $reportAnnualDues,

        'members' =>
            array_values($memberData),
    ];
}

    //Testing - working 2
// public function getContributionPerMonth(
//     Request $request,
//     ?string $campusId = null
// ): array {
//     /*
//      * =========================================================
//      * 1. RESOLVE FISCAL YEAR
//      * =========================================================
//      *
//      * Fiscal Year definition:
//      *
//      * FY 2026
//      * October 15, 2026 00:00:00
//      * through
//      * October 14, 2027 23:59:59
//      *
//      * The fiscal year is identified by the year in which
//      * the cycle STARTS.
//      *
//      * Preferred request parameters:
//      *
//      * fiscal_year = 2026
//      * or
//      * reporting_year = 2026
//      *
//      * date_from is retained as a backward-compatible fallback.
//      */

//     $fiscalYear = $request->input('fiscal_year')
//         ?? $request->input('reporting_year')
//         ?? null;

//     /*
//      * Backward compatibility with the old implementation.
//      *
//      * Previously date_from was used as the reporting-period
//      * selector. If the UI still sends something such as
//      * 2026-10-01, use its year as FY 2026.
//      */
//     if (!$fiscalYear && $request->filled('date_from')) {
//         $fiscalYear = Carbon::parse(
//             $request->input('date_from')
//         )->year;
//     }

//     if (!$fiscalYear) {
//         return [
//             'header_months_amount' => [],
//             'header_month_label' => [],
//             'total_monthly_collection_target' => [],
//             'actual_monthly_collection' => [],
//             'total_cna_share_collection_target' => [],
//             'actual_cna_share_collection' => [],
//             'total_annual_due_collection_target' => [],
//             'actual_annual_due_collection' => [],
//             'collection_adjustments' => [],
//             'collection_adjustment_details' => [],
//             'adjusted_monthly_collection' => [],
//             'balance_collectible' => [],
//             'cna_share' => [
//                 'amount' => 0,
//                 'total' => 0,
//             ],
//             'annual_due' => [
//                 'amount' => 0,
//                 'total' => 0,
//             ],
//             'members' => [],
//         ];
//     }

//     $fiscalYear = (int) $fiscalYear;

//     /*
//      * =========================================================
//      * 2. AUTHORITATIVE FISCAL-YEAR BOUNDARIES
//      * =========================================================
//      */

//     $dateFrom = Carbon::create(
//         $fiscalYear,
//         10,
//         15
//     )->startOfDay();

//     $dateTo = Carbon::create(
//         $fiscalYear + 1,
//         10,
//         14
//     )->endOfDay();

//     /*
//      * Example:
//      *
//      * FY 2026:
//      *
//      * October 15, 2026
//      * through
//      * October 14, 2027
//      *
//      * January inside this period is January 2027.
//      *
//      * Therefore:
//      *
//      * CNA Share year = 2027
//      * Annual Due year = 2027
//      */
//     $shareYear = $fiscalYear + 1;

//     /*
//      * =========================================================
//      * 3. NOVEMBER SETTLEMENT PERIOD
//      * =========================================================
//      *
//      * Missed deductions during the fiscal year are settled
//      * during November immediately after the fiscal period.
//      *
//      * FY 2026 ends October 14, 2027.
//      *
//      * Settlement month:
//      * November 2027
//      */

//     $settlementMonth = Carbon::create(
//         $fiscalYear + 1,
//         11,
//         1
//     )->startOfMonth();

//     $settlementMonthEnd = $settlementMonth
//         ->copy()
//         ->endOfMonth();

//     $settlementMonthKey = strtolower(
//         $settlementMonth->format('F Y')
//     );

//     /*
//      * =========================================================
//      * 4. INITIALIZE REPORT ARRAYS
//      * =========================================================
//      */

//     $headerMonthsAmount = [
//         'total' => 0,
//     ];

//     $headerMonthLabel = [];

//     $totalMonthlyCollectionTarget = [
//         'total' => 0,
//     ];

//     $actualMonthlyCollection = [
//         'total' => 0,
//     ];

//     $collectionAdjustments = [
//         'total' => 0,
//     ];

//     $collectionAdjustmentDetails = [];

//     $adjustedMonthlyCollection = [
//         'total' => 0,
//     ];

//     $balanceCollectible = [
//         'cna_share' => [],
//         'annual_due' => [],
//         'total' => 0,
//     ];

//     /*
//      * =========================================================
//      * 5. INITIALIZE MONTH COLUMNS
//      * =========================================================
//      *
//      * IMPORTANT:
//      *
//      * Exact fiscal boundaries:
//      *
//      * October 15, 2026 -> October 14, 2027
//      *
//      * But report columns need:
//      *
//      * October 2026
//      * November 2026
//      * ...
//      * September 2027
//      * October 2027
//      *
//      * Therefore the month iterator is normalized separately
//      * from the actual database filtering boundaries.
//      */

//     $currentMonth = $dateFrom
//         ->copy()
//         ->startOfMonth();

//     $lastReportMonth = $dateTo
//         ->copy()
//         ->startOfMonth();

//     $months = [];

//     while ($currentMonth->lte($lastReportMonth)) {
//         $monthKey = strtolower(
//             $currentMonth->format('F Y')
//         );

//         $months[$monthKey] = 0;

//         $headerMonthsAmount[$monthKey] = 0;

//         $totalMonthlyCollectionTarget[$monthKey] = 0;

//         $actualMonthlyCollection[$monthKey] = 0;

//         $collectionAdjustments[$monthKey] = 0;

//         $adjustedMonthlyCollection[$monthKey] = 0;

//         $balanceCollectible[$monthKey] = 0;

//         $collectionAdjustmentDetails[$monthKey] = [];

//         $headerMonthLabel[] = $monthKey;

//         $currentMonth->addMonth();
//     }

//     /*
//      * =========================================================
//      * 6. ADD NOVEMBER SETTLEMENT COLUMN
//      * =========================================================
//      *
//      * November is outside the formal fiscal-year boundary.
//      *
//      * It is included in the output because it represents
//      * accumulated missed salary deductions from the fiscal year.
//      */

//     $months[$settlementMonthKey] = 0;

//     $headerMonthsAmount[$settlementMonthKey] = 0;

//     $totalMonthlyCollectionTarget[$settlementMonthKey] = 0;

//     $actualMonthlyCollection[$settlementMonthKey] = 0;

//     $collectionAdjustments[$settlementMonthKey] = 0;

//     $adjustedMonthlyCollection[$settlementMonthKey] = 0;

//     $balanceCollectible[$settlementMonthKey] = 0;

//     $collectionAdjustmentDetails[$settlementMonthKey] = [];

//     $headerMonthLabel[] = $settlementMonthKey;

//     /*
//      * =========================================================
//      * 7. GET MEMBERS
//      * =========================================================
//      */

//     $members = Member::query()
//         ->with('user')
//         ->when(
//             $campusId,
//             function ($query, $campusId) {
//                 $query
//                     ->withoutGlobalScope(
//                         CampusMemberScope::class
//                     )
//                     ->where(
//                         'campus_id',
//                         $campusId
//                     );
//             }
//         )
//         ->get();

//     $memberIds = $members->pluck('id');

//     /*
//      * =========================================================
//      * 8. REPORT-LEVEL CNA / ANNUAL STRUCTURES
//      * =========================================================
//      */

//     $reportCnaShare = [
//         'amount' => 0,
//         'total' => 0,
//     ];

//     $reportAnnualDues = [
//         'amount' => 0,
//         'total' => 0,
//     ];

//     /*
//      * Internal yearly structures are retained because the
//      * existing report payload expects them.
//      */

//     $totalCNAShareCollectionTarget = [
//         $shareYear => [
//             'total' => 0,
//         ],
//     ];

//     $actualCNAShareCollectionTarget = [
//         $shareYear => [
//             'total' => 0,
//         ],
//     ];

//     $totalAnnualDueCollectionTarget = [
//         $shareYear => [
//             'total' => 0,
//         ],
//     ];

//     $actualAnnualDueCollectionTarget = [
//         $shareYear => [
//             'total' => 0,
//         ],
//     ];

//     $balanceCollectible['cna_share'][$shareYear] = [
//         'total' => 0,
//     ];

//     $balanceCollectible['annual_due'][$shareYear] = [
//         'total' => 0,
//     ];

//     /*
//      * =========================================================
//      * 9. LOAD CNA SHARE RECORDS
//      * =========================================================
//      */

//     $cnaShares = CnaShare::query()
//         ->where(
//             'year',
//             $shareYear
//         )
//         ->whereIn(
//             'member_id',
//             $memberIds
//         )
//         ->get()
//         ->groupBy('member_id');

//     /*
//      * =========================================================
//      * 10. LOAD ANNUAL CONTRIBUTIONS
//      * =========================================================
//      */

//     $annualDues = AnnualContribution::query()
//         ->where(
//             'contribution_year',
//             $shareYear
//         )
//         ->whereIn(
//             'member_id',
//             $memberIds
//         )
//         ->get()
//         ->groupBy('member_id');

//     /*
//      * =========================================================
//      * 11. BUILD MEMBER DATA
//      * =========================================================
//      */

//     $memberData = [];

//     foreach ($members as $member) {
//         $memberCnaShares = $cnaShares->get(
//             $member->id,
//             collect()
//         );

//         $memberAnnualDues = $annualDues->get(
//             $member->id,
//             collect()
//         );

//         $cnaShare = [
//             $shareYear => 0,
//         ];

//         $annualDue = [
//             $shareYear => 0,
//         ];

//         /*
//          * -----------------------------------------------------
//          * CNA SHARE
//          * -----------------------------------------------------
//          */

//         foreach ($memberCnaShares as $share) {
//             $amount = (float) $share->paid_amount;
//             $dueAmount = (float) $share->due_amount;

//             $cnaShare[$shareYear] += $amount;

//             $reportCnaShare['amount'] += $dueAmount;
//             $reportCnaShare['total'] += $dueAmount;

//             $totalCNAShareCollectionTarget
//                 [$shareYear]['total'] += $dueAmount;

//             $actualCNAShareCollectionTarget
//                 [$shareYear]['total'] += $amount;
//         }

//         /*
//          * -----------------------------------------------------
//          * ANNUAL CONTRIBUTION
//          * -----------------------------------------------------
//          */

//         foreach ($memberAnnualDues as $due) {
//             $amount = (float) $due->amount_paid;

//             $annualDue[$shareYear] += $amount;

//             $reportAnnualDues['amount'] += $amount;
//             $reportAnnualDues['total'] += $amount;

//             $totalAnnualDueCollectionTarget
//                 [$shareYear]['total'] += $amount;

//             if (
//                 $due->status ===
//                 ContributionStatus::Paid->value
//             ) {
//                 $actualAnnualDueCollectionTarget
//                     [$shareYear]['total'] += $amount;
//             }
//         }

//         /*
//          * -----------------------------------------------------
//          * MEMBER ROW
//          * -----------------------------------------------------
//          */

//         $memberData[$member->id] = [
//             'member_id' => $member->id,

//             'name' => $member->user->name ?? '',

//             'months' => $months,

//             /*
//              * Initially contains CNA + Annual.
//              *
//              * Monthly salary deductions are added later.
//              */
//             'total_contributed' =>
//                 array_sum($annualDue)
//                 + array_sum($cnaShare),

//             'cna_share' => $cnaShare,

//             'annual_due' => $annualDue,
//         ];
//     }

//     /*
//      * =========================================================
//      * 12. CNA / ANNUAL BALANCES
//      * =========================================================
//      */

//     foreach (
//         $totalCNAShareCollectionTarget
//         as $year => $targetData
//     ) {
//         $target = (float) (
//             $targetData['total'] ?? 0
//         );

//         $actual = (float) (
//             $actualCNAShareCollectionTarget
//                 [$year]['total']
//             ?? 0
//         );

//         $balanceCollectible
//             ['cna_share']
//             [$year]
//             ['total'] = $target - $actual;
//     }

//     foreach (
//         $totalAnnualDueCollectionTarget
//         as $year => $targetData
//     ) {
//         $target = (float) (
//             $targetData['total'] ?? 0
//         );

//         $actual = (float) (
//             $actualAnnualDueCollectionTarget
//                 [$year]['total']
//             ?? 0
//         );

//         $balanceCollectible
//             ['annual_due']
//             [$year]
//             ['total'] = $target - $actual;
//     }

//     /*
//      * CNA / Annual are included in the report-level totals.
//      */

//     $headerMonthsAmount['total'] =
//         (float) $reportAnnualDues['amount']
//         + (float) $reportCnaShare['amount'];

//     $totalMonthlyCollectionTarget['total'] =
//         array_sum(
//             array_column(
//                 $totalCNAShareCollectionTarget,
//                 'total'
//             )
//         )
//         +
//         array_sum(
//             array_column(
//                 $totalAnnualDueCollectionTarget,
//                 'total'
//             )
//         );

//     $actualMonthlyCollection['total'] =
//         array_sum(
//             array_column(
//                 $actualCNAShareCollectionTarget,
//                 'total'
//             )
//         )
//         +
//         array_sum(
//             array_column(
//                 $actualAnnualDueCollectionTarget,
//                 'total'
//             )
//         );

//     /*
//      * =========================================================
//      * 13. GET CLAIMS INSIDE THE EXACT FISCAL YEAR
//      * =========================================================
//      *
//      * FY 2026:
//      *
//      * INCLUDED:
//      * October 15, 2026
//      * through
//      * October 14, 2027
//      *
//      * EXCLUDED:
//      * October 14, 2026
//      * October 15, 2027
//      */

//     $claims = Claim::query()
//         ->with([
//             'claimType',

//             'contributionBatches.contributionDetails' =>
//                 function ($query) use ($campusId) {
//                     $query->when(
//                         $campusId,
//                         function (
//                             $query,
//                             $campusId
//                         ) {
//                             $query->whereHas(
//                                 'member',
//                                 function (
//                                     $query
//                                 ) use (
//                                     $campusId
//                                 ) {
//                                     $query->where(
//                                         'campus_id',
//                                         $campusId
//                                     );
//                                 }
//                             );
//                         }
//                     );
//                 },
//         ])
//         ->whereBetween(
//             'created_at',
//             [
//                 $dateFrom,
//                 $dateTo,
//             ]
//         )
//         ->get();

//     /*
//      * =========================================================
//      * 14. PROCESS CLAIMS
//      * =========================================================
//      */

//     foreach ($claims as $claim) {
//         $claimDate = Carbon::parse(
//             $claim->created_at
//         );

//         /*
//          * Additional defensive condition.
//          *
//          * The query already applies the fiscal boundary,
//          * but this guarantees no out-of-cycle claim can
//          * accidentally enter the calculation.
//          */
//         if (
//             !$claimDate->between(
//                 $dateFrom,
//                 $dateTo,
//                 true
//             )
//         ) {
//             continue;
//         }

//         $monthKey = strtolower(
//             $claimDate->format('F Y')
//         );

//         if (!array_key_exists(
//             $monthKey,
//             $months
//         )) {
//             continue;
//         }

//         /*
//          * -----------------------------------------------------
//          * A. CLAIM REQUIRED AMOUNT
//          * -----------------------------------------------------
//          */

//         if ($claim->claimType) {
//             $claimAmount = (float)
//                 $claim->claimType->required_amount;

//             $headerMonthsAmount[$monthKey] +=
//                 $claimAmount;

//             $headerMonthsAmount['total'] +=
//                 $claimAmount;
//         }

//         /*
//          * -----------------------------------------------------
//          * B. CONTRIBUTION BATCHES
//          * -----------------------------------------------------
//          */

//         foreach (
//             $claim->contributionBatches
//             as $batch
//         ) {
//             $amountPerMember = (float)
//                 $batch->amount_per_member;

//             $memberCount = (int)
//                 $batch->total_members;

//             $batchTarget =
//                 $amountPerMember
//                 * $memberCount;

//             /*
//              * Expected collection for the month.
//              */

//             $totalMonthlyCollectionTarget
//                 [$monthKey] += $batchTarget;

//             $totalMonthlyCollectionTarget
//                 ['total'] += $batchTarget;

//             /*
//              * -------------------------------------------------
//              * C. CONTRIBUTION DETAILS
//              * -------------------------------------------------
//              */

//             foreach (
//                 $batch->contributionDetails
//                 as $detail
//             ) {
//                 $memberId = $detail->member_id;

//                 if (!isset(
//                     $memberData[$memberId]
//                 )) {
//                     continue;
//                 }

//                 /*
//                  * Without paid_at there was no actual
//                  * salary deduction.
//                  */
//                 if (!$detail->paid_at) {
//                     continue;
//                 }

//                 $actualAmount = (float)
//                     $detail->amount;

//                 $paidAt = Carbon::parse(
//                     $detail->paid_at
//                 );

//                 $paidMonthKey = strtolower(
//                     $paidAt->format('F Y')
//                 );

//                 /*
//                  * ---------------------------------------------
//                  * NOVEMBER SETTLEMENT
//                  * ---------------------------------------------
//                  *
//                  * A payment made during the settlement
//                  * November belongs only to the November
//                  * lump-sum column.
//                  */

//                 if (
//                     $paidAt->between(
//                         $settlementMonth,
//                         $settlementMonthEnd,
//                         true
//                     )
//                 ) {
//                     $actualMonthlyCollection
//                         [$settlementMonthKey] +=
//                             $actualAmount;

//                     $actualMonthlyCollection
//                         ['total'] +=
//                             $actualAmount;

//                     $memberData
//                         [$memberId]
//                         ['months']
//                         [$settlementMonthKey] +=
//                             $actualAmount;

//                     $memberData
//                         [$memberId]
//                         ['total_contributed'] +=
//                             $actualAmount;

//                     continue;
//                 }

//                 /*
//                  * ---------------------------------------------
//                  * NORMAL FISCAL-YEAR DEDUCTION
//                  * ---------------------------------------------
//                  *
//                  * This exact-date condition is essential.
//                  *
//                  * For FY 2026:
//                  *
//                  * Oct 10, 2026 = EXCLUDED
//                  * Oct 15, 2026 = INCLUDED
//                  *
//                  * Oct 14, 2027 = INCLUDED
//                  * Oct 15, 2027 = EXCLUDED
//                  *
//                  * Checking only the month name is not enough,
//                  * because both boundary months are partial.
//                  */

//                 if (
//                     !$paidAt->between(
//                         $dateFrom,
//                         $dateTo,
//                         true
//                     )
//                 ) {
//                     continue;
//                 }

//                 if (
//                     !isset(
//                         $memberData
//                             [$memberId]
//                             ['months']
//                             [$paidMonthKey]
//                     )
//                 ) {
//                     continue;
//                 }

//                 $actualMonthlyCollection
//                     [$paidMonthKey] +=
//                         $actualAmount;

//                 $actualMonthlyCollection
//                     ['total'] +=
//                         $actualAmount;

//                 $memberData
//                     [$memberId]
//                     ['months']
//                     [$paidMonthKey] +=
//                         $actualAmount;

//                 $memberData
//                     [$memberId]
//                     ['total_contributed'] +=
//                         $actualAmount;
//             }
//         }
//     }

//     /*
//      * =========================================================
//      * 15. GET COLLECTION ADJUSTMENTS
//      * =========================================================
//      *
//      * adjustment_month appears to represent the reporting
//      * MONTH rather than an exact transaction timestamp.
//      *
//      * Therefore:
//      *
//      * October 2026 adjustment = 2026-10-01
//      *
//      * It should still be included in the October 2026 column
//      * even though the exact fiscal start is October 15.
//      *
//      * This is why startOfMonth() is intentionally used here.
//      */

//     $adjustmentDateFrom = $dateFrom
//         ->copy()
//         ->startOfMonth();

//     $adjustmentDateTo = $dateTo
//         ->copy()
//         ->startOfMonth();

//     $adjustments = CollectionsAdjustments::query()
//         ->when(
//             $campusId,
//             function (
//                 $query,
//                 $campusId
//             ) {
//                 $query->where(
//                     'campus_id',
//                     $campusId
//                 );
//             }
//         )
//         ->whereBetween(
//             'adjustment_month',
//             [
//                 $adjustmentDateFrom,
//                 $adjustmentDateTo,
//             ]
//         )
//         ->get();

//     /*
//      * =========================================================
//      * 16. PROCESS COLLECTION ADJUSTMENTS
//      * =========================================================
//      */

//     foreach ($adjustments as $adjustment) {
//         $adjustmentMonth = Carbon::parse(
//             $adjustment->adjustment_month
//         );

//         $monthKey = strtolower(
//             $adjustmentMonth->format('F Y')
//         );

//         if (
//             !array_key_exists(
//                 $monthKey,
//                 $months
//             )
//         ) {
//             continue;
//         }

//         $amount = (float)
//             $adjustment->amount;

//         $collectionAdjustments
//             [$monthKey] += $amount;

//         $collectionAdjustments
//             ['total'] += $amount;

//         $collectionAdjustmentDetails
//             [$monthKey][] = [
//                 'id' =>
//                     $adjustment->id,

//                 'amount' =>
//                     $amount,

//                 'type' =>
//                     $adjustment->adjustment_type,

//                 'description' =>
//                     $adjustment->description,

//                 'reference_month' =>
//                     $adjustment->reference_month
//                         ? Carbon::parse(
//                             $adjustment
//                                 ->reference_month
//                         )->format('F Y')
//                         : null,
//             ];
//     }

//     /*
//      * =========================================================
//      * 17. APPLY COLLECTION ADJUSTMENTS
//      * =========================================================
//      *
//      * Adjusted collection:
//      *
//      * actual collection + manual adjustment
//      */

//     foreach (
//         $months
//         as $monthKey => $unused
//     ) {
//         $adjustedMonthlyCollection
//             [$monthKey] =
//                 $actualMonthlyCollection
//                     [$monthKey]
//                 +
//                 $collectionAdjustments
//                     [$monthKey];
//     }

//     $adjustedMonthlyCollection['total'] =
//         $actualMonthlyCollection['total']
//         +
//         $collectionAdjustments['total'];

//     /*
//      * =========================================================
//      * 18. CALCULATE MONTHLY BALANCE
//      * =========================================================
//      *
//      * Balance:
//      *
//      * collection target
//      * -
//      * adjusted actual collection
//      */

//     foreach (
//         $months
//         as $monthKey => $unused
//     ) {
//         $balanceCollectible
//             [$monthKey] =
//                 $totalMonthlyCollectionTarget
//                     [$monthKey]
//                 -
//                 $adjustedMonthlyCollection
//                     [$monthKey];
//     }

//     /*
//      * =========================================================
//      * 19. GRAND TOTAL BALANCE
//      * =========================================================
//      */

//     $balanceCollectible['total'] =
//         $totalMonthlyCollectionTarget['total']
//         -
//         $adjustedMonthlyCollection['total'];

//     /*
//      * =========================================================
//      * 20. RETURN REPORT DATA
//      * =========================================================
//      */

//     return [
//         /*
//          * Required claim amount by month.
//          */
//         'header_months_amount' =>
//             $headerMonthsAmount,

//         /*
//          * Report column labels.
//          *
//          * Example FY 2026:
//          *
//          * october 2026
//          * november 2026
//          * ...
//          * october 2027
//          * november 2027 settlement
//          */
//         'header_month_label' =>
//             $headerMonthLabel,

//         /*
//          * Expected collection target.
//          */
//         'total_monthly_collection_target' =>
//             $totalMonthlyCollectionTarget,

//         /*
//          * Raw actual collection.
//          */
//         'actual_monthly_collection' =>
//             $actualMonthlyCollection,

//         /*
//          * Expected CNA Share collection.
//          */
//         'total_cna_share_collection_target' =>
//             $totalCNAShareCollectionTarget,

//         /*
//          * Actual CNA Share collection.
//          */
//         'actual_cna_share_collection' =>
//             $actualCNAShareCollectionTarget,

//         /*
//          * Expected Annual Due collection.
//          */
//         'total_annual_due_collection_target' =>
//             $totalAnnualDueCollectionTarget,

//         /*
//          * Actual Annual Due collection.
//          */
//         'actual_annual_due_collection' =>
//             $actualAnnualDueCollectionTarget,

//         /*
//          * Monthly adjustment totals.
//          */
//         'collection_adjustments' =>
//             $collectionAdjustments,

//         /*
//          * Adjustment record details.
//          */
//         'collection_adjustment_details' =>
//             $collectionAdjustmentDetails,

//         /*
//          * Actual collection after adjustments.
//          */
//         'adjusted_monthly_collection' =>
//             $adjustedMonthlyCollection,

//         /*
//          * Target minus adjusted actual.
//          */
//         'balance_collectible' =>
//             $balanceCollectible,

//         /*
//          * Report-level CNA Share.
//          */
//         'cna_share' =>
//             $reportCnaShare,

//         /*
//          * Report-level Annual Due.
//          */
//         'annual_due' =>
//             $reportAnnualDues,

//         /*
//          * Member rows.
//          */
//         'members' =>
//             array_values($memberData),
//     ];
// }   

    //--------WORKING-------------------------
    // public function getContributionPerMonth(Request $request, ?string $campusId = null): array
    // {
    //     $dateFrom = $request->date_from
    //         ? Carbon::parse($request->date_from)->startOfMonth()
    //         : null;
    
    //     $dateTo = $request->date_to
    //         ? Carbon::parse($request->date_to)->endOfMonth()
    //         : null;
    
    //     if (!$dateFrom || !$dateTo) {
    //         return [
    //             'header_months_amount' => [],
    //             'header_month_label' => [],
    //             'total_monthly_collection_target' => [],
    //             'actual_monthly_collection' => [],
    //             'collection_adjustments' => [],
    //             'adjusted_monthly_collection' => [],
    //             'balance_collectible' => [],
    //             'members' => [],
    //         ];
    //     }
    
    //     /*
    //      * ---------------------------------------------------------
    //      * 1. Initialize report months and totals.
    //      * ---------------------------------------------------------
    //      *
    //      * "total" represents the grand total across all months.
    //      */
    
    //     $headerMonthsAmount = [
    //         'total' => 0,
    //     ];
    
    //     $headerMonthLabel = [];

    //     // $totalAnnualDueCollectionTarget = [
    //     //     'total' => 0,
    //     // ];

    //     // $actualAnnualDueCollectionTarget = [
    //     //     'total' => 0,
    //     // ];
    
    //     $totalMonthlyCollectionTarget = [
    //         'total' => 0,
    //     ];
    
    //     /*
    //      * Actual collection BEFORE adjustments.
    //      */
    //     $actualMonthlyCollection = [
    //         'total' => 0,
    //     ];
    
    //     /*
    //      * Collection adjustments retrieved from the database.
    //      *
    //      * Positive amount = increase collection
    //      * Negative amount = decrease collection
    //      */
    //     $collectionAdjustments = [
    //         'total' => 0,
    //     ];

    //     $collectionAdjustmentDetails = [];
    
    //     /*
    //      * Actual collection AFTER adjustments.
    //      */
    //     $adjustedMonthlyCollection = [
    //         'total' => 0,
    //     ];
    
    //     $balanceCollectible = [
    //         'cna_share' => [],
    //         'annual_due' => [],
    //         'total' => 0,
    //     ];
    
    //     $currentMonth = $dateFrom->copy();

    //     $months = [];

    //     $cnaShare = [
    //         'total' => 0,
    //     ];
    //     $anualDues = [
    //         'total' => 0,
    //     ];
    
    //     while ($currentMonth->lte($dateTo)) {
    
    //         $monthKey = strtolower(
    //             $currentMonth->format('F Y')
    //         );
    
    //         $months[$monthKey] = 0;
    
    //         $headerMonthsAmount[$monthKey] = 0;
    
    //         $totalMonthlyCollectionTarget[$monthKey] = 0;
    
    //         $actualMonthlyCollection[$monthKey] = 0;
    
    //         $collectionAdjustments[$monthKey] = 0;
    
    //         $adjustedMonthlyCollection[$monthKey] = 0;
    
    //         $balanceCollectible[$monthKey] = 0;

    //         /*
    //          * Detailed adjustment records for this month.
    //          */
    //         $collectionAdjustmentDetails[$monthKey] = [];
    
    //         $headerMonthLabel[] = $monthKey;
    
    //         $currentMonth->addMonth();
    //     }
    
    //     /*
    //      * ---------------------------------------------------------
    //      * 2. Get all members belonging to the selected campus.
    //      * ---------------------------------------------------------
    //      *
    //      * Each member gets:
    //      *
    //      * - monthly contribution values
    //      * - total_contributed = Y-axis / row total
    //      */

    //     $members = Member::query()
    //         ->with('user')
    //         // Only apply the manual where clause if a specific campus filter was explicitly selected
    //         ->when($campusId, function ($query, $campusId) {
    //             // Safe bypass to avoid duplicate SQL where filters if the scope already handled it
    //             $query->withoutGlobalScope(CampusMemberScope::class)
    //                   ->where('campus_id', $campusId);
    //         })
    //         ->get();

    //     $yearsList = collect(\Carbon\CarbonPeriod::create(
    //         $dateFrom->copy()->startOfYear(),
    //         '1 year',
    //         $dateTo->copy()->startOfYear()
    //     ))
    //         ->map->year
    //         ->unique()
    //         ->values()
    //         ->toArray();   
            
    //     $reportCnaShare  = [
    //         'total' => 0,
    //     ];

    //     $reportAnnualDues = [
    //         'total' => 0,
    //     ];

    //     foreach ($yearsList as $year) {
    //         $reportCnaShare[$year] = 0;
    //         $totalCNAShareCollectionTarget[$year]['total'] = 0;
    //         $actualCNAShareCollectionTarget[$year]['total'] = 0;
    //         $balanceCollectible['cna_share'][$year]['total'] = 0;

    //         $reportAnnualDues[$year] = 0;
    //         $totalAnnualDueCollectionTarget[$year]['total'] = 0;
    //         $actualAnnualDueCollectionTarget[$year]['total'] = 0;
    //         $balanceCollectible['annual_due'][$year]['total'] = 0;

    //     }    

    //     $cnaShares = CnaShare::query()
    //         ->whereIn('year', $yearsList)
    //         ->whereIn('member_id', $members->pluck('id'))
    //         ->get()
    //         ->groupBy('member_id'); 

    //     $annualDues = AnnualContribution::query()
    //         ->whereIn('contribution_year', $yearsList)
    //         ->whereIn('member_id', $members->pluck('id'))
    //         ->get()
    //         ->groupBy('member_id');
            
    //     $memberData = [];
    
    //     foreach ($members as $member) {
    //         $memberCnaShares = $cnaShares->get($member->id, collect());
    //         $memberAnnualDues = $annualDues->get($member->id, collect());
    //         $annualDue = [];
    //         $cnaShare = [];

    //         foreach ($yearsList as $year) {
    //             $cnaShare[$year] = 0;
    //             $annualDue[$year] = 0;
    //         }

    //         foreach ($memberCnaShares as $share) {
    //             $amount = (float) $share->paid_amount;
    //             $cnaShare[$share->year] = $amount;

    //             $reportCnaShare[$share->year] = $share->due_amount;
    //             $reportCnaShare['total'] += $share->due_amount;

    //             $totalCNAShareCollectionTarget[$share->year]['total'] += $share->due_amount;
    //             $actualCNAShareCollectionTarget[$share->year]['total'] += $amount; 
                
    //         }

    //         foreach ($totalCNAShareCollectionTarget as $year => $targetData) {
    //             $target = $targetData['total'];
    //             $actual = $actualCNAShareCollectionTarget[$year]['total'] ?? 0;
                
    //             $balanceCollectible['cna_share'][$year]['total'] = (float) ($target - $actual);
    //         }
             
    //         foreach ($memberAnnualDues as $due) {
    //             $amount = (float) $due->amount_paid;
    //             $annualDue[$due->contribution_year] = $amount;
    //             $reportAnnualDues[$due->contribution_year] = $amount;
    //             $reportAnnualDues['total'] += $amount;
    //             $totalAnnualDueCollectionTarget[$due->contribution_year]['total'] += $amount;
                
    //             if ($due->status === ContributionStatus::Paid->value) {
    //                 $actualAnnualDueCollectionTarget[$due->contribution_year]['total'] += $amount;
    //             }
    //         }

    //         foreach ($totalAnnualDueCollectionTarget as $year => $targetData) {
                
    //             $target = (is_array($targetData) && isset($targetData['total'])) 
    //                       ? $targetData['total'] 
    //                       : (float) $targetData;
            
    //             $actualData = $actualAnnualDueCollectionTarget[$year] ?? 0;
    //             $actual = (is_array($actualData) && isset($actualData['total'])) 
    //                       ? $actualData['total'] 
    //                       : (float) $actualData;
                
    //             $balanceCollectible['annual_due'][$year]['total'] = (float) ($target - $actual);
    //         }
            
    //         $memberData[$member->id] = [
    //             'member_id' => $member->id,
    
    //             'name' => $member->user->name ?? '',
    
    //             /*
    //              * Monthly contribution values.
    //              */
    //             'months' => $months,
    
    //             /*
    //              * Y-axis / row total.
    //              */
    //             'total_contributed' => array_sum([...$annualDue, ...$cnaShare]),
    
    //             'cna_share' => $cnaShare,

    //             'annual_due' => $annualDue,
                
    //         ];

    //     }

    //     $headerMonthsAmount['total'] = array_sum([...array_diff_key($reportAnnualDues, ['total' => '']), ...array_diff_key($reportCnaShare, ['total' => ''])]);
    //     $totalMonthlyCollectionTarget['total'] = array_sum(array_column($totalCNAShareCollectionTarget, 'total')) + array_sum(array_column($totalAnnualDueCollectionTarget, 'total'));
    //     $actualMonthlyCollection['total'] = array_sum(array_column($actualCNAShareCollectionTarget, 'total')) + array_sum(array_column($actualAnnualDueCollectionTarget, 'total'));
    //     /*
    //      * ---------------------------------------------------------
    //      * 3. Get claims within the report period.
    //      * ---------------------------------------------------------
    //      *
    //      * claims.created_at determines the month.
    //      */
    
    //     $claims = Claim::query()
    //         ->with([
    //             'claimType',
    
    //             'contributionBatches.contributionDetails' => function ($query) use ($campusId) {
    
    //                 $query->whereHas('member', function ($query) use ($campusId) {
    //                     $query->where('campus_id', $campusId);
    //                 });
    
    //             },
    //         ])
    //         ->whereBetween('created_at', [
    //             $dateFrom,
    //             $dateTo,
    //         ])
    //         ->get();
    
    
    //     /*
    //      * ---------------------------------------------------------
    //      * 4. Process claims.
    //      * ---------------------------------------------------------
    //      */
    
    //     foreach ($claims as $claim) {
    
    //         $monthKey = strtolower(
    //             Carbon::parse($claim->created_at)->format('F Y')
    //         );
    
    //         if (!isset($months[$monthKey])) {
    //             continue;
    //         }
    
    
    //         /*
    //          * -----------------------------------------------------
    //          * A. HEADER MONTH AMOUNT
    //          * -----------------------------------------------------
    //          *
    //          * Total required amount of all claims created during
    //          * this month.
    //          *
    //          * Example:
    //          *
    //          * Burial      = 5,000
    //          * Burial      = 5,000
    //          * Medical     = 3,000
    //          * Educational = 2,000
    //          *
    //          * January = 15,000
    //          *
    //          * This is NOT based on the number of members.
    //          */
    
    //         if ($claim->claimType) {
    
    //             $claimAmount = (float) $claim->claimType->required_amount;
    
    //             $headerMonthsAmount[$monthKey] += $claimAmount;
    
    //             $headerMonthsAmount['total'] += $claimAmount;
    //         }
    
    
    //         /*
    //          * -----------------------------------------------------
    //          * B. CONTRIBUTION BATCHES
    //          * -----------------------------------------------------
    //          */
    
    //         foreach ($claim->contributionBatches as $batch) {
    
    //             $amountPerMember = (float) $batch->amount_per_member;
    
    //             /*
    //              * -------------------------------------------------
    //              * C. TOTAL MONTHLY COLLECTION TARGET
    //              * -------------------------------------------------
    //              *
    //              * Expected collection:
    //              *
    //              * amount_per_member × total_members
    //              */
    
    //             $memberCount = (int) $batch->total_members;
    
    //             $batchTarget = $amountPerMember * $memberCount;
    
    //             /*
    //              * X-axis / monthly target.
    //              */
    //             $totalMonthlyCollectionTarget[$monthKey] +=
    //                 $batchTarget;
    
    //             /*
    //              * Grand total target.
    //              */
    //             $totalMonthlyCollectionTarget['total'] +=
    //                 $batchTarget;
    
    
    //             /*
    //              * -------------------------------------------------
    //              * D. ACTUAL MEMBER CONTRIBUTIONS
    //              * -------------------------------------------------
    //              */
    
    //             foreach ($batch->contributionDetails as $detail) {
    
    //                 $memberId = $detail->member_id;
    
    //                 /*
    //                  * Ignore members outside the selected campus.
    //                  */
    //                 if (!isset($memberData[$memberId])) {
    //                     continue;
    //                 }
    
    //                 $actualAmount = (float) $detail->amount;
    
    
    //                 /*
    //                  * -------------------------------------------------
    //                  * X-AXIS / COLUMN TOTAL
    //                  * -------------------------------------------------
    //                  */
    
    //                 $actualMonthlyCollection[$monthKey] +=
    //                     $actualAmount;
    
    
    //                 /*
    //                  * Grand total actual collection.
    //                  */
    
    //                 $actualMonthlyCollection['total'] +=
    //                     $actualAmount;
    
    
    //                 /*
    //                  * -------------------------------------------------
    //                  * MEMBER MONTHLY CONTRIBUTION
    //                  * -------------------------------------------------
    //                  */
    
    //                 $memberData[$memberId]['months'][$monthKey] +=
    //                     $actualAmount;
    
    
    //                 /*
    //                  * -------------------------------------------------
    //                  * Y-AXIS / ROW TOTAL
    //                  * -------------------------------------------------
    //                  *
    //                  * This is NOT affected by collection adjustments
    //                  * because the adjustment is a campus/month-level
    //                  * collection adjustment, not a member contribution.
    //                  */
    
    //                 $memberData[$memberId]['total_contributed'] +=
    //                     $actualAmount;
    //             }
    //         }
    //     }
    
    
    //     /*
    //      * ---------------------------------------------------------
    //      * 5. GET COLLECTION ADJUSTMENTS FROM DATABASE
    //      * ---------------------------------------------------------
    //      *
    //      * An adjustment belongs to the campus and is applied only
    //      * to its adjustment_month.
    //      *
    //      * Example:
    //      *
    //      * June:
    //      *     Actual = 14,950
    //      *
    //      * September:
    //      *     Actual = 8,050
    //      *     Adjustment = -100
    //      *
    //      * September adjusted actual:
    //      *
    //      *     8,050 + (-100) = 7,950
    //      *
    //      * June remains 14,950.
    //      */
    
    //     $adjustments = CollectionsAdjustments::query()
    //         ->where('campus_id', $campusId)
    //         ->whereBetween('adjustment_month', [
    //             $dateFrom->copy()->startOfMonth(),
    //             $dateTo->copy()->startOfMonth(),
    //         ])
    //         ->get();
    
    
    //     foreach ($adjustments as $adjustment) {
    
    //         $monthKey = strtolower(
    //             Carbon::parse($adjustment->adjustment_month)->format('F Y')
    //         );
    
    //         /*
    //          * Ignore adjustments outside the initialized report
    //          * months.
    //          */
    //         if (!isset($months[$monthKey])) {
    //             continue;
    //         }
    
    //         $amount = (float) $adjustment->amount;
    
    
    //         /*
    //          * -----------------------------------------------------
    //          * MONTHLY ADJUSTMENT
    //          * -----------------------------------------------------
    //          */
    
    //         $collectionAdjustments[$monthKey] +=
    //             $amount;
    
    
    //         /*
    //          * -----------------------------------------------------
    //          * GRAND TOTAL ADJUSTMENT
    //          * -----------------------------------------------------
    //          */
    
    //         $collectionAdjustments['total'] +=
    //             $amount;

    //             /*
    //             * ---------------------------------------------------------
    //             * Detailed adjustment information used by the Blade view.
    //             * ---------------------------------------------------------
    //             */
    //            $collectionAdjustmentDetails[$monthKey][] = [
    //                'id' => $adjustment->id,
           
    //                'amount' => $amount,
           
    //                'type' => $adjustment->adjustment_type,
           
    //                'description' => $adjustment->description,
           
    //                'reference_month' => $adjustment->reference_month
    //                    ? Carbon::parse($adjustment->reference_month)
    //                        ->format('F Y')
    //                    : null,
    //            ];                
    //     }

    //     /*
    //      * ---------------------------------------------------------
    //      * 6. APPLY COLLECTION ADJUSTMENTS
    //      * ---------------------------------------------------------
    //      *
    //      * Adjusted actual =
    //      *
    //      * Raw actual + adjustment
    //      */
    
    //     foreach ($months as $monthKey => $unused) {
    
    //         $adjustedMonthlyCollection[$monthKey] =
    //             $actualMonthlyCollection[$monthKey]
    //             + $collectionAdjustments[$monthKey];
    //     }
    
    
    //     /*
    //      * Grand total adjusted collection.
    //      */
    
    //     $adjustedMonthlyCollection['total'] =
    //         $actualMonthlyCollection['total']
    //         + $collectionAdjustments['total'];
    
    
    //     /*
    //      * ---------------------------------------------------------
    //      * 7. CALCULATE COLLECTIBLE BALANCE
    //      * ---------------------------------------------------------
    //      *
    //      * Balance =
    //      *
    //      * Collection Target - Adjusted Actual Collection
    //      */
    
    //     foreach ($months as $monthKey => $unused) {
    
    //         $balanceCollectible[$monthKey] =
    //             $totalMonthlyCollectionTarget[$monthKey]
    //             - $adjustedMonthlyCollection[$monthKey];
    //     }
    
    
    //     /*
    //      * Grand total balance.
    //      */
    
    //     $balanceCollectible['total'] =
    //         $totalMonthlyCollectionTarget['total']
    //         - $adjustedMonthlyCollection['total'];
    
    
    //     /*
    //      * ---------------------------------------------------------
    //      * 8. RETURN REPORT-READY DATA
    //      * ---------------------------------------------------------
    //      */
    
    //     return [
    
    //         /*
    //          * Total claim required amount per month.
    //          */
    //         'header_months_amount' =>
    //             $headerMonthsAmount,
    
    
    //         /*
    //          * Month labels.
    //          */
    //         'header_month_label' =>
    //             $headerMonthLabel,
    
    
    //         /*
    //          * Expected collection target.
    //          */
    //         'total_monthly_collection_target' =>
    //             $totalMonthlyCollectionTarget,
    
    
    //         /*
    //          * Raw actual collection BEFORE adjustments.
    //          */
    //         'actual_monthly_collection' =>
    //             $actualMonthlyCollection,

    //         /**
    //          * Raw Expected CNA Share Collection
    //          */
    //         'total_cna_share_collection_target' =>
    //             $totalCNAShareCollectionTarget,

    //         /**
    //          * Raw actual CNS share collection before adjustment
    //          */
    //         'actual_cna_share_collection' => 
    //             $actualCNAShareCollectionTarget,
    
    //         /**
    //          * Raw Expected Annual Due Collection
    //          */
    //         'total_annual_due_collection_target' =>
    //             $totalAnnualDueCollectionTarget,
    
    //         /**
    //          * Raw actual annual due collection before adjustment. 
    //          */
    //         'actual_annual_due_collection' =>
    //             $actualAnnualDueCollectionTarget,

    //         /*
    //          * Collection adjustments from database.
    //          */
    //         'collection_adjustments' =>
    //             $collectionAdjustments,

    //         /**
    //          * Collection adjustemnts details
    //         */    
    //         'collection_adjustment_details' =>
    //         $collectionAdjustmentDetails,
    
    
    //         /*
    //          * Actual collection AFTER adjustments.
    //          */
    //         'adjusted_monthly_collection' =>
    //             $adjustedMonthlyCollection,
    
    
    //         /*
    //          * Target - adjusted actual.
    //          */
    //         'balance_collectible' =>
    //             $balanceCollectible,
    
    //         /**
    //          * CNA Shares
    //          */ 
    //         'cna_share' => $reportCnaShare,

    //         /**
    //          * Annual Dues  
    //          */ 
    //         'annual_due' => $reportAnnualDues,

    //         /*
    //          * Member rows.
    //          *
    //          * total_contributed = Y-axis / row total.
    //          */
    //         'members' =>
    //             array_values($memberData),
    //     ];
    // }    

    //----------------------------------------------------------------

//      public function getContributionPerMonth(Request $request, string $campusId): array
// {
//     $dateFrom = $request->date_from
//         ? Carbon::parse($request->date_from)->startOfMonth()
//         : null;

//     $dateTo = $request->date_to
//         ? Carbon::parse($request->date_to)->endOfMonth()
//         : null;

//     if (!$dateFrom || !$dateTo) {
//         return [
//             'header_months_amount' => [],
//             'total_monthly_collection_target' => [],
//             'actual_monthly_collection' => [],
//             'balance_collectible' => [],
//             'members' => [],
//         ];
//     }

//     /*
//      * ---------------------------------------------------------
//      * 1. Initialize report months and totals.
//      *
//      * "total" represents the grand total across all months.
//      * ---------------------------------------------------------
//      */
//     $headerMonthsAmount = [
//         'total' => 0,
//     ];

//     $headerMonthLabel = [];

//     $totalMonthlyCollectionTarget = [
//         'total' => 0,
//     ];

//     $actualMonthlyCollection = [
//         'total' => 0,
//     ];

//     $balanceCollectible = [
//         'total' => 0,
//     ];

//     $months = [];

//     $currentMonth = $dateFrom->copy();

//     while ($currentMonth->lte($dateTo)) {

//         $monthKey = strtolower($currentMonth->format('F'));

//         $months[$monthKey] = 0;

//         $headerMonthsAmount[$monthKey] = 0;
//         $totalMonthlyCollectionTarget[$monthKey] = 0;
//         $actualMonthlyCollection[$monthKey] = 0;
//         $balanceCollectible[$monthKey] = 0;

//         $currentMonth->addMonth();
//         array_push($headerMonthLabel, $monthKey);
//     }

//     /*
//      * ---------------------------------------------------------
//      * 2. Get all members belonging to the selected campus.
//      *
//      * Each member gets:
//      * - monthly contribution values
//      * - total_contributed = Y-axis / row total
//      * ---------------------------------------------------------
//      */
//     $members = Member::query()
//         ->with('user')
//         ->where('campus_id', $campusId)
//         ->get();

//     $memberData = [];

//     foreach ($members as $member) {

//         $memberData[$member->id] = [
//             'member_id' => $member->id,
//             'name' => $member->user->name ?? '',
//             'months' => $months,

//             // Y-axis / row total
//             'total_contributed' => 0,

//             // Placeholder for future CMA share calculations
//             'cna_share' => 0,
//         ];
//     }

//     /*
//      * ---------------------------------------------------------
//      * 3. Get claims within the report period.
//      *
//      * claims.created_at determines which month the claim
//      * belongs to.
//      * ---------------------------------------------------------
//      */
//     $claims = Claim::query()
//         ->with([
//             'claimType',

//             'contributionBatches.contributionDetails' => function ($query) use ($campusId) {
//                 $query->whereHas('member', function ($query) use ($campusId) {
//                     $query->where('campus_id', $campusId);
//                 });
//             },
//         ])
//         ->whereBetween('created_at', [
//             $dateFrom,
//             $dateTo,
//         ])
//         ->get();

//     /*
//      * ---------------------------------------------------------
//      * 4. Process claims.
//      * ---------------------------------------------------------
//      */
//     foreach ($claims as $claim) {

//         $monthKey = strtolower(
//             Carbon::parse($claim->created_at)->format('F')
//         );

//         if (!isset($months[$monthKey])) {
//             continue;
//         }

//         /*
//          * -----------------------------------------------------
//          * A. HEADER MONTH AMOUNT
//          *
//          * Total required amount of all claims created during
//          * this month.
//          *
//          * Example:
//          *
//          * Burial       = 5,000
//          * Burial       = 5,000
//          * Medical      = 3,000
//          * Educational  = 2,000
//          *
//          * January = 15,000
//          *
//          * This is NOT based on the number of members.
//          * -----------------------------------------------------
//          */
//         if ($claim->claimType) {

//             $claimAmount = (float) $claim->claimType->required_amount;

//             $headerMonthsAmount[$monthKey] += $claimAmount;

//             // Grand total
//             $headerMonthsAmount['total'] += $claimAmount;
//         }

//         /*
//          * -----------------------------------------------------
//          * B. CONTRIBUTION BATCHES
//          * -----------------------------------------------------
//          */
//         foreach ($claim->contributionBatches as $batch) {

//             $amountPerMember = (float) $batch->amount_per_member;

//             /*
//              * -------------------------------------------------
//              * C. TOTAL MONTHLY COLLECTION TARGET
//              *
//              * Expected collection for this batch:
//              *
//              * amount_per_member × total_members
//              *
//              * total_members is the number of members included
//              * in the contribution batch.
//              * -------------------------------------------------
//              */
//             $memberCount = (int) $batch->total_members;

//             $batchTarget = $amountPerMember * $memberCount;

//             // X-axis monthly target
//             $totalMonthlyCollectionTarget[$monthKey] += $batchTarget;

//             // Grand total target
//             $totalMonthlyCollectionTarget['total'] += $batchTarget;

//             /*
//              * -------------------------------------------------
//              * D. ACTUAL MEMBER CONTRIBUTIONS
//              *
//              * These values produce:
//              *
//              * 1. Monthly X-axis / column totals
//              * 2. Member Y-axis / row totals
//              * -------------------------------------------------
//              */
//             foreach ($batch->contributionDetails as $detail) {

//                 $memberId = $detail->member_id;

//                 /*
//                  * Ignore contribution details belonging to
//                  * members outside the selected campus.
//                  */
//                 if (!isset($memberData[$memberId])) {
//                     continue;
//                 }

//                 $actualAmount = (float) $detail->amount;

//                 /*
//                  * -------------------------------------------------
//                  * X-AXIS / COLUMN TOTAL
//                  *
//                  * Add actual contribution to the month's total.
//                  * -------------------------------------------------
//                  */
//                 $actualMonthlyCollection[$monthKey] += $actualAmount;

//                 /*
//                  * Grand total of all actual collections.
//                  */
//                 $actualMonthlyCollection['total'] += $actualAmount;

//                 /*
//                  * -------------------------------------------------
//                  * MEMBER MONTHLY CONTRIBUTION
//                  * -------------------------------------------------
//                  */
//                 $memberData[$memberId]['months'][$monthKey] +=
//                     $actualAmount;

//                 /*
//                  * -------------------------------------------------
//                  * Y-AXIS / ROW TOTAL
//                  *
//                  * Total contribution of this member across
//                  * all months.
//                  * -------------------------------------------------
//                  */
//                 $memberData[$memberId]['total_contributed'] +=
//                     $actualAmount;
//             }
//         }
//     }

//     /*
//      * ---------------------------------------------------------
//      * 5. Calculate collectible balance.
//      *
//      * Balance = Expected Collection - Actual Collection
//      *
//      * This is calculated for every month and for the grand
//      * total.
//      * ---------------------------------------------------------
//      */
//     foreach ($months as $monthKey => $unused) {

//         $balanceCollectible[$monthKey] =
//             $totalMonthlyCollectionTarget[$monthKey]
//             - $actualMonthlyCollection[$monthKey];
//     }

//     /*
//      * Grand total balance.
//      */
//     $balanceCollectible['total'] =
//         $totalMonthlyCollectionTarget['total']
//         - $actualMonthlyCollection['total'];

//     /*
//      * ---------------------------------------------------------
//      * 6. Return report-ready data.
//      * ---------------------------------------------------------
//      */
//     return [
//         /*
//          * Claim amount per month + grand total.
//          */
//         'header_months_amount' =>
//             $headerMonthsAmount,

//         'header_month_label' => 
//             $headerMonthLabel,    
        
//         /*
//          * Expected collection per month + grand total.
//          */
//         'total_monthly_collection_target' =>
//             $totalMonthlyCollectionTarget,

//         /*
//          * X-axis / column totals.
//          *
//          * Example:
//          *
//          * january => 1500
//          * february => 2200
//          * march => 1600
//          * total => 5300
//          */
//         'actual_monthly_collection' =>
//             $actualMonthlyCollection,

//         /*
//          * Expected - Actual.
//          */
//         'balance_collectible' =>
//             $balanceCollectible,

//         /*
//          * Member rows.
//          *
//          * Each member contains:
//          * - monthly contribution
//          * - total_contributed = Y-axis / row total
//          */
//         'members' =>
//             array_values($memberData),
//     ];
// }

/**
 * Persist the collection adjustment records into the database engine.
 *
 * @param array $data The validated payload values array map context.
 * @return CollectionsAdjustments
 */
public function contributionAdjustment(array $data)
{
    return CollectionsAdjustments::create([
        'campus_id'        => $data['campus_id'],
        'adjustment_month' => Carbon::createFromFormat('Y-m', $data['adjustment_month'])->startOfMonth(),
        'reference_month'  => !empty($data['reference_month'])
            ? Carbon::createFromFormat('Y-m', $data['reference_month'])->startOfMonth()
            : null,
        'adjustment_type'  => $data['adjustment_type'], // Pass validation clean data
        'amount'           => $data['amount'], 
        'description'      => $data['description'] ?? null,
        'created_by'       => Auth::id(),
    ]);
}

    /**
     * Persist the collection of CNA Share into the database engine.
     * 
     * @param array $data The contribution year for which the CNA Share is being set up.
     * @return void
     */
    public function setupCNAShare(array $data)
    {
        $user = Auth::user();

        // 1. Determine cross-campus access authorization
        $hasFullAccess = $user->can('view all campus data') || $user->hasAnyRole(['President', 'Treasurer']);

        // 2. Security Guard: Prevent restricted users from injecting other campus IDs
        if (!$hasFullAccess) {
            $userCampusId = $user->getCachedCampusId();

            if (!$userCampusId || $userCampusId != $data['campus_filter']) {
                abort(403, 'Unauthorized action. You can only set up shares for your assigned campus.');
            }
        }

        // 3. Prepare Member Query Builder
        $query = Member::query();

        if ($hasFullAccess) {
            $query->withoutGlobalScope(CampusMemberScope::class);
        }

        // 4. Fetch target campus members (selecting only the ID to minimize memory usage)
        $members = $query->whereHas('campus', function ($q) use ($data) {
            $q->where('id', $data['campus_filter']);
        })->select('id')->get();

        // If no members are found, exit early to avoid empty database calls
        if ($members->isEmpty()) {
            return;
        }

        $year = $data['contribution_year'] ?? now()->year;
        $amount = $data['contribution_amount'] ?? 100.00; // Default to 100.00 if not provided
        $timestamp = now();
        $insertData = [];

        // 5. Structure payload array for the bulk insert
        foreach ($members as $member) {
            $shareCode = 'CNAS-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            $insertData[] = [
                'id'               => (string) Str::orderedUuid(),
                'reference_number' => $shareCode,
                'member_id'   => $member->id,
                'year'        => $year,
                'due_amount'  => $amount,
                'paid_amount' => 0.00,
                'status'     =>  ContributionStatus::Unpaid->value,
                'created_by'  => $user->user_id,
                'created_at'  => $timestamp, // Bulk insert requires manual timestamps
                'updated_at'  => $timestamp,
            ];
        }

        // 6. Database Transaction Execution
        DB::beginTransaction();

        try {
            // Perform mass chunked bulk insert (chunks of 500 records to prevent SQL binding limits)
            foreach (array_chunk($insertData, 500) as $chunk) {
                CNAShare::insert($chunk);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            // Log the exact database error safely for debugging
            \Log::error('Failed to setup CNA Shares: ' . $e->getMessage(), [
                'campus_id' => $data['campus_filter'],
                'year'      => $year,
                'user_id'   => $user->id
            ]);

            // Rethrow or return a custom failure message depending on your architecture
            throw new Exception('An error occurred while establishing CNA Shares. Please try again.');
        }
    }

}
