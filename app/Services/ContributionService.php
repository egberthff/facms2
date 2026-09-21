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
                default  => throw new InvalidArgumentException("Invalid contribution tag: {$tag}"),
            };

            if ($tag == 'cna_share') {
                 $detail->update([
                    'paid_amount'  => $detail->due_amount,
                    'status'  => ContributionStatus::Paid->value,
                    'paid_at' => now(),
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

    public function getContributionPerMonth(Request $request, ?string $campusId = null): array
    {
        $dateFrom = $request->date_from
            ? Carbon::parse($request->date_from)->startOfMonth()
            : null;
    
        $dateTo = $request->date_to
            ? Carbon::parse($request->date_to)->endOfMonth()
            : null;
    
        if (!$dateFrom || !$dateTo) {
            return [
                'header_months_amount' => [],
                'header_month_label' => [],
                'total_monthly_collection_target' => [],
                'actual_monthly_collection' => [],
                'collection_adjustments' => [],
                'adjusted_monthly_collection' => [],
                'balance_collectible' => [],
                'members' => [],
            ];
        }
    
        /*
         * ---------------------------------------------------------
         * 1. Initialize report months and totals.
         * ---------------------------------------------------------
         *
         * "total" represents the grand total across all months.
         */
    
        $headerMonthsAmount = [
            'total' => 0,
        ];
    
        $headerMonthLabel = [];

        // $totalAnnualDueCollectionTarget = [
        //     'total' => 0,
        // ];

        // $actualAnnualDueCollectionTarget = [
        //     'total' => 0,
        // ];
    
        $totalMonthlyCollectionTarget = [
            'total' => 0,
        ];
    
        /*
         * Actual collection BEFORE adjustments.
         */
        $actualMonthlyCollection = [
            'total' => 0,
        ];
    
        /*
         * Collection adjustments retrieved from the database.
         *
         * Positive amount = increase collection
         * Negative amount = decrease collection
         */
        $collectionAdjustments = [
            'total' => 0,
        ];

        $collectionAdjustmentDetails = [];
    
        /*
         * Actual collection AFTER adjustments.
         */
        $adjustedMonthlyCollection = [
            'total' => 0,
        ];
    
        $balanceCollectible = [
            'cna_share' => [],
            'annual_due' => [],
            'total' => 0,
        ];
    
        $currentMonth = $dateFrom->copy();

        $months = [];

        $cnaShare = [
            'total' => 0,
        ];
        $anualDues = [
            'total' => 0,
        ];
    
        while ($currentMonth->lte($dateTo)) {
    
            $monthKey = strtolower(
                $currentMonth->format('F Y')
            );
    
            $months[$monthKey] = 0;
    
            $headerMonthsAmount[$monthKey] = 0;
    
            $totalMonthlyCollectionTarget[$monthKey] = 0;
    
            $actualMonthlyCollection[$monthKey] = 0;
    
            $collectionAdjustments[$monthKey] = 0;
    
            $adjustedMonthlyCollection[$monthKey] = 0;
    
            $balanceCollectible[$monthKey] = 0;

            /*
             * Detailed adjustment records for this month.
             */
            $collectionAdjustmentDetails[$monthKey] = [];
    
            $headerMonthLabel[] = $monthKey;
    
            $currentMonth->addMonth();
        }
    
        /*
         * ---------------------------------------------------------
         * 2. Get all members belonging to the selected campus.
         * ---------------------------------------------------------
         *
         * Each member gets:
         *
         * - monthly contribution values
         * - total_contributed = Y-axis / row total
         */

        $members = Member::query()
            ->with('user')
            // Only apply the manual where clause if a specific campus filter was explicitly selected
            ->when($campusId, function ($query, $campusId) {
                // Safe bypass to avoid duplicate SQL where filters if the scope already handled it
                $query->withoutGlobalScope(CampusMemberScope::class)
                      ->where('campus_id', $campusId);
            })
            ->get();

        $yearsList = collect(\Carbon\CarbonPeriod::create(
            $dateFrom->copy()->startOfYear(),
            '1 year',
            $dateTo->copy()->startOfYear()
        ))
            ->map->year
            ->unique()
            ->values()
            ->toArray();   
            
        $reportCnaShare  = [
            'total' => 0,
        ];

        $reportAnnualDues = [
            'total' => 0,
        ];

        foreach ($yearsList as $year) {
            $reportCnaShare[$year] = 0;
            $totalCNAShareCollectionTarget[$year]['total'] = 0;
            $actualCNAShareCollectionTarget[$year]['total'] = 0;
            $balanceCollectible['cna_share'][$year]['total'] = 0;

            $reportAnnualDues[$year] = 0;
            $totalAnnualDueCollectionTarget[$year]['total'] = 0;
            $actualAnnualDueCollectionTarget[$year]['total'] = 0;
            $balanceCollectible['annual_due'][$year]['total'] = 0;

        }    

        $cnaShares = CnaShare::query()
            ->whereIn('year', $yearsList)
            ->whereIn('member_id', $members->pluck('id'))
            ->get()
            ->groupBy('member_id'); 

        $annualDues = AnnualContribution::query()
            ->whereIn('contribution_year', $yearsList)
            ->whereIn('member_id', $members->pluck('id'))
            ->get()
            ->groupBy('member_id');
            
        $memberData = [];
    
        foreach ($members as $member) {
            $memberCnaShares = $cnaShares->get($member->id, collect());
            $memberAnnualDues = $annualDues->get($member->id, collect());
            $annualDue = [];
            $cnaShare = [];

            foreach ($yearsList as $year) {
                $cnaShare[$year] = 0;
                $annualDue[$year] = 0;
            }

            foreach ($memberCnaShares as $share) {
                $amount = (float) $share->paid_amount;
                $cnaShare[$share->year] = $amount;

                $reportCnaShare[$share->year] = $share->due_amount;
                $reportCnaShare['total'] += $share->due_amount;

                $totalCNAShareCollectionTarget[$share->year]['total'] += $share->due_amount;
                $actualCNAShareCollectionTarget[$share->year]['total'] += $amount; 
                
            }

            foreach ($totalCNAShareCollectionTarget as $year => $targetData) {
                $target = $targetData['total'];
                $actual = $actualCNAShareCollectionTarget[$year]['total'] ?? 0;
                
                $balanceCollectible['cna_share'][$year]['total'] = (float) ($target - $actual);
            }
             
            foreach ($memberAnnualDues as $due) {
                $amount = (float) $due->amount_paid;
                $annualDue[$due->contribution_year] = $amount;
                $reportAnnualDues[$due->contribution_year] = $amount;
                $reportAnnualDues['total'] += $amount;
                $totalAnnualDueCollectionTarget[$due->contribution_year]['total'] += $amount;
                
                if ($due->status === ContributionStatus::Paid->value) {
                    $actualAnnualDueCollectionTarget[$due->contribution_year]['total'] += $amount;
                }
            }

            foreach ($totalAnnualDueCollectionTarget as $year => $targetData) {
                
                $target = (is_array($targetData) && isset($targetData['total'])) 
                          ? $targetData['total'] 
                          : (float) $targetData;
            
                $actualData = $actualAnnualDueCollectionTarget[$year] ?? 0;
                $actual = (is_array($actualData) && isset($actualData['total'])) 
                          ? $actualData['total'] 
                          : (float) $actualData;
                
                $balanceCollectible['annual_due'][$year]['total'] = (float) ($target - $actual);
            }
            
            $memberData[$member->id] = [
                'member_id' => $member->id,
    
                'name' => $member->user->name ?? '',
    
                /*
                 * Monthly contribution values.
                 */
                'months' => $months,
    
                /*
                 * Y-axis / row total.
                 */
                'total_contributed' => array_sum([...$annualDue, ...$cnaShare]),
    
                'cna_share' => $cnaShare,

                'annual_due' => $annualDue,
                
            ];

        }

        $headerMonthsAmount['total'] = array_sum([...array_diff_key($reportAnnualDues, ['total' => '']), ...array_diff_key($reportCnaShare, ['total' => ''])]);
        $totalMonthlyCollectionTarget['total'] = array_sum(array_column($totalCNAShareCollectionTarget, 'total')) + array_sum(array_column($totalAnnualDueCollectionTarget, 'total'));
        $actualMonthlyCollection['total'] = array_sum(array_column($actualCNAShareCollectionTarget, 'total')) + array_sum(array_column($actualAnnualDueCollectionTarget, 'total'));
        /*
         * ---------------------------------------------------------
         * 3. Get claims within the report period.
         * ---------------------------------------------------------
         *
         * claims.created_at determines the month.
         */
    
        $claims = Claim::query()
            ->with([
                'claimType',
    
                'contributionBatches.contributionDetails' => function ($query) use ($campusId) {
    
                    $query->whereHas('member', function ($query) use ($campusId) {
                        $query->where('campus_id', $campusId);
                    });
    
                },
            ])
            ->whereBetween('created_at', [
                $dateFrom,
                $dateTo,
            ])
            ->get();
    
    
        /*
         * ---------------------------------------------------------
         * 4. Process claims.
         * ---------------------------------------------------------
         */
    
        foreach ($claims as $claim) {
    
            $monthKey = strtolower(
                Carbon::parse($claim->created_at)->format('F Y')
            );
    
            if (!isset($months[$monthKey])) {
                continue;
            }
    
    
            /*
             * -----------------------------------------------------
             * A. HEADER MONTH AMOUNT
             * -----------------------------------------------------
             *
             * Total required amount of all claims created during
             * this month.
             *
             * Example:
             *
             * Burial      = 5,000
             * Burial      = 5,000
             * Medical     = 3,000
             * Educational = 2,000
             *
             * January = 15,000
             *
             * This is NOT based on the number of members.
             */
    
            if ($claim->claimType) {
    
                $claimAmount = (float) $claim->claimType->required_amount;
    
                $headerMonthsAmount[$monthKey] += $claimAmount;
    
                $headerMonthsAmount['total'] += $claimAmount;
            }
    
    
            /*
             * -----------------------------------------------------
             * B. CONTRIBUTION BATCHES
             * -----------------------------------------------------
             */
    
            foreach ($claim->contributionBatches as $batch) {
    
                $amountPerMember = (float) $batch->amount_per_member;
    
                /*
                 * -------------------------------------------------
                 * C. TOTAL MONTHLY COLLECTION TARGET
                 * -------------------------------------------------
                 *
                 * Expected collection:
                 *
                 * amount_per_member × total_members
                 */
    
                $memberCount = (int) $batch->total_members;
    
                $batchTarget = $amountPerMember * $memberCount;
    
                /*
                 * X-axis / monthly target.
                 */
                $totalMonthlyCollectionTarget[$monthKey] +=
                    $batchTarget;
    
                /*
                 * Grand total target.
                 */
                $totalMonthlyCollectionTarget['total'] +=
                    $batchTarget;
    
    
                /*
                 * -------------------------------------------------
                 * D. ACTUAL MEMBER CONTRIBUTIONS
                 * -------------------------------------------------
                 */
    
                foreach ($batch->contributionDetails as $detail) {
    
                    $memberId = $detail->member_id;
    
                    /*
                     * Ignore members outside the selected campus.
                     */
                    if (!isset($memberData[$memberId])) {
                        continue;
                    }
    
                    $actualAmount = (float) $detail->amount;
    
    
                    /*
                     * -------------------------------------------------
                     * X-AXIS / COLUMN TOTAL
                     * -------------------------------------------------
                     */
    
                    $actualMonthlyCollection[$monthKey] +=
                        $actualAmount;
    
    
                    /*
                     * Grand total actual collection.
                     */
    
                    $actualMonthlyCollection['total'] +=
                        $actualAmount;
    
    
                    /*
                     * -------------------------------------------------
                     * MEMBER MONTHLY CONTRIBUTION
                     * -------------------------------------------------
                     */
    
                    $memberData[$memberId]['months'][$monthKey] +=
                        $actualAmount;
    
    
                    /*
                     * -------------------------------------------------
                     * Y-AXIS / ROW TOTAL
                     * -------------------------------------------------
                     *
                     * This is NOT affected by collection adjustments
                     * because the adjustment is a campus/month-level
                     * collection adjustment, not a member contribution.
                     */
    
                    $memberData[$memberId]['total_contributed'] +=
                        $actualAmount;
                }
            }
        }
    
    
        /*
         * ---------------------------------------------------------
         * 5. GET COLLECTION ADJUSTMENTS FROM DATABASE
         * ---------------------------------------------------------
         *
         * An adjustment belongs to the campus and is applied only
         * to its adjustment_month.
         *
         * Example:
         *
         * June:
         *     Actual = 14,950
         *
         * September:
         *     Actual = 8,050
         *     Adjustment = -100
         *
         * September adjusted actual:
         *
         *     8,050 + (-100) = 7,950
         *
         * June remains 14,950.
         */
    
        $adjustments = CollectionsAdjustments::query()
            ->where('campus_id', $campusId)
            ->whereBetween('adjustment_month', [
                $dateFrom->copy()->startOfMonth(),
                $dateTo->copy()->startOfMonth(),
            ])
            ->get();
    
    
        foreach ($adjustments as $adjustment) {
    
            $monthKey = strtolower(
                Carbon::parse($adjustment->adjustment_month)->format('F Y')
            );
    
            /*
             * Ignore adjustments outside the initialized report
             * months.
             */
            if (!isset($months[$monthKey])) {
                continue;
            }
    
            $amount = (float) $adjustment->amount;
    
    
            /*
             * -----------------------------------------------------
             * MONTHLY ADJUSTMENT
             * -----------------------------------------------------
             */
    
            $collectionAdjustments[$monthKey] +=
                $amount;
    
    
            /*
             * -----------------------------------------------------
             * GRAND TOTAL ADJUSTMENT
             * -----------------------------------------------------
             */
    
            $collectionAdjustments['total'] +=
                $amount;

                /*
                * ---------------------------------------------------------
                * Detailed adjustment information used by the Blade view.
                * ---------------------------------------------------------
                */
               $collectionAdjustmentDetails[$monthKey][] = [
                   'id' => $adjustment->id,
           
                   'amount' => $amount,
           
                   'type' => $adjustment->adjustment_type,
           
                   'description' => $adjustment->description,
           
                   'reference_month' => $adjustment->reference_month
                       ? Carbon::parse($adjustment->reference_month)
                           ->format('F Y')
                       : null,
               ];                
        }

        /*
         * ---------------------------------------------------------
         * 6. APPLY COLLECTION ADJUSTMENTS
         * ---------------------------------------------------------
         *
         * Adjusted actual =
         *
         * Raw actual + adjustment
         */
    
        foreach ($months as $monthKey => $unused) {
    
            $adjustedMonthlyCollection[$monthKey] =
                $actualMonthlyCollection[$monthKey]
                + $collectionAdjustments[$monthKey];
        }
    
    
        /*
         * Grand total adjusted collection.
         */
    
        $adjustedMonthlyCollection['total'] =
            $actualMonthlyCollection['total']
            + $collectionAdjustments['total'];
    
    
        /*
         * ---------------------------------------------------------
         * 7. CALCULATE COLLECTIBLE BALANCE
         * ---------------------------------------------------------
         *
         * Balance =
         *
         * Collection Target - Adjusted Actual Collection
         */
    
        foreach ($months as $monthKey => $unused) {
    
            $balanceCollectible[$monthKey] =
                $totalMonthlyCollectionTarget[$monthKey]
                - $adjustedMonthlyCollection[$monthKey];
        }
    
    
        /*
         * Grand total balance.
         */
    
        $balanceCollectible['total'] =
            $totalMonthlyCollectionTarget['total']
            - $adjustedMonthlyCollection['total'];
    
    
        /*
         * ---------------------------------------------------------
         * 8. RETURN REPORT-READY DATA
         * ---------------------------------------------------------
         */
    
        return [
    
            /*
             * Total claim required amount per month.
             */
            'header_months_amount' =>
                $headerMonthsAmount,
    
    
            /*
             * Month labels.
             */
            'header_month_label' =>
                $headerMonthLabel,
    
    
            /*
             * Expected collection target.
             */
            'total_monthly_collection_target' =>
                $totalMonthlyCollectionTarget,
    
    
            /*
             * Raw actual collection BEFORE adjustments.
             */
            'actual_monthly_collection' =>
                $actualMonthlyCollection,

            /**
             * Raw Expected CNA Share Collection
             */
            'total_cna_share_collection_target' =>
                $totalCNAShareCollectionTarget,

            /**
             * Raw actual CNS share collection before adjustment
             */
            'actual_cna_share_collection' => 
                $actualCNAShareCollectionTarget,
    
            /**
             * Raw Expected Annual Due Collection
             */
            'total_annual_due_collection_target' =>
                $totalAnnualDueCollectionTarget,
    
            /**
             * Raw actual annual due collection before adjustment. 
             */
            'actual_annual_due_collection' =>
                $actualAnnualDueCollectionTarget,

            /*
             * Collection adjustments from database.
             */
            'collection_adjustments' =>
                $collectionAdjustments,

            /**
             * Collection adjustemnts details
            */    
            'collection_adjustment_details' =>
            $collectionAdjustmentDetails,
    
    
            /*
             * Actual collection AFTER adjustments.
             */
            'adjusted_monthly_collection' =>
                $adjustedMonthlyCollection,
    
    
            /*
             * Target - adjusted actual.
             */
            'balance_collectible' =>
                $balanceCollectible,
    
            /**
             * CNA Shares
             */ 
            'cna_share' => $reportCnaShare,

            /**
             * Annual Dues  
             */ 
            'annual_due' => $reportAnnualDues,

            /*
             * Member rows.
             *
             * total_contributed = Y-axis / row total.
             */
            'members' =>
                array_values($memberData),
        ];
    }    

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
